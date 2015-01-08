<?php
class Response {
    private $status = 200;
    private $info_msg = null;
    private $global_errors = array();
    private $field_errors = array();

    function ok($msg, $data = null) {
        $this->info_msg = "INFO: $msg";
        $this->data = $data;
    }

    function created($msg, $data) {
        $this->info_msg = "INFO: $msg";
        $this->data = $data;
        $this->try_set_status(201);
    }

    function not_implemented() {
        global $req_path;
        $this->add_global_error("Item '$req_path' not implemented.", 501);
    }

    function server_error($msg) {
        $this->add_global_error("$msg", 500);
    }

    function item_not_found() {
        global $req_path;
        $this->add_global_error("Item '$req_path' not found.", 404);
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

    function get_data() {
        return $this->data;
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

    function _decompose($field_name) {
        global $req_data;
        $data = $req_data;
        $bits = explode('.', $field_name);
        foreach ($bits as $bit) {
            $data = $data[$bit];
            if (empty($data)) {
                return null;
            }
        }
        return $data;
    }
}

$response = new Response();
?>