<?php
if (!defined('ABSPATH')) exit;

function polaris_assets() {
  $css_path = get_template_directory() . '/assets/css/main.css';
  $js_path  = get_template_directory() . '/assets/js/main.js';
  $css_ver  = file_exists($css_path) ? (string) filemtime($css_path) : '1.0.0';
  $js_ver   = file_exists($js_path) ? (string) filemtime($js_path) : '1.0.0';

  // Fonts
  wp_enqueue_style(
    'polaris-fonts',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap',
    [],
    null
  );

  // Font Awesome
  wp_enqueue_style(
    'polaris-fa',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
    [],
    '6.5.2'
  );

  // Main CSS
  wp_enqueue_style(
    'polaris-main',
    get_template_directory_uri() . '/assets/css/main.css',
    ['polaris-fonts', 'polaris-fa'],
    $css_ver
  );

  // Main JS
  wp_enqueue_script(
    'polaris-main',
    get_template_directory_uri() . '/assets/js/main.js',
    [],
    $js_ver,
    true
  );

  // AJAX vars
  wp_localize_script('polaris-main', 'polaris_ajax', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('polaris_nonce'),
  ]);
}

add_action('wp_enqueue_scripts', 'polaris_assets');
