<?php
/**
 * Single-product hooks and render helpers.
 *
 * @package Polaris
 */

defined('ABSPATH') || exit;

/**
 * Configure the single-product presentation without changing WooCommerce core.
 */
function polaris_configure_single_product_hooks()
{
    if (!function_exists('is_product') || !is_product()) {
        return;
    }

    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
    add_action('woocommerce_before_single_product_summary', 'polaris_render_single_product_gallery', 20);

    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);

    add_action('woocommerce_single_product_summary', 'polaris_render_single_product_category', 4);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    add_action('woocommerce_single_product_summary', 'polaris_render_single_product_rating', 10);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 15);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    add_action('woocommerce_single_product_summary', 'polaris_render_product_highlights', 25);

    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
    add_action('woocommerce_after_single_product_summary', 'polaris_render_product_tags', 10);
}
add_action('wp', 'polaris_configure_single_product_hooks');

/**
 * Add product-specific CSS and JS only when they are needed.
 */
function polaris_enqueue_single_product_assets()
{
    if (!function_exists('is_product') || !is_product()) {
        return;
    }

    $css_path = get_theme_file_path('/assets/css/product-detail.css');
    $js_path  = get_theme_file_path('/assets/js/product-detail.js');

    wp_enqueue_style(
        'polaris-product-detail',
        get_theme_file_uri('/assets/css/product-detail.css'),
        ['polaris-main'],
        file_exists($css_path) ? (string) filemtime($css_path) : '1.0.0'
    );

    wp_enqueue_script(
        'polaris-product-detail',
        get_theme_file_uri('/assets/js/product-detail.js'),
        ['polaris-main'],
        file_exists($js_path) ? (string) filemtime($js_path) : '1.0.0',
        true
    );
    wp_script_add_data('polaris-product-detail', 'defer', true);
}
add_action('wp_enqueue_scripts', 'polaris_enqueue_single_product_assets', 30);

/**
 * Choose the most specific product category, respecting common SEO primary terms.
 *
 * @param int                 $product_id Product ID.
 * @param WP_Term[]|WP_Error $categories Product categories.
 * @return WP_Term|null
 */
function polaris_single_pick_primary_category($product_id, $categories)
{
    if (empty($categories) || is_wp_error($categories)) {
        return null;
    }

    $term_map = [];
    foreach ($categories as $term) {
        if ($term instanceof WP_Term) {
            $term_map[(int) $term->term_id] = $term;
        }
    }

    $primary_id = (int) get_post_meta($product_id, '_yoast_wpseo_primary_product_cat', true);
    if ($primary_id <= 0) {
        $primary_id = (int) get_post_meta($product_id, 'rank_math_primary_product_cat', true);
    }

    if ($primary_id > 0 && isset($term_map[$primary_id])) {
        return $term_map[$primary_id];
    }

    $specific_terms = array_values(array_filter($term_map, static function ($term) {
        $slug = sanitize_title($term->slug);
        $name = function_exists('mb_strtolower')
            ? mb_strtolower(wp_strip_all_tags($term->name))
            : strtolower(wp_strip_all_tags($term->name));

        return 'genel' !== $slug && 'genel' !== $name;
    }));

    $pool = !empty($specific_terms) ? $specific_terms : array_values($term_map);
    usort($pool, static function ($a, $b) {
        $depth_a = count(get_ancestors((int) $a->term_id, 'product_cat'));
        $depth_b = count(get_ancestors((int) $b->term_id, 'product_cat'));

        return $depth_a === $depth_b
            ? (int) $a->term_id <=> (int) $b->term_id
            : $depth_b <=> $depth_a;
    });

    return $pool[0] ?? null;
}

/**
 * Return the primary category for a product.
 *
 * @param WC_Product $product Product object.
 * @return WP_Term|null
 */
function polaris_get_product_primary_category($product)
{
    if (!$product instanceof WC_Product) {
        return null;
    }

    return polaris_single_pick_primary_category(
        $product->get_id(),
        get_the_terms($product->get_id(), 'product_cat')
    );
}

