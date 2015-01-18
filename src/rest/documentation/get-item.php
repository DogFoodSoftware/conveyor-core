<?php 
/**
 * <div class="p">
 *   Get a site document.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
$home = $_SERVER['HOME'];
require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/lib/rest-scaffold.php");
require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/domain-logic/documentation/get-item.php");

if ($response->check_request_ok()) {
    if ($req_accept == 'text/html') {
        require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/simple-html-template.php");
        echo_header();
        require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/ui/documentation-item-html.php");
        echo_footer();
    }
    else {
        if ($req_accept == 'text/plain') {
            require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/lib/json-to-text.php");
        }
    }
}
?>
<?php /**
</div><!-- .descirption -->
</div><!-- .blurbSummary#Implementation -->
*/ ?>
