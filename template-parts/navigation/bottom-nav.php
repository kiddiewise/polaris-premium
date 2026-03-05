<?php
if (!defined('ABSPATH')) exit;

$home = home_url('/');
$shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$cart = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '#';
$acct = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '#';
?>
<nav class="bottom-nav" aria-label="Mobile navigation">
  <a href="<?php echo esc_url($home); ?>" data-nav="home">
    <i class="fa-solid fa-house"></i>
    <span>Ana Sayfa</span>
  </a>
  <a href="<?php echo esc_url($shop); ?>" data-nav="shop">
    <i class="fa-solid fa-layer-group"></i>
    <span>Kategoriler</span>
  </a>
  <a href="#" class="js-search-open" data-nav="search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <span>Ara</span>
  </a>
  <a href="<?php echo esc_url($cart); ?>" data-nav="cart">
    <i class="fa-solid fa-bag-shopping"></i>
    <span>Sepet</span>
  </a>
  <a href="<?php echo esc_url($acct); ?>" data-nav="account">
    <i class="fa-regular fa-user"></i>
    <span>Hesabım</span>
  </a>
</nav>