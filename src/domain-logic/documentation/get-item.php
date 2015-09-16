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

$document_contents = '';
if (!file_exists($file_path)) {
    $msg = "404: Did not find '$req_path'.";
    $response->set_data(array("document" => array('contents' => $msg)));
    $response->item_not_found();
}
else {
    if (is_dir($file_path)) {
        $intro_path = $file_path."_intro";
        if (file_exists($intro_path)) {
            $document_contents .= '<div class="dir-intro">'."\n";
            $document_contents .= file_get_contents($intro_path);
            $document_contents .= "</div><!-- .dir-intro -->\n";
        }

        if ($dh = opendir($file_path)) {
            $document_contents .= "<ul>\n";
            while (($file = readdir($dh)) !== false) {
                if (!(preg_match("/^[._]/", $file) || preg_match('/~$/', $file))) {
                    $url_path = $req_path.(preg_match('|/$|', $req_path) ? '' : '/').$file;
                    $document_contents .= '<li><a href="'.$url_path.'">'.human_out($file)."</a></li>\n";
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
            if (preg_match('/\.html$/', $file_path)) {
                $document_contents .= '<iframe id="extern-html" src="/files/misc/'.basename($file_path).'" style="width: 100%; height: 100%"></iframe>';
                $document_contents .= '<script>$("#extern-html").load(function() { $(this).height($(this).contents().find("html").height());});</script>';
            }
            else {
                $document_contents .= file_get_contents($file_path);
            }
        }
        $document = array('contents' => $document_contents);
    }

    $crumb_specs = array();
    if (preg_match('/^\s*<!--\s+breadcrumbs:\s*((\/?[\(\)\w |-]+))\s*-->\s*$/m', $document_contents, $matches)) {
        $breadcrumbs_spec = $matches[1];
        if (!empty($breadcrumbs_spec)) {
            $crumb_specs = explode('|', $breadcrumbs_spec);
        }
    }
    if (empty($crumb_specs)) {
        $path_bits = explode('/', preg_replace('|/$|', '', $req_path));
        array_shift($path_bits); # remove '' entry from the leading '/'
        array_shift($path_bits); # remove the resource path; already handled in the resource bug
        foreach ($path_bits as $index => $path_bit) {
            array_push($crumb_specs,
                       ($index + 1 < count($path_bits) ?
                        '('.str_repeat('../', count($path_bits) - ($index + 1)).'.)':'')
                       .$path_bit);
        }
    }

    $document['title'] = array_pop($crumb_specs);
    $breadcrumbs = array();
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

    $document['breadcrumbs'] = $breadcrumbs;

    $response->ok('Document retrieved.', array("document" => $document));
}
/**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
