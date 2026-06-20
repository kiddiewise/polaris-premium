<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Polaris parola olusturma sayfasinin URL'si.
 * Atanmis bir template sayfasi yoksa guvenli sanal rota kullanilir.
 */
function polaris_get_password_reset_page_url()
{
    static $cached_url = null;
    if (null !== $cached_url) {
        return $cached_url;
    }

    $pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-sifre-olustur.php',
        'no_found_rows'  => true,
    ]);

    if (!empty($pages) && !is_wp_error($pages)) {
        $url = get_permalink((int) $pages[0]);
        if (!empty($url)) {
            $cached_url = $url;
            return $cached_url;
        }
    }

    $page = get_page_by_path('sifre-olustur', OBJECT, 'page');
    if ($page instanceof WP_Post && 'publish' === $page->post_status) {
        $url = get_permalink((int) $page->ID);
        if (!empty($url)) {
            $cached_url = $url;
            return $cached_url;
        }
    }

    $cached_url = home_url('/sifre-olustur/');
    return $cached_url;
}

function polaris_is_password_reset_page_request()
{
    if (is_page_template('page-sifre-olustur.php')) {
        return true;
    }

    $request_uri  = polaris_get_request_string($_SERVER, 'REQUEST_URI');
    $request_path = wp_parse_url($request_uri, PHP_URL_PATH);
    $target_path  = wp_parse_url(polaris_get_password_reset_page_url(), PHP_URL_PATH);

    if (!is_string($request_path) || !is_string($target_path)) {
        return false;
    }

    return untrailingslashit($request_path) === untrailingslashit($target_path);
}

/**
 * Reset anahtari ve kullanici login degerini yalnizca scalar isteklerden alir.
 */
function polaris_password_reset_get_credentials()
{
    $source = ('POST' === polaris_get_request_string($_SERVER, 'REQUEST_METHOD')) ? $_POST : $_GET;

    $key_raw   = polaris_get_request_string($source, 'key');
    $login_raw = polaris_get_request_string($source, 'login');

    $key   = preg_replace('/[^A-Za-z0-9]/', '', $key_raw);
    $login = sanitize_text_field($login_raw);

    if (!is_string($key) || strlen($key) > 128 || strlen($login) > 60) {
        return ['', ''];
    }

    return [$key, $login];
}

function polaris_password_reset_login_url()
{
    $login_url = function_exists('polaris_get_login_page_url') ? polaris_get_login_page_url() : '';
    return !empty($login_url) ? $login_url : home_url('/giris/');
}

function polaris_password_reset_is_too_weak($password)
{
    $length = function_exists('mb_strlen') ? mb_strlen($password) : strlen($password);
    if ($length < 10) {
        return true;
    }

    $characters = function_exists('mb_str_split') ? mb_str_split($password) : str_split($password);
    if (count(array_unique($characters)) < 4) {
        return true;
    }

    $character_groups = 0;
    $character_groups += preg_match('/[a-z]/u', $password) ? 1 : 0;
    $character_groups += preg_match('/[A-Z]/u', $password) ? 1 : 0;
    $character_groups += preg_match('/[0-9]/u', $password) ? 1 : 0;
    $character_groups += preg_match('/[^a-zA-Z0-9]/u', $password) ? 1 : 0;

    // Daha uzun parola cumlelerine izin ver; kisa parolalarda cesitlilik iste.
    return $length < 14 && $character_groups < 3;
}

function polaris_password_reset_clear_core_cookie()
{
    if (!defined('COOKIEHASH')) {
        return;
    }

    $cookie_path = wp_parse_url(site_url('wp-login.php', 'login'), PHP_URL_PATH);
    if (!is_string($cookie_path) || '' === $cookie_path) {
        $cookie_path = '/wp-login.php';
    }

    setcookie('wp-resetpass-' . COOKIEHASH, ' ', time() - YEAR_IN_SECONDS, $cookie_path, COOKIE_DOMAIN, is_ssl(), true);
}

/**
 * Form istegini WordPress'in reset key ve parola API'leriyle isler.
 */
