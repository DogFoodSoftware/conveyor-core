<?php
class Response {
    private $status = 200;
    private $global_errors = array();
    private $field_errors = array();

    function item_not_found() {
        $this->add_global_error("Item not found.", 404);
    }

    function check_request_ok() {
        return $status == 200
            && (count($global_errors) + count($field_errors)) == 0;
    }

    function add_global_error($message, $try_status) {
        array_push($this->global_errors, "ERROR: $message");
        $this->try_set_status($try_status);
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