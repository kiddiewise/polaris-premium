<?php
if (!defined('ABSPATH')) exit;

/**
 * Core
 */
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/assets.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/security.php';
require_once get_template_directory() . '/inc/seo.php';

/**
 * WooCommerce
 */
foreach (glob(get_template_directory() . '/inc/woocommerce/*.php') as $file) {
    require_once $file;
}

/**
 * AJAX
 */
foreach (glob(get_template_directory() . '/inc/ajax/*.php') as $file) {
    require_once $file;
}

/**
 * Admin
 */
foreach (glob(get_template_directory() . '/inc/admin/*.php') as $file) {
    require_once $file;
}

/**
 * Integrations
 */
foreach (glob(get_template_directory() . '/inc/integrations/*.php') as $file) {
    require_once $file;
}