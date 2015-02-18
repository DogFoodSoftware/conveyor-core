<?php 
/**
 * <div class="p">
 *   Patches host data.
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
# TODO: short term auth
if (php_sapi_name() != 'cli') {
    $response->unauthorized_request();
    return;
}
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/authorization-lib.php");
if (!$response->check_request_ok()) {
    return;
}

if ($req_data == null) {
    $response->invalid_request("Did not find required list of patch operations in request.");
    return;
}
if (! is_array($req_data)) {
    $response->invalid_request("Patch operations must be sent in a list.");
    return;
}


$host_data = json_decode(shell_exec("con -q GET $req_path"));
foreach ($req_data as $op) {
    if ($op['op'] == 'replace') {
        $path = explode('/', $op['path']);
        if ($op[0] == "") { array_shift($path); }
        var_dump($path);
    }
}

exit();

exec('find '.$home.'/.conveyor/runtime -follow -path "*/src/domain-logic/*" -type d -exec basename {} \\;', $resource_names);
sort($resource_names);
$resources = array();
function package_extract($path) {
    return preg_replace('|.*/([^/]+/[^/]+)/src/domain-logic/.+|', '$1', $path);
}
foreach ($resource_names as $resource_name) {
    if (!array_key_exists($resource_name, $resource_names)) {
        $package_providers = array();
        exec('find '.$home.'/.conveyor/runtime -follow -path "*/src/domain-logic/'.$resource_name.'" -type d', $package_providers);
        $package_providers = array_map('package_extract', $package_providers);

        $config_link = "{$home}/.conveyor/data/dogfoodsoftware.com/conveyor-apache/conf-inc/service-{$resource_name}.httpd.conf";
	$provider = null;
        $config = file_exists($config_link) ? readlink($config_link) : null;
        if ($config === FALSE) { # TODO: log something
        }
        elseif ($config != null) {
            $provider = preg_replace('|.*/([^/]+/[^/]+)/conf/.+|', '$1', $config);
        }

        $resources[$resource_name] =
	  array('name' => $resource_name,
	        'package-providers' => $package_providers,
		'provider' => $provider);
    }
}

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
