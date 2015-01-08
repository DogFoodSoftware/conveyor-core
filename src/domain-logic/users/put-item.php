<?php 
/**
 * <div class="p">
 *   Updates or inserts a <code>/users</code> item.
 * </div>
 * <div id="Implementation" data-perspective="implementation" class="blurbSummary grid_12">
 * <div class="blurbTitle">Implementation</div>
 */
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/response-lib.php");
if ('new' == $req_item_id) {
    $response->check_required_field('profile');
    $response->check_required_field('profile.nickname');
    $response->check_required_field('primary-email');
}
if (!$response->check_request_ok()) {
    return;
}
require("$home/.conveyor/runtime/dogfoodsoftware.com/conveyor-core/runnable/lib/authorization-lib.php");
if (!$response->check_request_ok()) {
    return;
}

require("$home/.conveyor/data/dogfoodsoftware.com/conveyor-composer/vendor/autoload.php");
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;

$client = new PhpOrient('localhost', 2424);
try {
    $cred_file = file_get_contents("$home/.conveyor/data/dogfoodsoftware.com/conveyor-core/odb-credentials");
    preg_match('/ODB_USERNAME="([^"]+)"\s*ODB_PASSWORD="([^"]+)"/m', $cred_file, $matches);
    $db_username = $matches[1];
    $db_password = $matches[2];
    # $client->username = $db_username;
    # $client->password = $db_password;
    $db_data = $client->dbOpen('conveyor', $db_username, $db_password);
    
    echo "HA";
    if ('new' == $req_item_id) {
        $user_data = $req_data;
        $profile_data = $user_data['profile'];

        // https://github.com/DogFoodSoftware/conveyor-core/issues/10   
        // $tx1 = $client->getTransactionStatement();
        // $tx1->begin();
        try {
            $new_profile_rec = (new Record())->setOData($profile_data)->setOClass('Profile')->setRid(new ID($db_data['id']));
            $create_profile = $client->recordCreate($new_profile_rec);
            var_dump($create_profile);
            $new_profile_rec = $create_profile->record;
            $user_data['profile'] = $new_profile_rec->getRid()->__toString();
            echo "\n\n";
            var_dump($user_data);
            // $tx2->attach($create_profile);
            // $tx2->commit();

            $new_user_rec = (new Record())->setOData($user_data)->setOClass('User')->setRid(new ID($db_data['id']));

            $create_user = $client->recordCreate($new_user_rec);
            echo "\n\n";
            var_dump($create_user);
            // $tx1->attach($create_user);
            // $tx1->commit();

            $response->created('User created.', $new_user_rec->getOData());
        }
        catch (Exception $e) {
            echo "Foo"
            $tx->rollback();
            
            $response->server_error($e->getMessage());
        }
    }
    else {
        $response->not_implemented();
    }
}
finally {
    $client->dbClose();
}
?>
<?php /**
</div><!-- .blurbSummary#Implementation -->
*/ ?>