/**
 * Collect the featured and gallery image IDs without duplicates.
 *
 * @param WC_Product $product Product object.
 * @return int[]
 */
function polaris_get_product_gallery_ids($product)
{
    $image_ids = [];
    $featured  = (int) $product->get_image_id();

    if ($featured > 0) {
        $image_ids[] = $featured;
    }

    foreach ((array) $product->get_gallery_image_ids() as $gallery_id) {
        $gallery_id = (int) $gallery_id;
        if ($gallery_id > 0 && !in_array($gallery_id, $image_ids, true)) {
            $image_ids[] = $gallery_id;
        }
    }

    return !empty($image_ids) ? $image_ids : [0];
}

/**
 * Render the custom, dependency-free product gallery.
 */
function polaris_render_single_product_gallery()
{
    global $product;

    if (!$product instanceof WC_Product) {
        return;
    }

    $image_ids = polaris_get_product_gallery_ids($product);
    ?>
    <section class="pd-gallery" id="polarisProductGalleryV2" aria-label="<?php echo esc_attr__('Ürün galerisi', 'polaris'); ?>">
        <div class="pd-gallery__viewer" aria-roledescription="<?php echo esc_attr__('Slayt gösterisi', 'polaris'); ?>">
            <?php $sale_badge = function_exists('polaris_get_product_sale_badge') ? polaris_get_product_sale_badge($product) : ''; ?>
            <?php if ($sale_badge !== '') : ?><span class="pd-gallery__sale"><?php echo esc_html($sale_badge); ?></span><?php endif; ?>
            <?php foreach ($image_ids as $index => $image_id) : ?>
                <?php
                $full_src = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'full') : wc_placeholder_img_src('woocommerce_single');
                $alt      = $image_id > 0 ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
                $alt      = $alt !== '' ? $alt : $product->get_name();
                ?>
                <button
                    class="pd-gallery__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
                    type="button"
                    data-pd-slide="<?php echo esc_attr($index); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('Görsel %1$d / %2$d; büyütmek için açın', 'polaris'), $index + 1, count($image_ids))); ?>"
                    aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>"
                    tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
                >
                    <?php if ($image_id > 0) : ?>
                        <?php
                        echo wp_get_attachment_image(
                            $image_id,
                            'large',
                            false,
                            [
                                'alt'      => $alt,
                                'loading'  => 0 === $index ? 'eager' : 'lazy',
                                'decoding' => 'async',
                            ]
                        ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    <?php else : ?>
                        <img src="<?php echo esc_url($full_src); ?>" alt="<?php echo esc_attr($alt); ?>">
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>

            <?php if (count($image_ids) > 1) : ?>
                <button class="pd-gallery__nav pd-gallery__nav--prev" type="button" data-pd-prev aria-label="<?php echo esc_attr__('Önceki görsel', 'polaris'); ?>">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button class="pd-gallery__nav pd-gallery__nav--next" type="button" data-pd-next aria-label="<?php echo esc_attr__('Sonraki görsel', 'polaris'); ?>">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
        </div>

        <?php if (count($image_ids) > 1) : ?>
            <div class="pd-gallery__dots" aria-label="<?php echo esc_attr__('Galeri sayfaları', 'polaris'); ?>">
                <?php foreach ($image_ids as $index => $image_id) : ?>
                    <button class="pd-gallery__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button" data-pd-dot="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr(sprintf(__('%d. görsele git', 'polaris'), $index + 1)); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
                <?php endforeach; ?>
            </div>

            <div class="pd-gallery__thumbs" aria-label="<?php echo esc_attr__('Ürün küçük görselleri', 'polaris'); ?>">
                <?php foreach ($image_ids as $index => $image_id) : ?>
                    <?php $thumb_alt = $image_id > 0 ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : $product->get_name(); ?>
                    <button class="pd-gallery__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button" data-pd-thumb="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr(sprintf(__('%d. görseli göster', 'polaris'), $index + 1)); ?>">
                        <?php if ($image_id > 0) : ?>
                            <?php echo wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, ['alt' => $thumb_alt, 'loading' => 'lazy']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(wc_placeholder_img_src('woocommerce_thumbnail')); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy">
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="screen-reader-text" data-pd-gallery-status aria-live="polite"></p>
    </section>

    <div class="pd-lightbox hidden" id="polarisProductLightbox" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Ürün görseli', 'polaris'); ?>" aria-hidden="true">
        <button class="pd-lightbox__close" type="button" data-pd-lightbox-close aria-label="<?php echo esc_attr__('Galeriyi kapat', 'polaris'); ?>">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
        <?php if (count($image_ids) > 1) : ?>
            <button class="pd-lightbox__nav pd-lightbox__nav--prev" type="button" data-pd-lightbox-prev aria-label="<?php echo esc_attr__('Önceki görsel', 'polaris'); ?>"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <button class="pd-lightbox__nav pd-lightbox__nav--next" type="button" data-pd-lightbox-next aria-label="<?php echo esc_attr__('Sonraki görsel', 'polaris'); ?>"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
        <?php endif; ?>
        <div class="pd-lightbox__track">
            <?php foreach ($image_ids as $index => $image_id) : ?>
                <?php
                $full_src = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'full') : wc_placeholder_img_src('woocommerce_single');
                $alt      = $image_id > 0 ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : $product->get_name();
                ?>
                <figure class="pd-lightbox__slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-pd-lightbox-slide="<?php echo esc_attr($index); ?>" aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>">
                    <img src="<?php echo esc_url($full_src); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Render a linked primary product category.
 */
