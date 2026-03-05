<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('WC') || !WC()) {
    return;
}

$template = WC()->plugin_path() . '/templates/checkout/form-checkout.php';
if (file_exists($template)) {
    include $template;
}
