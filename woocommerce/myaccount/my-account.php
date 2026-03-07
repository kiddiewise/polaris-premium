<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('WC') || !WC()) {
    return;
}

if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $user_id      = (int) $current_user->ID;

    $current_endpoint = WC()->query ? (string) WC()->query->get_current_endpoint() : '';
    $active_endpoint  = '' !== $current_endpoint ? $current_endpoint : 'dashboard';
    $menu_items       = wc_get_account_menu_items();
    $panel_title      = isset($menu_items[$active_endpoint]) ? $menu_items[$active_endpoint] : __('Hesabim', 'polaris');

    $orders_count = function_exists('wc_get_customer_order_count') ? (int) wc_get_customer_order_count($user_id) : 0;

    $last_order = null;
    if (function_exists('wc_get_orders')) {
        $recent_orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit'       => 1,
            'orderby'     => 'date',
            'order'       => 'DESC',
            'status'      => array_keys(wc_get_order_statuses()),
        ]);
        if (!empty($recent_orders)) {
            $last_order = $recent_orders[0];
        }
    }

    $last_order_meta = __('Henuz siparis bulunmuyor', 'polaris');
    if ($last_order instanceof WC_Order) {
        $order_date = $last_order->get_date_created();
        $date_text  = $order_date ? wp_date('d M Y', $order_date->getTimestamp()) : __('Tarih yok', 'polaris');
        $last_order_meta = sprintf(
            __('#%1$s - %2$s', 'polaris'),
            $last_order->get_order_number(),
            $date_text
        );
    }

    $address_types = wc_ship_to_billing_address_only() ? ['billing'] : ['billing', 'shipping'];
    $address_fields = ['first_name', 'last_name', 'address_1', 'city', 'postcode', 'country'];
    $saved_address_count = 0;

    foreach ($address_types as $address_type) {
        $has_any_field = false;

        foreach ($address_fields as $field_key) {
            $field_value = trim((string) get_user_meta($user_id, $address_type . '_' . $field_key, true));
            if ('' !== $field_value) {
                $has_any_field = true;
                break;
            }
        }

        if ($has_any_field) {
            $saved_address_count++;
        }
    }

    $icon_map = [
        'dashboard'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4zM13 4h7v4h-7zM13 10h7v10h-7zM4 13h7v7H4z"/></svg>',
        'orders'          => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h14l2 3v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zM4 6V4h12v2M8 12h8"/></svg>',
        'edit-address'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11zm0-8.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>',
        'edit-account'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-3.3 0-6 1.7-6 4v2h12v-2c0-2.3-2.7-4-6-4z"/></svg>',
        'change-password' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3M6 11h12a1 1 0 0 1 1 1v8H5v-8a1 1 0 0 1 1-1zm6 3a1.5 1.5 0 0 0-1.5 1.5A1.5 1.5 0 0 0 12 17a1.5 1.5 0 0 0 1.5-1.5A1.5 1.5 0 0 0 12 14z"/></svg>',
        'customer-logout' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H5v16h5M14 8l4 4-4 4M9 12h9"/></svg>',
    ];

    $svg_allowed = [
        'svg'  => ['viewBox' => true, 'aria-hidden' => true],
        'path' => ['d' => true],
    ];

    $display_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
    $email_text   = $current_user->user_email ? $current_user->user_email : __('E-posta bilgisi yok', 'polaris');

    $orders_url       = wc_get_account_endpoint_url('orders');
    $addresses_url    = wc_get_account_endpoint_url('edit-address');
    $account_url      = wc_get_account_endpoint_url('edit-account');
    $password_url     = wc_get_account_endpoint_url('change-password');
    ?>

    <section class="polaris-content polaris-account-content">
      <div class="container polaris-account">
        <div class="polaris-account__shell">
          <aside class="polaris-account__sidebar" aria-label="<?php echo esc_attr__('Hesap Menusu', 'polaris'); ?>">
            <div class="polaris-account__profile">
              <div class="polaris-account__avatar">
                <?php echo get_avatar($user_id, 72, '', $display_name, ['class' => 'polaris-account__avatar-image']); ?>
              </div>
              <div class="polaris-account__identity">
                <strong><?php echo esc_html($display_name); ?></strong>
                <span><?php echo esc_html($email_text); ?></span>
              </div>
            </div>

            <?php do_action('woocommerce_before_account_navigation'); ?>

            <nav class="polaris-account-nav">
              <ul class="polaris-account-nav__list">
                <?php foreach ($menu_items as $endpoint => $label) : ?>
                  <?php
                  $is_active = $endpoint === $active_endpoint;
                  $icon_svg  = isset($icon_map[$endpoint]) ? $icon_map[$endpoint] : $icon_map['dashboard'];
                  ?>
                  <li class="polaris-account-nav__item<?php echo $is_active ? ' is-active' : ''; ?>">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                      <span class="polaris-account-nav__icon"><?php echo wp_kses($icon_svg, $svg_allowed); ?></span>
                      <span class="polaris-account-nav__label"><?php echo esc_html($label); ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </nav>

            <?php do_action('woocommerce_after_account_navigation'); ?>
          </aside>

          <div class="polaris-account__main">
            <header class="polaris-account__head">
              <div>
                <p class="polaris-account__kicker"><?php esc_html_e('My Account', 'polaris'); ?></p>
                <h1><?php echo esc_html($panel_title); ?></h1>
              </div>
              <span class="polaris-account__status"><?php esc_html_e('Hesabiniz aktif', 'polaris'); ?></span>
            </header>

            <section class="polaris-account-stats" aria-label="<?php echo esc_attr__('Hesap Ozeti', 'polaris'); ?>">
              <article class="polaris-account-stat">
                <p><?php esc_html_e('Siparis Sayisi', 'polaris'); ?></p>
                <strong><?php echo esc_html((string) $orders_count); ?></strong>
              </article>
              <article class="polaris-account-stat">
                <p><?php esc_html_e('Son Siparis', 'polaris'); ?></p>
                <strong><?php echo esc_html($last_order_meta); ?></strong>
              </article>
              <article class="polaris-account-stat">
                <p><?php esc_html_e('Kayitli Adres', 'polaris'); ?></p>
                <strong><?php echo esc_html($saved_address_count . '/' . count($address_types)); ?></strong>
              </article>
            </section>

            <section class="polaris-account-actions" aria-label="<?php echo esc_attr__('Hizli Islemler', 'polaris'); ?>">
              <a class="polaris-account-actions__item" href="<?php echo esc_url($orders_url); ?>">
                <span><?php esc_html_e('Siparislerim', 'polaris'); ?></span>
                <small><?php esc_html_e('Gecmis ve durum takibi', 'polaris'); ?></small>
              </a>
              <a class="polaris-account-actions__item" href="<?php echo esc_url($addresses_url); ?>">
                <span><?php esc_html_e('Adresleri Duzenle', 'polaris'); ?></span>
                <small><?php esc_html_e('Teslimat ve fatura bilgileri', 'polaris'); ?></small>
              </a>
              <a class="polaris-account-actions__item" href="<?php echo esc_url($account_url); ?>">
                <span><?php esc_html_e('Profil Bilgileri', 'polaris'); ?></span>
                <small><?php esc_html_e('Ad, soyad ve iletisim bilgileri', 'polaris'); ?></small>
              </a>
              <a class="polaris-account-actions__item" href="<?php echo esc_url($password_url); ?>">
                <span><?php esc_html_e('Sifre Guncelle', 'polaris'); ?></span>
                <small><?php esc_html_e('Guvenlik ayarlarini guncelle', 'polaris'); ?></small>
              </a>
            </section>

            <section class="polaris-account-panel" data-endpoint="<?php echo esc_attr($active_endpoint); ?>">
              <?php wc_print_notices(); ?>
              <?php do_action('woocommerce_account_content'); ?>
            </section>
          </div>
        </div>
      </div>
    </section>

    <?php
    return;
}

