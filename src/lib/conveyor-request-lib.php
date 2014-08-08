<?php /**
<div class="p">
  A convenenience 'library' that "does the stuff" <em>most</em> REST service
  scripts require. To some extent, the 

, such as calling <code>start_session()</code> and
  determining authentication status and includes functions for stuff that
  <em>many</em> scripts will require, such as opening a DB connection. In some
  cases, the script will run setup code that isn't actually necessary. In many
  cases, it will define functions which are never called. This is understood
  and deemed acceptable because it means it's easier to write REST service
  scripts by reducing the details developers have to remember. Any
  particularly costly logic should be removed to it's own library which can be
  included on an as-needed basis.
</div>
<div id="Implementation" class="blurbSummary">
  <div class="blurbTitle">Implementation</div>
  <div class="description">
*/?>
<?php
/**
   <div class="subHeader">Ubiquitous Accept Processing</div>
   <div class="p">
     It's boring, but every single Kibbles call always needs to know what's
     being asked for. Nine times out of ten, REST service scripts can rely on
     the standard analysis of the <code>$_SERVER['REQUEST_METHOD']</code> and
     <code>$_SERVER['HTTP_ACCEPT'])</code> to handle the necessary
     processing. If the standard processing is not acceptable, then those
     scripts must implement their own processing. They should still try and
     use as much of the <a
     href="/documentation/kibbles/src/lib/accept-processing-lib.php">accept-processing-lib.php</a>
     as feasible and will have to manually implement the remainder of the
     scaffold setup themselves.
   </div>
 */
require_once('/home/user/playground/dogfoodsoftware.com/kibbles/runnable/lib/accept-processing-lib.php');
process_accept_header();
/**
   <div class="p">
     Next, we pre-emptively include the appropriate 'interface' or 'data'
     response support script. These libraries are pretty thin, and should work
     in most cases. When something else is needed, scripts are free to take
     another approach,<span class="note">E.g., instead of using the <a
     href="/documentation/kibbles/src/lib/data-response-lib.php#final-result-helper-methods">final
     result helper methods</a> and the automatetd PHP-based JSON encoding of
     results, a script may <code>echo</code> the JSON directly. In these
     cases, the overhead of including the relatively small
     <code>data-response-lib.php</code> or
     <code>interface-response-lib.php</code> is considered acceptable in order
     to simplify script structure.
   </div>
   <div class="p">
     If the client will not accept HTML or JSON content the current default
     handling is to stop execution and respond with a content-less <code>HTTP
     406 Not Acceptable<code> status response. Any given script may override
     this, in which case they'll be on their own regarding response
     generation.
   </div>
 */
if (respond_in_html())
    require_once('/home/user/playground/dogfoodsoftware.com/kibbles/runnable/lib/interface-response-lib.php');
else if (respond_in_json())
    require_once('/home/user/playground/dogfoodsoftware.com/kibbles/runnable/lib/data-response-lib.php');
/**
   <div class="p">
     If the script defines an <code>$access_url</code> or sets
     <code>$require_login = TRUE</code>, then we will need to retrieve the
     authenticated user. If authentication cannot be established, then we we
     respond with a <code>HTTP 400 Bad request</code> and appropriate contents
     as indicated by the request's accept header.
   </div>
   <div class="p">
     Once authentication is established we check authorization rights as well
     if requested. Many services provide public services for the most part,
     with secured items a less commomn option. These will generally handle the
     authorization on their own. For those services which are essentially
     secure and can only be called with sufficient privileges&mdash;most
     <code>PUT</code>/<code>POST</code>/<code>DELETE</code> requests&mdash;the
     script need merely define the <code>$auth_target</code> and possibly the
     <code>$auth_operation</code> as well. If <code>$auth_operation</code> is
     not defined, it is inferred from the HTTP request
     method. <code>$auth_operation</code> is ignored if
     <code>$auth_target</code> is null.
   </div>
   <div class="p" data-todo="Implement automated authorization.">
     The actual authentication logic is not yet implemented. We need to finish
     conversion of the authentication code imported from the previous
     iteration and then call it here.
   </div>
 */
