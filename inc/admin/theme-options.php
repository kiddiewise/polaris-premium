<?php
if (!defined('ABSPATH')) exit;

/**
 * Polaris Premium — Theme Customizer Options
 * Hero Banner slider resimleri kontrol et
 */

function polaris_customize_register($wp_customize) {
  
  // ========================================
  // SECTION: Hero Banner Settings
  // ========================================
  $wp_customize->add_section('polaris_hero_section', [
    'title'       => esc_html__('Hero Slider', 'polaris'),
    'description' => esc_html__('3 adet hero banner resmini buradan yükle', 'polaris'),
    'priority'    => 100,
  ]);

  // HERO 1
  $wp_customize->add_setting('polaris_hero_1', [
    'default'           => '',
    'sanitize_callback' => 'esc_url_raw',
    'transport'         => 'refresh',
  ]);
  $wp_customize->add_control(
    new WP_Customize_Image_Control($wp_customize, 'polaris_hero_1_control', [
      'label'       => esc_html__('Hero Banner 1', 'polaris'),
      'description' => esc_html__('İlk banner resmi (1200x500px önerilir)', 'polaris'),
      'section'     => 'polaris_hero_section',
      'settings'    => 'polaris_hero_1',
    ])
  );

  // HERO 2
  $wp_customize->add_setting('polaris_hero_2', [
    'default'           => '',
    'sanitize_callback' => 'esc_url_raw',
    'transport'         => 'refresh',
  ]);
  $wp_customize->add_control(
    new WP_Customize_Image_Control($wp_customize, 'polaris_hero_2_control', [
      'label'       => esc_html__('Hero Banner 2', 'polaris'),
      'description' => esc_html__('İkinci banner resmi (1200x500px önerilir)', 'polaris'),
      'section'     => 'polaris_hero_section',
      'settings'    => 'polaris_hero_2',
    ])
  );

  // HERO 3
  $wp_customize->add_setting('polaris_hero_3', [
    'default'           => '',
    'sanitize_callback' => 'esc_url_raw',
    'transport'         => 'refresh',
  ]);
  $wp_customize->add_control(
    new WP_Customize_Image_Control($wp_customize, 'polaris_hero_3_control', [
      'label'       => esc_html__('Hero Banner 3', 'polaris'),
      'description' => esc_html__('Üçüncü banner resmi (1200x500px önerilir)', 'polaris'),
      'section'     => 'polaris_hero_section',
      'settings'    => 'polaris_hero_3',
    ])
  );

  // AUTO-PLAY TOGGLE
  $wp_customize->add_setting('polaris_hero_autoplay', [
    'default'           => true,
    'sanitize_callback' => 'rest_sanitize_boolean',
    'transport'         => 'postMessage',
  ]);
  $wp_customize->add_control('polaris_hero_autoplay_control', [
    'label'       => esc_html__('Otomatik Keyif', 'polaris'),
    'description' => esc_html__('Slider otomatik olarak ileri gitsini mi?', 'polaris'),
    'section'     => 'polaris_hero_section',
    'settings'    => 'polaris_hero_autoplay',
    'type'        => 'checkbox',
  ]);

}
add_action('customize_register', 'polaris_customize_register');

/**
 * Helper: Get hero banners (3 image URLs)
 */
function polaris_get_hero_banners() {
  $images = [];
  
  for ($i = 1; $i <= 3; $i++) {
    $url = get_theme_mod("polaris_hero_$i", '');
    if ($url) {
      $images[] = $url;
    }
  }

  // Fallback: eğer settings'ten hiç resim yoksa boş array ver
  return $images;
}

/**
 * Helper: Check if autoplay enabled
 */
function polaris_hero_autoplay() {
  return (bool) get_theme_mod('polaris_hero_autoplay', true);
}
