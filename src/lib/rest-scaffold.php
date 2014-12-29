<?php
/**
 * <div class="p">

 *   In general, we support including the scaffolding multiple
 *   times. In practice, there are uses we have in mind. First, chaining handlers where one handler calls another directly. In this case, there's no need to do further processing as none of the values should ever be changed. Second, it enables us to support both direct invocation of the scripts by Apache<span class="note">Direct invocation as opposed to using a PHP controller. We assume that in the initial implementation, the Apache-as-controller route is faster.</span> whilel also allowing the CLI script to set the values as appropriate there. In other words, the default behavior is to extract the data from 

 * </div>
 */
if (!isset($req_resource)) {

// First we process the accept header.
if ($req_accept == null &&
	'GET' == $_SERVER['REQUEST_METHOD'])
	setup_for_get();
    else if ($_kibbles_supported_response_types == null)
	setup_for_not_get();

    if ($_kibbles_response_type == null) {
	if ($_SERVER['HTTP_ACCEPT'] == false) {
	    // then the client will accept anything and so we default to the
	    // service preference
	    $_kibbles_response_type = $_kibbles_supported_response_types[0];
	    return $_kibbles_response_type;
	}
	// else continue on and process the non-empty header
	/**
	   <div class="p">
	   First order of business is to sort the MIME types which the client
	   will accept according to the advertised preferences. <a
	   href="http://www.gethifi.com/blog/browser-rest-http-accept-headers">Recall</a>
	   the types are separated by ',' and preference is indicated by the
	   'q' parameter.
	   </div>
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
	   <div class="p">
	   The client preferences have now been established. We now look
	   through the services supported formats to find a match.
	   </div>
	*/
	ksort($client_preferences, SORT_NUMERIC);
	$client_preferences = array_reverse($client_preferences);
	foreach ($client_preferences as $q => $preference_set) {
	    foreach ($preference_set as $client_preference) {
		list($preferred_type, $preferred_subtype) =
		    explode('/', $client_preference);

		$deferred_type = null;
		$double_deferred_type = null;
		foreach ($_kibbles_supported_response_types as $supported_media) {
		    list($supported_type, $supported_subtype) = 
			explode('/', $supported_media);
		    
		    if ($preferred_type == $supported_type &&
			$preferred_subtype == $supported_subtype) {
			$_kibbles_response_type = $supported_media;
			// we are done
			return $_kibbles_response_type;
		    }
		    else if ($preferred_type == $supported_type &&
			     $preferred_subtype == '*' &&
			     $deferred_type == null)
			$deferred_type = $supported_media;
		    else if ($preferred_type == '*' &&
			     $double_deferred_type == null)
			$double_deferred_type = $supported_media;
		}
	    }
	    // if we get here, then we didn't find an exact match, but may
	    // have found a deferred match
	    if ($deferred_type != null)
		$_kibbles_response_type = $deferred_type;
	    else if ($double_deferred_type != null)
		$_kibbles_response_type = $double_deferred_type;
	}

	if ($_kibbles_response_type == null) {
	    // if we get here and there is still no match then we cannot
	    // fulfill the request and we halt all further processing
	    header("HTTP/1.0 406 Not Acceptable");
	    exit();
	}
    }
    // else $_kibbles_response_type is already set and there's nothing to do

?>