<?php
/**
 * <div class="p">
 * </div>
 * <div id="Implementation" data-perspective="coding" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
require("lib/authorization-lib.php");

$file_path = "/home/vagrant/documentation/$req_item_id";

if (!file_exists($file_path)) {
    $msg = "404: Did not find '$req_path'.";
    $response->set_data(array("document" => array('contents' => $msg)));
    $response->item_not_found();
}
else {
    $document_contents = file_get_contents($file_path);
    $document = array('contents' => $document_contents);
    if (preg_match('/^\s*<!--\s+breadcrumb:\s*((\/?[\(\)\w |-]+))\s*-->\s*$/m', $document_contents, $matches)) {
        $breadcrumb_spec = $matches[1];
        if (!empty($breadcrumb_spec)) {
            $breadcrumb = array();
            $crumb_specs = explode('|', $breadcrumb_spec);
            $document['title'] = array_pop($crumb_specs);
            foreach ($crumb_specs as $crumb_spec) {
                if (preg_match('/\(([^\)]+)\)?(.+)/', $crumb_spec, $matches)) {
                    $crumb = array('path' => $matches[1],
                                   'name' => $matches[2]);
                    if (empty($crumb['path'])) {
                        $crumb['path'] = $name;
                    }
                    array_push($breadcrumb, $crumb);
                }
                // else add warning to response
            }
            $document['breadcrumb'] = $breadcrumb;
                            
        }
    }
    else { // No breadcrumb def, let's divine the title.
        $document['title'] = basename($req_path);
    }
    $response->ok('Document retrieved.', array("document" => $document));
}
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
