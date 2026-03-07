<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $wp_query;

$queried_term   = get_queried_object();
$is_category    = is_tax('product_cat') && $queried_term instanceof WP_Term;
$hero_image_id  = $is_category ? (int) get_term_meta($queried_term->term_id, 'thumbnail_id', true) : 0;
$hero_image_url = $hero_image_id > 0 ? wp_get_attachment_image_url($hero_image_id, 'full') : '';
$hero_image_alt = $hero_image_id > 0 ? get_post_meta($hero_image_id, '_wp_attachment_image_alt', true) : '';
$hero_title     = woocommerce_page_title(false);
$hero_desc_raw  = '';
$hero_count     = (int) $wp_query->found_posts;
$shop_link      = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$home_link      = home_url('/');

if ($is_category) {
    $hero_desc_raw = term_description($queried_term->term_id, 'product_cat');
} elseif (is_shop()) {
    $shop_page_id = (int) wc_get_page_id('shop');
    if ($shop_page_id > 0) {
        $hero_desc_raw = get_post_field('post_excerpt', $shop_page_id);
        if (empty($hero_desc_raw)) {
            $hero_desc_raw = get_post_field('post_content', $shop_page_id);
        }
    }
}

$hero_desc = trim(wp_strip_all_tags((string) $hero_desc_raw));
if ($hero_desc !== '') {
    $hero_desc = wp_trim_words($hero_desc, 20, '...');
}

if (is_shop()) {
    $hero_title = esc_html__('Turkiye\'nin En Guclu Surf Kursunlari', 'polaris');
    $hero_desc = esc_html__('Polaris Premium serisi, agir deniz kosullarinda maksimum denge, daha uzak atis ve istikrarli dip tutus icin gelistirildi. Profesyonel balikcilarin guvendigi performansi simdi sen de deneyimle.', 'polaris');

    if (empty($hero_image_url) && function_exists('polaris_get_hero_banners')) {
        $hero_banners = polaris_get_hero_banners();
        if (!empty($hero_banners[0])) {
            $hero_image_url = esc_url($hero_banners[0]);
            $hero_image_alt = $hero_title;
        }
    }
}
?>

<section class="container polaris-catalog-page">
  <!-- Polaris Custom Catalog Template -->
  <header class="polaris-catalog-hero fade-up">
    <div class="polaris-catalog-hero__visual">
      <?php if (!empty($hero_image_url)) : ?>
        <img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($hero_image_alt !== '' ? $hero_image_alt : $hero_title); ?>" loading="eager" decoding="async">
      <?php else : ?>
        <div class="polaris-catalog-hero__fallback" aria-hidden="true">
          <i class="fa-solid fa-layer-group"></i>
        </div>
      <?php endif; ?>
    </div>

    <div class="polaris-catalog-hero__content">
      <nav class="polaris-catalog-breadcrumbs" aria-label="<?php echo esc_attr__('Breadcrumb', 'polaris'); ?>">
        <a href="<?php echo esc_url($home_link); ?>"><?php echo esc_html__('Ana Sayfa', 'polaris'); ?></a>
        <span aria-hidden="true">/</span>
        <?php if (!is_shop()) : ?>
          <a href="<?php echo esc_url($shop_link); ?>"><?php echo esc_html__('Magaza', 'polaris'); ?></a>
          <span aria-hidden="true">/</span>
        <?php endif; ?>
        <strong><?php echo esc_html($hero_title); ?></strong>
      </nav>

      <h1><?php echo esc_html($hero_title); ?></h1>

      <?php if ($hero_desc !== '') : ?>
        <p><?php echo esc_html($hero_desc); ?></p>
      <?php else : ?>
        <p><?php echo esc_html__('Bu kategorideki urunleri asagidaki kartlardan inceleyebilir, filtreleyebilir ve hizlica sepete ekleyebilirsiniz.', 'polaris'); ?></p>
      <?php endif; ?>

      <div class="polaris-catalog-hero__meta">
        <span class="polaris-catalog-chip">
          <i class="fa-solid fa-box-open" aria-hidden="true"></i>
          <?php echo esc_html(sprintf(__('%d urun listeleniyor', 'polaris'), $hero_count)); ?>
        </span>
        <span class="polaris-catalog-chip">
          <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
          <?php echo esc_html__('Hizli teslimat', 'polaris'); ?>
        </span>
      </div>
    </div>
  </header>

  <div class="polaris-catalog-toolbar polaris-surface fade-up" data-delay="1">
    <div class="polaris-catalog-toolbar__result">
      <?php woocommerce_result_count(); ?>
    </div>
    <div class="polaris-catalog-toolbar__sort">
      <?php woocommerce_catalog_ordering(); ?>
    </div>
  </div>

  <?php if (function_exists('woocommerce_output_all_notices')) : ?>
    <?php woocommerce_output_all_notices(); ?>
  <?php endif; ?>

  <?php if (woocommerce_product_loop()) : ?>
    <div class="polaris-catalog-grid fade-up" data-delay="2">
      <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php wc_get_template_part('content', 'product'); ?>
      <?php endwhile; ?>
    </div>

    <?php do_action('woocommerce_after_shop_loop'); ?>
  <?php else : ?>
    <?php do_action('woocommerce_no_products_found'); ?>
  <?php endif; ?>
</section>

<?php
get_footer();
