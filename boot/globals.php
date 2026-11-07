<?php
defined('ABSPATH') or die;

use NinjaPhotoFeed\Framework\Support\Arr;

/**
 ***** DO NOT CALL ANY FUNCTIONS DIRECTLY FROM THIS FILE ******
 *
 * This file will be loaded even before the framework is loaded
 * so the $app is not available here, only declare functions here.
 */


is_readable(__DIR__ . '/globals_dev.php') && include 'globals_dev.php';

/**
 * Get Ninja Photo Feed instance or other core modules
 *
 * @param string $key
 *
 * @return mixed
 */
if(!function_exists('npfeedNinjaPhotoFeed')) {
    function npfeedNinjaPhotoFeed($key = null)
    {
        return \NinjaPhotoFeed\App\App::make($key);
    }
}

if (! function_exists('npfeedDb')) {
    /**
     * @return \NinjaPhotoFeed\Framework\Database\Query\Builder
     */
    function npfeedDb()
    {
        return npfeedNinjaPhotoFeed('db');
    }
}

if(! function_exists('npfeed_backend_sanitizer')) {
    /**
     * Recursively sanitizes an array of inputs based on a sanitization map.
     *
     * @param array $inputs The array of data to sanitize (passed by reference).
     * @param array $sanitizeMap A map of 'key' => 'sanitization_function'. Supports dot notation for nested keys.
     * @param string $defaultMethod The default sanitization function to use if no specific rule is found.
     * @param string $keyPrefix Internal use only. The prefix for the current recursion level.
     * @return array The sanitized array, passed through a filter.
     */
    function npfeed_backend_sanitizer(&$inputs, $sanitizeMap = [], $defaultMethod = 'sanitize_text_field', $keyPrefix = '')
    {
        $originalValues = $inputs;

        foreach ($inputs as $key => &$value) {
            // Construct the full key for rule lookup (e.g., 'fields.author_company')
            $fullKey = empty($keyPrefix) ? $key : $keyPrefix . '.' . $key;

            if (is_array($value)) {
                // Check if there's a specific sanitization rule for this array
                $method = Arr::get($sanitizeMap, $fullKey);

                if ($method === 'npfeed_array_map_absint') {
                    $value = array_map('absint', $value);
                } elseif ($method === 'npfeed_array_map_sanitize_text_field') {
                    $value = array_map('sanitize_text_field', $value);
                } else {
                    // If no specific rule, recurse
                    npfeed_backend_sanitizer($value, $sanitizeMap, $defaultMethod, $fullKey);
                }
            } else {
                $method = Arr::get($sanitizeMap, $fullKey);

                if (is_callable($method)) {
                    $value = call_user_func($method, $value);
                } elseif (!empty($defaultMethod) && is_callable($defaultMethod)) {
                    $value = call_user_func($defaultMethod, $value);
                } elseif (!empty($defaultMethod) && function_exists($defaultMethod)) {
                    $value = $defaultMethod($value);
                }
            }
        }

        return apply_filters('ninjaphotofeed/backend_sanitized_values', $inputs, $originalValues);
    }
}

// Add global boolean sanitizer helper
if (! function_exists('npfeed_sanitize_boolean')) {
    /**
     * Normalize boolean-like values using WordPress REST helper when available.
     * Pass $as_string = false to get a real boolean true/false.
     *
     * @param mixed $value
     * @param bool $as_string
     * @return string|bool
     */
    function npfeed_sanitize_boolean($value, $as_string = true)
    {
        $bool = rest_sanitize_boolean($value);

        if ($as_string) {
            return $bool ? 'true' : 'false';
        }

        return (bool) $bool;
    }
}