function polaris_render_single_product_category()
{
    global $product;
    $category = polaris_get_product_primary_category($product);

    if (!$category) {
        return;
    }

    $url = get_term_link($category);
    if (is_wp_error($url)) {
        return;
    }

    echo '<a class="pd-category" href="' . esc_url($url) . '">' . esc_html($category->name) . '</a>';
}

/**
 * Render rating only when the product has reviews.
 */
function polaris_render_single_product_rating()
{
    global $product;

    if (!$product instanceof WC_Product || !wc_review_ratings_enabled()) {
        return;
    }

    $review_count = (int) $product->get_review_count();
    if ($review_count < 1) {
        return;
    }

    echo '<a class="pd-rating" href="#polaris-accordion-reviews" data-pd-open-accordion="reviews">';
    echo wc_get_rating_html($product->get_average_rating(), $review_count); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '<span>' . esc_html(sprintf(_n('%d yorum', '%d yorum', $review_count, 'polaris'), $review_count)) . '</span>';
    echo '</a>';
}

/**
 * Return product marketing highlights.
 *
 * @param WC_Product $product Product object.
 * @return string[]
 */
function polaris_get_product_highlights($product)
{
    $fallback = [
        __('Aerodinamik yapı', 'polaris'),
        __('Dayanıklı', 'polaris'),
        __('Kaplama altı gramaj', 'polaris'),
        __('Paslanmaz', 'polaris'),
        __('Çevreye duyarlı', 'polaris'),
        __('Üstün kalite', 'polaris'),
    ];

    if (!$product instanceof WC_Product) {
        return $fallback;
    }

    $stored = (string) $product->get_meta('_polaris_product_highlights', true);
    $items  = preg_split('/\r\n|\r|\n/', $stored);
    $items  = array_values(array_filter(array_map('sanitize_text_field', (array) $items)));

    return apply_filters('polaris_product_highlights', !empty($items) ? $items : $fallback, $product);
}

/**
 * Render the icon-free horizontal highlights rail.
 */
