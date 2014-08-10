<?php
function branch_create_github($repo, $branch) {
    // For the time being, we only support manipulating a git repo
    // which is the origin of the current working repo. Future
    // versions may be more flexible.
    $working_origin = exec('git config --get remote.origin.url', $output = array(), $retval);
    if ($retval != 0) {
        final_result_bad_request("Could not determine local working repo; cannot create remote branch.");
    }
    if (empty($working_origin)) {
        final_result_bad_request("Current working directory does not appear to be within a git repository; cannot create remote branch outside of clone.");
    }
    if ($working_origin != $repo) {
        final_result_bad_request("Current working repository origin ($working_origin) does not match target repo ($repo).");
    }

    # Now check branch status, local and remote.
    requir_once('git-local.php');
    

    require_once('/home/user/playground/dogfoodsoftware.com/third-party/pest/runnable/PestJSON.php');
    $pest = new PestJSON("https://api.github.com");

    
}
?>