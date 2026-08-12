<?php
/**
 * Plugin Name:       HaloPress-FX
 * Description:       为 WordPress 前台添加点击粒子、光环与光标拖尾动画。
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            HaloPress-FX Contributors
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       halopress-fx
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HALOPRESS_FX_VERSION', '0.1.0');
define('HALOPRESS_FX_FILE', __FILE__);
define('HALOPRESS_FX_DIR', plugin_dir_path(__FILE__));
define('HALOPRESS_FX_URL', plugin_dir_url(__FILE__));

require_once HALOPRESS_FX_DIR . 'includes/class-halopress-fx-settings.php';
require_once HALOPRESS_FX_DIR . 'includes/class-halopress-fx-admin.php';
require_once HALOPRESS_FX_DIR . 'includes/class-halopress-fx-frontend.php';

register_activation_hook(__FILE__, array('HaloPress_FX_Settings', 'activate'));

/**
 * Starts the plugin after WordPress has loaded all active plugins.
 */
function halopress_fx_bootstrap()
{
    HaloPress_FX_Settings::init();

    if (is_admin()) {
        HaloPress_FX_Admin::init();
        return;
    }

    HaloPress_FX_Frontend::init();
}
add_action('plugins_loaded', 'halopress_fx_bootstrap');
