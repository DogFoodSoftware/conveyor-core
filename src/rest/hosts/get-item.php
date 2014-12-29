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
require($_SERVER['HOME'].'/.conveyor/dogfoodsoftware.com/conveyor-core/runnable/lib/rest-scaffold.php');
}



global $request_errors;
$rest_id = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
// Process request for request errors effecting all response formats... if
// any.

/**
 * <div class="subHeader"><span>Format Processing</span></div>
 * <div class="p">
 *   Standard response processing is to check JSON, then check any special
 *   formats supported by the service, and to finally default to HTML.
 * </div>
 */
if (respond_in_html()) {
    // Build up $interface_html.
    $interface_html = <<<EOT
<script src="/files/conveyor/runnable/lib/jquery.mixitup.js"></script>
<script>
  $(function(){
     $('#Grid').mixitup();
     $('#toList').click(function() {
       $('#Grid').mixitup('toList');
     });
     $('#toGrid').click(function() {
       $('#Grid').mixitup('toGrid');
     });
  });
</script>
<div class="grid_12">
<div class="wrapper">
  <!-- FILTER CONTROLS -->
  <div class="controls alpha grid_3">
    <div class="subHeader"><span>Filter</span></div>
    <ul>
      <li class="filter grid_1 alpha" data-filter="all">Show All</li>
      <li class="filter grid_1" data-filter="category_1">Category 1</li>
      <li class="filter grid_1 omega" data-filter="category_2">Category 2</li>
      <div class="clear"></div>
      <li class="filter grid_1 alpha" data-filter="category_3">Category 3</li>
      <li class="filter grid_1" data-filter="category_3 category_1">Category 1 &amp; 3</li>
    </ul>
  </div>
			
  <div class="controls grid_3">
    <div class="subHeader"><span>Sort</span></div>
    <ul>
      <li class="sort" data-sort="data-cat" data-order="desc">Descending</li>
      <li class="sort" data-sort="data-cat" data-order="asc">Ascending</li>
      <li class="sort active" data-sort="default" data-order="desc">Default</li>
    </ul>
  </div>
  <div class="controls">
    <h3>Layout Controls</h3>
    <ul>
      <li class="layout" id="toList">List</li>
      <li class="layout" id="toGrid">Grid</li>
    </ul>
  </div>
  <div class="clear"></div>
  
  <ul id="Grid" class="collection-list">
    <li class="mix category_1" data-cat="1">
      <div class="title">/resources</div>
      <div class="summary">
	System nouns.
      </div>
    </li>
    <li class="mix category_3" data-cat="3">
      <div class="title">/tasks</div>
      <div class="summary">
	Stuff that needs to be done.
      </div>
    </li>
    <li class="mix category_2" data-cat="2">
      <div class="title">/documentation</div>
      <div class="summary">
	Documentation.
      </div>
    </li>
    <li class="mix category_3" data-cat="3">
      <div class="title">/demos</div>
      <div class="summary">
	Demo view of system resources.
      </div>
    </li>
    <li class="mix category_2" data-cat="2">
      <div class="title">/media</div>
      <div class="summary">
	Images, music, video, etc.
      </div>
    </li>
    <li class="mix category_1" data-cat="1">
      <div class="title">/files</div>
      <div class="summary">
	Low level file access.
      </div>
    </li>
    <li class="gap"></li> <!-- "gap" elements fill in the gaps in justified grid -->
  </ul>
  
</div>

<div class="p">
Next steps:
<ol>
  <li>Do zero-sketch for general resources browsing widget. Initially, just
  general list with inactive paging controls.</li>
  <li>Support self-demo documentation wherein we provide 'examples' section
  that generates widget off DIV elements with general purpose collection
  viewer class with self-contained JSON to allow in place demonstration and
  human verification of correct pager control activation, basic layout, search
  option handling, and page count display.</li>
</ol>
</div>
</div><!-- .grid_12 -->
EOT;

    global $pageTitle, $headerTitle;
    $pageTitle = $headerTitle = '/resources';

    echo_interface($interface_html);
}
else if (respond_in_json()) {
    if (count($request_errors) == 0) {
        $resources = array();
        $playground = '/home/user/playground';
        # Played around a lot with different ways to do this in PHP
        # and they all SUCK. Bash 'find' does what we want to do
        # directly with no fuss.
        exec("find -L $playground -path '*/runnable/rest/*' -type d",
             $resources);
        $resources = preg_filter('|^.+/runnable/rest/([^/]+)$|', '$1', $resources);
        $resources = array_values($resources);
        sort($resources);

        final_result_ok('Retrieved available resources.', $resources);
    }
    else final_result_bad_request(join("\n", $request_errors));
}
else final_result_internal_error('Configured service accept mismatch.');   
?>
<?php /**
</div><!-- .descirption -->
</div><!-- .blurbSummary#Implementation -->
*/ ?>
