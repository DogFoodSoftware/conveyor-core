</div><!-- #main.container-fluid -->
</div><!-- #content -->
</div><!-- #nonFooter -->
<div class="clear"></div>
<div id="footer" class="text-center container-fluid">
  <div class="row">
    <div class="col-xs-12">
      <a href="legal.html">legal</a> | <a href="credits.html">credits</a>
    </div>
    <div class="col-xs-12">
      &copy;
    </div>
  </div>
</div><!--#footer -->

<!-- Bootstrap core JavaScript
     ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="/minify/?g=default-js&debug=1"></script>
<!-- 'Debug' mode. -->
<!-- script src="/minify/?g=default-js&debug=1"></script -->
<script src="optcon.js"></script>
<!-- from Bootstrap template
<script src="../../assets/js/docs.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug - - >
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
-->
<script>
$(document).ready(function(){
    $(".sticky").sticky({topSpacing:0});
  });
  prettyPrint();

// Cache selectors
var $topMenu = $("#navbar.scroll-aware");
if ($topMenu.length > 0) {
    var topMenuHeight = topMenu.outerHeight();
    // All list items
    var menuItems = topMenu.find("a");
    // Anchors corresponding to menu items
    var scrollItems = menuItems.map(function(){
        var item = $($(this).attr("href"));
        if (item.length) { return item; }
    });
    
    $(window).scroll(function(){
        // Positioning based on the top makes for less optimal results
        // with short sections. Better to run ahead a little bit 
        // sometimes than often trail behind.
        var eyeball_pos = $(this).scrollTop()+topMenuHeight + $(window).height()/10;

        var cur = scrollItems.map(function(){
            if ($(this).offset().top < eyeball_pos)
                return this;
        });
        
        cur = cur[cur.length-1];
        var id = cur && cur.length ? cur[0].id : "";
        menuItems.removeClass("active").filter("[href=#"+id+"]").addClass("active");
    });
}
</script>
<script src="/jquery.superslides.min.js"></script>
<script>
    $(document).ready(function(){
        /* Superslides is close and the closest we've found four our
         * particular use case, but we still have to adapt it.
         *
         * In Superslides primary use cases, content can cropped and
         * stretched within. It's a smart, magnifying window onto the
         * content.
         *
         * In our case, the content must be laid out and scrolled, but
         * sholud never be cropped or stretched. We basically want to
         * get "the biggest slide area we can, according to our
         * needs", but the content within has to be fully accessible
         * regardless of the dimensions.
         *
         * To do this, we augment the Superslides logic as follows:



        /* In our style, we override the superslides default so we can
        * gauge the size of the contained content; instead of *
        un-displayed, we just hide. Now, we have to show. */
        var max_height = 0;
        $('#slides .slides-container > li').each(function() {
            if ($(this).outerHeight() > max_height) {
                max_height = $(this).outerHeight();
            }
        });
        $('#slides .slides-container > li')
            .css('position', '')
            .css('top', '')
            .css('left','');
        $('#slides .slides-container').parent()
            .css('height', (max_height) + "px");
        $(document).on('init.slides', function() {
            $('#slides').css('visibility', 'visible');
        });
        $('#slides').superslides({'inherit_height_from': '#Overview',
                'inherit_width_from': '#Overview',
                'hashchange': true,
                'scrollable': true});

                });
</script>
</body>
</html>

