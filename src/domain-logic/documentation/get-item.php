<?php
/**
 * <div class="p">
 * </div>
 * <div id="Implementation" data-perspective="coding" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/authorization-lib.php");

$file_path = "$home/.conveyor/data/dogfoodsoftware.com/conveyor-core/documentation/$req_item_id";

if (!file_exists($file_path)) {
    // $response->defer();
    $msg = "404: Did not find '$req_path'.";
    $response->set_data(array("document" => array('contents' => $msg)));
    $response->item_not_found();
    // $response->finish();
}
else {
    $document_contents = file_get_contents($file_path);
    $document = array("document" => array('contents' => $document_contents));
    $response->ok('Document retrieved.', $document);
}
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
