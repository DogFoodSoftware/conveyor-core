<?php
function domain_to_github_org($domain) {
    switch ($domain) {
    case 'liquid-labs.com':
        return "Liquid-Labs";
        break;
    case 'dogfoodsoftware.org':
        return "DogFoodSoftware";
        break;
    }
}
?>