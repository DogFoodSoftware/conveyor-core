<?php 
/**
 * <div class="p">
 *   Gets information regarding a host within the Conveyor
 *   environment.  Currently supports the single special id 'this',
 *   referring to the host currently executing the script.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/response-lib.php");
if ($req_item_id != 'this') {
    $response->item_not_found();
}
if (!$response->check_request_ok()) {
    return;
}
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/authorization-lib");
if (!$response->check_request_ok()) {
    return;
}

exec('find '.$home.'/.conveyor/runtime -follow -path "*/src/domain-logic/*" -type d', $resources);
sort($resources);

$host = array('resources' => $resources);

$response->ok('Retrieved available resources.', $resources);
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
