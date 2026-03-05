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
$woo_dir = get_template_directory() . '/inc/woocommerce/';
if (is_dir($woo_dir)) {
    foreach (glob($woo_dir . '*.php') as $file) {
        require_once $file;
    }
}

/**
 * AJAX
 */
$ajax_dir = get_template_directory() . '/inc/ajax/';
if (is_dir($ajax_dir)) {
    foreach (glob($ajax_dir . '*.php') as $file) {
        require_once $file;
    }
}

/**
 * Admin
 */
$admin_dir = get_template_directory() . '/inc/admin/';
if (is_dir($admin_dir)) {
    foreach (glob($admin_dir . '*.php') as $file) {
        require_once $file;
    }
}

/**
 * Integrations
 */
$integrations_dir = get_template_directory() . '/inc/integrations/';
if (is_dir($integrations_dir)) {
    foreach (glob($integrations_dir . '*.php') as $file) {
        require_once $file;
    }
}