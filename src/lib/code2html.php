<?php /**
<div class="p">
  Defines library method <code>code2html</code> to process
  documentation requests for code files. After processing,
  documentation sections, such as this, will appear part of the HTML
  page and code chunks between documentation will appear as syntax
  highlighted, numbered blocks.
</div>
<div class="p" data-todo="Link to an example.">
  To process a source code page, embedded HTML elements are extracted
  and copied directly to the output. The remainder of the source file
  (more or less non-comment source) is embedded in HTML elements
  suitable for processing by the 'prettify' JS library. This results
  in nicely formatted HTML output that interleaves the embedded HTML
  with 'prettified' source code. <span data-todo="Link to template
  docs or something.">The output is embedded in the standard header /
  footer template.</span>
</div>
<div class="subHeader">Code Resize Notes</div>
<div class="p">
  The resize handle must be within the resizable item. It seems there
  is some interaction with the PRE element, however, because the
  container won't resize even when the height is left unspecified
  which should, I believe, result in auto-resizing behavior.
</div>
<div class="p">
  One other (seeming) idosyncrosy to note: you can't place the drag-handle image
  inside the drag handle div, it has to be used as a background to the div;
  the problem is that if the image is it's own element in the div, you cannot
  actually grab where the image is, it hides the div and is not itself part of
  the drag handle. I.e., the drag handle element must not have any (visible)
  children.
</div>
*/
function code2html($file_path) {
    echo '<div class="grid_12">'."\n";
    /**
       <div class="p">
       It's now time to process the file itself. The basic idea is to iterate over
       the lines, looking for the documentation opening and closing
       markers. Documentation is extracted the rest is treated as code and placed
       into 'prettyprint' classes to be processed by
       <a href="https://code.google.com/p/google-code-prettify/">Google's pretty print JS</a>.
       </div>
       <div class="p">
       There are basically two states: '$inDoc' and '!$inDoc' == 'in code'. In
       order to keep the line numbers in the code blocks correct, we count the lines
       as we process them.
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
       <div class="p">
       The bit with <code>.wrap(...)</code> is to <a
       href="http://stackoverflow.com/questions/3858460/jquery-ui-resizable-with-scroll-bars">get
       <code>resizable()</code> working with scroll bars</a>.
       </div>
       <div class="p">
       The fiddly bit the <code>$refEl</code> as about getting the
       expansion 'container' properly set up. It seems like there
       should be a more elegant solution, but the
       <code>linenums</code> class within each
       <code>prettyprint</code> is 'almost' the right size, except we
       have to account for the border and padding of the containing
       elements. So, we create a hidden element pinned in position
       with the correct size. We can't just reference the containing
       elements as the container because we want to have our code
       blocks initially smaller so the reader focuses on the words.
       </div>
    */
    function code_close($codeCount, $currCodeId, $minExpandSize) {
        echo '</pre></div>'."\n";
        // if the $codeCount is greater than 6, then apply the 'long' modifier,
        // which sets the initial height
        if ($codeCount > $minExpandSize) {
            // What's all this? Well, it's actually pretty trick to
            // get jQuery UI 'resizable' to work with scrolling
            // content and get the proper containment. We follow the
            // solution below for the scroll problem. For the
            // containment, we reference the inner 'linenums' which
            // (seemingly) must be referenced as a selector. Using
            // searching with the 'this' context fails to give the
            // proper results.
            // 
            echo "<script>$('#".$currCodeId."').addClass('long').css('height', '5em');
$(document).ready(function() {
  var \$refEl = $('#".$currCodeId." .linenums');
  var refPos = \$refEl.position();
  var container = $('<div>&npbs;</div>')
      .attr('id', 'container".$currCodeId."')
      .height(\$refEl.height() + 10)
      .width((\$refEl.parent().parent().width() + 4) + 'px')
      .css({position: 'absolute',
            visibility: 'hidden', 
            top: refPos.top + 'px', 
            left: refPos.left + 'px'});

  $('#".$currCodeId."')
    .wrap('<div/>')
      .css({'overflow':'hidden'})
        .parent()
          .css({'display':'block',
                'overflow':'hidden',
                'height':function(){return $('.prettyprint',this).height();}

          })
          .resizable({handles:'s' })
          .css({maxHeight: \$refEl.height() + 14 + 'px'})
          .find('.prettyprint')
          .css({overflow:'auto',
                width:'100%',
                height:'100%'});
});
</script>";
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
