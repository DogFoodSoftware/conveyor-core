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

exec('find '.$home.'/.conveyor/runtime -follow -path "*/src/domain-logic/*" -type d', $resources);
function trim_resource_path($path) {

    return basename($path);
}
$resources = array_map('trim_resource_path', $resources);
sort($resources);

if (!file_exists("$home/.conveyor/host-id")) {
    $response->add_global_warning("Conveyor host ID not found; new ID generated.");
    exec("uuidgen > $home/.conveyor/host-id");
}
$host_id = trim(file_get_contents("$home/.conveyor/host-id"));

$host = array("host" => array('host-id' => $host_id,
                              'resources' => $resources));

$response->ok('Host information retrieved.', $host);
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
