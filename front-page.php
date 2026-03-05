<?php
if (!defined('ABSPATH')) exit;

/**
 * ===========================
 * Product card renderer
 * (Template içinde kalsın diye burada)
 * ===========================
 */
if (!function_exists('polaris_render_product_card')) :
  function polaris_render_product_card($product) {
    if (!$product) return;

    $pid   = $product->get_id();
    $link  = get_permalink($pid);
    $title = $product->get_name();

    $img_id = $product->get_image_id();
    $img    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : '';
    if (!$img) $img = wc_placeholder_img_src('woocommerce_thumbnail');

    $is_sale    = $product->is_on_sale();
    $price_html = $product->get_price_html();

    // Sale badge % hesap (varsa)
    $sale_badge = '';
    if ($is_sale) {
      $reg = (float) $product->get_regular_price();
      $sal = (float) $product->get_sale_price();
      if ($reg > 0 && $sal > 0 && $sal < $reg) {
        $pct = (int) round((($reg - $sal) / $reg) * 100);
        $sale_badge = '%' . max(1, $pct);
      } else {
        $sale_badge = '%';
      }
    }

    echo '<article class="p-card" data-product-id="' . esc_attr($pid) . '">';

    echo '  <a class="p-card__media" href="' . esc_url($link) . '">';
    echo '    <div class="p-card__badges">';
    if ($is_sale) echo '      <span class="badge badge-sale">' . esc_html($sale_badge) . '</span>';
    echo '    </div>';
    echo '    <img src="' . esc_url($img) . '" alt="' . esc_attr($title) . '" loading="lazy">';
    echo '  </a>';

    echo '  <div class="p-card__float">';
    echo '    <button class="p-fab js-like" type="button" aria-label="Beğen" data-like-id="' . esc_attr($pid) . '"><i class="fa-regular fa-heart"></i></button>';
    echo '    <button class="p-fab js-share" type="button" aria-label="Paylaş" data-share-url="' . esc_url($link) . '" data-share-title="' . esc_attr($title) . '"><i class="fa-solid fa-share-nodes"></i></button>';
    echo '  </div>';

    echo '  <div class="p-card__body">';
    echo '    <a class="p-card__title" href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
    echo '    <div class="p-card__price">' . wp_kses_post($price_html) . '</div>';

    if ($product->is_purchasable() && $product->is_in_stock()) {
      // JS selector: .p-card__cta.ajax_add_to_cart
      echo '    <a href="' . esc_url($product->add_to_cart_url()) . '" data-quantity="1" class="p-card__cta add_to_cart_button ajax_add_to_cart" data-product_id="' . esc_attr($pid) . '" data-product_sku="' . esc_attr($product->get_sku()) . '">';
      echo '      <span>Sepete Ekle</span><i class="fa-solid fa-bag-shopping"></i>';
      echo '    </a>';
    } else {
      echo '    <button class="p-card__cta p-card__cta--disabled" type="button" disabled>Stokta Yok</button>';
    }

    echo '  </div>';
    echo '</article>';
  }
endif;

get_header();

/**
 * ===========================
 * AYAR: Sen burayı düzenle
 * ===========================
 */

// Yeni ürünler rail
$show_new_products   = true;
$new_products_title  = 'Yeni Ürünler';
$new_products_limit  = 12;

// Kategori sırası (slug) — "genel" yazma
$category_slugs_in_order = [
  'asansor-sistem-olta-bedeni',
  'polaris-firdondulu-fosforlu-aparatli-surf-kursun',
  // 'pater-noster-2-igneli-surf-casting-takimi',
];

// Hero banner(lar) — istersen çoğalt
$hero_banners = [
  get_template_directory_uri() . '/assets/img/banners/banner1.jpg',
  // get_template_directory_uri() . '/assets/img/banners/banner2.jpg',
  // get_template_directory_uri() . '/assets/img/banners/banner3.jpg',
];

?>

<div class="container">

  <!-- HERO (şimdilik basit; slider için hazır yapı) -->
  <section class="polaris-section fade-up">
    <div class="hero-slider" aria-label="<?php echo esc_attr__('Ana banner', 'polaris'); ?>">
      <?php foreach ($hero_banners as $src): ?>
        <div class="slide">
          <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr__('Polaris Banner', 'polaris'); ?>" loading="eager">
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- KATEGORİ SWIPE RAIL -->
  <section class="polaris-section fade-up">
    <div class="section-title">
      <h2><?php echo esc_html__('Kategoriler', 'polaris'); ?></h2>
      <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php echo esc_html__('Tümünü gör', 'polaris'); ?></a>
    </div>

    <div class="cat-rail" role="list" aria-label="<?php echo esc_attr__('Product categories', 'polaris'); ?>">
      <?php
      $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
        'number'     => 60,
      ]);

      if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
          if ($term->slug === 'genel') continue;

          $link = get_term_link($term);
          if (is_wp_error($link)) continue;

          echo '<div class="cat-item" role="listitem">';
          echo '  <a href="' . esc_url($link) . '">';
          echo '    <div class="cat-box">';
          echo '      <div class="cat-name">' . esc_html($term->name) . '</div>';
          echo '      <div class="cat-meta">' . sprintf(esc_html__('%d ürün', 'polaris'), (int) $term->count) . '</div>';
          echo '    </div>';
          echo '  </a>';
          echo '</div>';
        }
      }
      ?>
    </div>
  </section>

  <?php if ($show_new_products): ?>
    <!-- YENİ ÜRÜNLER (RAIL) -->
    <section class="polaris-section fade-up">
      <div class="section-title">
        <h2><?php echo esc_html($new_products_title); ?></h2>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php echo esc_html__('Mağaza', 'polaris'); ?></a>
      </div>

      <?php
      $q = new WC_Product_Query([
        'status'  => 'publish',
        'limit'   => $new_products_limit,
        'orderby' => 'date',
        'order'   => 'DESC',
        'return'  => 'objects',
      ]);
      $products = $q->get_products();
      ?>

      <div class="product-rail" data-rail>
        <?php
        if (!empty($products)) :
          foreach ($products as $product) :
            polaris_render_product_card($product);
          endforeach;
        else:
          echo '<div class="search-empty">Ürün bulunamadı.</div>';
        endif;
        ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- KATEGORİ BÖLÜMLERİ (senin verdiğin sırayla) -->
  <?php foreach ($category_slugs_in_order as $slug): ?>
    <?php
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term)) continue;
    if ($term->slug === 'genel') continue;

    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) $term_link = wc_get_page_permalink('shop');

    $qcat = new WC_Product_Query([
      'status'   => 'publish',
      'limit'    => 14,
      'orderby'  => 'menu_order',
      'order'    => 'ASC',
      'category' => [$slug],
      'return'   => 'objects',
    ]);
    $cat_products = $qcat->get_products();
    ?>

    <section class="polaris-section fade-up">
      <div class="section-title">
        <h2><?php echo esc_html($term->name); ?></h2>
        <a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html__('Tümünü gör', 'polaris'); ?></a>
      </div>

      <div class="product-rail" data-rail>
        <?php
        if (!empty($cat_products)) :
          foreach ($cat_products as $product) :
            polaris_render_product_card($product);
          endforeach;
        else:
          echo '<div class="search-empty">Bu kategoride ürün yok.</div>';
        endif;
        ?>
      </div>
    </section>

  <?php endforeach; ?>

</div><!-- /.container -->

<?php get_footer(); ?>