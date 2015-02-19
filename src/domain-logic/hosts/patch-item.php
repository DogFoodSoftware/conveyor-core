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

function process_path(&$host_data, $op, $path) {
    $bit = array_shift($path);
    switch ($bit) {
        // We list the field names in order they are displayed.
    case 'subscriptions':
        process_subscriptions($host_data[$bit], $op, $path);
        break;
    case 'resources':
        process_resources($host_data[$bit], $op, $path);
        break;
    default:
        global $response;
        $response->invalid_request("Path '{$op['path']}' invalid.");
        exit;
    }
}

function process_subscriptions(&$subscriptions_data, $op, $path) {
    global $response;

    $bit = array_shift($path);
    if ($op['path'] == 'add') {
        if (count($path) > 0) {
            $response->invalid_request("Invalid path for 'add': '{$op['path']}'.");
            exit;
        }
        $subscription_data = json_decode($op['value'], true);
        $expected_count = 2;
        if (!array_key_exists('name', $subscription_data)) {
            $response->add_field_error('subscriptions.name',
                                       "Missing required parameter 'subscriptions.name'.");
            $expected_count -= 1;
        }
        if (!array_key_exists('source', $subscription_data)) {
            $response->add_field_error('subscriptions.source',
                                       "Missing required parameter 'subscriptions.source'.");
            $expected_count -= 1;
        }
        if (array_key_exists('development', $subscription_data)) {
            $expected_count += 1;
        }
        if (count($subscription_data) != $expected_count) {
            $response->add_field_error('subscriptions',
                                       "Unknown subscription attributes found.");
        }
        $subscription_source = $subscription_data['source'];
        if (!preg_match('|^https?://|', $subscription_source)) {
            response->add_field_error('subscription.source',
                                      "Unknown subscription source protocol. Must be 'http' or 'https'.");
        }
        if (!$response->check_request_ok()) {
            $response->_output();
            exit;
        }
        
        // else, create the subscription
        if (array_key_exists('development', $subscription_data)) {
            # Then we checkout to playground and symlink.
            if (!is_dir("$home/playground/{$subscription_data['name']}")) {
                mkdir("$home/playground/{$subscription_data['name']}", 0700, true);
            }
            exec("cd $home/playground/{$subscription_data['name']} && git clone {$subscription_source} {$subscription_data['name']");
            symlink($matches[1], "$home/.conveyor/subscriptions/{$subscription_data['name']");
        }
        else {
            # Then we check out directly.
            exec("cd $home/.conveyor/subscriptions/ && git clone {$subscription_source} {$subscription_data['name']");
        }
    }
    else {
        $response->invalid_request("Unsupported operation '{$op['op']}' for path '{$op['path']}'.");
        exit;
    }
}

function process_resources(&$resources_data, $op, $path) {
    $bit = array_shift($path);
    if (array_key_exists($bit, $resources_data)) {
        process_resource($bit, $resources_data[$bit], $op, $path);
    }
    else {
        global $response;
        $response->invalid_request("Path '{$op['path']}' invalid; no such resource '$bit'.");
        exit;
    }
}

function process_resource($resource, &$resource_data, $op, $path) {
    global $response;
    global $home;

    $bit = array_shift($path);
    if ($bit == 'provider') {
        if ($op['op'] == 'replace') {
            $src_file = "$home/.conveyor/runtime/{$op['value']}/conf/service-{$resource}.httpd.conf";
            if (file_exists($src_file)) {
                $target_link = "$home/.conveyor/data/dogfoodsoftware.com/conveyor-apache/conf-inc/service-{$resource}.httpd.conf";
                unlink($target_link);
                symlink($src_file, $target_link);
            }
            else {
                $response->invalid_request("No such provider '{$op['value']}'.");
                exit;
            }
        }
        else {
            $response->invalid_request("Operation '{$op['op']}' invalid for attribute '{$op['path']}'.");
            exit;
        }
    }
    else {
        $response->invalid_request("Path '{$op['path']}' invalid; no such resource attribute '$bit'.");
    }        
}

$host_data = json_decode(shell_exec("con -q GET $req_path"), true);
$host_data = array_reduce($host_data, 'array_merge', array());
foreach ($req_data as $op) {
    if ($op['op'] == 'replace') {
        $path = explode('/', $op['path']);
        if ($path[0] == "") { array_shift($path); }

        process_path($host_data, $op, $path);
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
