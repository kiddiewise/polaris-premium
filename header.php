<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$cart_url   = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/');
$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : wp_login_url();
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$cart_count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
?>

<header class="header" role="banner">
  <div class="trustbar">
    <div class="container trustbar__inner">
      <div class="trustbar__item"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Türkiye geneli hızlı teslimat</div>
      <div class="trustbar__dot" aria-hidden="true"></div>
      <div class="trustbar__item"><i class="fa-solid fa-certificate" aria-hidden="true"></i> %100 yerli üretim</div>
      <div class="trustbar__dot" aria-hidden="true"></div>
      <div class="trustbar__item"><i class="fa-solid fa-lock" aria-hidden="true"></i> Güvenli ödeme</div>
    </div>
  </div>

  <div class="container header-inner">
    <div class="logo">
      <?php
      if (function_exists('the_custom_logo') && has_custom_logo()) {
          the_custom_logo();
      } else {
          printf(
              '<a href="%1$s" class="site-title">%2$s</a>',
              esc_url(home_url('/')),
              esc_html(get_bloginfo('name'))
          );
      }
      ?>
    </div>

    <nav class="desktop-menu" aria-label="<?php echo esc_attr__('Ana menü', 'polaris'); ?>">
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
      <button class="header-icon-btn js-search-open" type="button" aria-label="<?php echo esc_attr__('Ürün ara', 'polaris'); ?>">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      </button>

      <a class="header-icon-btn" href="<?php echo esc_url($account_url); ?>" aria-label="<?php echo esc_attr__('Hesabım', 'polaris'); ?>">
        <i class="fa-regular fa-user" aria-hidden="true"></i>
      </a>

      <a class="header-icon-btn cart-icon" href="<?php echo esc_url($cart_url); ?>" aria-label="<?php echo esc_attr__('Sepet', 'polaris'); ?>">
        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
        <span class="cart-count" aria-label="<?php echo esc_attr__('Sepet ürün sayısı', 'polaris'); ?>"><?php echo (int) $cart_count; ?></span>
      </a>
    </div>
  </div>
</header>

<div class="polaris-search-overlay hidden" id="polarisSearchOverlay" aria-hidden="true">
  <div class="polaris-search-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Ürün ara', 'polaris'); ?>">
    <div class="polaris-search-top">
      <div class="polaris-search-heading"><?php echo esc_html__('Arama', 'polaris'); ?></div>
      <button class="header-icon-btn js-search-close" type="button" aria-label="<?php echo esc_attr__('Aramayı kapat', 'polaris'); ?>">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>

    <form class="polaris-search-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" autocomplete="off">
      <input type="search" name="s" id="polarisSearchInput" placeholder="<?php echo esc_attr__('Ürün, kategori veya anahtar kelime ara...', 'polaris'); ?>" aria-label="<?php echo esc_attr__('Arama', 'polaris'); ?>">
      <input type="hidden" name="post_type" value="product">
      <button class="btn btn-primary" type="submit"><?php echo esc_html__('Ara', 'polaris'); ?></button>
    </form>

    <div class="polaris-search-results" id="polarisSearchResults"></div>
  </div>
</div>

<div class="polaris-drawer hidden" id="polarisCartDrawer" aria-hidden="true">
  <div class="polaris-drawer__backdrop" data-cart-close></div>

  <aside class="polaris-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Sepet', 'polaris'); ?>">
    <div class="polaris-drawer__top">
      <div class="polaris-drawer__title"><?php echo esc_html__('Sepet', 'polaris'); ?></div>
      <button class="header-icon-btn polaris-drawer__close" type="button" data-cart-close aria-label="<?php echo esc_attr__('Kapat', 'polaris'); ?>">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>

    <div class="polaris-freeship" id="polarisFreeShip">
      <div class="polaris-freeship__top">
        <div class="polaris-freeship__title"><i class="fa-solid fa-gift" aria-hidden="true"></i> <?php echo esc_html__('Ücretsiz kargo', 'polaris'); ?></div>
        <div class="polaris-freeship__meta" id="polarisFreeShipText"><?php echo esc_html__('Hesaplanıyor...', 'polaris'); ?></div>
      </div>
      <div class="polaris-freeship__bar"><div class="polaris-freeship__fill" id="polarisFreeShipFill" style="width:0%"></div></div>
    </div>

    <div class="polaris-drawer__body" id="polarisMiniCart">
      <div class="search-empty"><?php echo esc_html__('Sepet yükleniyor...', 'polaris'); ?></div>
    </div>

    <div class="polaris-drawer__bottom">
      <a class="btn btn-primary" href="<?php echo esc_url($cart_url); ?>" style="width:100%;"><?php echo esc_html__('Sepete git', 'polaris'); ?></a>
      <a class="btn btn-ghost" href="<?php echo esc_url($shop_url); ?>" style="width:100%;margin-top:8px;"><?php echo esc_html__('Alışverişe devam et', 'polaris'); ?></a>
    </div>
  </aside>
</div>

<div class="polaris-toast hidden" id="polarisToast" role="status" aria-live="polite"></div>

<main id="content" class="site-content" role="main">
