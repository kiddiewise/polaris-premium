<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_polaris_live_search', 'polaris_live_search');
add_action('wp_ajax_nopriv_polaris_live_search', 'polaris_live_search');

function polaris_live_search()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'polaris_nonce')) {
        wp_send_json_error(['message' => 'Geçersiz nonce'], 403);
    }

    $query_raw = isset($_POST['q']) ? sanitize_text_field(wp_unslash($_POST['q'])) : '';
    $query_raw = trim($query_raw);

    if ($query_raw === '' || mb_strlen($query_raw) < 2) {
        wp_send_json_success([]);
    }

    $query_term = mb_substr($query_raw, 0, 64);

    $query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        's'                      => $query_term,
        'posts_per_page'         => 8,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    $items = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $product_id = get_the_ID();
            $product    = function_exists('wc_get_product') ? wc_get_product($product_id) : null;

            $image_url = '';
            if (has_post_thumbnail($product_id)) {
                $image_url = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');
            }

            if ($image_url === '' && function_exists('wc_placeholder_img_src')) {
                $image_url = wc_placeholder_img_src('woocommerce_thumbnail');
            }

            $category_name = '';
            $terms         = get_the_terms($product_id, 'product_cat');
            if (!is_wp_error($terms) && !empty($terms)) {
                $first_term = reset($terms);
                if ($first_term instanceof WP_Term) {
                    $category_name = $first_term->name;
                }
            }

            $items[] = [
                'title'    => wp_strip_all_tags(get_the_title($product_id)),
                'url'      => esc_url_raw(get_permalink($product_id)),
                'image'    => esc_url_raw((string) $image_url),
                'category' => wp_strip_all_tags($category_name),
                'price'    => $product ? wp_strip_all_tags($product->get_price_html()) : '',
            ];
        }

        wp_reset_postdata();
    }

    wp_send_json_success($items);
}