function polaris_password_reset_process_request()
{
    list($key, $login) = polaris_password_reset_get_credentials();
    $errors            = new WP_Error();
    $user              = null;

    if ('' !== $key && '' !== $login) {
        $user = check_password_reset_key($key, $login);
    }

    if (!$user instanceof WP_User) {
        $error_code = is_wp_error($user) ? $user->get_error_code() : 'invalid_key';
        $errors->add(
            in_array($error_code, ['expired_key', 'invalid_key'], true) ? $error_code : 'invalid_key',
            __('Bu şifre oluşturma bağlantısı geçersiz veya süresi dolmuş. Lütfen yeni bir bağlantı isteyin.', 'polaris')
        );

        return [
            'user'   => null,
            'key'    => '',
            'login'  => '',
            'errors' => $errors,
        ];
    }

    if ('POST' === polaris_get_request_string($_SERVER, 'REQUEST_METHOD')) {
        $nonce = sanitize_text_field(polaris_get_request_string($_POST, 'polaris_password_reset_nonce'));
        if (!wp_verify_nonce($nonce, 'polaris_password_reset_' . (int) $user->ID)) {
            $errors->add('invalid_nonce', __('Güvenlik doğrulaması başarısız. Lütfen bağlantıyı yeniden açın.', 'polaris'));
        }

        // Parolalar sanitize edilmez; karakterleri degistirmeden scalar ve uzunluk kontrolu uygulanir.
        $password_1 = polaris_get_request_string($_POST, 'pass1');
        $password_2 = polaris_get_request_string($_POST, 'pass2');
        $password_1 = trim($password_1);
        $password_2 = trim($password_2);

        $password_length = function_exists('mb_strlen') ? mb_strlen($password_1) : strlen($password_1);

        if ('' === $password_1 || '' === $password_2) {
            $errors->add('password_required', __('Lütfen iki şifre alanını da doldurun.', 'polaris'));
        } elseif (!hash_equals($password_1, $password_2)) {
            $errors->add('password_mismatch', __('Şifreler birbiriyle eşleşmiyor.', 'polaris'));
        } elseif (polaris_password_reset_is_too_weak($password_1)) {
            $errors->add('password_too_weak', __('Şifreniz çok zayıf. En az 10 karakter ve farklı karakter türleri kullanın.', 'polaris'));
        } elseif ($password_length > 150) {
            $errors->add('password_too_long', __('Şifre en fazla 150 karakter olabilir.', 'polaris'));
        }

        /** WordPress ve eklentilerin standart ek parola kontrollerini korur. */
        do_action('validate_password_reset', $errors, $user);

        if (!$errors->has_errors()) {
            reset_password($user, $password_1);
            polaris_password_reset_clear_core_cookie();

            $success_url = add_query_arg('password-reset', 'success', polaris_password_reset_login_url());
            wp_safe_redirect($success_url);
            exit;
        }
    }

    return [
        'user'   => $user,
        'key'    => $key,
        'login'  => $login,
        'errors' => $errors,
    ];
}

/**
 * Core veya eklenti tarafindan uretilmis reset URL'lerini ozel sayfaya tasir.
 */
function polaris_password_reset_replace_urls($message)
{
    if (!is_string($message) || '' === $message) {
        return $message;
    }

    $pattern = '~https?://[^\s<>"\']*wp-login\.php\?[^\s<>"\']+~i';

    return preg_replace_callback($pattern, function ($matches) {
        $original_url = html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
        $query        = wp_parse_url($original_url, PHP_URL_QUERY);

        if (!is_string($query)) {
            return $matches[0];
        }

        parse_str($query, $args);
        $action = isset($args['action']) && is_string($args['action']) ? sanitize_key($args['action']) : '';
        $key    = isset($args['key']) && is_string($args['key']) ? preg_replace('/[^A-Za-z0-9]/', '', $args['key']) : '';
        $login  = isset($args['login']) && is_string($args['login']) ? sanitize_text_field($args['login']) : '';

        if (!in_array($action, ['rp', 'resetpass'], true) || empty($key) || empty($login)) {
            return $matches[0];
        }

        $custom_url = add_query_arg([
            'key'   => $key,
            'login' => $login,
        ], polaris_get_password_reset_page_url());

        return false !== strpos($matches[0], '&amp;') ? esc_url($custom_url) : esc_url_raw($custom_url);
    }, $message);
}

