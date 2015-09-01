<?php
/**
 * <div class="p">
 * </div>
 * <div id="Implementation" data-perspective="coding" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
require("lib/authorization-lib.php");
require("lib/code2html.php");

$file_path = "/home/vagrant/documentation/$req_item_id";

function human_out($in) {
    $out = preg_replace('/-/', ' ', $in);
    $out = preg_replace('/\.[^.]+$/', '', $out);
    return $out;
}

if (!file_exists($file_path)) {
    $msg = "404: Did not find '$req_path'.";
    $response->set_data(array("document" => array('contents' => $msg)));
    $response->item_not_found();
}
else {
    if (is_dir($file_path)) {
        if ($dh = opendir($file_path)) {
            $document_contents .= "<ul>\n";
            while (($file = readdir($dh)) !== false) {
                if (!preg_match("/^\./", $file)) {
                    if (is_dir("{$file_path}{$file}")) {
                        $document_contents .= '<li><a href="'.$req_path.$file.'">'.human_out($file)."</a></li>\n";
                    }
                    else {
                        $document_contents .= '<li><a href="'.$req_path.'/'.$file.'">'.human_out($file)."</a></li>\n";
                    }
                }
            }
            closedir($dh);
            $document_contents .= "</ul>\n";

            $document = array('contents' => $document_contents);
        }
        else {
            $msg = "404: Could not open directory-path '$file_path'.";
            $response->set_data(array("document" => array('contents' => $msg)));
            $response->item_not_found();
        }
    }
    else {
        if (preg_match('|/src/|', $file_path)) {
            ob_start();
            code2html($file_path);
            $document_contents = ob_get_clean();
        }
        else {
            $document_contents = file_get_contents($file_path);
        }
        $document = array('contents' => $document_contents);
        $breadcrumbs = array();
        if (preg_match('/^\s*<!--\s+breadcrumbs:\s*((\/?[\(\)\w |-]+))\s*-->\s*$/m', $document_contents, $matches)) {
            $breadcrumbs_spec = $matches[1];
            if (!empty($breadcrumbs_spec)) {
                $crumb_specs = explode('|', $breadcrumbs_spec);
            }
        }
        else if ($req_resource == 'documentation') {
            $crumb_specs = explode('/', $req_path);
            array_shift($crumb_specs); # remove '' entry from the leading '/'
        }

        if (isset($crumb_specs)) {
            $document['title'] = array_pop($crumb_specs);
            if ($req_resource == 'documentation') {
                foreach ($crumb_specs as $index => $directory) {
                    $crumb = array('path' => str_repeat('../', count($crumb_specs) - ($index + 1)),
                                   'name' => $directory);
                    array_push($breadcrumbs, $crumb);
                }
            }
            else {
                foreach ($crumb_specs as $crumb_spec) {
                    if (preg_match('/\(([^\)]+)\)?(.+)/', $crumb_spec, $matches)) {
                        $crumb = array('path' => $matches[1],
                                       'name' => $matches[2]);
                        if (empty($crumb['path'])) {
                            $crumb['path'] = $name;
                        }
                        array_push($breadcrumbs, $crumb);
                    }
                    // TODO: else add warning to response
                }
            }
            $document['breadcrumbs'] = $breadcrumbs;
        }
        else { // No breadcrumbs def, let's divine the title.
            $document['title'] = basename($req_path);
        }
    }

    $response->ok('Document retrieved.', array("document" => $document));
}
/**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
