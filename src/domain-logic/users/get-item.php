<?php 
/**
 * <div class="p">
 *   Gets information regarding a user within the Conveyor
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

if (!empty($req_parameters['inc-fields'])) {
    $inc_fields = explode(',',$req_parameters['inc-fields']);

    foreach ($inc_fields as $inc_field) {
        $inc_field = trim($inc_field);
        switch ($inc_field) {
        case "next-topics":
            $topics_lib = "$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-workflow/runnable/lib/topics-lib.php";
            if (file_exists($topics_lib)) {
                require $topics_lib;
                $user['next-topics'] = TopicsLib::get_next_topics($user);
            }
            else {
                $response->invalid_request("The host system is not setup to determine 'next-topics'. 'conveyor-workflow' must be installed.");
            }
            break;
        default:
            $response->invalid_request("Unknown 'inc-fields' member: '$inc_field'.");
        }
    }
}

$user = array("user" => $user);
if (!empty($user_errors)) { $user['user']['errors'] = $user_errors; }
if (!empty($user_warnings)) { $user['user']['warnings'] = $user_warnings; }

$response->ok('User information retrieved.', $user);
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
