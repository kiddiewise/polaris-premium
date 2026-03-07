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
<div class="bottom-freeship is-open" id="polarisBottomFreeShip">
  <button
    type="button"
    class="bottom-freeship__tab"
    id="polarisBottomFreeShipToggle"
    aria-controls="polarisBottomFreeShipPanel"
    aria-expanded="true"
  >
    <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
    <span id="polarisBottomFreeShipLabel"><?php echo esc_html__('Kargo durumu', 'polaris'); ?></span>
  </button>

  <div class="bottom-freeship__panel" id="polarisBottomFreeShipPanel" aria-hidden="false">
    <div class="bottom-freeship__top">
      <div class="bottom-freeship__title">
        <i class="fa-solid fa-gift" aria-hidden="true"></i>
        <span><?php echo esc_html__('Ücretsiz kargo', 'polaris'); ?></span>
      </div>
      <button type="button" class="bottom-freeship__close" id="polarisBottomFreeShipClose" aria-label="<?php echo esc_attr__('Kapat', 'polaris'); ?>">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>
    <div class="bottom-freeship__meta" id="polarisBottomFreeShipText"><?php echo esc_html__('Hesaplanıyor...', 'polaris'); ?></div>
    <div class="bottom-freeship__bar">
      <div class="bottom-freeship__fill" id="polarisBottomFreeShipFill" style="width:0%"></div>
    </div>
  </div>
</div>

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