// Add global opaque-token sanitizer helper
if (! function_exists('npfeed_sanitize_opaque_token')) {
    /**
     * Strict charset validation for opaque OAuth-bridge tokens (payload, result_id, flow_id).
     *
     * These are machine values, not human text — the payload is standard base64
     * (base64_encode of iv+ciphertext, i.e. A-Za-z0-9+/=), result_id is base64url,
     * and flow_id is a UUID4. Their contract is "matches this charset", not "is safe
     * HTML", so validate them for what they are instead of running an HTML sanitizer
     * over them. Anything outside the allowed set is rejected to an empty string so a
     * malformed value fails loudly downstream rather than being silently mangled.
     *
     * @param mixed $value
     * @return string sanitized value, or '' if it contains any disallowed character
     */
    function npfeed_sanitize_opaque_token($value)
    {
        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        // Union of standard base64 (+ /), base64url (- _), padding (=), and UUID (-).
        return preg_match('/^[A-Za-z0-9+\/=_-]+$/', $value) ? $value : '';
    }
}

// Add global recipients sanitizer helper
if (! function_exists('npfeed_sanitize_recipients')) {
    /**
     * Sanitize recipients input (comma-separated string or array) into a clean comma-separated string
     * or an array if $as_string is false. Uses sanitize_email.
     *
     * @param string|array $value
     * @param bool $as_string
     * @return string|array
     */
    function npfeed_sanitize_recipients($value, $as_string = true)
    {
        if (is_null($value) || $value === '') {
            return $as_string ? '' : [];
        }

        $items = is_array($value) ? $value : array_map('trim', explode(',', (string) $value));
        $clean = array();

        foreach ($items as $email) {
            if ($email === '') {
                continue;
            }
            // Use only sanitize_email as requested
            $s = sanitize_email($email);
            if (!empty($s)) {
                $clean[] = $s;
            }
        }

        if ($as_string) {
            return implode(',', $clean);
        }

        return $clean;
    }
}

if(! function_exists('npfeed_sanitize_css_selector')) {
    /**
     * Sanitizes a CSS selector, allowing only safe characters.
     * Strips anything that could be used to inject malicious code.
     */
    function npfeed_sanitize_css_selector($selector) {
        // Allow alphanumeric, spaces, and common CSS selectors/combinators . # : > + ~ [ ] - _
        return preg_replace('/[^a-zA-Z0-9\s\.#:>+\~\[\]\-\_\*\,\|\="]/', '', $selector);
    }
}

if(! function_exists('npfeed_sanitize_color')) {
    /**
     * Sanitizes a CSS color value.
     * Uses WordPress core for hex colors and a regex for other formats.
     */
    function npfeed_sanitize_color($color) {
        $color = trim($color);
        if (empty($color)) {
            return '';
        }

        // 1. Use WordPress core function for hex colors first.
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
            return sanitize_hex_color($color);
        }

        // 2. Allow rgba(), rgb(), hsla(), hsl()
        if (preg_match('/^(rgba?|hsla?)\(\s*(\d{1,3}%?\s*,\s*){2}\d{1,3}%?\s*(,\s*[0-9.]+)?\s*\)$/', $color, $npfeed_match)) {
            $npfeed_fn = strtolower($npfeed_match[1]);
            $npfeed_parts = array_map('trim', explode(',', trim(substr($color, strpos($color, '(') + 1), ' )')));
            $npfeed_alpha = count($npfeed_parts) === 4 ? array_pop($npfeed_parts) : null;

            // The pattern checks shape only, so rgb(999,0,0) and rgba(0,0,0,1.5) passed.
            $npfeed_limits = ($npfeed_fn === 'hsl' || $npfeed_fn === 'hsla') ? [360, 100, 100] : [255, 255, 255];
            foreach ($npfeed_parts as $npfeed_index => $npfeed_part) {
                $npfeed_limit = substr($npfeed_part, -1) === '%' ? 100 : $npfeed_limits[$npfeed_index];
                if ((float) rtrim($npfeed_part, '%') > $npfeed_limit) {
                    return '';
                }
            }

            if ($npfeed_alpha !== null && (float) $npfeed_alpha > 1) {
                return '';
            }

            return $color;
        }

        // 3. Allow common named colors (a subset for safety)
        $named_colors = ['inherit', 'transparent', 'currentcolor', 'black', 'white', 'red', 'green', 'blue', 'yellow', 'cyan', 'magenta'];
        if (in_array(strtolower($color), $named_colors)) {
            return $color;
        }

        return ''; // Return empty if no match
    }
}

