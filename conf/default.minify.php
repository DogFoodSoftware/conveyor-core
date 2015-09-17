<?php
/**
 * Defines the default 'minify  bundle' (TODO: link) for a Conveyor web site.
 *
 * <h2>Design Notes</h2>
 * <div class="p">
 * The original implementation just listed files. This was fine, but a
 *  bit inconvenient for trying things out. The second approach
 *  globbed files, which was nice, but also made it impossible to know
 *  what files were actually expected by just looking at the
 *  configuration file. Thus, we come to the current implemnetation
 *  which lists all known, required files and globs the rest. This
 *  allows for devs and designers to try things out by just dropping a
 *  JS or CSS file in the directory (so long as dependencies work out)
 *  while also documenting the proper information. Once a file is
 *  actually committed, it <em>should</em> be listed here rather than
 *  relying on globbing. I.e., no globs in production.
 * </div>
 */
$files_dir = '/home/vagrant/data/files';
/**
 * We load jquery in the HEAD so it's available in the body. We could
 * be stricter and eek out a little performance, but there are
 * probably more impactive optimizations to do first and in any case
 * we value ease of use for the developer and designer as well.
 */
$jquery = array("$files_dir/js/jquery-2.1.4.js",
                "$files_dir/js/jquery-ui-1.11.4.js");
$other_req_js = array("$files_dir/js/ICanHaz-0.10.2.js",
		      "$files_dir/js/bootstrap-3.3.5.js",
		      "$files_dir/js/jquery.sticky-1.0.3.js",
		      "$files_dir/js/prettify.js");
$css = array("$files_dir/css/jquery-ui-1.11.4.css",
	     "$files_dir/css/master.css");

# We merge the two defined arrays with the glob, then 'unique' away
# the defined files from the glob array. This is all order
# preserving, and since the jquery stuff is included separately, it's
# removed entirely from the result.
$js = array_slice(
    array_unique(
        array_merge($jquery,
                    $other_req_js,
                    glob("$files_dir/js/*.js"))),
    2);
$css = array_unique(array_merge($css, glob("$files_dir/css/*.css")));

return array('jquery-js' => $jquery,
             'default-js' => $js,
             'default-css' => $css);
?>
