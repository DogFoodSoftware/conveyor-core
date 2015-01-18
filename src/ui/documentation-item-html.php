<?php
$matches = array();

if (preg_match('|^implementation/(.+)|', $req_id, $matches)
    && file_exists("$home/.conveyor/runtime/".$matches[1])) {
    # 'source code' documentation
    global $extraHeader;
    $extraHeader = <<<'EOD'
<script>
  $(document).ready(function() {
    $('.prettyprintBox').each(function(i, el) {
      $prettyprint = $(el).find('.prettyprint');
      // TODO: the '- 4' is for padding, totally style dependent and should be made dynamic
     });
  });
</script>
EOD;

    $file_path = "$home/.conveyor/runtime/".$matches[1];
    require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/code-to-html.php");
    ob_start();
    code_to_html($file_path);
    $contents = ob_get_clean();

}
else { 
    # We expect request to be vetted, so we can assume that if not
    # found as implementation artifact, then must be in the knowledge
    # base
    $doc_path = "$home/.conveyor/data/conveyor-core/documentation/$req_item"
    $contents = file_get_contents($doc_path);
    // if it's starts with '<?php', treat it as a script and be done
    if (preg_match('/^<\?php/', $contents)) {
	ob_start();
	require $doc_path;
	$contents = ob_get_clean();
    }
    // else, the $contents are just the file, which is already read
}
?>
