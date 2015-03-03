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

# Get data on subscriptions and installed packages. Also, construct 'sites'
# data.
$install_report = array();
$sites = array();
$raw_install_report = json_decode(shell_exec('nix-env -q --meta --json'), true);
foreach ($raw_install_report as $key => $package_data) {
    if (array_key_exists('meta', $package_data) && array_key_exists('position', $package_data['meta'])) {
        $src_position = $package_data['meta']['position'];
        if (preg_match('|/home/user/\.conveyor/subscriptions/([^/]+/[^/]+).*|', $src_position, $matches)) {
            $fqn_repository = $matches[1];
            if (!array_key_exists($fqn_repository, $install_report)) {
                $install_report[$fqn_repository] = array();
            }
            if (preg_match('/(-(\d+\.\d+.*))$/', $package_data['name'], $matches)) {
                $version = $matches[2];
                $name = preg_replace("/-{$version}$/", '', $package_data['name']);
            }
            else {
                $name = $package_data['name'];
                # TODO: issue warning
            }
            $install_report[$fqn_repository][$name] = array('name' => $name);
            if (!empty($version)) {
                $install_report[$fqn_repository][$name]['version'] = $version;
            }
            if (array_key_exists('package-type', $package_data['meta'])) {
                $pkg_type = $package_data['meta']['package-type'];
                if ($pkg_type == 'site') {
                    $sites[$name] = array('name' => $name,
                                          'package' => explode('/',$fqn_repository)[0]."/{$name}",
                                          'installed' => is_link("$home/.conveyor/data/conveyor-apache/conf-inc/site-{$name}.httpd.conf"));
                }
            }
        }
    }
}
$subscriptions = array();
foreach (glob("$home/.conveyor/subscriptions/*") as $path_a) {
    $sub_domain = basename($path_a);
    foreach (glob("$home/.conveyor/subscriptions/{$sub_domain}/*") as $path_b) {
        $name = "{$sub_domain}/".basename($path_b);
        $subscriptions[$name] = array("name" => $name,
                                      "installed-packages" =>
                                      array_key_exists($name, $install_report) ? $install_report[$name] : array());
    }
}
# Set development status for the subscriptions.
foreach (array_keys($subscriptions) as $subscription) {
    if (is_link("{$home}/.conveyor/subscriptions/{$subscription}")) {
        $subscriptions[$subscription]['development-ready'] = true;
    }
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
