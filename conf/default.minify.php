<?php

$files_dir = '/home/vagrant/data/files';
$js = array("$files_dir/js/jquery-2.1.4.js",
            "$files_dir/js/ICanHaz-0.10.2.js",
            "$files_dir/js/bootstrap-3.3.5.js",
            "$files_dir/js/jquery.sticky-1.0.3.js");
$css = array("$files_dir/css/master.css");

return array('default-js' => $js,
             'default-css' => $css);
?>