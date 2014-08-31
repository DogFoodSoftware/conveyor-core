<?php
function branch_create($repo_url, $branch_name) {
    $repo_url = _regularize_repo_url($repo_url);

    if (preg_match('|^file://|', $repo_url)) {
        require_once(dirname(__FILE__).'/git/git-local.php');
        branch_create_local($repo_url, $branch_name);
    }
    elseif (preg_match('|https?://github.com|', $repo_url)) {
        require_once(dirname(__FILE__).'/git/git-github.php');
        branch_create_github($repo_url, $branch_name);
    }
}

function branch_checkout($repo_url, $branch_name) {
    $repo_url = _regularize_repo_url($repo_url);

    # We can count on the protocol being explicit after the
    # regularization.
    if (preg_match('|^file://|', $repo_url)) {
        require_once(dirname(__FILE__).'/git/git-local.php');
        branch_checkout_local($repo_url, $branch_name);
    }
    else {
        final_result_bad_request("Operation 'checkout' incompatible with remote repositories.");
    }
}

function _regularize_repo_url($repo_url) {
    # At the time of writing, this 'file://' protocol will later be
    # tripped off. But we want to keep the code simple while also
    # allowing the user to cheat a little when the meaning is clear. So
    # we first regularize, and then let the particular sub-handlers
    # trust they're getting a common input.
    if (!preg_match('|^\w+://|', $repo_url)) {
        $repo_url = 'file://'.$repo_url;
    }

    return $repo_url;
}
?>