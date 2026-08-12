<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the Settings > HaloPress-FX screen.
 */
final class HaloPress_FX_Admin
{
    const PAGE_SLUG = 'halopress-fx';

    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue'));
        add_filter('plugin_action_links_' . plugin_basename(HALOPRESS_FX_FILE), array(__CLASS__, 'action_links'));
    }

    public static function add_menu()
    {
        add_options_page(
            'HaloPress-FX',
            'HaloPress-FX',
            'manage_options',
            self::PAGE_SLUG,
            array(__CLASS__, 'render')
        );
    }

    public static function enqueue($hook_suffix)
    {
        if ('settings_page_' . self::PAGE_SLUG !== $hook_suffix) {
            return;
        }

        wp_enqueue_style(
            'halopress-fx-admin',
            HALOPRESS_FX_URL . 'assets/css/admin.css',
            array(),
            HALOPRESS_FX_VERSION
        );
        wp_enqueue_script(
            'halopress-fx-admin',
            HALOPRESS_FX_URL . 'assets/js/admin.js',
            array(),
            HALOPRESS_FX_VERSION,
            true
        );
    }

    public static function action_links($links)
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('options-general.php?page=' . self::PAGE_SLUG)),
            esc_html__('设置', 'halopress-fx')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    public static function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = HaloPress_FX_Settings::get();
        $name = HaloPress_FX_Settings::OPTION_NAME;
        ?>
        <div class="wrap halopress-fx-admin">
            <div class="halopress-fx-header">
                <div>
                    <h1>HaloPress-FX</h1>
                    <p><?php esc_html_e('为网站前台添加点击光环、粒子碎片和光标拖尾。', 'halopress-fx'); ?></p>
                </div>
                <span class="halopress-fx-version">v<?php echo esc_html(HALOPRESS_FX_VERSION); ?></span>
            </div>

            <?php settings_errors(); ?>

            <form method="post" action="options.php" id="halopress-fx-settings-form">
                <?php settings_fields(HaloPress_FX_Settings::SETTINGS_GROUP); ?>

                <section class="halopress-fx-card">
                    <h2><?php esc_html_e('基础设置', 'halopress-fx'); ?></h2>
                    <p class="description"><?php esc_html_e('这些设置适合大多数 WordPress 网站。动画仅在网站前台加载。', 'halopress-fx'); ?></p>

                    <?php self::checkbox($name, 'enabled', $settings, '启用 HaloPress-FX', '关闭后前台不会加载动画脚本。'); ?>
                    <?php self::checkbox($name, 'click_enabled', $settings, '点击特效', '鼠标点击或触摸轻点时显示光环与碎片。'); ?>
                    <?php self::checkbox($name, 'trail_enabled', $settings, '光标拖尾', '按住鼠标拖动时显示连续拖尾。'); ?>
                    <?php self::checkbox($name, 'trail_always', $settings, '移动时始终显示拖尾', '桌面端无需按住鼠标即可显示拖尾；移动端会自动忽略此项。'); ?>
                    <?php self::checkbox($name, 'mobile_enabled', $settings, '移动端启用', '移动端保留原生滚动，轻点显示动画，滑动不会强制维持完整拖尾。'); ?>

                    <div class="halopress-fx-grid">
                        <?php self::color($name, 'theme_color', $settings, '主题颜色'); ?>
                        <?php self::select($name, 'quality_preset', $settings, '画质预设', array(
                            'performance' => '性能优先',
                            'balanced' => '平衡（推荐）',
                            'high' => '高清',
                            'custom' => '自定义',
                        )); ?>
                        <?php self::range($name, 'scale', $settings, '特效大小', 0.25, 3, 0.05); ?>
                        <?php self::range($name, 'opacity', $settings, '整体透明度', 0, 1, 0.05); ?>
                        <?php self::range($name, 'click_time_scale', $settings, '点击动画速度', 0.25, 3, 0.05); ?>
                        <?php self::range($name, 'trail_time_scale', $settings, '拖尾消散速度', 0.25, 3, 0.05); ?>
                    </div>
                </section>

                <details class="halopress-fx-card halopress-fx-advanced">
                    <summary>
                        <span><?php esc_html_e('高级设置', 'halopress-fx'); ?></span>
                        <small><?php esc_html_e('渲染、合成与性能参数', 'halopress-fx'); ?></small>
                    </summary>
                    <p class="description"><?php esc_html_e('修改这些参数会把画质预设切换为“自定义”。如果不确定，请保留默认值。', 'halopress-fx'); ?></p>

                    <div class="halopress-fx-grid halopress-fx-advanced-fields">
                        <?php self::select($name, 'effect_backend', $settings, '渲染后端', array(
                            'auto' => '自动', 'canvas2d' => 'Canvas 2D', 'webgl2' => 'WebGL2', 'webgpu' => 'WebGPU',
                        ), true); ?>
                        <?php self::select($name, 'bloom_backend', $settings, 'Bloom 后端', array(
                            'auto' => '自动', 'native' => '原生辉光', 'software' => '软件 Bloom', 'webgl2' => 'WebGL2 Bloom',
                        ), true); ?>
                        <?php self::select($name, 'rendering_mode', $settings, '渲染模式', array(
                            'enhanced' => '增强', 'legacy' => '兼容模式',
                        ), true); ?>
                        <?php self::number($name, 'max_dpr', $settings, '最大 DPR', 1, 3, 0.25, true); ?>
                        <?php self::select($name, 'theme_color_mode', $settings, '颜色映射', array(
                            'hue-only' => '仅色相', 'relative-oklch' => '相对 OKLCH',
                        ), true); ?>
                        <?php self::select($name, 'output_compositing', $settings, '输出合成', array(
                            'browser-overlay' => '网页透明覆盖层', 'scene' => '场景合成',
                        ), true); ?>
                        <?php self::select($name, 'overlay_alpha_policy', $settings, '透明度策略', array(
                            'coverage' => 'Coverage', 'visual-max' => 'Visual Max',
                        ), true); ?>
                        <?php self::select($name, 'overlay_color_compensation', $settings, '浅色背景色彩补偿', array(
                            'none' => '关闭', 'bright-core' => '仅增强高亮核心',
                        ), true); ?>
                        <?php self::select($name, 'host_compositing', $settings, '宿主混合模式', array(
                            'source-over' => 'Source Over', 'screen' => 'Screen', 'plus-lighter' => 'Plus Lighter',
                        ), true); ?>
                        <?php self::number($name, 'light_background_contrast_alpha', $settings, '浅色背景轮廓', 0, 1, 0.05, true); ?>
                        <?php self::number($name, 'input_sampling_rate', $settings, '输入采样率（Hz，0 为不限）', 0, 1000, 1, true); ?>
                    </div>

                    <?php self::checkbox($name, 'isolated_compositing', $settings, '隔离合成', '在透明组内完成多 Canvas 合成。', true); ?>
                    <?php self::checkbox($name, 'webgpu_prefer_hdr', $settings, 'WebGPU 优先 HDR', '仅在浏览器和显示链均支持时生效。', true); ?>
                </details>

                <div class="halopress-fx-actions">
                    <?php submit_button(__('保存设置', 'halopress-fx'), 'primary', 'submit', false); ?>
                    <button type="button" class="button" id="halopress-fx-reset"><?php esc_html_e('恢复默认设置', 'halopress-fx'); ?></button>
                </div>
            </form>
        </div>
        <?php
    }

    private static function checkbox($name, $key, $settings, $label, $description = '', $advanced = false)
    {
        ?>
        <label class="halopress-fx-check<?php echo $advanced ? ' halopress-fx-advanced-control' : ''; ?>">
            <input type="checkbox" name="<?php echo esc_attr($name . '[' . $key . ']'); ?>" value="1" <?php checked(!empty($settings[$key])); ?>>
            <span><strong><?php echo esc_html($label); ?></strong><?php if ($description) : ?><small><?php echo esc_html($description); ?></small><?php endif; ?></span>
        </label>
        <?php
    }

    private static function field_open($label, $advanced = false)
    {
        ?><label class="halopress-fx-field<?php echo $advanced ? ' halopress-fx-advanced-control' : ''; ?>"><span><?php echo esc_html($label); ?></span><?php
    }

    private static function color($name, $key, $settings, $label)
    {
        self::field_open($label);
        ?><input type="color" name="<?php echo esc_attr($name . '[' . $key . ']'); ?>" value="<?php echo esc_attr($settings[$key]); ?>"></label><?php
    }

    private static function range($name, $key, $settings, $label, $min, $max, $step)
    {
        self::field_open($label);
        ?><div class="halopress-fx-range"><input type="range" name="<?php echo esc_attr($name . '[' . $key . ']'); ?>" value="<?php echo esc_attr($settings[$key]); ?>" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>" step="<?php echo esc_attr($step); ?>"><output><?php echo esc_html($settings[$key]); ?></output></div></label><?php
    }

    private static function number($name, $key, $settings, $label, $min, $max, $step, $advanced = false)
    {
        self::field_open($label, $advanced);
        ?><input type="number" name="<?php echo esc_attr($name . '[' . $key . ']'); ?>" value="<?php echo esc_attr($settings[$key]); ?>" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>" step="<?php echo esc_attr($step); ?>"></label><?php
    }

    private static function select($name, $key, $settings, $label, $options, $advanced = false)
    {
        self::field_open($label, $advanced);
        ?><select name="<?php echo esc_attr($name . '[' . $key . ']'); ?>"><?php foreach ($options as $value => $option_label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($settings[$key], $value); ?>><?php echo esc_html($option_label); ?></option><?php endforeach; ?></select></label><?php
    }
}
