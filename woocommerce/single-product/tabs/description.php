<?php
/**
 * Product description proxy.
 *
 * @package WooCommerce\Templates
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('WC') || !WC()) {
    return;
}

$template = WC()->plugin_path() . '/templates/single-product/tabs/description.php';
if (file_exists($template)) {
    include $template;
}
