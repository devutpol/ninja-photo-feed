<?php

namespace NinjaPhotoFeed\App\Http\Controllers;

use NinjaPhotoFeed\Framework\Http\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected function dispatchAction($npfeed_action, array $npfeed_args = [], $npfeed_error_message = '')
    {
        if (false === has_action($npfeed_action)) {
            return $this->sendError([
                'message' => $npfeed_error_message ?: __('No handler is registered for this request.', 'ninja-photo-feed')
            ], 423);
        }

        do_action_ref_array($npfeed_action, $npfeed_args);
    }

    /**
     * Decode a JSON request parameter into an array. Don't unslash around this call:
     * request values are already unslashed, so another pass strips the user's backslashes.
     *
     * @param mixed $npfeed_value Raw request parameter.
     * @return array|null Null when the value is absent or is not a JSON array.
     */
    protected function decodeJsonParam($npfeed_value)
    {
        if (is_array($npfeed_value)) {
            return $npfeed_value;
        }

        if (!is_string($npfeed_value) || '' === trim($npfeed_value)) {
            return null;
        }

        $npfeed_decoded = json_decode($npfeed_value, true);

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($npfeed_decoded)) {
            return null;
        }

        return $npfeed_decoded;
    }

    /**
     * @return \WP_REST_Response
     */
    protected function invalidJsonPayload()
    {
        return $this->sendError([
            'message' => __('The request data could not be read. Please try again.', 'ninja-photo-feed')
        ], 422);
    }
}
