<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('polaris_render_product_card')) {
    function polaris_get_cart_qty_map() {
        static $qty_map = null;

        if (is_array($qty_map)) {
            return $qty_map;
        }

        $qty_map = [];

        if (!function_exists('WC') || !WC()->cart) {
            return $qty_map;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $pid = isset($cart_item['variation_id']) && (int) $cart_item['variation_id'] > 0
                ? (int) $cart_item['variation_id']
                : (int) $cart_item['product_id'];

            $qty_map[$pid] = ($qty_map[$pid] ?? 0) + (int) $cart_item['quantity'];
        }

        return $qty_map;
    }

    function polaris_render_product_card($product) {
        if (!is_object($product) || !method_exists($product, 'get_id')) {
            return;
        }

        $product_id = $product->get_id();
        $title      = $product->get_name();
        $link       = get_permalink($product_id);
        $image      = $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']);
        $price_html = $product->get_price_html();
        $cart_qty_map = polaris_get_cart_qty_map();
        $initial_qty = isset($cart_qty_map[$product_id]) ? (int) $cart_qty_map[$product_id] : 0;

        $badge = '';
        if ($product->is_on_sale()) {
            $regular = (float) $product->get_regular_price();
            $sale    = (float) $product->get_sale_price();
            if ($regular > 0 && $sale > 0 && $sale < $regular) {
                $badge = sprintf('-%d%%', (int) round((($regular - $sale) / $regular) * 100));
            }
        }

        echo '<article class="p-card" data-product-id="' . esc_attr($product_id) . '">';
        echo '  <a class="p-card__media" href="' . esc_url($link) . '">';
        if ($badge !== '') {
            echo '    <span class="badge badge-sale">' . esc_html($badge) . '</span>';
        }
        echo      $image;
        echo '  </a>';

        echo '  <div class="p-card__body">';
        echo '    <a class="p-card__title" href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
        echo '    <div class="p-card__price">' . wp_kses_post($price_html) . '</div>';

        if ($product->is_purchasable() && $product->is_in_stock()) {
            echo '    <div class="p-card__cart-actions" data-card-actions>';
            echo '      <button class="p-card__cta js-add-to-cart' . ($initial_qty > 0 ? ' hidden' : '') . '" type="button" data-product-id="' . esc_attr($product_id) . '">';
            echo            esc_html__('Sepete ekle', 'polaris');
            echo '      </button>';
            echo '      <div class="p-card__qty' . ($initial_qty > 0 ? '' : ' hidden') . '" data-card-qty-wrap>';
            echo '        <button class="p-card__qty-btn" type="button" data-card-minus aria-label="' . esc_attr__('Azalt', 'polaris') . '">-</button>';
            echo '        <div class="p-card__qty-center">';
            echo '          <span class="p-card__qty-label">' . esc_html__('Sepette', 'polaris') . '</span>';
            echo '          <span class="p-card__qty-value" data-card-qty>' . (int) max(1, $initial_qty) . '</span>';
            echo '        </div>';
            echo '        <button class="p-card__qty-btn" type="button" data-card-plus aria-label="' . esc_attr__('Arttır', 'polaris') . '">+</button>';
            echo '      </div>';
            echo '    </div>';
        } else {
            echo '    <button class="p-card__cta p-card__cta--disabled" type="button" disabled>' . esc_html__('Stokta yok', 'polaris') . '</button>';
        }

        echo '  </div>';
        echo '</article>';
    }
}

$hero_banners  = function_exists('polaris_get_hero_banners') ? polaris_get_hero_banners() : [];
$hero_autoplay = function_exists('polaris_hero_autoplay') ? polaris_hero_autoplay() : true;
$has_woocommerce = class_exists('WooCommerce') && class_exists('WC_Product_Query');

$new_products_title = esc_html__('Yeni Ürünler', 'polaris');
$new_products_limit = 12;

$ordered_category_slugs = [
    'asansor-sistem-olta-bedeni',
    'polaris-firdondulu-fosforlu-aparatli-surf-kursun',
];

get_header();
?>

