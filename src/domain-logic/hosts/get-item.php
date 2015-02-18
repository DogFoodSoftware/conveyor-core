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

$host_errors = array();
$host_warnings = array();

# Get data on subscription and installed packages.
$install_report = array();
$raw_install_report = json_decode(shell_exec('nix-env -q --meta --json'), true);
foreach ($raw_install_report as $key => $package_data) {
    if (array_key_exists('meta', $package_data) && array_key_exists('position', $package_data['meta'])) {
        $src_position = $package_data['meta']['position'];
        if (preg_match('|\.conveyor/subscriptions/([^/]+)/|', $src_position, $matches)) {
            if (!array_key_exists($matches[1], $install_report)) {
                $install_report[$matches[1]] = array();
            }
            array_push($install_report[$matches[1]], $package_data['name']);
        }
    }
}
$subscriptions = array();
foreach (glob("$home/.conveyor/subscriptions/*") as $subscription) {
    $name = basename($subscription);
    $subscriptions[$name] = array("name" => $name,
                                  "installed-packages" =>
                                    array_key_exists($name, $install_report) ? $install_report[$name] : array());
}

exec('find '.$home.'/.conveyor/runtime -follow -path "*/src/domain-logic/*" -type d -exec basename {} \\;', $resource_names);
sort($resource_names);
$resources = array();
function package_extract($path) {
    return preg_replace('|.*/([^/]+/[^/]+)/src/domain-logic/.+|', '$1', $path);
}
# First, we build the list of potential providers. A provider must define two
# things. Under 'src/domain-logic', the resource handlers in a sub-directory
# named for the resource. Second in 'conf/', a properly named
# 'services-<resource>.httpd.conf' file.
foreach ($resource_names as $resource_name) {
    if (!array_key_exists($resource_name, $resource_names)) {
        $package_providers = array();
        exec('find '.$home.'/.conveyor/runtime -follow -path "*/src/domain-logic/'.$resource_name.'" -type d', $package_providers);
        $package_providers = array_map('package_extract', $package_providers);

        foreach ($package_providers as $index => $provider) {
            if (!file_exists("$home/.conveyor/runtime/{$provider}/conf/service-{$resource_name}.httpd.conf")) {
                unset($package_providers[$index]);
                array_push($host_warnings, "Found domain logic for resource '$resource_name', but no configuration file.");
            }
        }

        $resources[$resource_name] = array('name' => $resource_name,
                                           'package-providers' => $package_providers);
    }
}
# Now we check to see if any of the providers are actually active.
foreach ($resources as $resource_name => $resource_data) {
    $config_link = "{$home}/.conveyor/data/dogfoodsoftware.com/conveyor-apache/conf-inc/service-{$resource_name}.httpd.conf";
    $provider = null;
    $config = file_exists($config_link) ? readlink($config_link) : null;
    if ($config === FALSE) {
        if (is_link($config_link)) {
            array_push($host_errors, "Found dangling service link: '$config_link'.");
        }
        else {
            array_push($host_errors, "File '$config_link' is not a link.");
        }
    }
    elseif ($config != null) {
        $provider = preg_replace('|.*/([^/]+/[^/]+)/conf/.+|', '$1', $config);
    }
    # TODO: check that the link is actually back to one of the providers.

    $resources[$resource_name]['provider'] = $provider;
}

# Process available sites.
exec('find '.$home.'/.conveyor/runtime -follow -path "*/conf/site-*.httpd.conf" -type f', $site_configs);
$sites = array();
foreach ($site_configs as $site_config) {
    preg_match('|([^/]+/[^/]+)/conf/site-(.+).httpd.conf|', $actual_config, $matches);
    $package_provider = $matches[1];
    $name = $matches[2];
    if (array_key_exists($name, $sites)) {
        // Multiple providers for the same, just fine.
        array_push($package_provider, $sites[$name]['package-providers']);
    }
    else {
        $sites[$name] = array('name' => $name,
                              'package-providers' => array($pacakge_provider),
                              'provider' => null);
    }
}
# Now set the actual site providers.
$site_configs = glob("$home/.conveyor/data/dogfoodsoftware.com/conveyor-apache/conf-inc/site-*.httpd.conf");
foreach ($site_configs as $site_config) {
    if (!is_link($site_config)) {
        array_push($host_warnings,
                   "Appearent site configuration '$site_config' is not a link as expected. Cannot fully evaluate.");
    }
    else {
        $actual_config = readlink($site_config);
        preg_match('|([^/]+/[^/]+)/conf/site-(.+).httpd.conf|', $actual_config, $matches);
        $package_provider = $matches[1];
        $name = $matches[2];

        if (!array_key_exists($name, $sites)) {
            array_push($host_warnings,
                       "Site configuration links outside of known conveyor runtime.");
        }
        else {
            $sites[$name] = array('provider'=> $package_provider);
        }
    }
}

if (!file_exists("$home/.conveyor/host-id")) {
    # TODO: info, not warning
    $response->add_global_warning("Conveyor host ID not found; new ID generated.");
    exec("uuidgen > $home/.conveyor/host-id");
}
$host_id = trim(file_get_contents("$home/.conveyor/host-id"));

$host = array("host" => array('host-id' => $host_id,
                              'subscriptions' => $subscriptions,
                              'resources' => $resources,
                              'sites' => $sites));
if (!empty($host_errors)) { $host['host']['errors'] = $host_errors; }
if (!empty($host_warnings)) { $host['host']['warnings'] = $host_warnings; }

$response->ok('Host information retrieved.', $host);
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
