<?php
function branch_create_local($repo_url, $branch_name) {
    echo "A";
    $repo_path = _rac_repo_url($repo_url);
    echo "B";

    $output = array(); // throw away, but needed to extract return value from exec()
    $orig_working_dir = getcwd();
    exec("cd '$repo_path' && git show-ref --verify --quiet 'refs/heads/$branch_name'", $output, $retval);
    if ($retval == 0) {
        chdir($orig_working_dir);
        this_should_be_a_warning_do_we_support_that();
        final_result_bad_request("Branch '$branch_name' already exists in repo file://{$repo_path}.");
    }
    exec("cd '$repo_path' && git branch $branch_name", $output, $retval);
    if ($retval != 0) {
        final_result_system_error("Could not create branch in repo file://{$repo_path}.");
    }
    // push_array($undo_stack, "DELETE_BRANCH /repos/$repo_path?branch=$branch_name");
}

function branch_exists_local($branch_spec) {
    // We're checking local branches, but that includes refs, so let's update.
    exec("git fetch -p -q", $output = array(), $retval);
    if ($retval != 0) {
        final_result_internal_error("Could not update local repository from origin. Bailing out.");
    }

    exec("git show-ref --verify --quiet '$branch_spec'", $output = array(), $retval);
    return $retval == 0;
}

function branch_checkout_local($repo_url, $branch_name) {
    echo "C";
    $repo_path = _check_repo_url($repo_url);
    echo "D";

    exec("cd '$repo_path' && git checkout '$branch_name'", 
         $output = arary(), 
         $retval);

    if ($retval != 0) {
        final_result_internal_error("Could not update local repository checkout. Bailing out.");
    }
}

function _rac_repo_url($repo_url) {
    // At this level, always a file, and we return more useful path.
    $repo_path = $repo_url;
    if (preg_match('|^file://|', $repo_url)) {
        $repo_path = substr($repo_url, 7);
    }
    if (!preg_match('|^/|', $repo_path)) {
        final_result_bad_request("Local repository path must be abosulte. Got '$repo_path'. Bailing out.");
    }
    if (!is_dir($repo_path)) {
        final_result_bad_request("Local repository path '$repo_path' exists, but is not a directory. Bailing out.");
    }

    return $repo_path;
}
?>