/*
if (isset($auth_target) || (isset($require_login) && $require_login === TRUE)) {
    // At this point, we know we have to determine authentication.
    require_once('/home/user/playground/kibbles-user-acacount/runnable/lib/authentication-credentials-lib.php');
    global $credentials;
    $credentials = Credentials::get_authenticated_credentials();
    if ($credentials == null)
	final_result_bad_request();

    // and maybe we need to check the authorization as well
    if (isset($auth_target)) {
	/**
	   <div class="subHeader">Determining <code>$auth_operation</code></div>
	   <div class="p">
	     If no explicit <code>$auth_opertainon</code> is defined, then we
	     attempt to infer the authorization from the request method. If
	     the request method is not understood, then the client is deemed
	     to have made an 'invalid request'.
	   </div>
	 * /
	if (!isset($auth_operation)) {
	    switch ($_SERVER['HTTP_METHOD']) {
	    case "GET":
		$auth_operation = '/read'; break;
	    case "PUT":
		$auth_operation = '/create'; break;
	    case "POST":
		$auth_operation = '/update'; break;
	    case "DELETE":
		$auth_operation = '/delete'; break;
	    default:
		final_result_bad_request(); exit(0);
	    }
	}

	final_result_internal_error("Automated authorization check not yet implemented.");
    }
}
*/
function get_item_id() {
    global $argv, $argc;
    if (PHP_SAPI == "cli") {
        return $argv[1]; // The 1st (index-0) argument is always the script name.
    }
    else {
        return preg_replace('|/[^/]+(/.+)\?.*$/', '$1', $_SERVER['REQUEST_URI']);
    }
}

function get_parameters() {
    // It's important to globalize '$parameters' rather than return
    // because the parameters may be modified and then passed along to
    // other scripts which are also handlers. In that case, if the
    // chained-script is unaware of the previous modifications and
    // goes back to the source, it will recreate the unmodified
    // parameters. For convenience, we also return the global.
    global $parameters;

    # In the case of chained request handlers, the parameters are
    # already be set.
    if ($parameters != null) {
        return $parameters;
    }

    if (PHP_SAPI == "cli") {
        $parameters = array();
        // The 1st (index-0) argument is always the script name. The
        // parameters may start at 1 or 2; so we test to see if the
        // first item contains a '='. If so, it's a parameter. If not,
        // it's an item ID and we move our parameter search ahead to
        // the second position. Notice this works fine for instances
        // where there are no parameters.
        for ($i = ($argc > 1 && strpos($argv[1], "=")) ? 1 : 2;
             $i < $argc; $i+= 1) {
            $bits = preg_split("/=/", $argv[$i]);
            # TODO: check if count($bits) > 2
            if (preg_match('/\[\]$/', $bits[0])) {
                $p_name = substr($bits[0], 0, strlen($bits[0]) - 2);
                if (!isset($parameters[$p_name])) {
                    $parameters[$p_name] = array();
                }
                array_push($parameters[$p_name], $bits[1]);
            }
            else {
                $parameters[$bits[0]] = $parameters[$bits[1]];
            }
        }
        
        return $parameters;
    }
    else {
        return $_REQUEST;
    }
}

function generic_resource_id_check($id) {
    if (trim($id) == "") {
        final_result_bad_request("Resource ID cannot be blank.");
    }
    elseif (strpos($id, ' ') === FALSE) {
        final_result_bad_request("Resource ID cannot contain spaces.");
    }
    elseif (strpos($id, '_') === FALSE) {
        final_result_bad_request("Resource IDs cannot contain '_'. Use '-'.");
    }
}
/**
   <div class="subHeader">What's Not Here</div>
   <div class="p">
     The basic scaffolding does not include DB support, which must be loaded on it's own with:
<pre><code>
require_once('/home/user/playground/kibbles/runnable/lib/db-connect-lib.php');
</code></pre>
   </div>
   <div class="p">
     The credentials library is only required if <code>$login_required</code>
     or <code>$auth_target</code> is set to a non-null value. Scripts that
     need don't require login / automated-authorization, but do need to deal
     with credentials, must include the library themselves:
<pre><code>
require_once('/home/user/playground/kibbles-user-accounts/runnable/lib/authentication-credentials-lib.php');
</code></pre>
   </div>
 */
?>