function polaris_render_product_highlights()
{
    global $product;
    $items = polaris_get_product_highlights($product);

    if (empty($items)) {
        return;
    }
    ?>
    <div class="pd-highlights" aria-label="<?php echo esc_attr__('Ürün özellikleri', 'polaris'); ?>">
        <?php foreach ($items as $item) : ?>
            <span class="pd-highlight"><?php echo esc_html($item); ?></span>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Render one accessible accordion item.
 *
 * @param string   $key      Stable key.
 * @param string   $label    Button label.
 * @param callable $callback Panel renderer.
 * @param bool     $open     Initial state.
 */
function polaris_render_accordion_item($key, $label, $callback, $open = false)
{
    $button_id = 'polaris-accordion-' . sanitize_html_class($key);
    $panel_id  = $button_id . '-panel';
    ?>
    <section class="pd-accordion__item<?php echo $open ? ' is-open' : ''; ?>">
        <h2 class="pd-accordion__heading">
            <button id="<?php echo esc_attr($button_id); ?>" class="pd-accordion__trigger" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($panel_id); ?>" data-pd-accordion-trigger="<?php echo esc_attr($key); ?>">
                <span><?php echo esc_html($label); ?></span>
                <span class="pd-accordion__symbol" aria-hidden="true"></span>
            </button>
        </h2>
        <div id="<?php echo esc_attr($panel_id); ?>" class="pd-accordion__panel" role="region" aria-labelledby="<?php echo esc_attr($button_id); ?>"<?php echo $open ? '' : ' hidden'; ?>>
            <div class="pd-accordion__content"><?php call_user_func($callback); ?></div>
        </div>
    </section>
    <?php
}

/**
 * Render description, reviews and additional information as an accordion.
 */
function polaris_render_product_accordion()
{
    global $product;

    if (!$product instanceof WC_Product) {
        return;
    }

    $review_count = (int) $product->get_review_count();
    ?>
    <div class="pd-accordion" data-pd-accordion>
        <?php
        polaris_render_accordion_item('description', __('Tüm detaylar', 'polaris'), static function () use ($product) {
            $description = apply_filters('the_content', $product->get_description());
            if (trim(wp_strip_all_tags($description)) === '') {
                echo '<p class="pd-empty">' . esc_html__('Bu ürün için uzun açıklama eklenmemiş.', 'polaris') . '</p>';
                return;
            }
            echo '<div class="pd-richtext">' . wp_kses_post($description) . '</div>';
        });

        polaris_render_accordion_item(
            'reviews',
            sprintf(__('Yorumlar (%d)', 'polaris'), $review_count),
            static function () {
                comments_template();
            }
        );

        polaris_render_accordion_item('specifications', __('Teknik özellikler', 'polaris'), static function () use ($product) {
            ob_start();
            do_action('woocommerce_product_additional_information', $product);
            $additional_information = trim((string) ob_get_clean());

            if ($additional_information === '') {
                echo '<p class="pd-empty">' . esc_html__('Bu ürün için teknik özellik eklenmemiş.', 'polaris') . '</p>';
                return;
            }

            echo wp_kses_post($additional_information);
        });
        ?>
    </div>
    <?php
}

/**
 * Return cart quantities keyed by product ID.
 *
 * @return int[]
 */
function polaris_single_get_cart_qty_map()
{
    $qty_map = [];

    if (!function_exists('WC') || !WC()->cart) {
        return $qty_map;
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = !empty($cart_item['variation_id'])
            ? (int) $cart_item['variation_id']
            : (int) $cart_item['product_id'];

        $qty_map[$product_id] = ($qty_map[$product_id] ?? 0) + (int) $cart_item['quantity'];
    }

    return $qty_map;
}

/**
 * Return products in the same exact primary category.
 *
 * @param WC_Product $product Product object.
 * @param WP_Term    $category Primary category.
 * @return WC_Product[]
 */
function polaris_get_product_family($product, $category)
{
    if (!$product instanceof WC_Product || !$category instanceof WP_Term) {
        return [$product];
    }

    $query_args = [
        'status'     => 'publish',
        'limit'      => -1,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
        'return'     => 'objects',
        'tax_query'  => [
            [
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => [(int) $category->term_id],
                'include_children' => false,
            ],
        ],
    ];

    $items = function_exists('polaris_get_cached_products')
        ? polaris_get_cached_products($query_args, 'single_family_' . (int) $category->term_id, 300)
        : (new WC_Product_Query($query_args))->get_products();

    $items = array_values(array_filter((array) $items, static function ($item) use ($product) {
        return $item instanceof WC_Product
            && ($item->is_visible() || $item->get_id() === $product->get_id());
    }));

    $contains_current = false;
    foreach ($items as $item) {
        if ($item->get_id() === $product->get_id()) {
            $contains_current = true;
            break;
        }
    }

    if (!$contains_current) {
        array_unshift($items, $product);
    }

    usort($items, static function ($a, $b) use ($product) {
        $a_current = $a->get_id() === $product->get_id();
        $b_current = $b->get_id() === $product->get_id();

        if ($a_current === $b_current) {
            return 0;
        }

        return $a_current ? -1 : 1;
    });

    return !empty($items) ? $items : [$product];
}

/**
 * Render a same-category product row.
 *
 * @param WC_Product $item       Listed product.
 * @param int[]      $qty_map    Cart quantity map.
 * @param bool       $is_current Whether this is the viewed product.
 */
function polaris_render_product_family_item($item, $qty_map, $is_current)
{
    $product_id  = (int) $item->get_id();
    $initial_qty = isset($qty_map[$product_id]) ? (int) $qty_map[$product_id] : 0;
    $quick_add   = function_exists('polaris_product_supports_quick_add') && polaris_product_supports_quick_add($item);
    ?>
    <article class="pd-family-item<?php echo $is_current ? ' is-current' : ''; ?>" data-product-card data-product-id="<?php echo esc_attr($product_id); ?>">
        <?php if ($is_current) : ?><span class="pd-family-item__current"><?php echo esc_html__('Şu an inceleniyor', 'polaris'); ?></span><?php endif; ?>
        <a class="pd-family-item__image" href="<?php echo esc_url($item->get_permalink()); ?>"><?php echo wp_kses_post($item->get_image('woocommerce_thumbnail', ['loading' => 'lazy'])); ?></a>
        <div class="pd-family-item__body">
            <a class="pd-family-item__name" href="<?php echo esc_url($item->get_permalink()); ?>"><?php echo esc_html($item->get_name()); ?></a>
            <div class="pd-family-item__price"><?php echo wp_kses_post($item->get_price_html()); ?></div>
        </div>
        <div class="pd-family-item__actions" data-card-actions>
            <?php if ($quick_add) : ?>
                <button class="pd-family-item__add js-add-to-cart<?php echo $initial_qty > 0 ? ' hidden' : ''; ?>" type="button" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('%s ürününü sepete ekle', 'polaris'), $item->get_name())); ?>"><i class="fa-solid fa-cart-plus" aria-hidden="true"></i></button>
                <div class="pd-family-item__qty<?php echo $initial_qty > 0 ? '' : ' hidden'; ?>" data-card-qty-wrap>
                    <button class="p-card__qty-btn" type="button" data-card-minus aria-label="<?php echo esc_attr__('Azalt', 'polaris'); ?>">−</button>
                    <span data-card-qty><?php echo (int) max(1, $initial_qty); ?></span>
                    <button class="p-card__qty-btn" type="button" data-card-plus aria-label="<?php echo esc_attr__('Arttır', 'polaris'); ?>">+</button>
                </div>
            <?php elseif ($item->is_purchasable() && $item->is_in_stock()) : ?>
                <a class="pd-family-item__add" href="<?php echo esc_url($item->get_permalink()); ?>" aria-label="<?php echo esc_attr($item->add_to_cart_text()); ?>"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <?php else : ?>
                <button class="pd-family-item__add is-disabled" type="button" disabled aria-label="<?php echo esc_attr__('Stokta yok', 'polaris'); ?>"><i class="fa-solid fa-ban" aria-hidden="true"></i></button>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

