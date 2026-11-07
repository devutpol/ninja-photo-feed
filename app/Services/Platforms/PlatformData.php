<?php
namespace NinjaPhotoFeed\App\Services\Platforms;

use NinjaPhotoFeed\App\Services\Platforms\Feeds\CacheHandler;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram\Common;
use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\App\Services\Platforms\ImageOptimizationHandler;

class PlatformData
{
    public $platform;

    private $npfeed_status_option_key = 'npfeed_statuses';

    private $npfeed_revoke_platform_data_option_key = '';

    private $npfeed_app_permission_revoked_status_key = 'npfeed_app_permission_revoked';

    private $npfeed_unused_feed_warning_email_sent_status_key = 'npfeed_unused_feed_warning_email_sent_status';

    public function __construct($platform)
    {
        $this->platform = $platform;
        $this->npfeed_revoke_platform_data_option_key = 'npfeed_'.$this->platform.'_revoke_platform_data';
        $this->registerHooks();
    }

    public function registerHooks()
    {
        $platform_hook = $this->platform.'_feed';

        add_action( 'ninjaphotofeed/'.$platform_hook.'_api_connect_response', [$this, 'handlePlatformDataOnApiResponse'], 10, 1 );
        add_action( 'ninjaphotofeed/before_display_'.$platform_hook, [$this, 'handleAppPermissionError'] );
        add_action( 'ninjaphotofeed/'.$platform_hook.'_app_permission_revoked', [$this, 'handleAppPermissionStatus']);

        add_action( 'ninjaphotofeed/before_display_'.$platform_hook, [$this, 'updateLastUsed'] );
        add_action( 'npfeed_scheduled_weekly', [$this, 'maybeDeleteOldData']);

        add_action( 'ninjaphotofeed/before_delete_old_data', [$this, 'beforeDeleteOldData'], 10, 1);
    }

    public function handlePlatformDataOnApiResponse( $response )
    {
        if ( is_wp_error( $response ) ) {
            return;
        }

        if ( empty( $response['response'] ) || empty( $response['response']['code'] ) ) {
            return;
        }

        if ( $response['response']['code'] !== 200 ) {
            return;
        }

        (new PlatformErrorManager($this->platform))->removeErrors('platform_data_deleted');

        $statuses_option = get_option( $this->npfeed_status_option_key, [] );

        if (empty( $statuses_option[$this->platform][ $this->npfeed_app_permission_revoked_status_key ] )) {
            return;
        }

        if($this->platform === 'instagram') {
            $api_response = json_decode( Arr::get($response, 'body'), true );
            $api_response_username = Arr::get($api_response, 'username', '');

            $connectedAccounts = (new Common())->findConnectedAccounts();

            $username = '';
            foreach ($connectedAccounts as $account){
                $username = Arr::get($account, 'username', '');
                if($username === $api_response_username){
                    break;
                }
            }

            if ( empty($username) ) {
                return;
            }

            if ( $username !== $api_response_username ) {
                return;
            }
        }

        $this->deleteRevokedAccount( $statuses_option );

        (new PlatformErrorManager($this->platform))->resetApiErrors();
    }

    public function handleAppPermissionError()
    {
        $npfeed_statuses = get_option( $this->npfeed_status_option_key, [] );

        if ( empty( $npfeed_statuses[$this->platform][ $this->npfeed_app_permission_revoked_status_key ] ) ) {
            return;
        }

        $revoke_platform_data = get_option( $this->npfeed_revoke_platform_data_option_key, [] );
        $revoke_platform_data_timestamp = Arr::get($revoke_platform_data, 'revoke_platform_data_timestamp', 0);

        if ( !$revoke_platform_data_timestamp ) {
            return;
        }

        $current_timestamp = current_time( 'timestamp', true );
        if ( $current_timestamp < $revoke_platform_data_timestamp ) {
            return;
        }

        $this->deletePlatformData();
        $this->deleteRevokedAccount( $npfeed_statuses );
        $error = __( 'An account lost access to the Ninja Photo Feed app: either an account admin removed the app, or its access token expired and could not be renewed. The account was not reconnected within the 7-day limit, and all Instagram account data was automatically deleted on your website due to data privacy rules.', 'ninja-photo-feed' );
        (new PlatformErrorManager($this->platform))->addError('platform_data_deleted', $error);
    }


