<?php
class Response {
    const OUTPUT_CLI = 'CLI';
    const OUTPUT_HTML = 'HTML';
    const OUTPUT_JSON = 'JSON';

    private $status = 200;
    private $info_msg = null;
    private $global_errors = array();
    private $field_errors = array();
    private $deferred = false;

    private $output = self::OUTPUT_JSON;
    private $output_field = null;

    function set_output($output) {
        $this->output = $output;
    }

    function set_output_field($field_spec) {
        $this->output_field = $field_spec;
    }

    // Terminal methods.
    function ok($msg, $data = null) {
        $this->info_msg = "INFO: $msg";
        $this->data = $data;
        $this->_output();
    }

    function created($msg, $data) {
        $this->info_msg = "INFO: $msg";
        $this->data = $data;
        $this->try_set_status(201);
        $this->_output();
    }

    function not_implemented() {
        global $req_path;
        $this->add_global_error("Item '$req_path' not implemented.", 501);
        $this->_output();
    }

    function server_error($msg) {
        $this->add_global_error("$msg", 500);
        $this->_output();
    }

    function invalid_request($msg = null) {
        if ($msg == null) {
            $msg = "Invalid request '$req_path'.";
        }
        $this->add_global_error($msg, 400);
        $this->_output();
    }

    function item_not_found($msg = null) {
        global $req_path;
        if (empty($msg)) {
            $msg = "Item '$req_path' not found.";
        }
        $this->add_global_error($msg, 404);
        $this->_output();
    }

    function check_request_ok() {
        // Note, there may be 'WARNING' in the 'errors' with a '2xx'
        // status.
        return $this->status == 200 || $this->status == 201;
    }

    function add_global_warning($message) {
        array_push($this->global_errors, "WARNING: $message");
    }

    function add_global_error($message, $try_status) {
        array_push($this->global_errors, "ERROR: $message");
        $this->try_set_status($try_status);
    }

    function get_global_errors() {
        return $this->global_errors;
    }

    function check_required_parameter($field_name) {
        global $req_parameters;
        if (empty($req_parameters[$field_name])) {
            $this->add_field_error($field_name, 
                                   "Required parameter '$field_name' missing.", 
                                   400);
        }
    }

    function check_required_field($field_name) {
        if (empty($this->_decompose($field_name))) {
            $this->add_field_error($field_name, 
                                   "Required field '$field_name' missing.", 
                                   400);
        }
    }
        
    function add_field_error($field_name, $msg, $status=200) {
        $field_messages = $this->field_errors[$field_name];
        if ($field_messages == null) {
            $field_messages = array();
        }
        array_push($field_messages, $msg);
        $this->field_errors[$field_name] = $field_messages;
        $this->try_set_status($status);
    }

    function get_field_errors() {
        return $this->field_errors;
    }

    function get_status() {
        return $this->status;
    }

    function get_info_msg() {
        return $this->info_msg;
    }

    function set_data($data) {
        $this->data = $data;
    }

    function get_data() {
        return $this->data;
    }

    function defer() {
        $this->deferred = true;
    }

    function finish() {
        $this->deferred = false;
        $this->_output();
    }

    function is_deferred() {
        return $this->deferred;
    }

    function try_set_status($try_status) {
        $status_series = $this->status / 100;
        $try_status_series = $try_status / 100;
        // If the 'try_status' is either 'more sever' or of the same
        // severity, but more specifc, we update the status to the new
        // try status. Otherwise, we leave the status as is.
        if ($try_status_series > $status_series
            || (($this->status % 100) == 0
                && ($try_status % 100) != 0)) {
            $this->status = $try_status;
        }
    }

    function get_bash_status() {
        switch ($this->status) {
        case 200:
        case 201:
            return 0;
        case 400:
            return 40;
        case 404:
            return 44;
        case 500:
            return 50;
        case 501:
            return 51;
        default:
            return 127;
        }
    }

