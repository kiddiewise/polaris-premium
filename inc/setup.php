<?php

if (!defined('ABSPATH')) exit;

function polaris_setup() {

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('custom-logo');

    register_nav_menus([
        'main_menu' => 'Main Menu',
        'footer_menu' => 'Footer Menu',
    ]);

    add_image_size('polaris_single', 900, 900, false);
}

add_action('after_setup_theme', 'polaris_setup');