    public function deleteDataByUser($connectedAccount)
    {
        $userId = Arr::get($connectedAccount, 'user_id', '');
        $has_npfeed_optimize_images_table = get_option( 'npfeed_optimize_images_table_status', false);

        if($has_npfeed_optimize_images_table){
            (new ImageOptimizationHandler($this->platform))->cleanData($connectedAccount);
        }

        (new PlatformErrorManager($this->platform))->resetApiErrors($userId);
    }

    public function deletePlatformData()
    {
        if($this->platform === 'instagram') {
            $connectedAccounts = (new Common())->findConnectedAccounts();
            foreach ($connectedAccounts as $connectedAccount) {
                $user_id = Arr::get($connectedAccount, 'user_id', '');
                $has_app_permission_error = Arr::get($connectedAccount, 'has_app_permission_error', false);

                $errors = (new PlatformErrorManager($this->platform))->getErrors($this->platform);
                $error_accounts = Arr::get($errors, 'accounts', []);

                if($has_app_permission_error && in_array($user_id, array_keys($error_accounts))){
                    $api_errors = Arr::get($error_accounts, $user_id.'.api');
                    if($this->isAppPermissionError($api_errors)){
                        $this->deleteDataByUser($connectedAccount);
                    }
                }
            }
        }
    }

    public function handleAppPermissionStatus()
    {
        $npfeed_statuses = get_option( $this->npfeed_status_option_key, [] );

        if ( isset( $npfeed_statuses[$this->platform]['npfeed_app_permission_revoked'] ) && true === $npfeed_statuses[$this->platform]['npfeed_app_permission_revoked'] ) {
            return;
        }

        $this->updateAppPermissionRevokedStatus( $npfeed_statuses, true );

        $current_timestamp              = current_time( 'timestamp', true );
        $revoke_platform_data_timestamp = strtotime( '+7 days', $current_timestamp );

        update_option( $this->npfeed_revoke_platform_data_option_key, [
            'revoke_platform_data_timestamp' => $revoke_platform_data_timestamp
        ] );
    }

    protected function updateAppPermissionRevokedStatus( $npfeed_statuses, $is_revoked )
    {
        if ( $is_revoked ) {
            $npfeed_statuses[$this->platform][ $this->npfeed_app_permission_revoked_status_key ] = true;
        } else {
            unset( $npfeed_statuses[$this->platform][ $this->npfeed_app_permission_revoked_status_key ] );
        }
        update_option( $this->npfeed_status_option_key, $npfeed_statuses );
    }

    public function deleteRevokedAccount( $statuses_option )
    {
        $this->updateAppPermissionRevokedStatus( $statuses_option, false );

        delete_option( $this->npfeed_revoke_platform_data_option_key );
    }

    public function getAllMetaPlatformConnectedAccounts()
    {
        $connectedIgAccounts = get_option('npfeed_instagram_verification_configs');

        $data = wp_parse_args(
            [
                'instagram' => array_keys(Arr::get($connectedIgAccounts, 'connected_accounts', [])),
            ]
        );

        return $data;
    }