function polaris_password_reset_retrieve_message($message, $key, $user_login, $user_data)
{
    return polaris_password_reset_replace_urls($message);
}
add_filter('retrieve_password_message', 'polaris_password_reset_retrieve_message', 20, 4);

function polaris_password_reset_new_user_email($email, $user, $blogname)
{
    if (is_array($email) && isset($email['message'])) {
        $email['message'] = polaris_password_reset_replace_urls($email['message']);
    }

    return $email;
}
add_filter('wp_new_user_notification_email', 'polaris_password_reset_new_user_email', 20, 3);

/**
 * Eski mailler ve core URL'leri icin rp/resetpass giris noktasini yakalar.
 */
function polaris_password_reset_redirect_core_form()
{
    if ('GET' !== polaris_get_request_string($_SERVER, 'REQUEST_METHOD')) {
        return;
    }

    $key   = preg_replace('/[^A-Za-z0-9]/', '', polaris_get_request_string($_GET, 'key'));
    $login = sanitize_text_field(polaris_get_request_string($_GET, 'login'));

    if ((empty($key) || empty($login)) && defined('COOKIEHASH')) {
        $cookie_name  = 'wp-resetpass-' . COOKIEHASH;
        $cookie_value = polaris_get_request_string($_COOKIE, $cookie_name);

        if (false !== strpos($cookie_value, ':')) {
            list($cookie_login, $cookie_key) = explode(':', $cookie_value, 2);
            $key   = preg_replace('/[^A-Za-z0-9]/', '', $cookie_key);
            $login = sanitize_text_field($cookie_login);
        }
    }

    if (empty($key) || empty($login) || strlen($key) > 128 || strlen($login) > 60) {
        return;
    }

    $target = add_query_arg([
        'key'   => $key,
        'login' => $login,
    ], polaris_get_password_reset_page_url());

    wp_safe_redirect($target);
    exit;
}
add_action('login_form_rp', 'polaris_password_reset_redirect_core_form', 1);
add_action('login_form_resetpass', 'polaris_password_reset_redirect_core_form', 1);

function polaris_password_reset_add_body_classes($classes)
{
    if (polaris_is_password_reset_page_request()) {
        $classes[] = 'page-template-page-login';
        $classes[] = 'polaris-auth-screen';
        $classes[] = 'polaris-password-reset-screen';
    }

    return array_values(array_unique($classes));
}
add_filter('body_class', 'polaris_password_reset_add_body_classes');

function polaris_password_reset_noindex($robots)
{
    if (polaris_is_password_reset_page_request()) {
        $robots['noindex']  = true;
        $robots['nofollow'] = true;
    }

    return $robots;
}
add_filter('wp_robots', 'polaris_password_reset_noindex');

function polaris_password_reset_security_headers($headers)
{
    if (polaris_is_password_reset_page_request()) {
        $headers['Referrer-Policy'] = 'no-referrer';
        $headers['X-Robots-Tag']    = 'noindex, nofollow, noarchive';
    }

    return $headers;
}
add_filter('wp_headers', 'polaris_password_reset_security_headers');

function polaris_password_reset_enqueue_assets()
{
    if (!polaris_is_password_reset_page_request()) {
        return;
    }

    $script_path = get_theme_file_path('/assets/js/password-reset.js');
    $version     = file_exists($script_path) ? (string) filemtime($script_path) : '1.0.0';

    wp_enqueue_script(
        'polaris-password-reset',
        get_theme_file_uri('/assets/js/password-reset.js'),
        ['password-strength-meter'],
        $version,
        true
    );
    wp_script_add_data('polaris-password-reset', 'defer', true);
}
add_action('wp_enqueue_scripts', 'polaris_password_reset_enqueue_assets', 35);

function polaris_force_password_reset_template()
{
    if (is_admin() || wp_doing_ajax() || !polaris_is_password_reset_page_request()) {
        return;
    }

    polaris_mark_account_pages_uncacheable();

    $template = get_theme_file_path('/page-sifre-olustur.php');
    if (!file_exists($template)) {
        return;
    }

    status_header(200);
    include $template;
    exit;
}
add_action('template_redirect', 'polaris_force_password_reset_template', -5);
