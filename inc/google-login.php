<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google Login ayarlari.
 *
 * Tavsiye edilen kullanim:
 * wp-config.php icine asagidaki sabitleri ekleyin:
 * - POLARIS_GOOGLE_CLIENT_ID
 * - POLARIS_GOOGLE_CLIENT_SECRET
 * - POLARIS_GOOGLE_REDIRECT_URI
 */
function polaris_google_login_get_config()
{
    $default_redirect_uri = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('myaccount')
        : home_url('/giris/');

    $config = [
        'client_id'     => defined('POLARIS_GOOGLE_CLIENT_ID') ? trim((string) POLARIS_GOOGLE_CLIENT_ID) : '',
        'client_secret' => defined('POLARIS_GOOGLE_CLIENT_SECRET') ? trim((string) POLARIS_GOOGLE_CLIENT_SECRET) : '',
        'redirect_uri'  => defined('POLARIS_GOOGLE_REDIRECT_URI') ? trim((string) POLARIS_GOOGLE_REDIRECT_URI) : $default_redirect_uri,
        'scope'         => 'openid email profile',
        'state_ttl'     => 10 * MINUTE_IN_SECONDS,
        'debug'         => defined('WP_DEBUG') && WP_DEBUG,
    ];

    $config['redirect_uri'] = esc_url_raw($config['redirect_uri']);

    return apply_filters('polaris_google_login_config', $config);
}

function polaris_google_login_is_enabled()
{
    $config = polaris_google_login_get_config();

    return !empty($config['client_id'])
        && !empty($config['client_secret'])
        && !empty($config['redirect_uri']);
}

function polaris_google_login_log($message, array $context = [])
{
    $config = polaris_google_login_get_config();
    if (empty($config['debug'])) {
        return;
    }

    $line = '[Polaris Google Login] ' . (string) $message;
    if (!empty($context)) {
        $line .= ' | ' . wp_json_encode($context);
    }

    error_log($line);
}

function polaris_google_login_bool($value)
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_numeric($value)) {
        return (int) $value === 1;
    }

    if (is_string($value)) {
        return in_array(strtolower($value), ['1', 'true', 'yes'], true);
    }

    return false;
}

function polaris_google_login_state_key($state)
{
    return 'polaris_google_state_' . hash_hmac('sha256', (string) $state, wp_salt('nonce'));
}

function polaris_google_login_current_user_agent_hash()
{
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    return hash('sha256', $ua);
}

function polaris_google_login_default_redirect_url()
{
    $fallback = home_url('/');

    if (function_exists('wc_get_page_permalink')) {
        $my_account = wc_get_page_permalink('myaccount');
        if (!empty($my_account)) {
            $fallback = $my_account;
        }
    }

    return $fallback;
}

function polaris_google_login_validate_redirect($redirect_raw = '')
{
    $default = polaris_google_login_default_redirect_url();

    $redirect = '';
    if (!empty($redirect_raw)) {
        $redirect = wp_validate_redirect(esc_url_raw((string) $redirect_raw), '');
    }

    if (empty($redirect) && isset($_REQUEST['redirect_to'])) {
        $request_redirect = sanitize_text_field(wp_unslash($_REQUEST['redirect_to']));
        $redirect         = wp_validate_redirect(esc_url_raw($request_redirect), '');
    }

    return !empty($redirect) ? $redirect : $default;
}

function polaris_google_login_send_error($code, $message, $http_status = 400, array $context = [])
{
    polaris_google_login_log($message, array_merge(['error_code' => $code], $context));

    wp_send_json_error([
        'code'    => sanitize_key($code),
        'message' => sanitize_text_field($message),
    ], (int) $http_status);
}

