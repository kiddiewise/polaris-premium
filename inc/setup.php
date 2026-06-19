<?php

if (!defined('ABSPATH')) exit;

function polaris_setup() {
    load_theme_textdomain('polaris', get_theme_file_path('/assets/languages'));

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    add_theme_support('woocommerce');

    register_nav_menus([
        'main_menu'   => esc_html__('Main Menu', 'polaris'),
        'footer_menu' => esc_html__('Footer Menu', 'polaris'),
    ]);

    add_image_size('polaris_single', 900, 900, false);
}

add_action('after_setup_theme', 'polaris_setup');
