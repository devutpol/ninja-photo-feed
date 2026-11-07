<?php

namespace NinjaPhotoFeed\App\Http\Controllers\Platforms\Feeds;

use NinjaPhotoFeed\App\Http\Controllers\Controller;
use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\Framework\Http\Request\Request;

class ConfigsController extends Controller
{
    public function index(Request $request)
    {
        $platform = $request->get('platform');
        do_action('ninjaphotofeed/get_verification_configs_' . $platform);
    }

    public function store(Request $request)
    {
        $settings = $this->decodeJsonParam($request->get('settings'));

        if (null === $settings) {
            return $this->invalidJsonPayload();
        }

        $platform = $request->get('platform');
        do_action('ninjaphotofeed/verify_credential_' . $platform, $settings);
    }

    public function prepare(Request $request)
    {
        $npfeed_sanitize_map = [
            'platform'   => 'sanitize_key',
            'provider'   => 'sanitize_key',
            'flow'       => 'sanitize_key',
            'return_url' => 'esc_url_raw',
            'source'     => 'sanitize_text_field'
        ];
        $npfeed_request_data = $request->all();
        $npfeed_settings = npfeed_backend_sanitizer($npfeed_request_data, $npfeed_sanitize_map);
        $npfeed_platform = Arr::get($npfeed_settings, 'platform', '');

        if (!$npfeed_platform) {
            return $this->sendError([
                'message' => __('Missing platform data.', 'ninja-photo-feed')
            ], 423);
        }

        return $this->dispatchAction(
            'ninjaphotofeed/prepare_feed_auth_' . $npfeed_platform,
            [$npfeed_settings],
            __('No authentication handler is registered for this platform.', 'ninja-photo-feed')
        );
    }

    public function consume(Request $request)
    {
        $npfeed_sanitize_map = [
            'platform'  => 'sanitize_key',
            'result_id' => 'npfeed_sanitize_opaque_token',
            'flow_id'   => 'npfeed_sanitize_opaque_token',
            'payload'   => 'npfeed_sanitize_opaque_token'
        ];
        $npfeed_request_data = $request->all();
        $npfeed_settings = npfeed_backend_sanitizer($npfeed_request_data, $npfeed_sanitize_map);
        $npfeed_platform = Arr::get($npfeed_settings, 'platform', '');

        if (!$npfeed_platform) {
            return $this->sendError([
                'message' => __('Missing platform data.', 'ninja-photo-feed')
            ], 423);
        }

        return $this->dispatchAction(
            'ninjaphotofeed/consume_feed_auth_' . $npfeed_platform,
            [$npfeed_settings],
            __('No authentication handler is registered for this platform.', 'ninja-photo-feed')
        );
    }

    public function delete(Request $request)
    {
        $platform = $request->get('platform');
        $userId   = $request->get('user_id'); //for instagram only
        do_action('ninjaphotofeed/clear_verification_configs_' . $platform, $userId);
    }
}
