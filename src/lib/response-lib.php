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

    private $quiet = false;

    private $output = self::OUTPUT_JSON;
    private $output_field = null;

    function set_output($output) {
        $this->output = $output;
    }

    function set_output_field($field_spec) {
        $this->output_field = $field_spec;
    }

    function set_quiet($quiet) {
        $this->quiet = $quiet;
    }

    // final methods.
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

    function not_implemented($msg = null) {
        if ($msg == null) {
            global $req_path;
            global $req_action;

            $msg = "'$req_action $req_path' not implemented.";
        }
        $this->add_global_error($msg, 501);
        $this->_output();
    }

    function server_error($msg) {
        $this->add_global_error("$msg", 500);
        $this->_output();
    }

    function invalid_request($msg = null) {
        if ($msg == null) {
            global $req_action;
            global $req_path;
            $msg = "Invalid request '$req_action $req_path'.";
        }
        $this->add_global_error($msg, 400);
        $this->_output();
    }

    function unauthorized_request($msg = null) {
        if ($msg == null) {
            $msg = "Unauthorized request '$req_path'.";
        }
        $this->add_global_error($msg, 401);
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

    # TODO: This and 'check_required_fields(...) are near copies; except the
    # other supports decomposition. Delete this method, I think.
    function check_required_parameter($field_name, $params = null) {
        if ($params == null) {
            global $req_parameters;
            $params = $req_parameters;
        }
        if (empty($params[$field_name])) {
            $this->add_field_error($field_name, 
                                   "Missing required parameter '$field_name'.", 
                                   400);
        }
    }

    function check_extraneous_fields($required_count, $optional_fields = array(), $params = null) {
    /**
     * <div class="function">
     *   <div class="p">
     *     Checks that there are no 'unknown' parameters in the data. By
     *     default, processes using the request parameters
     *     (<code>$req_parameters</code>) as the data to test against. Assumes
     *     that the required fields have been checked. The structure is
     *     searched for each optional field, which gives us an expected count
     *     of parameters. If the number of parameters in the structure is
     *     greater than the expected count, then the structure contains
     *     unknown parameters and is rejected.
     *   </div>
     * </div>
     */
        if ($this->check_request_ok()) {
            global $req_parameters;

            if ($params == null) {
                $params = $req_parameters;
            }
            
            $expected_count = $required_count;
            foreach($optional_fields as $optional_field) {
                if (array_key_exists($optional_field, $params)) {
                    $expected_count += 1;
                }
            }
            
            $actual_count = count($params);
            if ($actual_count != $expected_count) {
                $msg = "Unexpected field in request "
                     .($req_parameters == $params ? "request " : "")
                     ."data.";
                $response->add_global_error($msg, 400);
            }
        }
    }

    function check_required_field($field_name, $params = null) {
        if ($params == null) {
            global $req_parameters;
            $params = $req_parameters;
        }
        if (empty($this->_decompose($field_name, $params))) {
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
            if (!empty($this->get_info_msg()) && !$this->quiet) {
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
            if ($this->check_request_ok() && !empty($this->get_data())) {
                // Most data is JSON, but there are some cases where we get raw text.
                $output_data = array();
                function trunc_walk($src, &$target) {
                    $trim_point = 96;
                    foreach ($src as $key => $value) {
                        if (is_array($value)) {
                            $target[$key] = array();
                            trunc_walk($value, $target[$key]);
                        }
                        # The '+3' is because we're going to add '...' AFTER
                        # the cuttoff point. No point in truncating if the
                        # substitute string is just as long.
                        elseif (is_string($value) && strlen($value) > $trim_point + 3) {
                            $target[$key] = substr($value, 0, $trim_point).'...';
                        }
                        else {
                            $target[$key] = $value;
                        }
                    }
                }
                trunc_walk($this->get_data(), $output_data);
                $json_string = json_encode($output_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
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
                $template_prefix = '/home/vagrant/playground/DogFoodSoftware/conveyor-core/src/ui/documentation-default-';
                # TODO: in future, read config file or something
                require("{$template_prefix}page-open.php");

                $breadcrumb = $this->get_data['document']['breadcrumb'];
                $this->_output_breadcrumb();
                $this->_output_page_header();

                if (!empty($this->output_field)) {
                    
                    echo $this->_decompose($this->output_field, $this->get_data());

                }
                else {
                    echo "TODO!";
                }

                $this->_output_breadcrumb();
                require("{$template_prefix}page-close.php");
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
        $breadcrumb = $this->get_data()['document']['breadcrumb'];
        if (empty($breadcrumb)) {
            return;
        }

        echo "<nav>\n";
        echo "  <ol class=\"breadcrumb\">\n";
        foreach ($breadcrumb as $crumb) {
            echo "    <li><a href=\"/documentation/{$crumb['path']}\">{$crumb['name']}</a></li>\n";
        }
        $title = $this->get_data()['document']['title'];
        echo "    <li class=\"active\">{$title}</li>\n";
        echo "  </ol>\n";
        echo "</nav>\n";
    }

    function _output_page_header() {
        $title = $this->get_data()['document']['title'];
        if (!empty($title)) {
            echo "<div class=\"page-header\">{$title}</div>\n";
        }
    }
}

$response = new Response();
?>
