<?php 
/**
<div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
<div class="blurbTitle">Implementation</div>
<div class="description">
 */
require('/home/user/playground/kibbles/runnable/lib/accept-processing-lib.php');
// it stops here with a 406 if the client ain't buying what we're selling
process_accept_header();

// TODO: we might use this, but not sure yet
$rest_id = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);

if (respond_in_json()) {
    // TODO
   
}
else { // respond in HTML
    require('/home/user/playground/kibbles/runnable/lib/interface-response-lib.php');
   $interface_id = preg_replace('/\/demos/', '', $rest_id);
   require_once('/home/user/playground/kibbles/runnable/lib/pest/Pest.php');

   $client = new Pest('http://127.0.0.1:42069');
   $response = $client->get($interface_id);

   echo_interface($response, false);
}
?>
<?php /**
</div><!-- .descirption -->
</div><!-- .blurbSummary#Implementation -->
*/ ?>