if(! function_exists('npfeed_sanitize_css_unit')) {
    /**
     * Sanitizes a CSS unit value (e.g., 10px, 1.5em, 100%).
     * Ensures the value is a number followed by a valid CSS unit.
     */
    function npfeed_sanitize_css_unit($value) {
        // Allow numbers, decimals, negative signs, and common CSS units.
        // This is a strict but practical regex.
        if (preg_match('/^(\-?\d*\.?\d+)(px|em|rem|%|vw|vh|vmin|vmax|ex|ch|cm|mm|in|pt|pc|deg|rad|turn|s|ms)?$/', $value)) {
            return $value;
        }
        return '';
    }
}

if(! function_exists('npfeed_sanitize_css_keyword')) {
    /**
     * Sanitizes a CSS keyword (e.g., bold, underline, center, block).
     * Uses WordPress core's sanitize_key which is perfect for this.
     */
    function npfeed_sanitize_css_keyword($value) {
        return sanitize_key($value);
    }
}

if(! function_exists('npfeed_sanitize_styles_config')) {
    /**
     * Sanitizes a deeply nested styles array (like styles_config or responsive_styles).
     *
     * @param array $styles_config The raw styles array.
     * @return array The sanitized styles array.
     */
    function npfeed_sanitize_styles_config($styles_config) {
        if (!is_array($styles_config)) {
            return [];
        }

        $sanitized_config = [];
        foreach ($styles_config as $key => $value) {
            // The main container is usually 'styles'
            if ($key === 'styles' && is_array($value)) {
                $sanitized_config[$key] = npfeed_sanitize_style_properties($value);
            } else {
                // Handle any other top-level keys generically.
                $sanitized_config[$key] = sanitize_text_field($value);
            }
        }
        return $sanitized_config;
    }
}

if(! function_exists('npfeed_sanitize_style_properties')) {
    /**
     * Recursively sanitizes an array of style properties for a single element.
     *
     * @param array $properties The style properties array.
     * @return array The sanitized properties.
     */
    function npfeed_sanitize_style_properties($properties) {
        $sanitized_properties = [];
        foreach ($properties as $property_name => $property_data) {
            $sanitized_property_data = [];
            foreach ($property_data as $data_key => $data_value) {
                switch ($data_key) {
                    case 'selector':
                        $sanitized_property_data[$data_key] = npfeed_sanitize_css_selector($data_value);
                        break;

                    case 'color':
                    case 'typography':
                    case 'slider':
                    case 'padding':
                    case 'border':
                    case 'border_radius':
                    case 'box_shadow':
                    case 'spacing': // often used instead of padding
                        // These are arrays of values, so we recurse.
                        $sanitized_property_data[$data_key] = npfeed_sanitize_style_data($data_value);
                        break;

                    default:
                        // Fallback for any unexpected keys.
                        $sanitized_property_data[$data_key] = sanitize_text_field($data_value);
                }
            }
            $sanitized_properties[$property_name] = $sanitized_property_data;
        }
        return $sanitized_properties;
    }
}

