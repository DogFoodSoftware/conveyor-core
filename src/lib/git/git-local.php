<?php
function branch_create_local($repo_path, $branch_name) {
    $output = array(); // throw away, but needed to extract return value from exec()
    $orig_working_dir = getcwd();
    chdir($repo_path);
    exec("git show-ref --verify --quiet 'refs/heads/$branch_name'", $output, $retval);
    if ($retval == 0) {
        chdir($orig_working_dir);
        final_result_bad_request("Branch '$branch_name' already exists in repo file://{$repo_path}.");
    }
    exec("git branch $BRANCH_NAME", $output, $retval);
    chdir($orig_working_dir);
    if ($retval != 0) {
        final_result_system_error("Could not create branch in repo file://{$repo_path}.");
    }
    // push_array($undo_stack, "DELETE_BRANCH /repos/$repo_path?branch=$branch_name");
}
?>