<div class="container">
  <section class="polaris-section fade-up">
    <div class="hero" id="polarisHero" data-autoplay="<?php echo $hero_autoplay ? 'true' : 'false'; ?>" data-interval="5000">
      <div class="hero__track">
        <?php if (!empty($hero_banners)) : ?>
          <?php foreach ($hero_banners as $index => $src) : ?>
            <article class="hero__slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-index="<?php echo (int) $index; ?>">
              <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr__('Ana banner', 'polaris'); ?>" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>" decoding="async">
            </article>
          <?php endforeach; ?>
        <?php else : ?>
          <article class="hero__slide is-active" data-index="0">
            <div class="hero__empty"><?php echo esc_html__('Banner görsellerini Özelleştir menüsünden yükleyin.', 'polaris'); ?></div>
          </article>
        <?php endif; ?>
      </div>

      <?php if (count($hero_banners) > 1) : ?>
        <button class="hero__nav hero__nav--prev" type="button" data-hero-prev aria-label="<?php echo esc_attr__('Önceki slayt', 'polaris'); ?>">
          <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button class="hero__nav hero__nav--next" type="button" data-hero-next aria-label="<?php echo esc_attr__('Sonraki slayt', 'polaris'); ?>">
          <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>

        <div class="hero__dots" role="tablist" aria-label="<?php echo esc_attr__('Slayt kontrolleri', 'polaris'); ?>">
          <?php foreach ($hero_banners as $index => $src) : ?>
            <button
              class="hero__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
              type="button"
              data-hero-dot="<?php echo (int) $index; ?>"
              role="tab"
              aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
              aria-label="<?php echo esc_attr(sprintf(__('Slayt %d', 'polaris'), $index + 1)); ?>"
            ></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="polaris-section fade-up" data-delay="1">
    <div class="section-title">
      <h2><?php echo esc_html__('Kategoriler', 'polaris'); ?></h2>
      <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/')); ?>"><?php echo esc_html__('Tümünü gör', 'polaris'); ?></a>
    </div>
    <p class="section-kicker"><?php echo esc_html__('2026 koleksiyonunu kategori bazında keşfet.', 'polaris'); ?></p>

    <div class="cat-rail" role="list" aria-label="<?php echo esc_attr__('Ürün kategorileri', 'polaris'); ?>">
      <?php
      $terms = get_terms([
          'taxonomy'   => 'product_cat',
          'hide_empty' => true,
          'orderby'    => 'menu_order',
          'order'      => 'ASC',
          'number'     => 12,
      ]);

      if (!is_wp_error($terms) && !empty($terms)) {
          foreach ($terms as $term) {
              if ($term->slug === 'genel') {
                  continue;
              }

              $link = get_term_link($term);
              if (is_wp_error($link)) {
                  continue;
              }

              echo '<a class="cat-item" href="' . esc_url($link) . '" role="listitem">';
              echo '  <span class="cat-item__name">' . esc_html($term->name) . '</span>';
              echo '  <span class="cat-item__count">' . sprintf(esc_html__('%d ürün', 'polaris'), (int) $term->count) . '</span>';
              echo '  <span class="cat-item__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>';
              echo '</a>';
          }
      } else {
          echo '<div class="search-empty">' . esc_html__('Kategori bulunamadı.', 'polaris') . '</div>';
      }
      ?>
    </div>
  </section>

  <?php if ($has_woocommerce) : ?>
    <section class="polaris-section fade-up" data-delay="2">
      <div class="section-title">
        <h2><?php echo esc_html($new_products_title); ?></h2>
      </div>

      <?php
      $new_query = new WC_Product_Query([
          'status'  => 'publish',
          'limit'   => $new_products_limit,
          'orderby' => 'date',
          'order'   => 'DESC',
          'return'  => 'objects',
      ]);
      $new_products = $new_query->get_products();
      ?>

      <div class="product-rail" data-rail>
        <?php
        if (!empty($new_products)) {
            foreach ($new_products as $product) {
                polaris_render_product_card($product);
            }
        } else {
            echo '<div class="search-empty">' . esc_html__('Ürün bulunamadı.', 'polaris') . '</div>';
        }
        ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($has_woocommerce) : ?>
    <?php foreach ($ordered_category_slugs as $slug) : ?>
    <?php
    $category = get_term_by('slug', $slug, 'product_cat');
    if (!$category || is_wp_error($category)) {
        continue;
    }

    $products_query = new WC_Product_Query([
        'status'   => 'publish',
        'limit'    => 12,
        'orderby'  => 'menu_order',
        'order'    => 'ASC',
        'category' => [$slug],
        'return'   => 'objects',
    ]);
    $category_products = $products_query->get_products();
    $category_link = get_term_link($category);
    if (is_wp_error($category_link)) {
        $category_link = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
    }
    ?>

    <section class="polaris-section fade-up" data-delay="3">
      <div class="section-title">
        <h2><?php echo esc_html($category->name); ?></h2>
        <a href="<?php echo esc_url($category_link); ?>"><?php echo esc_html__('Tümünü gör', 'polaris'); ?></a>
      </div>

      <div class="product-rail" data-rail>
        <?php
        if (!empty($category_products)) {
            foreach ($category_products as $product) {
                polaris_render_product_card($product);
            }
        } else {
            echo '<div class="search-empty">' . esc_html__('Bu kategoride ürün bulunamadı.', 'polaris') . '</div>';
        }
        ?>
      </div>
    </section>
    <?php endforeach; ?>
  <?php else : ?>
    <section class="polaris-section fade-up" data-delay="3">
      <div class="search-empty"><?php echo esc_html__('Ürünleri göstermek için WooCommerce etkin olmalıdır.', 'polaris'); ?></div>
    </section>
  <?php endif; ?>
</div>

<?php
get_footer();
