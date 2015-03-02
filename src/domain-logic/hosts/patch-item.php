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
    global $home;

    $bit = array_shift($path);
    if (empty($bit) && $op['op'] == 'add') { // Add a new subscription (to a repo).
        $subscription_data = $op['value'];
        // Verify the data.
        $response->check_required_field('name', $subscription_data);
        $response->check_required_field('source', $subscription_data);
        $response->check_extraneous_fields(2, array('development-ready'), $subscription_data);
        
        $subscription_source = $subscription_data['source'];
        if (!preg_match('|^https?://|', $subscription_source)) {
            $response->add_field_error('subscription.source',
                                       "Unknown subscription source protocol. Must be 'http' or 'https'.");
        }
        if (!$response->check_request_ok()) {
            $response->finish();
        }
        // Clear to create the subscription
        list($sub_domain, $sub_name) = explode('/', $subscription_data['name']);
        if (!is_dir("{$home}/.conveyor/subscriptions/{$sub_domain}")) {
            mkdir("{$home}/.conveyor/subscriptions/{$sub_domain}", 0700, true);
        }
        if (array_key_exists('development-ready', $subscription_data)) {
            # Then we checkout to playground and symlink.
            if (!is_dir("{$home}/playground/{$sub_domain}")) {
                mkdir("{$home}/playground/{$sub_domain}", 0700, true);
            }
            exec("cd {$home}/playground/{$sub_domain} && git clone {$subscription_source} {$sub_name}");
            symlink("{$home}/playground/{$sub_domain}/{$sub_name}", "$home/.conveyor/subscriptions/{$sub_domain}/{$sub_name}");
        }
        else {
            # Then we check out directly.
            exec("cd $home/.conveyor/subscriptions/{$sub_domain} && git clone {$subscription_source} {$sub_name}");
        }
    } // if (empty($bit) && $op['op'] == 'add') {
    elseif (empty($bit)) {
        $response->invalid_request();
    }
    else { // whatever the opp, should reference an actual subscription
        // Note that the FQN subscription name has two parts, '$bit' is
        // currently only the first part.
        if (count($path) < 2) {
            $response->invalid_request("Invalid JSON data bundle; cound not determine FQN repository name.");
        }
        $fqn_subscription = $bit.'/'.array_shift($path);
        if (array_key_exists($fqn_subscription, $subscriptions_data)) {
            process_subscription($fqn_subscription, $subscriptions_data[$fqn_subscription], $op, $path);
        }
        else {
            $response->add_field_error("-/subscriptions", "Unknown subscription ID: '{$fqn_subscirption}'.");
        }
    }
}

function process_subscription($fqn_subscription, &$subscription_data, $op, $path) {
    global $home;
    global $response;

    $bit = array_shift($path);
    if ($bit == 'installed-packages' && empty($path) && $op['op'] == 'add') {
        $pkg_data = $op['value'];

        $response->check_required_parameter('name', $pkg_data);
        $response->check_extraneous_fields(1, array('source', 'version'), $pkg_data);

        list($domain) = explode('/', $fqn_subscription);
        $pkg_name = $pkg_data['name'];
        
        # Since Conveyor compliant nix install packages know where to look
        # for development source, to enable for development, we just check
        # out the source. The install process will use it if available.
        if (array_key_exists('source', $pkg_data)) {
            $pkg_source = $pkg_data['source'];
            if (!is_dir("{$home}/playground/{$domain}/")) {
                mkdir("{$home}/playground/{$domain}/", 0700, true);
            }
            exec("cd $home/playground/{$domain}/ && git clone $pkg_source", $output, $result);
            if ($result != 0) {
                $response->server_error("Git clone failed for '{$fqn_subscription}/{$pkg_name}'. ".implode('; ', $output));
            }
        }
        echo "nix-env -f {$home}/.conveyor/subscriptions/{$fqn_subscription}/default.nix -iA {$pkg_name}\n";
        exec("nix-env -f {$home}/.conveyor/subscriptions/{$fqn_subscription}/default.nix -iA {$pkg_name}",
             $output,
             $result);
        if ($result != 0) {
            $response->server_error("Install failed for '{$fqn_subscription}/{$pkg_name}'");
        }
        
        $response->ok("Package '{$fqn_subscription}/{$pkg_name}' installed.");
    }
    elseif ($bit == 'installed-packages' && count($path) == 2 && $op['op'] == 'replace') {
        list($package_name, $attribute) = $path;
        if (!array_key_exists($package_name, $subscription_data['installed-packages'])) {
	    var_dump($subscription_data);
            $response->invalid_request("Could not find package '{$package_name}' in repository '{$fqn_subscription}'.");
        }
        switch ($attribute) {
        case "source":
            update_package_source($fqn_subscription, $package_name, $subscription_data['installed-package'][$package_name], $op['value']);
            break;
        default:
            $response->invalid_request("Invalid package attribute '{$attribute}'.");
        }
    }
    else {
        global $req_action;
        $response->invalid_request("Could not process data path '{$op['path']}' for '{$req_action}'.");
    }
} 

function update_package_source($fqn_subscription, $package_name, &$package_data, $new_source) {
    global $response;
    global $home;

    if ($new_source == $package_data['source']) {
        $response->ok("No change to source.");
    }
    elseif ($new_source == 'distribution') { # Turn to non-development.
        $response->not_implemented("Changing package 'source' status to 'distro' not currently supported.");        
    }
    else {
        list($domain) = explode('/', $fqn_subscription);
        if (!is_dir("$home/playground/$domain")) {
            mkdir("$home/playground/$domain", 0777, true);
        }
        exec("cd $home/playground/{$domain}/ && git clone $new_source", $output, $result);
        if ($result != 0) {
            $response->server_error("Git clone failed for '{$domain}/{$new_source}'. ".implode('; ', $output));
        }
        echo "HEY:\n\tnix-env -f $home/.conveyor/subscriptions/$fqn_subscription/default.nix -uA $package_name\n";
        exec("nix-env -f $home/.conveyor/subscriptions/$fqn_subscription/default.nix -uA $package_name");
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
                if (file_exists($target_link)) {
                    unlink($target_link);
                }
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
if ($host_data === FALSE) {
    $response->server_error("ERROR: Could not decode host data.");
    exit;
}
$host_data = array_reduce($host_data, 'array_merge', array());
foreach ($req_data as $op) {
    if (preg_match('/replace|add/', $op['op'])) {
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
