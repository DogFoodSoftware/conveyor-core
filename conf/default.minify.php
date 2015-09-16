<?php

$files_dir = '/home/vagrant/data/files';
$jquery = array("$files_dir/js/jquery-2.1.4.js",
                "$files_dir/js/jquery-ui-1.11.4.js");
$js = array_slice(
    array_unique(
        array_merge($jquery,
                    array(
                        "$files_dir/js/ICanHaz-0.10.2.js",
                        "$files_dir/js/bootstrap-3.3.5.js",
                        "$files_dir/js/jquery.sticky-1.0.3.js",
                        "$files_dir/js/prettify.js"),
                    glob("$files_dir/js/*.js"))),
    2);
$css = glob("$files_dir/css/*.css");

return array('jquery-js' => $jquery,
             'default-js' => $js,
             'default-css' => $css);
?>