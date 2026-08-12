<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Owns the persistent option schema and all server-side validation.
 */
final class HaloPress_FX_Settings
{
    const OPTION_NAME = 'halopress_fx_settings';
    const SETTINGS_GROUP = 'halopress_fx_settings_group';

    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'register'));
    }

    public static function activate()
    {
        if (false === get_option(self::OPTION_NAME, false)) {
            add_option(self::OPTION_NAME, self::defaults());
        }
    }

    public static function register()
    {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_NAME,
            array(
                'type' => 'array',
                'sanitize_callback' => array(__CLASS__, 'sanitize'),
                'default' => self::defaults(),
                'show_in_rest' => false,
            )
        );
    }

    public static function defaults()
    {
        return array(
            'enabled' => true,
            'click_enabled' => true,
            'trail_enabled' => true,
            'trail_always' => false,
            'mobile_enabled' => true,
            'theme_color' => '#4ca7ff',
            'scale' => 1.0,
            'opacity' => 1.0,
            'click_time_scale' => 1.0,
            'trail_time_scale' => 1.0,
            'quality_preset' => 'balanced',
            'effect_backend' => 'webgl2',
            'bloom_backend' => 'webgl2',
            'rendering_mode' => 'enhanced',
            'max_dpr' => 1.0,
            'theme_color_mode' => 'hue-only',
            'output_compositing' => 'browser-overlay',
            'overlay_alpha_policy' => 'coverage',
            'overlay_color_compensation' => 'none',
            'host_compositing' => 'source-over',
            'isolated_compositing' => false,
            'light_background_contrast_alpha' => 0.0,
            'input_sampling_rate' => 0,
            'webgpu_prefer_hdr' => false,
        );
    }

    public static function get()
    {
        $stored = get_option(self::OPTION_NAME, array());
        if (!is_array($stored)) {
            $stored = array();
        }

        return self::normalize(array_merge(self::defaults(), $stored));
    }

    public static function sanitize($input)
    {
        $input = is_array($input) ? $input : array();
        $boolean_keys = array(
            'enabled',
            'click_enabled',
            'trail_enabled',
            'trail_always',
            'mobile_enabled',
            'isolated_compositing',
            'webgpu_prefer_hdr',
        );

        foreach ($boolean_keys as $key) {
            $input[$key] = !empty($input[$key]);
        }

        return self::normalize($input);
    }

    private static function normalize($input)
    {
        $defaults = self::defaults();
        $value = array_merge($defaults, is_array($input) ? $input : array());

        foreach (array('enabled', 'click_enabled', 'trail_enabled', 'trail_always', 'mobile_enabled', 'isolated_compositing', 'webgpu_prefer_hdr') as $key) {
            $value[$key] = (bool) $value[$key];
        }

        $theme_color = sanitize_hex_color($value['theme_color']);
        $value['theme_color'] = $theme_color ? $theme_color : $defaults['theme_color'];

        $value['scale'] = self::clamp_float($value['scale'], 0.25, 3.0, $defaults['scale']);
        $value['opacity'] = self::clamp_float($value['opacity'], 0.0, 1.0, $defaults['opacity']);
        $value['click_time_scale'] = self::clamp_float($value['click_time_scale'], 0.25, 3.0, $defaults['click_time_scale']);
        $value['trail_time_scale'] = self::clamp_float($value['trail_time_scale'], 0.25, 3.0, $defaults['trail_time_scale']);
        $value['max_dpr'] = self::clamp_float($value['max_dpr'], 1.0, 3.0, $defaults['max_dpr']);
        $value['light_background_contrast_alpha'] = self::clamp_float($value['light_background_contrast_alpha'], 0.0, 1.0, 0.0);
        $value['input_sampling_rate'] = self::clamp_int($value['input_sampling_rate'], 0, 1000, 0);

        $value['quality_preset'] = self::enum($value['quality_preset'], array('performance', 'balanced', 'high', 'custom'), $defaults['quality_preset']);
        $value['effect_backend'] = self::enum($value['effect_backend'], array('auto', 'canvas2d', 'webgl2', 'webgpu'), $defaults['effect_backend']);
        $value['bloom_backend'] = self::enum($value['bloom_backend'], array('auto', 'native', 'software', 'webgl2'), $defaults['bloom_backend']);
        $value['rendering_mode'] = self::enum($value['rendering_mode'], array('enhanced', 'legacy'), $defaults['rendering_mode']);
        $value['theme_color_mode'] = self::enum($value['theme_color_mode'], array('hue-only', 'relative-oklch'), $defaults['theme_color_mode']);
        $value['output_compositing'] = self::enum($value['output_compositing'], array('scene', 'browser-overlay'), $defaults['output_compositing']);
        $value['overlay_alpha_policy'] = self::enum($value['overlay_alpha_policy'], array('coverage', 'visual-max'), $defaults['overlay_alpha_policy']);
        $value['overlay_color_compensation'] = self::enum($value['overlay_color_compensation'], array('none', 'bright-core'), $defaults['overlay_color_compensation']);
        $value['host_compositing'] = self::enum($value['host_compositing'], array('source-over', 'screen', 'plus-lighter'), $defaults['host_compositing']);

        return $value;
    }

    private static function enum($value, $allowed, $fallback)
    {
        $value = is_string($value) ? sanitize_key($value) : '';
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private static function clamp_float($value, $minimum, $maximum, $fallback)
    {
        if (!is_numeric($value)) {
            return $fallback;
        }

        return max($minimum, min($maximum, (float) $value));
    }

    private static function clamp_int($value, $minimum, $maximum, $fallback)
    {
        if (!is_numeric($value)) {
            return $fallback;
        }

        return max($minimum, min($maximum, (int) $value));
    }
}