    function _output() {
        if ($this->deferred) {
            return;
        }

        if ($this->output == self::OUTPUT_CLI) {
            if (!empty($this->get_info_msg())) {
                echo $this->get_info_msg()."\n";
            }
            if (!$this->check_request_ok()) {
                error_log("Failed with status: ".$this->get_status());
            }
            // There may be warnings even if status is OK.
            foreach ($this->get_global_errors() as $error_msg) {
                error_log("$error_msg");
            }
            foreach ($this->get_field_errors() as $field_name => $error_msgs) {
                foreach ($error_msgs as $error_msg) {
                    error_log("$field_name: $error_msg");
                }
            }
            // Data is only included if everything OK.
            if ($this->check_request_ok()) {
                // Most data is JSON, but there are some cases where we get raw text.
                $output_data = array();
                function trunc_walk($src, &$target) {
                    foreach ($src as $key => $value) {
                        echo "src: $src\n";
                        echo "key: $key\n";
                        echo "value: $value\n";
                        if (is_array($value)) {
                            $target[$key] = array();
                            trunc_walk($value, $target[$key]);
                        }
                        elseif (is_string($value) && strlen($value) > 64) {
                            $target[$key] = substr($value, 0, 64).'...';
                        }
                        else {
                            $target[$key] = $value;
                        }
                    }
                }
                trunc_walk($this->get_data(), $output_data);
                $json_string = json_encode($output_data, JSON_PRETTY_PRINT)."\n";
                echo ($json_string != null ? $json_string : $this->get_data());
            }
        }
        else { // Both HTML and JSON outputs are HTTP so we handle the
               // headers universally.
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
            $this->_output_http_status();

            if ($this->output == self::OUTPUT_HTML) {
                header("Content-Type: text/html");
                $template_path = apache_getenv('TEMPLATE_PATH');
                if (!empty($template_path)) {
                    require("$template_path/page_open.php");
                    $breadcrumb = $this->get_data['breadcrumb'];

                    $this->_output_breadcrumb();
                    $this->_output_page_header();
                }
                if (!empty($this->output_field)) {
                    
                    echo $this->_decompose($this->output_field, $this->get_data());

                }
                else {
                    echo "TODO!";
                }
                if (!empty($template_path)) {
                    $this->_output_breadcrumb();
                    require("$template_path/page_close.php");
                }
            }
            else { // JSON should be only option, and in any case
                   // we'll takes default
                header("Content-Type: application/json");
            }
        }

        exit($this->get_bash_status());
    }

    function _output_http_status() {
        $pre = 'HTTP/1.0 ';

        switch ($this->get_status()) {
        case 200:
            header($pre.'200 OK');
            break;
        case 201:
            header($pre.'201 Created');
            break;
        case 400:
            header($pre.'400 Bad Request');
            break;
        case 404:
            header($pre.'404 Not Found');
            break;
        case 500:
            header($pre.'500 Server Error');
            break;
        default:
            header($pre."501 Status '".$this->get_status()."' not implemented");
            break;
        };
    }

    function _decompose($field_name, $data = null) {
        if ($data == null) {
            global $req_data;
            $data = $req_data;
        }
        $bits = explode('.', $field_name);
        foreach ($bits as $bit) {
            $data = $data[$bit];
            if (empty($data)) {
                return null;
            }
        }
        return $data;
    }

    function _output_breadcrumb() {
        $breadcrumb = $this->get_data()['breadcrumb'];
        if (empty($breadcrumb)) {
            echo "WHAT?";
            return;
        }

        echo "<nav>\n";
        echo "  <ol class=\"breadcrumb\">\n";
        foreach ($breadcrumb as $crumb) {
            echo "    <li><a href=\"/documentation/{$crumb['path']}\">{$crumb['name']}</a></li>\n";
        }
        $title = $this->get_data()['title'];
        echo "    <li class=\"active\">{$title}</li>\n";
        echo "  </ol>\n";
        echo "</nav>\n";
    }

    function _output_page_header() {
        $title = $this->get_data()['title'];
        if (!empty($title)) {
            echo "<div class=\"page-header\">{$title}</div>\n";
        }
    }
}

$response = new Response();
?>