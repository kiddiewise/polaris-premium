<?php
if (!defined('ABSPATH')) {
    exit;
}

$home = home_url('/');
$shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$cart = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
$acct = function_exists('polaris_get_account_entry_url') ? polaris_get_account_entry_url() : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url());
$cart_count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
?>
<nav class="bottom-nav" aria-label="<?php echo esc_attr__('Mobil gezinme', 'polaris'); ?>">
  <a href="<?php echo esc_url($home); ?>" data-nav="home">
    <i class="fa-solid fa-house" aria-hidden="true"></i>
    <span><?php echo esc_html__('Ana Sayfa', 'polaris'); ?></span>
  </a>
  <a href="<?php echo esc_url($shop); ?>" data-nav="shop">
    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
    <span><?php echo esc_html__('Mağaza', 'polaris'); ?></span>
  </a>
  <a href="#" class="js-search-open" data-nav="search">
    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
    <span><?php echo esc_html__('Ara', 'polaris'); ?></span>
  </a>
  <a href="<?php echo esc_url($cart); ?>" class="cart-icon" data-nav="cart" aria-label="<?php echo esc_attr__('Sepet', 'polaris'); ?>">
    <span class="bottom-nav__icon-wrap">
      <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
      <span class="cart-count" aria-label="<?php echo esc_attr__('Sepet ürün sayısı', 'polaris'); ?>"><?php echo (int) $cart_count; ?></span>
    </span>
    <span><?php echo esc_html__('Sepet', 'polaris'); ?></span>
  </a>
  <a href="<?php echo esc_url($acct); ?>" data-nav="account">
    <i class="fa-regular fa-user" aria-hidden="true"></i>
    <span><?php echo esc_html__('Hesabım', 'polaris'); ?></span>
  </a>
</nav>
