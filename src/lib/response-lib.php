<?php
class Response {
    private $status = 200;
    private $info_msg = null;
    private $global_errors = array();
    private $field_errors = array();

    function item_not_found() {
        global $req_path;
        $this->add_global_error("Item '$req_path' not found.", 404);
    }

    function check_request_ok() {
        // Note, there may be 'WARNING' in the 'errors' with a '200'
        // status.
        return $this->status == 200;
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

    function ok($msg, $data = null) {
        $this->info_msg = $msg;
        $this->data = $data;
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
}

$response = new Response();
?>