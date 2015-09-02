<?php /**
<div class="p">
  Here we define library method <code>code2html</code> to process
  documentation requests for 'src' files. This will display any file
  as a series of documentation blocks interspersed with the literal
  code blocks, nicely formatted and resizable.
</div>
*/
function code2html($file_path) {
    echo '<div class="grid_12">'."\n";
/**
<div class="p">
  The basic idea is to iterate over each line, looking for the
  documentation opening and closing markers. Documentation is
  extracted as HTML or plain text. Non-documentation is placed into
  <code>prettyprint</code> blocks processed by <a
  href="https://code.google.com/p/google-code-prettify/">Google's
  pretty print JS</a>.
</div>
    */
    $minExpandSize = 5;
    $inDoc = false; // track state
    $show_php = false;
    $doc_style = 'unknown'; // can be 'unknown', 'plain', or 'html'
    $i = 0; // count lines
    $codeCount = 0;
    $currCodeId = null;
    $contents = file_get_contents($file_path);
    $lines = preg_split('/((\r?\n)|(\r\n?))/', $contents);
    /**
       <div class="p">
       The code blocks are analyzed for length and those longer that
       <code>$minExpandSize</code> are annotated with an expandable control allowing
       viewers to expand the code block size. The <code>code_close()</code> function
       makes the determination.
       </div>
    */
    function code_close($codeCount, $currCodeId, $minExpandSize) {
        echo '</pre></div>'."\n";
        // if the $codeCount is greater than 6, then apply the 'long' modifier,
        // which sets the initial height
        if ($codeCount > $minExpandSize) {
/**
<div class="p">
  The element ID tracked by <code>$currCodeId</code> is the
  <code>pre</code> within the <code>.prettyPrintBox</code>. It's the
  <code>.prettyPrintBox</code> we actually target with the resizable
  (<code>parent()</code>). We set the <code>height</code> to keep the
  longer code block from breaking up the narrative documentation, and
  the <code>maxHeight</code> to limit the resizable to 'as much as
  needed'. The hard coded '14' is good enough for now, but could be
  made dynamic. Finally, we have to set the
  <code>pre.prettyprint</code> <code>height: '100%'</code> so that it
  will 'fill up' the containing box as it expands.
</div>
*/
            echo "<script>$(window).load(function() {
  var \$refEl = $('#".$currCodeId." .linenums');

  $('#".$currCodeId."')
    .parent()
    .resizable({handles: 's'})
    .css({height: '5em',overflow:'hidden',maxHeight: (Math.round(\$refEl.height()) + 14) + 'px'})
    .find('.prettyprint')
    .css({height:'100%',overflow:'auto'});
});</script>";
        }
    }
      
    function output_code($line, $show_php) {
        if ($show_php) {
            echo htmlspecialchars("<?php\n");
        }
        echo htmlspecialchars($line)."\n";

        return false;
    }

    foreach ($lines as $line) {
	// first, process the state changes
	if (preg_match('=^\s*(<\?php\s+)?(#\s*)?/\*\*\s*$=', $line)) {
        // If we start with '<?php', nice to hide, but we do want to show it at start of implementation.
        if (preg_match('=^\s*<\?php\s+=', $line)) {
            $show_php = true;
        }
	    $inDoc = true;
	    $doc_style = 'unknown';
	    if ($i > 0) {
		code_close($codeCount, $currCodeId, $minExpandSize);
		$codeCount = 0;
	    }
	}
	else if ($inDoc && preg_match('=^#?\s*\*{1,}/(\s+\?>)?($|.+)=', $line)) { // TODO: this will swallow up anything else on the line
	    // we only want to open a code block if there's any code left... in
	    // other words, check for last line or last line blank (this isn't
	    // foolproof but allows us to work around the issue for now)
	    if ($i + 1 < count($lines) && ($i + 2 < count($lines) || strlen(trim($lines[$i + 1])) > 0)) {
		$currCodeId = 'codeBlock'.$i;
		echo '<div class="prettyprintBox resizable-block-widget"><pre id="'.$currCodeId.'" class="prettyprint linenums:'.($i + ($show_php ? 1 : 2)).'">'."\n";
	    }
	    $inDoc = false;
	    $codeCount = -1; // start at -1 because we don't want to count this line, but '$codeCount' will be incremented
	}
	else if ($i == 0) { // if we don't start with the special <?php /**, then the first line is treated as code
	    $currCodeId = 'codeBlock'.$i;
	    echo '<div id="'.$currCodeId.'" class="prettyprintBox resizable-block-widget"><pre class="prettyprint linenums:'.($i + 1).'">'."\n";
	    $codeCount = -1; // start at -1 because we don't want to count this line, but '$codeCount' will be incremented
	    $show_php = output_code($line, $show_php);
	}
	// Otherwise, process the line according to the state. In doc-mode, remove
	// leading stars and spaces to make compatibla with Java-style docs set-off.
	else if ($inDoc) {
	    /**
	     * <div class="p">
	     * We try to guess whether the documentatino is embedded HTML or
	     * 'plain' text. If HTML, we expect proper escaping. If Plain, we do
	     * the escraping.
	     * </div>
	     */
	    if ($doc_style == 'unknown' && preg_match('/\s*\*?\s*</', $line))
		$doc_style = 'html';
	    else if ($doc_style == 'unknown' && preg_match('/\s*\*?\s*[^<]/', $line)) {
		$doc_style = 'plain';
	    }
	    
	    // strip off leading '*' to make compatible with JavaDoc style
	    $real_line = preg_replace('/^#?\s*\*?\s*/', '', $line);
	    
	    if ($doc_style == 'plain')
            $show_php = output_code($real_line, $show_php);
	    else echo $real_line."\n";
	}
	else {// in code
		$show_php = output_code($line, $show_php);
	}
	$i += 1;
	if (!$inDoc) $codeCount += 1;
    }
    /**
       <div class="p">
       Finally, we check whether we need to close the final code block and spit out
       the page closing stuff.
       </div>
    */
    if (!$inDoc) {
	code_close($codeCount, $currCodeId, $minExpandSize);
	$codeCount = 0;
    }
    echo '
</div><!-- .blurbSummary .grid_12 -->
';
  }
?>
