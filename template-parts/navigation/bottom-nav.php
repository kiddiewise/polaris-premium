<?php
if (!defined('ABSPATH')) {
    exit;
}

$home = home_url('/');
$shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$cart = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
$acct = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
?>
<nav class="bottom-nav" aria-label="<?php echo esc_attr__('Mobile navigation', 'polaris'); ?>">
  <a href="<?php echo esc_url($home); ?>" data-nav="home">
    <i class="fa-solid fa-house" aria-hidden="true"></i>
    <span><?php echo esc_html__('Home', 'polaris'); ?></span>
  </a>
  <a href="<?php echo esc_url($shop); ?>" data-nav="shop">
    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
    <span><?php echo esc_html__('Shop', 'polaris'); ?></span>
  </a>
  <a href="#" class="js-search-open" data-nav="search">
    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
    <span><?php echo esc_html__('Search', 'polaris'); ?></span>
  </a>
  <a href="<?php echo esc_url($cart); ?>" data-nav="cart">
    <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
    <span><?php echo esc_html__('Cart', 'polaris'); ?></span>
  </a>
  <a href="<?php echo esc_url($acct); ?>" data-nav="account">
    <i class="fa-regular fa-user" aria-hidden="true"></i>
    <span><?php echo esc_html__('Account', 'polaris'); ?></span>
  </a>
</nav>
