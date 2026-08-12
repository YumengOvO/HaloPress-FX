<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Loads the prebuilt animation engine on public-facing WordPress pages only.
 */
final class HaloPress_FX_Frontend
{
    public static function init()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue'));
    }

    public static function enqueue()
    {
        $settings = HaloPress_FX_Settings::get();
        if (empty($settings['enabled'])) {
            return;
        }

        $engine_path = HALOPRESS_FX_DIR . 'assets/js/ba-click-fx.iife.js';
        $frontend_path = HALOPRESS_FX_DIR . 'assets/js/halopress-fx.js';
        if (!file_exists($engine_path) || !file_exists($frontend_path)) {
            return;
        }

        wp_enqueue_script(
            'halopress-fx-engine',
            HALOPRESS_FX_URL . 'assets/js/ba-click-fx.iife.js',
            array(),
            HALOPRESS_FX_VERSION,
            true
        );
        wp_enqueue_script(
            'halopress-fx',
            HALOPRESS_FX_URL . 'assets/js/halopress-fx.js',
            array('halopress-fx-engine'),
            HALOPRESS_FX_VERSION,
            true
        );

        wp_add_inline_script(
            'halopress-fx',
            'window.HaloPressFXSettings = ' . wp_json_encode(self::frontend_settings($settings)) . ';',
            'before'
        );
    }

    private static function frontend_settings($settings)
    {
        return array(
            'clickEnabled' => (bool) $settings['click_enabled'],
            'trailEnabled' => (bool) $settings['trail_enabled'],
            'trailAlways' => (bool) $settings['trail_always'],
            'mobileEnabled' => (bool) $settings['mobile_enabled'],
            'themeColor' => $settings['theme_color'],
            'scale' => (float) $settings['scale'],
            'opacity' => (float) $settings['opacity'],
            'clickTimeScale' => (float) $settings['click_time_scale'],
            'trailTimeScale' => (float) $settings['trail_time_scale'],
            'qualityPreset' => $settings['quality_preset'],
            'effectBackend' => $settings['effect_backend'],
            'bloomBackend' => $settings['bloom_backend'],
            'renderingMode' => $settings['rendering_mode'],
            'maxDpr' => (float) $settings['max_dpr'],
            'themeColorMode' => $settings['theme_color_mode'],
            'outputCompositing' => $settings['output_compositing'],
            'overlayAlphaPolicy' => $settings['overlay_alpha_policy'],
            'overlayColorCompensation' => $settings['overlay_color_compensation'],
            'hostCompositing' => $settings['host_compositing'],
            'isolatedCompositing' => (bool) $settings['isolated_compositing'],
            'lightBackgroundContrastAlpha' => (float) $settings['light_background_contrast_alpha'],
            'inputSamplingRate' => (int) $settings['input_sampling_rate'],
            'webgpuPreferHdr' => (bool) $settings['webgpu_prefer_hdr'],
        );
    }
}
