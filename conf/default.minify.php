<?php

$files_dir = '/home/vagrant/data/files';
$js = glob("$files_dir/js/*.js");
error_log(print_r($js,TRUE));
$css = glob("$files_dir/css/*.css");

return array('default-js' => $js,
             'default-css' => $css);
?>