/**
 * Render the complete same-category product list.
 */
function polaris_render_product_family()
{
    global $product;

    if (!$product instanceof WC_Product) {
        return;
    }

    $category = polaris_get_product_primary_category($product);
    if (!$category) {
        return;
    }

    $category_url = get_term_link($category);
    $items        = polaris_get_product_family($product, $category);
    $qty_map      = polaris_single_get_cart_qty_map();
    ?>
    <aside class="pd-family" aria-labelledby="pd-family-title">
        <div class="pd-section-heading">
            <h2 id="pd-family-title"><?php echo esc_html__('Bu kategorideki diğer ürünler', 'polaris'); ?></h2>
            <?php if (!is_wp_error($category_url)) : ?><a href="<?php echo esc_url($category_url); ?>"><?php echo esc_html__('Tümünü gör', 'polaris'); ?></a><?php endif; ?>
        </div>
        <div class="pd-family__list">
            <?php foreach ($items as $item) : ?>
                <?php polaris_render_product_family_item($item, $qty_map, $item->get_id() === $product->get_id()); ?>
            <?php endforeach; ?>
        </div>
    </aside>
    <?php
}

/**
 * Render linked product tags before related products.
 */
function polaris_render_product_tags()
{
    global $product;

    if (!$product instanceof WC_Product) {
        return;
    }

    $tags = get_the_terms($product->get_id(), 'product_tag');
    if (empty($tags) || is_wp_error($tags)) {
        return;
    }
    ?>
    <section class="pd-tags" aria-labelledby="pd-tags-title">
        <h2 id="pd-tags-title"><?php echo esc_html__('Etiketler', 'polaris'); ?></h2>
        <div class="pd-tags__list">
            <?php foreach ($tags as $tag) : ?>
                <?php $url = get_term_link($tag); ?>
                <?php if (!is_wp_error($url)) : ?><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($tag->name); ?></a><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/**
 * Set a restrained related-products layout on single product pages.
 *
 * @param array $args Related-product arguments.
 * @return array
 */
function polaris_single_related_products_args($args)
{
    if (function_exists('is_product') && is_product()) {
        $args['posts_per_page'] = 6;
        $args['columns']        = 6;
    }

    return $args;
}
add_filter('woocommerce_output_related_products_args', 'polaris_single_related_products_args');

function polaris_single_related_products_heading($heading)
{
    return function_exists('is_product') && is_product()
        ? __('İlgilenebileceğiniz diğer ürünler', 'polaris')
        : $heading;
}
add_filter('woocommerce_product_related_products_heading', 'polaris_single_related_products_heading');

/**
 * Match the theme's article cards with valid related-products container markup.
 *
 * @param string $html Default loop opening markup.
 * @return string
 */
function polaris_single_product_loop_start($html)
{
    if (!function_exists('is_product') || !is_product()) {
        return $html;
    }

    $columns = function_exists('wc_get_loop_prop') ? absint(wc_get_loop_prop('columns')) : 6;
    return '<div class="products columns-' . max(1, $columns) . '">';
}
add_filter('woocommerce_product_loop_start', 'polaris_single_product_loop_start');

/**
 * Close the related-products container opened above.
 *
 * @param string $html Default loop closing markup.
 * @return string
 */
function polaris_single_product_loop_end($html)
{
    return function_exists('is_product') && is_product() ? '</div>' : $html;
}
add_filter('woocommerce_product_loop_end', 'polaris_single_product_loop_end');

/**
 * Add the marketing-highlights field to the product editor.
 */
function polaris_product_highlights_admin_field()
{
    if (!function_exists('woocommerce_wp_textarea_input')) {
        return;
    }

    woocommerce_wp_textarea_input([
        'id'          => '_polaris_product_highlights',
        'label'       => __('Öne çıkan özellikler', 'polaris'),
        'description' => __('Her satıra bir kısa özellik yazın. Boş bırakılırsa tema varsayılanları kullanılır.', 'polaris'),
        'desc_tip'    => true,
        'rows'        => 5,
    ]);
}
add_action('woocommerce_product_options_general_product_data', 'polaris_product_highlights_admin_field');

/**
 * Sanitize and save the marketing-highlights field.
 *
 * @param WC_Product $product Product being saved.
 */
function polaris_save_product_highlights($product)
{
    if (!$product instanceof WC_Product || !isset($_POST['_polaris_product_highlights'])) {
        return;
    }

    $raw   = wp_unslash($_POST['_polaris_product_highlights']);
    $lines = preg_split('/\r\n|\r|\n/', is_string($raw) ? $raw : '');
    $lines = array_slice(array_values(array_filter(array_map('sanitize_text_field', (array) $lines))), 0, 12);

    $product->update_meta_data('_polaris_product_highlights', implode("\n", $lines));
}
add_action('woocommerce_admin_process_product_object', 'polaris_save_product_highlights');