    public function updateLastUsed($accounts = [])
    {
         if(empty($accounts)) return;

         $npfeed_statuses = get_option( $this->npfeed_status_option_key, []);
         $npfeed_statuses = empty($npfeed_statuses) ? [] : $npfeed_statuses;

         $validConnectedAccounts = $this->getAllMetaPlatformConnectedAccounts();

         foreach($accounts as $account_id) {
             if(isset($validConnectedAccounts[$this->platform]) && in_array($account_id, $validConnectedAccounts[$this->platform])) {
                 if (isset($npfeed_statuses[$this->platform]['last_used'][$account_id])) {
                     if ($npfeed_statuses[$this->platform]['last_used'][$account_id] < time() - 3600) {
                         $npfeed_statuses[$this->platform]['last_used'][$account_id] = time();
                     }
                 } else {
                     if (isset($npfeed_statuses[$this->platform]['last_used']) && gettype($npfeed_statuses[$this->platform]['last_used']) === 'integer') {
                         $npfeed_statuses[$this->platform]['last_used'] = [];
                     }
                     $npfeed_statuses[$this->platform]['last_used'][$account_id] = time();
                 }

                 update_option($this->npfeed_status_option_key, $npfeed_statuses);
             }
         }
         (new PlatformErrorManager($this->platform))->removeErrors('unused_feed');

        $unused_feed_warning_email_sent_status_key = Arr::get($npfeed_statuses, $this->platform.'.'.$this->npfeed_unused_feed_warning_email_sent_status_key);
        if($unused_feed_warning_email_sent_status_key){
            unset($npfeed_statuses[$this->platform][$this->npfeed_unused_feed_warning_email_sent_status_key]);
        }

        update_option($this->npfeed_status_option_key, $npfeed_statuses);
    }

    public function deleteLastUsedTime($userId)
    {
        $npfeed_statuses = get_option( $this->npfeed_status_option_key, []);
        $npfeed_statuses = empty($npfeed_statuses) ? [] : $npfeed_statuses;

        if(isset($npfeed_statuses[$this->platform]['last_used'][$userId])) {
            unset($npfeed_statuses[$this->platform]['last_used'][$userId]);
            update_option('npfeed_statuses', $npfeed_statuses );
        }
    }

    public function beforeDeleteOldData($user_id)
    {
        $npfeed_statuses = get_option( $this->npfeed_status_option_key, [] );
        $already_unused_feed_warning_email_sent = Arr::get($npfeed_statuses, $this->platform.'.'.$this->npfeed_unused_feed_warning_email_sent_status_key);
        if($already_unused_feed_warning_email_sent){
            return;
        }

        if( isset($npfeed_statuses[$this->platform]['last_used'][$user_id]) && $npfeed_statuses[$this->platform]['last_used'][$user_id] < time() - ( 53 * DAY_IN_SECONDS ) ){
            $error = __( 'Your Instagram Feed has not been viewed in the last 53 days. Due to meta data privacy rules, all data for this feed will be deleted in 7 days. To avoid automated data deletion, simply view the Instagram feed on your website within the next 7 days.', 'ninja-photo-feed' );
            (new PlatformErrorManager($this->platform))->addError('unused_feed', $error);

            $npfeed_statuses[$this->platform][$this->npfeed_unused_feed_warning_email_sent_status_key] = true;
            update_option($this->npfeed_status_option_key, $npfeed_statuses);
        }
    }

    public function maybeDeleteOldData()
    {
        $npfeed_statuses = get_option( $this->npfeed_status_option_key, [] );

        if( $this->platform === 'instagram' ) {
            $connectedAccounts = (new Common())->findConnectedAccounts();
            foreach ($connectedAccounts as $connectedAccount) {
                $user_id = Arr::get($connectedAccount, 'user_id', '');
                do_action( 'ninjaphotofeed/before_delete_old_data', $user_id );

                if( isset($npfeed_statuses[$this->platform]['last_used'][$user_id]) && $npfeed_statuses[$this->platform]['last_used'][$user_id] < time() - ( 60 * DAY_IN_SECONDS ) ) {
                    $this->deleteDataByUser($connectedAccount);
                }
            }
        }
    }

    public function isAppPermissionError($response)
    {
        $error_code    = (int) Arr::get($response, 'error.code', 0);
        $error_subcode = (int) Arr::get($response, 'error.error_subcode', 0);

        $error_codes_to_check = array(
            190,
        );

        $error_subcodes_to_check = array(
            458,
        );

        if (in_array( $error_code, $error_codes_to_check, true )) {
            if (str_contains(Arr::get($response, 'error.message'), 'user has not authorized application') || str_contains(Arr::get($response, 'error.message'), 'Error validating access token')) {
                return true;
            }
            return in_array( $error_subcode, $error_subcodes_to_check, true );
        }

        return false;
    }
}
