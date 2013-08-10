<?php 
/**
 * <div id="progenitor-reference" class="p">
 *   This file derived from template
 *   <code>~/conveyor/kdata/documentation/ref/code-templates/php-get-services.php</code>
 *   last udptaed 2013-08-10 16:01 GMT.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 *  <div class="blurbTitle">Implementation</div>
 * <div class="subHeader"><span>Initial Setup and Request Processing</span></div>
 * <div class="p">
 *  Standard <code>GET</code> handling starts with processing the HTTP
 *  'Accept' headers. The script will bail out at
 *  <code>process_accept_header()</code> if the client requires a response
 *  format which we don't provide; the default is to accept JSON or HTML. In
 *  no 'Accept' headers are defined, <code>GET</code> requests default to
 *  HTML.
 * </div>
 * <div class="p">
 *   We declare the <code>$request_errors</code> as required by the standard
 *   request processing methods and extract the <code>$rest_id</code> from the
 *   request headers. If there are any request requirements effecting all
 *   formats, these are checked at the end of the this section.
 * </div>
 */
require('/home/user/playground/kibbles/runnable/lib/accept-processing-lib.php');
process_accept_header();

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
<code>php-get-services.php</code> template
EOT;

    require('/home/user/playground/kibbles/runnable/lib/interface-response-lib.php');
   echo_interface($interface_html, false);
}
// We would handle other special response formats.
else if (respond_in_json()) {
    if (count($request_errors) == 0) {
	$data = <<<EOT
php-get-services.php data
EOT;
	final_result_ok('Template code accessed.', $data);
    }
    else final_result_bad_request(join("\n", $request_errors));
}
else final_result_internal_error('Configured service accept mismatch.');
?>
<?php /**
</div><!-- .descirption -->
</div><!-- .blurbSummary#Implementation -->
*/ ?>

