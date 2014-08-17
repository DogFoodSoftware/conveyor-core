<?php
function branch_create($repo, $branch_name) {
    # Regularize $repo; if no protocol, assume it's a file.
    if (!preg_match('|^\w+://|', $repo)) {
        $repo = 'file://'.$repo;
    }

    if (preg_match('|^file://|', $repo)) {
        $path = substr($repo, 7, strlen($repo) - 7);

        # All files must be absolute.
        if (!preg_match('|^/|', $repo)) {
            final_result_bad_request("File repos must be absolute.");
        }

        require_once(dirname(__FILE__).'/git/git-local.php');
        branch_create_local($path, $branch_name);
    }
    elseif (preg_match('|https?://github.com|', $repo)) {
        require_once(dirname(__FILE__).'/git/git-github.php');
        branch_create_github($repo, $branch_name);
    }
}
?>