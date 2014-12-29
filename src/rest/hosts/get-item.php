<?php 
/**
 * <div class="p">
 *   Gets information regarding a host within the Conveyor
 *   environment.  Currently supports the single special id 'this',
 *   referring to the host currently executing the script.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
$home = $_SERVER['HOME'];
require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/lib/rest-scaffold.php");
if ($req_accept == 'text/html') {
    require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/simple-html-template.php");
    echo_header();
    require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/ui/hosts-item-html.php");
    echo_footer();
}
else {
    require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/domain-logic/get-item-data.php");
    
    if ($req_accept == 'text/plain') {
        require("$home/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/ui/hosts/get-item-text.php");
    }
}
?>
<?php /**
</div><!-- .descirption -->
</div><!-- .blurbSummary#Implementation -->
*/ ?>
