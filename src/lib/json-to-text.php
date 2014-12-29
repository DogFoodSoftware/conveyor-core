<?php
if (!$response->check_request_ok()) {
    echo "Failed with status: ".$response->get_status()."\n";
    foreach ($response->get_global_errors() as $error_msg) {
        echo "$error_msg\n";
    }
    foreach ($response->get_field_errors() as $field_name => $error_msg) {
        echo "$field_name: $error_msg\n";
    }
}
else { // Response is good.
    // Most data is JSON, but there are some cases where we get raw text.
    $json_string = json_encode($response->get_data(), JSON_PRETTY_PRINT)."\n";
    echo ($json_string != null ? $json_string : $response->get_data());
}
?>