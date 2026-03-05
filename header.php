<?php
if (!defined('ABSPATH')) exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="header" role="banner">
    <div class="trustbar">
  <div class="container trustbar__inner">
    <div class="trustbar__item"><i class="fa-solid fa-truck-fast"></i> Türkiye'nin en güçlü surf kurşunları</div>
    <div class="trustbar__dot"></div>
    <div class="trustbar__item"><i class="fa-solid fa-badge-check"></i> %100 Yerli Üretim</div>
    <div class="trustbar__dot"></div>
    <div class="trustbar__item"><i class="fa-solid fa-lock"></i> Güvenli Ödeme</div>
  </div>
</div>
  <div class="container header-inner">

    <div class="logo">
      <?php
      if (function_exists('the_custom_logo') && has_custom_logo()) {
        the_custom_logo();
      } else {
        echo '<a href="' . esc_url(home_url('/')) . '" style="color:#fff;font-weight:800;">' . esc_html(get_bloginfo('name')) . '</a>';
      }
      ?>
    </div>

    <nav class="desktop-menu" aria-label="<?php echo esc_attr__('Primary menu', 'polaris'); ?>">
      <?php
      wp_nav_menu([
        'theme_location' => 'main_menu',
        'container'      => false,
        'fallback_cb'    => '__return_false',
        'depth'          => 2,
      ]);
      ?>
    </nav>

    <div class="header-actions">

      <!-- Search toggle -->
      <button class="header-icon-btn js-search-open" type="button" aria-label="<?php echo esc_attr__('Search', 'polaris'); ?>">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      </button>

      <!-- Account -->
      <?php if (function_exists('wc_get_page_permalink')): ?>
        <a class="header-icon-btn" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" aria-label="<?php echo esc_attr__('My account', 'polaris'); ?>">
          <i class="fa-regular fa-user" aria-hidden="true"></i>
        </a>
      <?php endif; ?>

      <!-- Cart -->
      <?php
      $cart_url   = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '#';
      $cart_count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
      ?>
      <a class="header-icon-btn cart-icon" href="<?php echo esc_url($cart_url); ?>" aria-label="<?php echo esc_attr__('Cart', 'polaris'); ?>">
        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
        <span class="cart-count" aria-label="<?php echo esc_attr__('Cart items count', 'polaris'); ?>">
          <?php echo (int) $cart_count; ?>
        </span>
      </a>

    </div>
  </div>
</header>

<!-- Search Overlay (AJAX search UI hazır; JS ile aç/kapat bağlayacağız) -->
<div class="polaris-search-overlay hidden" id="polarisSearchOverlay" aria-hidden="true">
  <div class="polaris-search-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Search products', 'polaris'); ?>">
    <div class="polaris-search-top">
      <div style="font-weight:800;color:var(--primary);"><?php echo esc_html__('Arama', 'polaris'); ?></div>
      <button class="header-icon-btn js-search-close" type="button" aria-label="<?php echo esc_attr__('Close search', 'polaris'); ?>" style="background: rgba(9,32,55,0.06); border-color: rgba(9,32,55,0.10); color: var(--primary);">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>

    <form class="polaris-search-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" autocomplete="off">
      <input
        type="search"
        name="s"
        id="polarisSearchInput"
        placeholder="<?php echo esc_attr__('Ürün ara…', 'polaris'); ?>"
        aria-label="<?php echo esc_attr__('Search', 'polaris'); ?>"
      />
      <input type="hidden" name="post_type" value="product">
      <button class="btn btn-primary" type="submit">
        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        <?php echo esc_html__('Ara', 'polaris'); ?>
      </button>
    </form>

    <!-- AJAX results container (main.js ile dolduracağız) -->
    <div class="polaris-search-results" id="polarisSearchResults"></div>
  </div>
</div>

<!-- Mini Cart Drawer -->
<div class="polaris-drawer hidden" id="polarisCartDrawer" aria-hidden="true">
  <div class="polaris-drawer__backdrop" data-cart-close></div>

  <aside class="polaris-drawer__panel" role="dialog" aria-modal="true" aria-label="Sepet">
    <div class="polaris-drawer__top">
      <div class="polaris-drawer__title">Sepet</div>
      <button class="header-icon-btn polaris-drawer__close" type="button" data-cart-close aria-label="Kapat">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>

    <div class="polaris-freeship" id="polarisFreeShip">
  <div class="polaris-freeship__top">
    <div class="polaris-freeship__title"><i class="fa-solid fa-gift"></i> Ücretsiz Kargo</div>
    <div class="polaris-freeship__meta" id="polarisFreeShipText">Hesaplanıyor…</div>
  </div>
  <div class="polaris-freeship__bar">
    <div class="polaris-freeship__fill" id="polarisFreeShipFill" style="width:0%"></div>
  </div>
</div>

    <div class="polaris-drawer__body" id="polarisMiniCart">
      <!-- AJAX ile dolacak -->
      <div class="search-empty">Yükleniyor…</div>
    </div>

    <div class="polaris-drawer__bottom">
      <a class="btn btn-primary" href="<?php echo esc_url(wc_get_cart_url()); ?>" style="width:100%;">Sepete Git</a>
    </div>
  </aside>
</div>

<!-- Toast -->
<div class="polaris-toast hidden" id="polarisToast" role="status" aria-live="polite"></div>

<main id="content" class="site-content" role="main">