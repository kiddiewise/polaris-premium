<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('WC') || !WC()) {
    return;
}

if (!function_exists('polaris_single_get_cart_qty_map')) {
    function polaris_single_get_cart_qty_map() {
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
}

if (!function_exists('polaris_single_render_family_item')) {
    function polaris_single_render_family_item($product, $qty_map = [], $is_current = false) {
        if (!is_object($product) || !method_exists($product, 'get_id')) {
            return;
        }

        $product_id   = (int) $product->get_id();
        $title        = $product->get_name();
        $link         = get_permalink($product_id);
        $image        = $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']);
        $price_html   = $product->get_price_html();
        $initial_qty  = isset($qty_map[$product_id]) ? (int) $qty_map[$product_id] : 0;
        $is_available = $product->is_purchasable() && $product->is_in_stock();
        $badge        = '';

        if ($product->is_on_sale()) {
            $regular = (float) $product->get_regular_price();
            $sale    = (float) $product->get_sale_price();

            if ($regular > 0 && $sale > 0 && $sale < $regular) {
                $badge = sprintf('-%d%%', (int) round((($regular - $sale) / $regular) * 100));
            }
        }

        echo '<article class="pd-variant' . ($is_current ? ' is-current' : '') . '" data-product-card data-product-id="' . esc_attr($product_id) . '">';
        if ($badge !== '') {
            echo '<span class="badge pd-variant__sale">' . esc_html($badge) . '</span>';
        }

        if ($is_current) {
            echo '<span class="pd-variant__current">' . esc_html__('Su an inceleniyor', 'polaris') . '</span>';
        }

        echo '  <a class="pd-variant__thumb" href="' . esc_url($link) . '">' . $image . '</a>';
        echo '  <div class="pd-variant__main">';
        echo '    <a class="pd-variant__title" href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
        echo '    <div class="pd-variant__price">' . wp_kses_post($price_html) . '</div>';
        echo '  </div>';
        echo '  <div class="pd-variant__actions" data-card-actions>';

        if ($is_available) {
            echo '    <button class="pd-variant__add js-add-to-cart' . ($initial_qty > 0 ? ' hidden' : '') . '" type="button" data-product-id="' . esc_attr($product_id) . '" aria-label="' . esc_attr__('Sepete ekle', 'polaris') . '">';
            echo '      <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>';
            echo '    </button>';
            echo '    <div class="pd-variant__qty' . ($initial_qty > 0 ? '' : ' hidden') . '" data-card-qty-wrap>';
            echo '      <button class="p-card__qty-btn" type="button" data-card-minus aria-label="' . esc_attr__('Azalt', 'polaris') . '">-</button>';
            echo '      <span class="pd-variant__qty-value" data-card-qty>' . (int) max(1, $initial_qty) . '</span>';
            echo '      <button class="p-card__qty-btn" type="button" data-card-plus aria-label="' . esc_attr__('Arttir', 'polaris') . '">+</button>';
            echo '    </div>';
        } else {
            echo '    <button class="pd-variant__add pd-variant__add--disabled" type="button" disabled aria-label="' . esc_attr__('Stokta yok', 'polaris') . '">';
            echo '      <i class="fa-solid fa-ban" aria-hidden="true"></i>';
            echo '    </button>';
        }

        echo '  </div>';
        echo '</article>';
    }
}

if (!function_exists('polaris_single_pick_primary_category')) {
    function polaris_single_pick_primary_category($product_id, $categories) {
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

        $non_generic_terms = array_values(array_filter($categories, function ($term) {
            if (!($term instanceof WP_Term)) {
                return false;
            }

            $term_slug = sanitize_title($term->slug);
            $term_name = wp_strip_all_tags($term->name);
            $term_name = function_exists('mb_strtolower') ? mb_strtolower($term_name) : strtolower($term_name);

            return $term_slug !== 'genel' && $term_name !== 'genel';
        }));

        $pool = !empty($non_generic_terms) ? $non_generic_terms : $categories;

        usort($pool, function ($a, $b) {
            $a_depth = count(get_ancestors((int) $a->term_id, 'product_cat'));
            $b_depth = count(get_ancestors((int) $b->term_id, 'product_cat'));

            if ($a_depth !== $b_depth) {
                return $b_depth <=> $a_depth;
            }

            return (int) $a->term_id <=> (int) $b->term_id;
        });

        return $pool[0] ?? null;
    }
}

get_header();

echo "\n<!-- Polaris Custom Product Detail Template -->\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

while (have_posts()) :
    the_post();

    $product = wc_get_product(get_the_ID());
    if (!$product) {
        continue;
    }

    do_action('woocommerce_before_single_product');

    if (post_password_required()) {
        echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        continue;
    }

    $product_id      = (int) $product->get_id();
    $price_html      = $product->get_price_html();
    $average_rating  = (float) $product->get_average_rating();
    $review_count    = (int) $product->get_review_count();
    $short_desc      = apply_filters('woocommerce_short_description', $post->post_excerpt ?? '');
    $full_desc       = apply_filters('the_content', get_the_content(null, false, $post));
    $cart_qty_map    = polaris_single_get_cart_qty_map();
    $image_ids       = [];
    $featured_image  = (int) $product->get_image_id();
    $gallery_ids     = $product->get_gallery_image_ids();
    $whatsapp_message = sprintf(__('Merhaba, "%s" urunu hakkinda bilgi alabilir miyim?', 'polaris'), $product->get_name());
    $whatsapp_url     = function_exists('polaris_get_whatsapp_url')
        ? polaris_get_whatsapp_url($whatsapp_message)
        : esc_url('https://wa.me/905462629002?text=' . rawurlencode($whatsapp_message));
    $attributes      = $product->get_attributes();
    $sku             = $product->get_sku();

    if ($featured_image > 0) {
        $image_ids[] = $featured_image;
    }

    if (!empty($gallery_ids) && is_array($gallery_ids)) {
        foreach ($gallery_ids as $gallery_id) {
            $gallery_id = (int) $gallery_id;
            if ($gallery_id > 0 && !in_array($gallery_id, $image_ids, true)) {
                $image_ids[] = $gallery_id;
            }
        }
    }

    if (empty($image_ids)) {
        $image_ids[] = 0;
    }

    $categories       = get_the_terms($product_id, 'product_cat');
    $primary_category = null;
    $family_products  = [$product];

    $primary_category = polaris_single_pick_primary_category($product_id, $categories);

    if ($primary_category) {
        if (function_exists('polaris_get_cached_products')) {
            $family_products = polaris_get_cached_products([
                'status'    => 'publish',
                'limit'     => -1,
                'orderby'   => 'menu_order',
                'order'     => 'ASC',
                'tax_query' => [
                    [
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => [(int) $primary_category->term_id],
                        'include_children' => false,
                    ],
                ],
            ], 'single_family_' . (int) $primary_category->term_id, 300);
        } elseif (class_exists('WC_Product_Query')) {
            $family_query = new WC_Product_Query([
                'status'    => 'publish',
                'limit'     => -1,
                'orderby'   => 'menu_order',
                'order'     => 'ASC',
                'return'    => 'objects',
                'tax_query' => [
                    [
                        'taxonomy'         => 'product_cat',
                        'field'            => 'term_id',
                        'terms'            => [(int) $primary_category->term_id],
                        'include_children' => false,
                    ],
                ],
            ]);

            $family_products = $family_query->get_products();
        }

        if (empty($family_products)) {
            $family_products = [$product];
        }
    }

    $ordered_family = [];
    $rest_family    = [];
    foreach ($family_products as $family_item) {
        if ((int) $family_item->get_id() === $product_id) {
            $ordered_family[] = $family_item;
        } else {
            $rest_family[] = $family_item;
        }
    }
    $family_products = array_merge($ordered_family, $rest_family);

    $rating_total = 0;
    for ($star = 1; $star <= 5; $star++) {
        $rating_total += (int) $product->get_rating_count($star);
    }

    $reviews = get_comments([
        'post_id' => $product_id,
        'status'  => 'approve',
        'type'    => 'review',
        'number'  => 20,
        'order'   => 'DESC',
    ]);

    $current_user      = wp_get_current_user();
    $customer_email    = isset($current_user->user_email) ? (string) $current_user->user_email : '';
    $customer_id       = isset($current_user->ID) ? (int) $current_user->ID : 0;
    $review_verified   = 'no' === get_option('woocommerce_review_rating_verification_required')
        || wc_customer_bought_product($customer_email, $customer_id, $product_id);
    ?>
    <article id="product-<?php the_ID(); ?>" <?php wc_product_class('pd-shell', $product); ?>>
      <div class="container">
        <div class="pd-grid">
          <section class="pd-gallery" id="polarisProductGallery" aria-label="<?php echo esc_attr__('Urun galerisi', 'polaris'); ?>">
            <div class="pd-gallery__viewer">
              <?php foreach ($image_ids as $index => $img_id) : ?>
                <?php
                $full_src = $img_id > 0 ? wp_get_attachment_image_url($img_id, 'full') : wc_placeholder_img_src('woocommerce_single');
                $main_src = $img_id > 0 ? wp_get_attachment_image_url($img_id, 'large') : wc_placeholder_img_src('woocommerce_single');
                $alt      = $img_id > 0 ? get_post_meta($img_id, '_wp_attachment_image_alt', true) : $product->get_name();
                ?>
                <button
                  class="pd-gallery__slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
                  type="button"
                  data-pd-slide="<?php echo (int) $index; ?>"
                  data-full-src="<?php echo esc_url($full_src); ?>"
                  aria-label="<?php echo esc_attr(sprintf(__('Gorsel %d', 'polaris'), $index + 1)); ?>"
                >
                  <img src="<?php echo esc_url($main_src); ?>" alt="<?php echo esc_attr($alt); ?>" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                </button>
              <?php endforeach; ?>

              <?php if (count($image_ids) > 1) : ?>
                <button class="pd-gallery__nav pd-gallery__nav--prev" type="button" data-pd-prev aria-label="<?php echo esc_attr__('Onceki gorsel', 'polaris'); ?>">
                  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button class="pd-gallery__nav pd-gallery__nav--next" type="button" data-pd-next aria-label="<?php echo esc_attr__('Sonraki gorsel', 'polaris'); ?>">
                  <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
              <?php endif; ?>
            </div>

            <div class="pd-gallery__thumbs">
              <?php foreach ($image_ids as $index => $img_id) : ?>
                <?php
                $thumb_src = $img_id > 0 ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : wc_placeholder_img_src('woocommerce_thumbnail');
                $alt       = $img_id > 0 ? get_post_meta($img_id, '_wp_attachment_image_alt', true) : $product->get_name();
                ?>
                <button
                  class="pd-gallery__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
                  type="button"
                  data-pd-thumb="<?php echo (int) $index; ?>"
                  aria-label="<?php echo esc_attr(sprintf(__('Kucuk gorsel %d', 'polaris'), $index + 1)); ?>"
                >
                  <img src="<?php echo esc_url($thumb_src); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
                </button>
              <?php endforeach; ?>
            </div>
          </section>

          <section class="pd-info">
            <?php if ($primary_category) : ?>
              <div class="pd-kicker"><?php echo esc_html($primary_category->name); ?></div>
            <?php endif; ?>

            <h1 class="pd-title"><?php echo esc_html($product->get_name()); ?></h1>

            <div class="pd-topline">
              <div class="pd-price"><?php echo wp_kses_post($price_html); ?></div>
              <?php if ($average_rating > 0) : ?>
                <div class="pd-rating">
                  <?php echo wc_get_rating_html($average_rating, $review_count); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                  <span><?php echo esc_html(number_format_i18n($average_rating, 1)); ?></span>
                </div>
              <?php endif; ?>
            </div>

            <?php if (!empty($short_desc)) : ?>
              <div class="pd-shortdesc"><?php echo wp_kses_post($short_desc); ?></div>
            <?php endif; ?>

            <a class="btn btn-ghost pd-wa" href="<?php echo $whatsapp_url; ?>" target="_blank" rel="noopener">
              <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
              <?php echo esc_html__('WhatsApp ile iletisime gec', 'polaris'); ?>
            </a>

            <div class="pd-family">
              <div class="pd-family__lead">
                <?php echo esc_html__('Bu urunun diger gramaj seceneklerini direkt buradan ekleyebilirsiniz.', 'polaris'); ?>
              </div>

              <div class="pd-family__list">
                <?php foreach ($family_products as $family_product) : ?>
                  <?php polaris_single_render_family_item($family_product, $cart_qty_map, (int) $family_product->get_id() === $product_id); ?>
                <?php endforeach; ?>
              </div>
            </div>
          </section>
        </div>

        <section class="pd-tabs" id="polarisProductTabs">
          <div class="pd-tabs__nav" role="tablist" aria-label="<?php echo esc_attr__('Urun icerik sekmeleri', 'polaris'); ?>">
            <button class="pd-tab-btn is-active" type="button" role="tab" aria-selected="true" data-pd-tab-btn="description"><?php echo esc_html__('Aciklama', 'polaris'); ?></button>
            <button class="pd-tab-btn" type="button" role="tab" aria-selected="false" data-pd-tab-btn="features"><?php echo esc_html__('Ozellikler', 'polaris'); ?></button>
            <button class="pd-tab-btn" type="button" role="tab" aria-selected="false" data-pd-tab-btn="reviews">
              <?php echo esc_html__('Yorumlar', 'polaris'); ?>
              <span class="pd-tab-btn__count"><?php echo (int) $review_count; ?></span>
            </button>
          </div>

          <div class="pd-tab-panel is-active" role="tabpanel" data-pd-tab-panel="description">
            <?php if (!empty(trim(wp_strip_all_tags($full_desc)))) : ?>
              <div class="pd-richtext"><?php echo wp_kses_post($full_desc); ?></div>
            <?php else : ?>
              <div class="search-empty"><?php echo esc_html__('Bu urun icin aciklama eklenmemis.', 'polaris'); ?></div>
            <?php endif; ?>
          </div>

          <div class="pd-tab-panel" role="tabpanel" data-pd-tab-panel="features">
            <div class="pd-features">
              <div class="pd-feature-row">
                <span><?php echo esc_html__('Stok durumu', 'polaris'); ?></span>
                <strong><?php echo esc_html($product->is_in_stock() ? __('Stokta var', 'polaris') : __('Stokta yok', 'polaris')); ?></strong>
              </div>

              <?php if (!empty($sku)) : ?>
                <div class="pd-feature-row">
                  <span><?php echo esc_html__('SKU', 'polaris'); ?></span>
                  <strong><?php echo esc_html($sku); ?></strong>
                </div>
              <?php endif; ?>

              <?php
              if (!empty($attributes)) {
                  foreach ($attributes as $attribute) {
                      if (!$attribute) {
                          continue;
                      }

                      $label = wc_attribute_label($attribute->get_name());
                      $value = '';

                      if ($attribute->is_taxonomy()) {
                          $terms = wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names']);
                          if (!empty($terms) && !is_wp_error($terms)) {
                              $value = implode(', ', $terms);
                          }
                      } else {
                          $options = $attribute->get_options();
                          if (!empty($options) && is_array($options)) {
                              $value = implode(', ', $options);
                          }
                      }

                      if ($value === '') {
                          continue;
                      }
                      ?>
                      <div class="pd-feature-row">
                        <span><?php echo esc_html($label); ?></span>
                        <strong><?php echo esc_html($value); ?></strong>
                      </div>
                      <?php
                  }
              }
              ?>
            </div>
          </div>

          <div class="pd-tab-panel" role="tabpanel" data-pd-tab-panel="reviews">
            <div class="pd-reviews">
              <div class="pd-review-summary">
                <div class="pd-review-score">
                  <strong><?php echo esc_html(number_format_i18n($average_rating > 0 ? $average_rating : 0, 1)); ?></strong>
                  <span><?php echo esc_html__('5 uzerinden', 'polaris'); ?></span>
                  <small><?php echo esc_html(sprintf(_n('%d yorum', '%d yorum', $review_count, 'polaris'), $review_count)); ?></small>
                </div>

                <div class="pd-review-bars">
                  <?php for ($star = 5; $star >= 1; $star--) : ?>
                    <?php
                    $star_count = (int) $product->get_rating_count($star);
                    $ratio = $rating_total > 0 ? ($star_count / $rating_total) * 100 : 0;
                    ?>
                    <div class="pd-review-bar">
                      <span><?php echo (int) $star; ?></span>
                      <div class="pd-review-bar__track">
                        <i style="width: <?php echo esc_attr(number_format($ratio, 2, '.', '')); ?>%;"></i>
                      </div>
                      <em><?php echo esc_html($star_count); ?></em>
                    </div>
                  <?php endfor; ?>
                </div>
              </div>

              <div class="pd-review-list">
                <?php if (!empty($reviews)) : ?>
                  <?php foreach ($reviews as $review) : ?>
                    <?php
                    $reviewer   = get_comment_author($review);
                    $rating     = (int) get_comment_meta($review->comment_ID, 'rating', true);
                    $date_label = get_comment_date(get_option('date_format'), $review);
                    ?>
                    <article class="pd-review-item">
                      <div class="pd-review-item__top">
                        <strong><?php echo esc_html($reviewer); ?></strong>
                        <span><?php echo esc_html($date_label); ?></span>
                      </div>
                      <?php if ($rating > 0) : ?>
                        <div class="pd-review-item__rating"><?php echo wc_get_rating_html($rating); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                      <?php endif; ?>
                      <div class="pd-review-item__content"><?php echo wp_kses_post(wpautop($review->comment_content)); ?></div>
                    </article>
                  <?php endforeach; ?>
                <?php else : ?>
                  <div class="search-empty"><?php echo esc_html__('Henuz yorum yapilmamis.', 'polaris'); ?></div>
                <?php endif; ?>
              </div>

              <?php if (comments_open()) : ?>
                <div class="pd-review-form-wrap">
                  <?php if ($review_verified) : ?>
                    <?php
                    $commenter = wp_get_current_commenter();
                    $comment_form = [
                        'title_reply'          => __('Yorum birakin', 'polaris'),
                        'title_reply_to'       => __('Yaniti duzenle', 'polaris'),
                        'label_submit'         => __('Yorumu gonder', 'polaris'),
                        'class_submit'         => 'btn btn-primary',
                        'comment_notes_after'  => '',
                        'logged_in_as'         => '',
                        'id_form'              => 'pd-review-form',
                        'comment_field'        => '',
                        'fields'               => [
                            'author' => '<p class="comment-form-author"><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author'] ?? '') . '" placeholder="' . esc_attr__('Adiniz', 'polaris') . '"' . (get_option('require_name_email') ? ' required' : '') . '></p>',
                            'email'  => '<p class="comment-form-email"><input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email'] ?? '') . '" placeholder="' . esc_attr__('E-posta', 'polaris') . '"' . (get_option('require_name_email') ? ' required' : '') . '></p>',
                        ],
                    ];

                    if (wc_review_ratings_enabled()) {
                        $comment_form['comment_field'] .= '<p class="comment-form-rating"><label for="rating">' . esc_html__('Puaniniz', 'polaris') . '</label><select name="rating" id="rating" required><option value="">' . esc_html__('Puan secin', 'polaris') . '</option><option value="5">' . esc_html__('5 - Mukemmel', 'polaris') . '</option><option value="4">' . esc_html__('4 - Iyi', 'polaris') . '</option><option value="3">' . esc_html__('3 - Orta', 'polaris') . '</option><option value="2">' . esc_html__('2 - Zayif', 'polaris') . '</option><option value="1">' . esc_html__('1 - Cok kotu', 'polaris') . '</option></select></p>';
                    }

                    $comment_form['comment_field'] .= '<p class="comment-form-comment"><textarea id="comment" name="comment" placeholder="' . esc_attr__('Yorumunuzu yazin', 'polaris') . '" rows="4" required></textarea></p>';

                    comment_form(apply_filters('woocommerce_product_review_comment_form_args', $comment_form)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                  <?php else : ?>
                    <div class="search-empty"><?php echo esc_html__('Yorum yapabilmek icin urunu satin almis olmalisiniz.', 'polaris'); ?></div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </section>
      </div>
    </article>

    <div class="pd-lightbox hidden" id="polarisProductLightbox" aria-hidden="true">
      <button class="pd-lightbox__close" type="button" data-pd-lightbox-close aria-label="<?php echo esc_attr__('Kapat', 'polaris'); ?>">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
      <button class="pd-lightbox__nav pd-lightbox__nav--prev" type="button" data-pd-lightbox-prev aria-label="<?php echo esc_attr__('Onceki gorsel', 'polaris'); ?>">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
      </button>
      <button class="pd-lightbox__nav pd-lightbox__nav--next" type="button" data-pd-lightbox-next aria-label="<?php echo esc_attr__('Sonraki gorsel', 'polaris'); ?>">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
      <div class="pd-lightbox__track">
        <?php foreach ($image_ids as $index => $img_id) : ?>
          <?php
          $full_src = $img_id > 0 ? wp_get_attachment_image_url($img_id, 'full') : wc_placeholder_img_src('woocommerce_single');
          $alt      = $img_id > 0 ? get_post_meta($img_id, '_wp_attachment_image_alt', true) : $product->get_name();
          ?>
          <figure class="pd-lightbox__slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-pd-lightbox-slide="<?php echo (int) $index; ?>">
            <img src="<?php echo esc_url($full_src); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
          </figure>
        <?php endforeach; ?>
      </div>
    </div>
    <?php

    do_action('woocommerce_after_single_product');
endwhile;

get_footer();
