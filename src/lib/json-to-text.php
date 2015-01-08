<?php
if (!empty($response->get_info_msg())) {
    echo $response->get_info_msg()."\n";
}
if (!$response->check_request_ok()) {
    echo "Failed with status: ".$response->get_status()."\n";
}
// There may be warnings even if status is OK.
foreach ($response->get_global_errors() as $error_msg) {
    echo "$error_msg\n";
}
foreach ($response->get_field_errors() as $field_name => $error_msgs) {
    foreach ($error_msgs as $error_msg) {
        echo "$field_name: $error_msg\n";
    }
}
// Data is only included if everything OK.
if ($response->check_request_ok()) {
    // Most data is JSON, but there are some cases where we get raw text.
    $json_string = json_encode($response->get_data(), JSON_PRETTY_PRINT)."\n";
    echo ($json_string != null ? $json_string : $response->get_data());
}
?>