if (!function_exists('wc_get_template')) {
    return;
}

$logo_id    = get_theme_mod('custom_logo');
$logo_image = $logo_id ? wp_get_attachment_image($logo_id, 'full', false, [
    'class'   => 'polaris-auth-brand__logo-img',
    'loading' => 'eager',
    'alt'     => get_bloginfo('name'),
]) : '';
?>

<section class="polaris-content polaris-auth-content">
  <section class="container polaris-auth-page">
    <div class="polaris-auth-shell">
      <div class="polaris-auth-orb polaris-auth-orb--one" aria-hidden="true"></div>
      <div class="polaris-auth-orb polaris-auth-orb--two" aria-hidden="true"></div>

      <div class="polaris-auth-layout">
        <aside class="polaris-auth-brand fade-up active">
          <a class="polaris-auth-brand__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
            <?php if (!empty($logo_image)) : ?>
              <?php echo wp_kses_post($logo_image); ?>
            <?php else : ?>
              <span class="polaris-auth-brand__logo-fallback"><?php echo esc_html(get_bloginfo('name')); ?></span>
            <?php endif; ?>
          </a>

          <span class="polaris-auth-kicker">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            <?php esc_html_e('Güvenli giriş alanı', 'polaris'); ?>
          </span>

          <h1><?php esc_html_e('Hesabına gir, siparişlerini hızla yönet', 'polaris'); ?></h1>
          <p><?php esc_html_e('Tek alanda giriş yap veya kayıt ol. Google ile giriş seçeneğiyle saniyeler içinde devam et.', 'polaris'); ?></p>

          <div class="polaris-auth-trust">
            <span><i class="fa-solid fa-lock" aria-hidden="true"></i> <?php esc_html_e('SSL koruması', 'polaris'); ?></span>
            <span><i class="fa-solid fa-bolt" aria-hidden="true"></i> <?php esc_html_e('Hızlı erişim', 'polaris'); ?></span>
            <span><i class="fa-solid fa-user-check" aria-hidden="true"></i> <?php esc_html_e('Tek tıkla giriş', 'polaris'); ?></span>
          </div>
        </aside>

        <article class="polaris-surface polaris-auth-card fade-up active">
          <header class="polaris-auth-head">
            <h2><?php esc_html_e('Giriş / Kayıt', 'polaris'); ?></h2>
            <p>
              <?php if (function_exists('polaris_google_login_is_enabled') && polaris_google_login_is_enabled()) : ?>
                <?php esc_html_e('Google ile giriş seçeneği aktif. Formdan tek tıkla devam edebilirsiniz.', 'polaris'); ?>
              <?php else : ?>
                <?php esc_html_e('Google ile giriş alanı görünür, entegrasyon ayarı tamamlandığında aktif olur.', 'polaris'); ?>
              <?php endif; ?>
            </p>
          </header>

          <?php wc_get_template('myaccount/form-login.php'); ?>
        </article>
      </div>
    </div>
  </section>
</section>