if(! function_exists('npfeed_sanitize_style_data')) {

    /**
     * Sanitizes a generic style data array (e.g., a 'color' or 'typography' array).
     *
     * @param array $data The style data array.
     * @return array The sanitized data.
     */
    function npfeed_sanitize_style_data($data) {
        if (!is_array($data)) {
            return sanitize_text_field($data);
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // For responsive values like 'font_size' => ['desktop' => '16px', 'mobile' => '14px']
                $sanitized[$key] = npfeed_sanitize_style_data($value);
            } else {
                // Apply specific sanitizers based on the key name.
                if (strpos($key, 'color') !== false) {
                    $sanitized[$key] = npfeed_sanitize_color($value);
                } elseif (in_array($key, ['font_size', 'letter_spacing', 'line_height', 'top', 'right', 'bottom', 'left', 'width', 'height', 'max_width', 'spaceBetween', 'horizontal', 'vertical', 'blur', 'spread'])) {
                    $sanitized[$key] = npfeed_sanitize_css_unit($value);
                } elseif (in_array($key, ['font_weight', 'font_style', 'text_transform', 'text_decoration', 'border_style', 'linked', 'box_shadow_style', 'inset'])) {
                    $sanitized[$key] = npfeed_sanitize_css_keyword($value);
                } else {
                    // General fallback for any other text-based value.
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        return $sanitized;
    }
}

if (!function_exists('npfeed_trim_url')) {
    /**
     * Trim a URL the way JavaScript's String.prototype.trim() does.
     *
     * The character set is mirrored by NPFEED_TRIM_SET in utils/videoUrl.js.
     *
     * @param mixed $value
     *
     * @return string
     */
    function npfeed_trim_url($value)
    {
        if (!is_string($value)) {
            return '';
        }

        $set = '[\s\x{00A0}\x{FEFF}\x{2000}-\x{200D}\x{2028}\x{2029}\x{3000}]+';
        $trimmed = preg_replace('/^' . $set . '|' . $set . '$/u', '', $value);

        return $trimmed === null ? trim($value) : $trimmed;
    }
}

if (!function_exists('npfeed_is_http_url')) {
    /**
     * One http(s) URL rule, shared by the form, the REST validator and every renderer.
     *
     * @param mixed $value
     *
     * @return bool
     */
    function npfeed_is_http_url($value)
    {
        return npfeed_sanitize_http_url($value) !== '';
    }
}

if (!function_exists('npfeed_sanitize_http_url')) {
    /**
     * Validate one http(s) URL, host included.
     *
     * Tested before esc_url_raw(), which turns a bare word into a URL. IDN must
     * arrive as punycode; a non-ASCII path is fine and preserved.
     *
     * @param mixed $value
     *
     * @return string the normalized URL, or '' when it is not a usable http(s) URL
     */
    function npfeed_sanitize_http_url($value)
    {
        $raw = npfeed_trim_url($value);

        if (!preg_match('#^https?://#i', $raw)) {
            return '';
        }

        $raw_host = wp_parse_url($raw, PHP_URL_HOST);
        if (!is_string($raw_host) || $raw_host === '') {
            return '';
        }

        if (preg_match('/[^\x21-\x7E]/', $raw_host)) {
            return '';
        }

        $url = esc_url_raw($raw);
        if (!preg_match('#^https?://#i', $url)) {
            return '';
        }

        $host = wp_parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return '';
        }

        return preg_match('/[\p{L}\p{N}]/u', $host) ? $url : '';
    }
}

if (!function_exists('npfeed_sanitize_media_url')) {
    /**
     * Gate for a URL the server itself will fetch later.
     *
     * wp_http_validate_url() is the rule: it settles scheme, credentials, port,
     * the site's own host, and the private/reserved address list that WordPress
     * keeps current. Only the http(s) shape check is ours.
     *
     * @param mixed $value
     *
     * @return string the normalized URL, or '' when it is not safe to fetch
     */
    function npfeed_sanitize_media_url($value)
    {
        $url = npfeed_sanitize_http_url($value);
        if ($url === '') {
            return '';
        }

        return wp_http_validate_url($url) ? $url : '';
    }
}

if (!function_exists('npfeed_is_media_url')) {
    /**
     * @param mixed $value
     *
     * @return bool
     */
    function npfeed_is_media_url($value)
    {
        return npfeed_sanitize_media_url($value) !== '';
    }
}
