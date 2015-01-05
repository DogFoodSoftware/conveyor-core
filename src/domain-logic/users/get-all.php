<?php 
/**
 * <div class="p">
 *   Retrieves a batch of user data.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/response-lib.php");

# TODO: Verify parameters.

if (!$response->check_request_ok()) {
    return;
}
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/authorization-lib.php");
if (!$response->check_request_ok()) {
    return;
}

$users = array();

$users = array("users" => $users);

$response->ok('User information retrieved.', $users);
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
