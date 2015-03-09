<?php 
/**
 * <div class="p">
 *   Gets information regarding a host within the Conveyor
 *   environment. Currently supports the single special id 'this',
 *   referring to authenticated user associated with the request.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
if ($req_item_id != 'this') {
    $response->item_not_found();
}
if (!$response->check_request_ok()) {
    return;
}
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/authorization-lib.php");
if (!$response->check_request_ok()) {
    return;
}

$user_errors = array();
$user_warnings = array();

if (!file_exists("$home/.conveyor/user.json")) {
    $response->item_not_found("Could not find any authenticated user associated with request.");
}

$user = json_decode(file_get_contents("$home/.conveyor/user.json"), true);


$user = array("user" => $user);
if (!empty($host_errors)) { $user['user']['errors'] = $host_errors; }
if (!empty($host_warnings)) { $user['user']['warnings'] = $host_warnings; }

$response->ok('User information retrieved.', $user);
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