function polaris_google_login_parse_jwt_payload($jwt)
{
    $parts = explode('.', (string) $jwt);
    if (count($parts) !== 3) {
        return null;
    }

    $payload = strtr($parts[1], '-_', '+/');
    $padding = strlen($payload) % 4;
    if ($padding > 0) {
        $payload .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode($payload, true);
    if ($decoded === false) {
        return null;
    }

    $claims = json_decode($decoded, true);

    return is_array($claims) ? $claims : null;
}

function polaris_google_login_generate_username($email)
{
    $base = strstr((string) $email, '@', true);
    $base = sanitize_user((string) $base, true);

    if (empty($base)) {
        $base = 'googleuser';
    }

    $username = $base;
    $suffix   = 1;

    while (username_exists($username)) {
        $username = $base . $suffix;
        $suffix++;
    }

    return $username;
}

function polaris_google_login_exchange_code($code, array $config)
{
    $response = wp_remote_post('https://oauth2.googleapis.com/token', [
        'timeout'     => 20,
        'redirection' => 0,
        'headers'     => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'body'        => [
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $config['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('http_error', $response->get_error_message());
    }

    $body      = wp_remote_retrieve_body($response);
    $http_code = (int) wp_remote_retrieve_response_code($response);
    $data      = json_decode($body, true);

    if ($http_code !== 200 || !is_array($data)) {
        $error_msg = is_array($data) && !empty($data['error_description'])
            ? sanitize_text_field((string) $data['error_description'])
            : 'Google token endpoint beklenen cevabi donmedi.';

        return new WP_Error('token_exchange_failed', $error_msg, [
            'http_code' => $http_code,
            'body'      => $data,
        ]);
    }

    return $data;
}

function polaris_google_login_fetch_userinfo($access_token)
{
    $response = wp_remote_get('https://openidconnect.googleapis.com/v1/userinfo', [
        'timeout' => 20,
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('userinfo_http_error', $response->get_error_message());
    }

    $body      = wp_remote_retrieve_body($response);
    $http_code = (int) wp_remote_retrieve_response_code($response);
    $data      = json_decode($body, true);

    if ($http_code !== 200 || !is_array($data)) {
        return new WP_Error('userinfo_failed', 'Google user info alinamadi.', [
            'http_code' => $http_code,
            'body'      => $data,
        ]);
    }

    return $data;
}

function polaris_google_login_prepare_state_ajax()
{
    check_ajax_referer('polaris_google_login_nonce', 'nonce');

    if (!polaris_google_login_is_enabled()) {
        polaris_google_login_send_error('not_configured', 'Google ile giris su anda aktif degil.', 500);
    }

    $config = polaris_google_login_get_config();

    $state = wp_generate_password(48, false, false);
    $state = preg_replace('/[^A-Za-z0-9_-]/', '', $state);

    if (empty($state)) {
        polaris_google_login_send_error('state_generation_failed', 'State olusturulamadi.', 500);
    }

    // State tek kullanimlik saklanir; callback sonrasi silinir.
    $payload = [
        'created_at' => time(),
        'ua_hash'    => polaris_google_login_current_user_agent_hash(),
    ];

    set_transient(polaris_google_login_state_key($state), $payload, (int) $config['state_ttl']);

    wp_send_json_success([
        'state' => $state,
    ]);
}

function polaris_google_login_exchange_ajax()
{
    check_ajax_referer('polaris_google_login_nonce', 'nonce');

    if (!polaris_google_login_is_enabled()) {
        polaris_google_login_send_error('not_configured', 'Google ile giris su anda aktif degil.', 500);
    }

    $code        = isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '';
    $state       = isset($_POST['state']) ? sanitize_text_field(wp_unslash($_POST['state'])) : '';
    $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : '';

    if (empty($code) || empty($state)) {
        polaris_google_login_send_error('missing_fields', 'Google giris verileri eksik.', 400);
    }

    if (strlen($code) > 2048 || strlen($state) > 128) {
        polaris_google_login_send_error('invalid_payload', 'Gecersiz giris verisi.', 400);
    }

    // State dogrulamasi: request, baslatilan ayni browser oturumundan mi geldi?
    $state_key  = polaris_google_login_state_key($state);
    $state_data = get_transient($state_key);

    if (empty($state_data) || !is_array($state_data)) {
        polaris_google_login_send_error('invalid_state', 'Gecersiz ya da suresi dolmus state.', 403);
    }

    delete_transient($state_key);

    $expected_ua = isset($state_data['ua_hash']) ? (string) $state_data['ua_hash'] : '';
    $current_ua  = polaris_google_login_current_user_agent_hash();

    if (!empty($expected_ua) && !hash_equals($expected_ua, $current_ua)) {
        polaris_google_login_send_error('state_ua_mismatch', 'Guvenlik dogrulamasi basarisiz.', 403);
    }

    $config      = polaris_google_login_get_config();
    // Authorization code -> token exchange islemi server-side yapilir.
    $token_data  = polaris_google_login_exchange_code($code, $config);

    if (is_wp_error($token_data)) {
        polaris_google_login_send_error('token_exchange_failed', $token_data->get_error_message(), 401, [
            'details' => $token_data->get_error_data(),
        ]);
    }

    if (empty($token_data['id_token']) || empty($token_data['access_token'])) {
        polaris_google_login_send_error('missing_tokens', 'Google token bilgileri eksik.', 401);
    }

    $id_claims = polaris_google_login_parse_jwt_payload($token_data['id_token']);

    if (empty($id_claims) || !is_array($id_claims)) {
        polaris_google_login_send_error('invalid_id_token', 'ID token cozumlenemedi.', 401);
    }

    $issuer = isset($id_claims['iss']) ? (string) $id_claims['iss'] : '';
    if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
        polaris_google_login_send_error('invalid_issuer', 'Gecersiz token issuer.', 401);
    }

    $aud = isset($id_claims['aud']) ? (string) $id_claims['aud'] : '';
    if (!hash_equals((string) $config['client_id'], $aud)) {
        polaris_google_login_send_error('invalid_audience', 'Token client dogrulamasi basarisiz.', 401);
    }

    $exp = isset($id_claims['exp']) ? (int) $id_claims['exp'] : 0;
    if ($exp > 0 && $exp < time() - 60) {
        polaris_google_login_send_error('expired_token', 'Token suresi dolmus.', 401);
    }

    $userinfo = polaris_google_login_fetch_userinfo($token_data['access_token']);
    if (is_wp_error($userinfo)) {
        polaris_google_login_send_error('userinfo_failed', $userinfo->get_error_message(), 401, [
            'details' => $userinfo->get_error_data(),
        ]);
    }

    $email = sanitize_email(isset($userinfo['email']) ? $userinfo['email'] : (isset($id_claims['email']) ? $id_claims['email'] : ''));

    if (empty($email) || !is_email($email)) {
        polaris_google_login_send_error('invalid_email', 'Google hesabinda gecerli e-posta bulunamadi.', 401);
    }

    $email_verified = polaris_google_login_bool($userinfo['email_verified'] ?? ($id_claims['email_verified'] ?? false));
    if (!$email_verified) {
        polaris_google_login_send_error('email_not_verified', 'Google e-posta adresi dogrulanmamis.', 403);
    }

    $google_sub = isset($userinfo['sub']) ? sanitize_text_field((string) $userinfo['sub']) : '';
    $first_name = isset($userinfo['given_name']) ? sanitize_text_field((string) $userinfo['given_name']) : '';
    $last_name  = isset($userinfo['family_name']) ? sanitize_text_field((string) $userinfo['family_name']) : '';
    $full_name  = trim($first_name . ' ' . $last_name);
    $picture    = isset($userinfo['picture']) ? esc_url_raw((string) $userinfo['picture']) : '';

    $user = null;
    $existing_user_id = email_exists($email);
    if (!empty($existing_user_id)) {
        $user = get_user_by('id', (int) $existing_user_id);
    }

    $raw_password = '';

    if (!$user) {
        $username = polaris_google_login_generate_username($email);
        $raw_password = wp_generate_password(32, true, true);

        $user_id = wp_create_user($username, $raw_password, $email);
        if (is_wp_error($user_id)) {
            polaris_google_login_send_error('user_create_failed', $user_id->get_error_message(), 500);
        }

        $display_name = !empty($full_name) ? $full_name : $username;

        wp_update_user([
            'ID'           => (int) $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $display_name,
        ]);

        $user = get_user_by('id', (int) $user_id);

        polaris_google_login_log('Yeni kullanici olusturuldu.', [
            'user_id' => (int) $user_id,
            'email'   => $email,
        ]);
    } else {
        $update_data = [
            'ID' => (int) $user->ID,
        ];

        if (!empty($first_name)) {
            $update_data['first_name'] = $first_name;
        }

        if (!empty($last_name)) {
            $update_data['last_name'] = $last_name;
        }

        if (!empty($full_name)) {
            $update_data['display_name'] = $full_name;
        }

        if (count($update_data) > 1) {
            wp_update_user($update_data);
        }
    }

    if (!$user instanceof WP_User) {
        polaris_google_login_send_error('user_resolve_failed', 'Kullanici olusturulamadi.', 500);
    }

    if (!empty($google_sub)) {
        update_user_meta($user->ID, 'polaris_google_sub', $google_sub);
    }

    if (!empty($picture)) {
        update_user_meta($user->ID, 'polaris_google_avatar', $picture);
    }

    update_user_meta($user->ID, 'polaris_google_last_login', current_time('mysql', true));

    $is_logged_in = false;

    // Sadece yeni olusturulan kullanicida wp_signon denenir.
    if (!empty($raw_password)) {
        $signon_user = wp_signon([
            'user_login'    => $user->user_login,
            'user_password' => $raw_password,
            'remember'      => true,
        ], is_ssl());

        if (!is_wp_error($signon_user) && $signon_user instanceof WP_User) {
            $user         = $signon_user;
            $is_logged_in = true;
        } else {
            polaris_google_login_log('wp_signon fallback calisti.', [
                'user_id' => (int) $user->ID,
            ]);
        }
    }

    if (!$is_logged_in) {
        wp_set_current_user((int) $user->ID);
        wp_set_auth_cookie((int) $user->ID, true, is_ssl());
    }

    do_action('wp_login', $user->user_login, $user);

    $redirect_url = polaris_google_login_validate_redirect($redirect_to);

    wp_send_json_success([
        'message'      => __('Google ile giris basarili.', 'polaris'),
        'redirect_url' => $redirect_url,
    ]);
}

function polaris_google_login_render_button($context = 'default')
{
    if (!polaris_google_login_is_enabled()) {
        return;
    }

    $context = sanitize_html_class((string) $context);

    $redirect_to = '';
    if (isset($_GET['redirect_to'])) {
        $redirect_to = esc_url_raw(wp_unslash($_GET['redirect_to']));
    }

    get_template_part('template-parts/auth/google-login-button', null, [
        'context'     => $context,
        'redirect_to' => $redirect_to,
    ]);
}

function polaris_google_login_render_woo_login_button()
{
    polaris_google_login_render_button('woo-login');
}

function polaris_google_login_render_woo_register_button()
{
    polaris_google_login_render_button('woo-register');
}

function polaris_google_login_shortcode($atts = [])
{
    $atts = shortcode_atts([
        'context' => 'shortcode',
    ], $atts, 'polaris_google_login_button');

    ob_start();
    polaris_google_login_render_button($atts['context']);
    return ob_get_clean();
}

function polaris_google_login_enqueue_assets()
{
    if (is_admin() || !polaris_google_login_is_enabled()) {
        return;
    }

    $config = polaris_google_login_get_config();
    $js_path = get_template_directory() . '/assets/js/google-login.js';
    $js_ver  = file_exists($js_path) ? (string) filemtime($js_path) : '1.0.0';

    wp_enqueue_script(
        'polaris-google-gsi',
        'https://accounts.google.com/gsi/client',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'polaris-google-login',
        get_template_directory_uri() . '/assets/js/google-login.js',
        ['polaris-google-gsi'],
        $js_ver,
        true
    );

    $redirect_hint = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';

    wp_localize_script('polaris-google-login', 'polarisGoogleLogin', [
        'ajaxUrl'         => admin_url('admin-ajax.php'),
        'nonce'           => wp_create_nonce('polaris_google_login_nonce'),
        'clientId'        => $config['client_id'],
        'redirectUri'     => $config['redirect_uri'],
        'scope'           => $config['scope'],
        'prepareAction'   => 'polaris_google_login_prepare',
        'exchangeAction'  => 'polaris_google_login_exchange',
        'defaultRedirect' => polaris_google_login_validate_redirect($redirect_hint),
        'isEnabled'       => true,
        'messages'        => [
            'loading'      => __('Google ile giris baslatiliyor...', 'polaris'),
            'notReady'     => __('Google kutuphanesi henuz yuklenmedi. Lutfen tekrar deneyin.', 'polaris'),
            'failed'       => __('Google girisi tamamlanamadi. Lutfen tekrar deneyin.', 'polaris'),
            'cancelled'    => __('Google girisi iptal edildi.', 'polaris'),
            'success'      => __('Giris basarili, yonlendiriliyorsunuz...', 'polaris'),
        ],
    ]);
}

add_action('wp_enqueue_scripts', 'polaris_google_login_enqueue_assets', 30);

add_action('wp_ajax_nopriv_polaris_google_login_prepare', 'polaris_google_login_prepare_state_ajax');
add_action('wp_ajax_polaris_google_login_prepare', 'polaris_google_login_prepare_state_ajax');

add_action('wp_ajax_nopriv_polaris_google_login_exchange', 'polaris_google_login_exchange_ajax');
add_action('wp_ajax_polaris_google_login_exchange', 'polaris_google_login_exchange_ajax');

add_action('woocommerce_login_form_end', 'polaris_google_login_render_woo_login_button', 25);
add_action('woocommerce_register_form_end', 'polaris_google_login_render_woo_register_button', 25);

add_shortcode('polaris_google_login_button', 'polaris_google_login_shortcode');
