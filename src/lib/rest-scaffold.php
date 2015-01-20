<?php
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/response-lib.php");
/**
 * <div class="p">
 *   The scaffold is intended to be called once from the initial REST
 *   protocol handler. After that point, any references to other
 *   resources will be made through domain logic calls.
 * </div>
 */

$req_path = $_SERVER['REQUEST_URI'];

// Process req_accept
if ('GET' == $_SERVER['REQUEST_METHOD']) {
    $_supported_response_types = array('text/html', 'application/json');
}
else {
    $_supported_response_types = array('application/json');
}

if ($_SERVER['HTTP_ACCEPT'] == false) {
    // then the client will accept anything and so we default to the
    // service preference
    $req_accept = $_supported_response_types[0];
}
else { // Parse the client HTTP_ACCEPT header to try and match.
/**
 * <div class="p">
 * First order of business is to sort the MIME types which the client
 * will accept according to the advertised preferences. <a
 * href="http://www.gethifi.com/blog/browser-rest-http-accept-headers">Recall</a>
 * the types are separated by ',' and preference is indicated by the
 * 'q' parameter.
 * </div>
 */
    $client_preferences = array();
    $accept_headers = explode(',',$_SERVER['HTTP_ACCEPT']);
	
    foreach ($accept_headers as $accept_header) {
        $q = '1.0';
        $matches = array();
        if (preg_match('/q=(\d*(\.\d*))/', $accept_header, $matches))
            $q = $matches[1];
	    
        if (!array_key_exists($q, $client_preferences))
            $client_preferences[$q] = array();
        // now remove the 'q' parameter
        $accept_header = preg_replace('/;q=\d*(\.\d*)?/', '', $accept_header);
        array_push($client_preferences[$q], $accept_header);
    }

/**
 * <div class="p">
 * The client preferences have now been established. We now look
 * through the services supported formats to find a match.
 * </div>
 */
    ksort($client_preferences, SORT_NUMERIC);
    $client_preferences = array_reverse($client_preferences);
    foreach ($client_preferences as $q => $preference_set) {
        foreach ($preference_set as $client_preference) {
            list($preferred_type, $preferred_subtype) =
                explode('/', $client_preference);

            $deferred_type = null;
            $double_deferred_type = null;
            foreach ($_supported_response_types as $supported_media) {
                list($supported_type, $supported_subtype) = 
                    explode('/', $supported_media);
		    
                if ($preferred_type == $supported_type &&
                    $preferred_subtype == $supported_subtype) {
                    $req_accept = $supported_media;
                    // we are done
                    break;
                }
                else if ($preferred_type == $supported_type
                         && $preferred_subtype == '*'
                         && $deferred_type == null)
                    $deferred_type = $supported_media;
                else if ($preferred_type == '*'
                         && $double_deferred_type == null)
                    $double_deferred_type = $supported_media;
            }
        }
        if (empty($req_accept)) {
            // if we get here, then we didn't find an exact match, but may
            // have found a deferred match
	    if ($deferred_type != null)
		$req_accept = $deferred_type;
	    else if ($double_deferred_type != null)
		$req_accept = $double_deferred_type;
	}
        
	if (empty($req_accept)) {
	    // if we get here and there is still no match then we cannot
	    // fulfill the request and we halt all further processing
	    header("HTTP/1.0 406 Not Acceptable");
	    exit();
	}
    }
}

# Now that we have the accept parameters, we can condition the
# $response object.
if ($req_accept == 'text/html') {
    $response->set_output(Response::OUTPUT_HTML);
    # require_once('/home/user/playground/dogfoodsoftware.com/kibbles/runnable/lib/interface-response-lib.php');
}
else { // (respond_in_json())
    $response->set_output(Response::OUTPUT_JSON);
    # require_once('/home/user/playground/dogfoodsoftware.com/kibbles/runnable/lib/data-response-lib.php');
}

// Process req_resource and req_item_id
if (!preg_match('|/?([\w-]+)(/$)?((/+[\w-]+)*)|', $req_path, $matches)) {
    $response->invalid_request("Could not parse '$req_path' as valid request.");
}
$req_resource = strtolower($matches[1]);
$req_item_id = count($matches) >= 3 ?
    substr($matches[3], 1) :
    null;
?>