<?php
if (!defined('ABSPATH')) {
    exit;
}

function polaris_customize_register($wp_customize) {
    if (!class_exists('WP_Customize_Image_Control')) {
        return;
    }

    $wp_customize->add_section('polaris_hero_section', [
        'title'       => esc_html__('Hero Slider', 'polaris'),
        'description' => esc_html__('Upload up to three hero banner images.', 'polaris'),
        'priority'    => 100,
    ]);

    for ($i = 1; $i <= 3; $i++) {
        $setting_id = 'polaris_hero_' . $i;

        $wp_customize->add_setting($setting_id, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control(
            new WP_Customize_Image_Control($wp_customize, $setting_id . '_control', [
                'label'       => sprintf(esc_html__('Hero Banner %d', 'polaris'), $i),
                'description' => esc_html__('Recommended size: 1600x600', 'polaris'),
                'section'     => 'polaris_hero_section',
                'settings'    => $setting_id,
            ])
        );
    }

    $wp_customize->add_setting('polaris_hero_autoplay', [
        'default'           => true,
        'sanitize_callback' => function ($value) {
            return (bool) $value;
        },
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('polaris_hero_autoplay_control', [
        'label'       => esc_html__('Autoplay', 'polaris'),
        'description' => esc_html__('Automatically rotate slides.', 'polaris'),
        'section'     => 'polaris_hero_section',
        'settings'    => 'polaris_hero_autoplay',
        'type'        => 'checkbox',
    ]);
}
add_action('customize_register', 'polaris_customize_register');

if (!function_exists('polaris_get_hero_banners')) {
    function polaris_get_hero_banners() {
        $images = [];

        for ($i = 1; $i <= 3; $i++) {
            $url = get_theme_mod('polaris_hero_' . $i, '');
            if (is_string($url) && $url !== '') {
                $images[] = esc_url($url);
            }
        }

        return $images;
    }
}

if (!function_exists('polaris_hero_autoplay')) {
    function polaris_hero_autoplay() {
        return (bool) get_theme_mod('polaris_hero_autoplay', true);
    }
}
