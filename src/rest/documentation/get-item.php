<?php 
/**
 * <div class="p">
 *   Get a site document.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
$home = $_ENV['HOME'];
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/rest-scaffold.php");
$response->set_output_field("document.contents");
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/domain-logic/documentation/get-item.php");
?>
<?php /**
</div><!-- .descirption -->
</div><!-- .blurbSummary#Implementation -->
*/ ?>
