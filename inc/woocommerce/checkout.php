<?php
if (!defined('ABSPATH')) {
    exit;
}

function polaris_checkout_get_tr_state_map()
{
    if (!function_exists('WC') || !WC() || !WC()->countries) {
        return [];
    }

    $states = WC()->countries->get_states('TR');

    return is_array($states) ? $states : [];
}

function polaris_checkout_get_city_state_map()
{
    $states = polaris_checkout_get_tr_state_map();
    $map    = [];

    foreach ($states as $state_code => $city_name) {
        $city_name  = wc_clean((string) $city_name);
        $state_code = wc_clean((string) $state_code);

        if ('' === $city_name || '' === $state_code) {
            continue;
        }

        $map[$city_name] = $state_code;
    }

    return apply_filters('polaris_checkout_city_state_map', $map, $states);
}

function polaris_checkout_get_city_options()
{
    $options = [
        '' => __('Şehir seçin', 'polaris'),
    ];

    foreach (polaris_checkout_get_city_state_map() as $city_name => $state_code) {
        $options[$city_name] = $city_name;
    }

    return apply_filters('polaris_checkout_city_options', $options);
}

function polaris_checkout_get_default_district_map()
{
    return [
        'Istanbul' => ['Adalar', 'Arnavutkoy', 'Atasehir', 'Avcilar', 'Bagcilar', 'Bahcelievler', 'Bakirkoy', 'Basaksehir', 'Bayrampasa', 'Besiktas', 'Beykoz', 'Beylikduzu', 'Beyoglu', 'Buyukcekmece', 'Catalca', 'Esenler', 'Esenyurt', 'Eyupsultan', 'Fatih', 'Gaziosmanpasa', 'Gungoren', 'Kadikoy', 'Kagithane', 'Kartal', 'Kucukcekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sariyer', 'Silivri', 'Sisli', 'Sultanbeyli', 'Sultangazi', 'Tuzla', 'Umraniye', 'Uskudar', 'Zeytinburnu'],
        'Ankara'   => ['Altindag', 'Cankaya', 'Etimesgut', 'Golbasi', 'Kecioren', 'Mamak', 'Pursaklar', 'Sincan', 'Yenimahalle'],
        'Izmir'    => ['Aliaga', 'Balcova', 'Bayindir', 'Bayrakli', 'Bergama', 'Bornova', 'Buca', 'Cesme', 'Cigli', 'Dikili', 'Foca', 'Gaziemir', 'Guzelbahce', 'Karabaglar', 'Karaburun', 'Karsiyaka', 'Kemalpasa', 'Konak', 'Menderes', 'Menemen', 'Narlidere', 'Odemis', 'Seferihisar', 'Selcuk', 'Tire', 'Torbali', 'Urla'],
        'Bursa'    => ['Gemlik', 'Gursu', 'Inegol', 'Karacabey', 'Mudanya', 'Nilufer', 'Orhangazi', 'Osmangazi', 'Yildirim'],
        'Antalya'  => ['Aksu', 'Alanya', 'Dosemealti', 'Kepez', 'Konyaalti', 'Muratpasa', 'Serik'],
        'Kocaeli'  => ['Basiskele', 'Cayirova', 'Darica', 'Derince', 'Dilovasi', 'Gebze', 'Golcuk', 'Izmit', 'Kandira', 'Karamursel', 'Kartepe', 'Korfez'],
    ];
}

function polaris_checkout_get_district_map()
{
    return apply_filters('polaris_checkout_district_map', polaris_checkout_get_default_district_map());
}

function polaris_checkout_get_google_places_api_key()
{
    $key = defined('POLARIS_GOOGLE_PLACES_API_KEY') ? (string) POLARIS_GOOGLE_PLACES_API_KEY : '';
    $key = apply_filters('polaris_checkout_google_places_api_key', $key);

    return trim((string) $key);
}

function polaris_checkout_normalize_token($value)
{
    $value = trim((string) $value);

    if ('' === $value) {
        return '';
    }

    if (function_exists('remove_accents')) {
        $value = remove_accents($value);
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '', $value);

    return (string) $value;
}

function polaris_checkout_find_state_code_by_city($city_name)
{
    $city_name = wc_clean((string) $city_name);
    if ('' === $city_name) {
        return '';
    }

    $map = polaris_checkout_get_city_state_map();
    if (isset($map[$city_name])) {
        return (string) $map[$city_name];
    }

    $target = polaris_checkout_normalize_token($city_name);
    if ('' === $target) {
        return '';
    }

    foreach ($map as $city_label => $state_code) {
        if ($target === polaris_checkout_normalize_token($city_label)) {
            return (string) $state_code;
        }
    }

    return '';
}

function polaris_checkout_normalize_phone($value)
{
    $digits = preg_replace('/\D+/', '', (string) $value);
    if ('' === $digits) {
        return '';
    }

    if (12 === strlen($digits) && 0 === strpos($digits, '90')) {
        $digits = substr($digits, 2);
    }

    if (11 === strlen($digits) && '0' === $digits[0]) {
        $digits = substr($digits, 1);
    }

    if (!preg_match('/^[2-5][0-9]{9}$/', $digits)) {
        return '';
    }

    return sprintf(
        '0 (%s) %s %s %s',
        substr($digits, 0, 3),
        substr($digits, 3, 3),
        substr($digits, 6, 2),
        substr($digits, 8, 2)
    );
}

function polaris_checkout_split_full_name($full_name)
{
    $full_name = trim(preg_replace('/\s+/u', ' ', wc_clean((string) $full_name)));

    if ('' === $full_name) {
        return ['', ''];
    }

    $parts = explode(' ', $full_name);
    if (1 === count($parts)) {
        return [$parts[0], '-'];
    }

    $last_name  = (string) array_pop($parts);
    $first_name = trim(implode(' ', $parts));

    return [$first_name, $last_name];
}

function polaris_checkout_truthy_post_value($value)
{
    $value = is_string($value) ? strtolower(trim($value)) : $value;

    return in_array($value, ['1', 1, true, 'true', 'yes', 'on'], true);
}

add_filter('woocommerce_checkout_fields', function ($fields) {
    if (!is_array($fields)) {
        return $fields;
    }

    $city_options = polaris_checkout_get_city_options();

    if (isset($fields['billing']['billing_first_name'])) {
        $fields['billing']['billing_first_name']['type']     = 'hidden';
        $fields['billing']['billing_first_name']['required'] = false;
        $fields['billing']['billing_first_name']['class']    = ['polaris-hidden-field'];
        $fields['billing']['billing_first_name']['priority'] = 5;
    }

    if (isset($fields['billing']['billing_last_name'])) {
        $fields['billing']['billing_last_name']['type']     = 'hidden';
        $fields['billing']['billing_last_name']['required'] = false;
        $fields['billing']['billing_last_name']['class']    = ['polaris-hidden-field'];
        $fields['billing']['billing_last_name']['priority'] = 6;
    }

    $fields['billing']['billing_full_name'] = [
        'type'         => 'text',
        'label'        => __('Ad Soyad', 'polaris'),
        'placeholder'  => __('Ad Soyad', 'polaris'),
        'required'     => true,
        'class'        => ['form-row-wide'],
        'clear'        => true,
        'priority'     => 10,
        'autocomplete' => 'name',
    ];

    $fields['billing']['billing_tc_kimlik_no'] = [
        'type'        => 'text',
        'label'       => __('T.C. Kimlik No', 'polaris'),
        'placeholder' => __('11 haneli T.C. Kimlik No', 'polaris'),
        'required'    => false,
        'class'       => ['form-row-wide'],
        'priority'    => 20,
        'input_class' => ['polaris-tc-input'],
        'custom_attributes' => [
            'inputmode'    => 'numeric',
            'maxlength'    => '11',
            'pattern'      => '[0-9]*',
            'autocomplete' => 'off',
        ],
    ];

    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['label']       = __('Telefon', 'polaris');
        $fields['billing']['billing_phone']['placeholder'] = __('0 (5xx) xxx xx xx', 'polaris');
        $fields['billing']['billing_phone']['required']    = true;
        $fields['billing']['billing_phone']['class']       = ['form-row-first'];
        $fields['billing']['billing_phone']['priority']    = 30;
        $fields['billing']['billing_phone']['input_class'] = ['polaris-phone-input'];
        $fields['billing']['billing_phone']['custom_attributes'] = [
            'inputmode'    => 'tel',
            'maxlength'    => '18',
            'autocomplete' => 'tel',
        ];
    }

    if (isset($fields['billing']['billing_email'])) {
        $fields['billing']['billing_email']['label']    = __('E-posta', 'polaris');
        $fields['billing']['billing_email']['required'] = true;
        $fields['billing']['billing_email']['class']    = ['form-row-last'];
        $fields['billing']['billing_email']['priority'] = 40;
    }

    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country']['label']    = __('Ülke', 'polaris');
        $fields['billing']['billing_country']['required'] = true;
        $fields['billing']['billing_country']['priority'] = 50;
        $fields['billing']['billing_country']['default']  = 'TR';
        $fields['billing']['billing_country']['class']    = ['form-row-wide'];
    }

    if (isset($fields['billing']['billing_address_1'])) {
        $fields['billing']['billing_address_1']['label']       = __('Adres', 'polaris');
        $fields['billing']['billing_address_1']['placeholder'] = __('Mahalle, sokak, bina no, daire no', 'polaris');
        $fields['billing']['billing_address_1']['required']    = true;
        $fields['billing']['billing_address_1']['class']       = ['form-row-wide'];
        $fields['billing']['billing_address_1']['priority']    = 60;
        $fields['billing']['billing_address_1']['custom_attributes'] = [
            'autocomplete'                      => 'street-address',
            'data-polaris-address-autocomplete' => 'billing',
        ];
    }

    if (isset($fields['billing']['billing_address_2'])) {
        $fields['billing']['billing_address_2']['type']     = 'hidden';
        $fields['billing']['billing_address_2']['required'] = false;
        $fields['billing']['billing_address_2']['class']    = ['polaris-hidden-field'];
        $fields['billing']['billing_address_2']['priority'] = 61;
    }

    $fields['billing']['billing_district'] = [
        'type'        => 'text',
        'label'       => __('İlçe', 'polaris'),
        'placeholder' => __('İlçe', 'polaris'),
        'required'    => true,
        'class'       => ['form-row-wide'],
        'priority'    => 70,
        'custom_attributes' => [
            'list'         => 'billing_district_list',
            'autocomplete' => 'address-level2',
        ],
    ];

    $fields['billing']['billing_city'] = [
        'type'        => 'select',
        'label'       => __('Şehir', 'polaris'),
        'required'    => true,
        'class'       => ['form-row-wide'],
        'priority'    => 80,
        'options'     => $city_options,
        'input_class' => ['polaris-city-select'],
    ];

    if (isset($fields['billing']['billing_state'])) {
        $fields['billing']['billing_state']['type']     = 'hidden';
        $fields['billing']['billing_state']['required'] = false;
        $fields['billing']['billing_state']['class']    = ['polaris-hidden-field'];
        $fields['billing']['billing_state']['priority'] = 81;
    }

    if (isset($fields['billing']['billing_postcode'])) {
        $fields['billing']['billing_postcode']['label']       = __('Posta Kodu', 'polaris');
        $fields['billing']['billing_postcode']['placeholder'] = __('5 haneli posta kodu', 'polaris');
        $fields['billing']['billing_postcode']['required']    = true;
        $fields['billing']['billing_postcode']['class']       = ['form-row-wide'];
        $fields['billing']['billing_postcode']['priority']    = 90;
        $fields['billing']['billing_postcode']['custom_attributes'] = [
            'inputmode'    => 'numeric',
            'maxlength'    => '5',
            'pattern'      => '[0-9]*',
            'autocomplete' => 'postal-code',
        ];
    }

    unset($fields['billing']['billing_company']);

    if (isset($fields['shipping']['shipping_first_name'])) {
        $fields['shipping']['shipping_first_name']['type']     = 'hidden';
        $fields['shipping']['shipping_first_name']['required'] = false;
        $fields['shipping']['shipping_first_name']['class']    = ['polaris-hidden-field'];
        $fields['shipping']['shipping_first_name']['priority'] = 5;
    }

    if (isset($fields['shipping']['shipping_last_name'])) {
        $fields['shipping']['shipping_last_name']['type']     = 'hidden';
        $fields['shipping']['shipping_last_name']['required'] = false;
        $fields['shipping']['shipping_last_name']['class']    = ['polaris-hidden-field'];
        $fields['shipping']['shipping_last_name']['priority'] = 6;
    }

    $fields['shipping']['shipping_full_name'] = [
        'type'         => 'text',
        'label'        => __('Ad Soyad', 'polaris'),
        'placeholder'  => __('Ad Soyad', 'polaris'),
        'required'     => true,
        'class'        => ['form-row-wide'],
        'clear'        => true,
        'priority'     => 10,
        'autocomplete' => 'name',
    ];

    $fields['shipping']['shipping_phone'] = [
        'type'        => 'text',
        'label'       => __('Telefon', 'polaris'),
        'placeholder' => __('0 (5xx) xxx xx xx', 'polaris'),
        'required'    => true,
        'class'       => ['form-row-wide'],
        'priority'    => 20,
        'input_class' => ['polaris-phone-input'],
        'custom_attributes' => [
            'inputmode'    => 'tel',
            'maxlength'    => '18',
            'autocomplete' => 'tel',
        ],
    ];

    if (isset($fields['shipping']['shipping_country'])) {
        $fields['shipping']['shipping_country']['label']    = __('Ülke', 'polaris');
        $fields['shipping']['shipping_country']['required'] = true;
        $fields['shipping']['shipping_country']['priority'] = 30;
        $fields['shipping']['shipping_country']['default']  = 'TR';
        $fields['shipping']['shipping_country']['class']    = ['form-row-wide'];
    }

    if (isset($fields['shipping']['shipping_address_1'])) {
        $fields['shipping']['shipping_address_1']['label']       = __('Adres', 'polaris');
        $fields['shipping']['shipping_address_1']['placeholder'] = __('Mahalle, sokak, bina no, daire no', 'polaris');
        $fields['shipping']['shipping_address_1']['required']    = true;
        $fields['shipping']['shipping_address_1']['class']       = ['form-row-wide'];
        $fields['shipping']['shipping_address_1']['priority']    = 40;
        $fields['shipping']['shipping_address_1']['custom_attributes'] = [
            'autocomplete'                      => 'street-address',
            'data-polaris-address-autocomplete' => 'shipping',
        ];
    }

    if (isset($fields['shipping']['shipping_address_2'])) {
        $fields['shipping']['shipping_address_2']['type']     = 'hidden';
        $fields['shipping']['shipping_address_2']['required'] = false;
        $fields['shipping']['shipping_address_2']['class']    = ['polaris-hidden-field'];
        $fields['shipping']['shipping_address_2']['priority'] = 41;
    }

    $fields['shipping']['shipping_district'] = [
        'type'        => 'text',
        'label'       => __('İlçe', 'polaris'),
        'placeholder' => __('İlçe', 'polaris'),
        'required'    => true,
        'class'       => ['form-row-wide'],
        'priority'    => 50,
        'custom_attributes' => [
            'list'         => 'shipping_district_list',
            'autocomplete' => 'address-level2',
        ],
    ];

    $fields['shipping']['shipping_city'] = [
        'type'        => 'select',
        'label'       => __('Şehir', 'polaris'),
        'required'    => true,
        'class'       => ['form-row-wide'],
        'priority'    => 60,
        'options'     => $city_options,
        'input_class' => ['polaris-city-select'],
    ];

    if (isset($fields['shipping']['shipping_state'])) {
        $fields['shipping']['shipping_state']['type']     = 'hidden';
        $fields['shipping']['shipping_state']['required'] = false;
        $fields['shipping']['shipping_state']['class']    = ['polaris-hidden-field'];
        $fields['shipping']['shipping_state']['priority'] = 61;
    }

    if (isset($fields['shipping']['shipping_postcode'])) {
        $fields['shipping']['shipping_postcode']['label']       = __('Posta Kodu', 'polaris');
        $fields['shipping']['shipping_postcode']['placeholder'] = __('5 haneli posta kodu', 'polaris');
        $fields['shipping']['shipping_postcode']['required']    = true;
        $fields['shipping']['shipping_postcode']['class']       = ['form-row-wide'];
        $fields['shipping']['shipping_postcode']['priority']    = 70;
        $fields['shipping']['shipping_postcode']['custom_attributes'] = [
            'inputmode'    => 'numeric',
            'maxlength'    => '5',
            'pattern'      => '[0-9]*',
            'autocomplete' => 'postal-code',
        ];
    }

    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['label']       = __('Sipariş notu', 'polaris');
        $fields['order']['order_comments']['placeholder'] = __('Siparişiniz ile ilgili not ekleyebilirsiniz.', 'polaris');
        $fields['order']['order_comments']['required']    = false;
        $fields['order']['order_comments']['priority']    = 150;
    }

    $fields['order']['billing_corporate_invoice'] = [
        'type'     => 'checkbox',
        'label'    => __('Kurumsal fatura istiyorum', 'polaris'),
        'required' => false,
        'class'    => ['form-row-wide', 'polaris-corporate-toggle-row'],
        'priority' => 120,
    ];

    $fields['order']['billing_company'] = [
        'type'        => 'text',
        'label'       => __('Şirket Ünvanı', 'polaris'),
        'placeholder' => __('Şirket Ünvanı', 'polaris'),
        'required'    => false,
        'class'       => ['form-row-wide', 'polaris-corporate-field'],
        'priority'    => 121,
    ];

    $fields['order']['billing_tax_office'] = [
        'type'        => 'text',
        'label'       => __('Vergi Dairesi', 'polaris'),
        'placeholder' => __('Vergi Dairesi', 'polaris'),
        'required'    => false,
        'class'       => ['form-row-first', 'polaris-corporate-field'],
        'priority'    => 122,
    ];

    $fields['order']['billing_tax_number'] = [
        'type'        => 'text',
        'label'       => __('Vergi Numarası', 'polaris'),
        'placeholder' => __('10 veya 11 haneli vergi numarası', 'polaris'),
        'required'    => false,
        'class'       => ['form-row-last', 'polaris-corporate-field'],
        'priority'    => 123,
        'custom_attributes' => [
            'inputmode' => 'numeric',
            'maxlength' => '11',
            'pattern'   => '[0-9]*',
        ],
    ];

    return $fields;
}, 999);

add_filter('woocommerce_default_address_fields', function ($address_fields) {
    if (!is_array($address_fields)) {
        return $address_fields;
    }

    if (isset($address_fields['address_1'])) {
        $address_fields['address_1']['label']       = __('Adres', 'polaris');
        $address_fields['address_1']['placeholder'] = __('Mahalle, sokak, bina no, daire no', 'polaris');
    }

    if (isset($address_fields['city'])) {
        $address_fields['city']['label'] = __('Şehir', 'polaris');
    }

    if (isset($address_fields['state'])) {
        $address_fields['state']['label'] = __('İl', 'polaris');
    }

    return $address_fields;
}, 999);

add_filter('woocommerce_form_field_args', function ($args, $key) {
    $label_map = [
        'billing_address_1'         => __('Adres', 'polaris'),
        'shipping_address_1'        => __('Adres', 'polaris'),
        'billing_city'              => __('Şehir', 'polaris'),
        'shipping_city'             => __('Şehir', 'polaris'),
        'billing_district'          => __('İlçe', 'polaris'),
        'shipping_district'         => __('İlçe', 'polaris'),
        'billing_company'           => __('Şirket Ünvanı', 'polaris'),
        'billing_tax_office'        => __('Vergi Dairesi', 'polaris'),
        'billing_tax_number'        => __('Vergi Numarası', 'polaris'),
        'billing_corporate_invoice' => __('Kurumsal fatura istiyorum', 'polaris'),
        'order_comments'            => __('Sipariş notu', 'polaris'),
    ];

    if (isset($label_map[$key])) {
        $args['label'] = $label_map[$key];
    }

    return $args;
}, 999, 2);

add_filter('default_checkout_billing_country', function () {
    return 'TR';
});

add_filter('default_checkout_shipping_country', function () {
    return 'TR';
});

add_filter('woocommerce_ship_to_different_address_checked', '__return_false', 20);

add_filter('woocommerce_checkout_get_value', function ($value, $input) {
    if (!in_array($input, ['billing_city', 'shipping_city'], true)) {
        return $value;
    }

    if (isset($_POST[$input])) {
        return $value;
    }

    return '';
}, 20, 2);

add_filter('woocommerce_checkout_posted_data', function ($data) {
    if (!is_array($data)) {
        return $data;
    }

    foreach (['billing', 'shipping'] as $address_type) {
        $full_name_key = $address_type . '_full_name';
        if (isset($data[$full_name_key])) {
            $full_name = trim(preg_replace('/\s+/u', ' ', wc_clean((string) $data[$full_name_key])));
            $data[$full_name_key] = $full_name;

            [$first_name, $last_name] = polaris_checkout_split_full_name($full_name);
            $data[$address_type . '_first_name'] = $first_name;
            $data[$address_type . '_last_name']  = $last_name;
        }

        $city_key  = $address_type . '_city';
        $state_key = $address_type . '_state';
        if (isset($data[$city_key])) {
            $data[$city_key] = wc_clean((string) $data[$city_key]);
            $state_code = polaris_checkout_find_state_code_by_city($data[$city_key]);
            if ('' !== $state_code) {
                $data[$state_key] = $state_code;
            }
        }

        $postcode_key = $address_type . '_postcode';
        if (isset($data[$postcode_key])) {
            $data[$postcode_key] = preg_replace('/\D+/', '', (string) $data[$postcode_key]);
        }
    }

    if (isset($data['billing_phone'])) {
        $normalized_billing_phone = polaris_checkout_normalize_phone($data['billing_phone']);
        if ('' !== $normalized_billing_phone) {
            $data['billing_phone'] = $normalized_billing_phone;
        }
    }

    if (isset($data['shipping_phone'])) {
        $normalized_shipping_phone = polaris_checkout_normalize_phone($data['shipping_phone']);
        if ('' !== $normalized_shipping_phone) {
            $data['shipping_phone'] = $normalized_shipping_phone;
        }
    }

    $tc_number = isset($data['billing_tc_kimlik_no']) ? preg_replace('/\D+/', '', (string) $data['billing_tc_kimlik_no']) : '';
    $data['billing_tc_kimlik_no'] = '' === $tc_number ? '11111111111' : $tc_number;

    $corporate_enabled = isset($data['billing_corporate_invoice']) && polaris_checkout_truthy_post_value($data['billing_corporate_invoice']);
    $data['billing_corporate_invoice'] = $corporate_enabled ? '1' : '0';

    foreach (['billing_company', 'billing_tax_office', 'billing_tax_number'] as $corp_key) {
        if (!isset($data[$corp_key])) {
            $data[$corp_key] = '';
            continue;
        }

        $clean_value = wc_clean((string) $data[$corp_key]);
        if ('billing_tax_number' === $corp_key) {
            $clean_value = preg_replace('/\D+/', '', $clean_value);
        }

        $data[$corp_key] = $clean_value;
    }

    if (!$corporate_enabled) {
        $data['billing_company']    = '';
        $data['billing_tax_office'] = '';
        $data['billing_tax_number'] = '';
    }

    return $data;
}, 20);

add_action('woocommerce_checkout_process', function () {
    $billing_full_name = isset($_POST['billing_full_name']) ? trim(wc_clean(wp_unslash((string) $_POST['billing_full_name']))) : '';
    if ('' === $billing_full_name) {
        wc_add_notice(__('Ad Soyad alani zorunludur.', 'polaris'), 'error');
    }

    $billing_tc = isset($_POST['billing_tc_kimlik_no']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['billing_tc_kimlik_no'])) : '';
    if ('' !== $billing_tc && !preg_match('/^[0-9]{11}$/', $billing_tc)) {
        wc_add_notice(__('T.C. Kimlik No 11 haneli sayısal değer olmalıdır.', 'polaris'), 'error');
    }

    $billing_phone = isset($_POST['billing_phone']) ? wc_clean(wp_unslash((string) $_POST['billing_phone'])) : '';
    if ('' === polaris_checkout_normalize_phone($billing_phone)) {
        wc_add_notice(__('Lütfen geçerli bir Türkiye telefon numarası girin.', 'polaris'), 'error');
    }

    $billing_city = isset($_POST['billing_city']) ? wc_clean(wp_unslash((string) $_POST['billing_city'])) : '';
    if ('' === $billing_city || '' === polaris_checkout_find_state_code_by_city($billing_city)) {
        wc_add_notice(__('Lütfen bir şehir seçin.', 'polaris'), 'error');
    }

    $billing_district = isset($_POST['billing_district']) ? trim(wc_clean(wp_unslash((string) $_POST['billing_district']))) : '';
    if ('' === $billing_district) {
        wc_add_notice(__('İlçe alanı zorunludur.', 'polaris'), 'error');
    }

    $billing_postcode = isset($_POST['billing_postcode']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['billing_postcode'])) : '';
    if (!preg_match('/^[0-9]{5}$/', $billing_postcode)) {
        wc_add_notice(__('Posta kodu 5 haneli olmalıdır.', 'polaris'), 'error');
    }

    $ship_to_different = isset($_POST['ship_to_different_address']) && polaris_checkout_truthy_post_value(wp_unslash($_POST['ship_to_different_address']));

    if ($ship_to_different) {
        $shipping_full_name = isset($_POST['shipping_full_name']) ? trim(wc_clean(wp_unslash((string) $_POST['shipping_full_name']))) : '';
        if ('' === $shipping_full_name) {
            wc_add_notice(__('Teslimat için Ad Soyad alanı zorunludur.', 'polaris'), 'error');
        }

        $shipping_phone = isset($_POST['shipping_phone']) ? wc_clean(wp_unslash((string) $_POST['shipping_phone'])) : '';
        if ('' === polaris_checkout_normalize_phone($shipping_phone)) {
            wc_add_notice(__('Teslimat için geçerli bir telefon numarası girin.', 'polaris'), 'error');
        }

        $shipping_city = isset($_POST['shipping_city']) ? wc_clean(wp_unslash((string) $_POST['shipping_city'])) : '';
        if ('' === $shipping_city || '' === polaris_checkout_find_state_code_by_city($shipping_city)) {
            wc_add_notice(__('Teslimat için bir şehir seçin.', 'polaris'), 'error');
        }

        $shipping_district = isset($_POST['shipping_district']) ? trim(wc_clean(wp_unslash((string) $_POST['shipping_district']))) : '';
        if ('' === $shipping_district) {
            wc_add_notice(__('Teslimat ilçesi zorunludur.', 'polaris'), 'error');
        }

        $shipping_postcode = isset($_POST['shipping_postcode']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['shipping_postcode'])) : '';
        if (!preg_match('/^[0-9]{5}$/', $shipping_postcode)) {
            wc_add_notice(__('Teslimat posta kodu 5 haneli olmalıdır.', 'polaris'), 'error');
        }
    }

    $corporate_invoice = isset($_POST['billing_corporate_invoice']) && polaris_checkout_truthy_post_value(wp_unslash($_POST['billing_corporate_invoice']));
    if ($corporate_invoice) {
        $company_title = isset($_POST['billing_company']) ? trim(wc_clean(wp_unslash((string) $_POST['billing_company']))) : '';
        $tax_office    = isset($_POST['billing_tax_office']) ? trim(wc_clean(wp_unslash((string) $_POST['billing_tax_office']))) : '';
        $tax_number    = isset($_POST['billing_tax_number']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['billing_tax_number'])) : '';

        if ('' === $company_title) {
            wc_add_notice(__('Kurumsal fatura için Şirket Ünvanı zorunludur.', 'polaris'), 'error');
        }

        if ('' === $tax_office) {
            wc_add_notice(__('Kurumsal fatura için Vergi Dairesi zorunludur.', 'polaris'), 'error');
        }

        if (!preg_match('/^[0-9]{10,11}$/', $tax_number)) {
            wc_add_notice(__('Vergi Numarası 10 veya 11 haneli sayısal değer olmalıdır.', 'polaris'), 'error');
        }
    }
}, 20);

add_action('woocommerce_checkout_create_order', function ($order, $data) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $billing_tc = '';
    if (isset($data['billing_tc_kimlik_no'])) {
        $billing_tc = preg_replace('/\D+/', '', (string) $data['billing_tc_kimlik_no']);
    }
    if ('' === $billing_tc) {
        $billing_tc = '11111111111';
    }

    $order->update_meta_data('_billing_tc_kimlik_no', $billing_tc);

    if (isset($data['billing_district'])) {
        $order->update_meta_data('_billing_district', sanitize_text_field((string) $data['billing_district']));
    }

    if (isset($data['shipping_district'])) {
        $order->update_meta_data('_shipping_district', sanitize_text_field((string) $data['shipping_district']));
    }

    if (isset($data['shipping_phone'])) {
        $order->update_meta_data('_shipping_phone', sanitize_text_field((string) $data['shipping_phone']));
    }

    $corporate_invoice = isset($data['billing_corporate_invoice']) && polaris_checkout_truthy_post_value($data['billing_corporate_invoice']) ? '1' : '0';
    $order->update_meta_data('_billing_corporate_invoice', $corporate_invoice);

    if ('1' === $corporate_invoice) {
        if (!empty($data['billing_company'])) {
            $order->set_billing_company(sanitize_text_field((string) $data['billing_company']));
        }

        if (isset($data['billing_tax_office'])) {
            $order->update_meta_data('_billing_tax_office', sanitize_text_field((string) $data['billing_tax_office']));
        }

        if (isset($data['billing_tax_number'])) {
            $order->update_meta_data('_billing_tax_number', preg_replace('/\D+/', '', (string) $data['billing_tax_number']));
        }
    } else {
        $order->set_billing_company('');
        $order->delete_meta_data('_billing_tax_office');
        $order->delete_meta_data('_billing_tax_number');
    }
}, 20, 2);

function polaris_checkout_get_order_meta($order, $key)
{
    if (!$order instanceof WC_Order) {
        return '';
    }

    $value = $order->get_meta($key, true);
    if ('' !== (string) $value) {
        return $value;
    }

    if (0 !== strpos($key, '_')) {
        $value = $order->get_meta('_' . $key, true);
    }

    return $value;
}

add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $tc_number         = polaris_checkout_get_order_meta($order, '_billing_tc_kimlik_no');
    $billing_district  = polaris_checkout_get_order_meta($order, '_billing_district');
    $corporate_invoice = polaris_checkout_get_order_meta($order, '_billing_corporate_invoice');
    $tax_office        = polaris_checkout_get_order_meta($order, '_billing_tax_office');
    $tax_number        = polaris_checkout_get_order_meta($order, '_billing_tax_number');

    if ('' !== (string) $tc_number) {
        echo '<p><strong>' . esc_html__('T.C. Kimlik No:', 'polaris') . '</strong> ' . esc_html((string) $tc_number) . '</p>';
    }

    if ('' !== (string) $billing_district) {
        echo '<p><strong>' . esc_html__('İlçe:', 'polaris') . '</strong> ' . esc_html((string) $billing_district) . '</p>';
    }

    if ('1' === (string) $corporate_invoice) {
        echo '<p><strong>' . esc_html__('Kurumsal Fatura:', 'polaris') . '</strong> ' . esc_html__('Evet', 'polaris') . '</p>';

        if ('' !== (string) $tax_office) {
            echo '<p><strong>' . esc_html__('Vergi Dairesi:', 'polaris') . '</strong> ' . esc_html((string) $tax_office) . '</p>';
        }

        if ('' !== (string) $tax_number) {
            echo '<p><strong>' . esc_html__('Vergi Numarası:', 'polaris') . '</strong> ' . esc_html((string) $tax_number) . '</p>';
        }
    }
}, 20);

add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    if (!$order instanceof WC_Order) {
        return;
    }

    $shipping_phone    = polaris_checkout_get_order_meta($order, '_shipping_phone');
    $shipping_district = polaris_checkout_get_order_meta($order, '_shipping_district');

    if ('' !== (string) $shipping_phone) {
        echo '<p><strong>' . esc_html__('Telefon:', 'polaris') . '</strong> ' . esc_html((string) $shipping_phone) . '</p>';
    }

    if ('' !== (string) $shipping_district) {
        echo '<p><strong>' . esc_html__('İlçe:', 'polaris') . '</strong> ' . esc_html((string) $shipping_district) . '</p>';
    }
}, 20);

add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {
    if (!$order instanceof WC_Order) {
        return $fields;
    }

    $tc_number         = polaris_checkout_get_order_meta($order, '_billing_tc_kimlik_no');
    $billing_district  = polaris_checkout_get_order_meta($order, '_billing_district');
    $shipping_phone    = polaris_checkout_get_order_meta($order, '_shipping_phone');
    $shipping_district = polaris_checkout_get_order_meta($order, '_shipping_district');
    $corporate_invoice = polaris_checkout_get_order_meta($order, '_billing_corporate_invoice');
    $tax_office        = polaris_checkout_get_order_meta($order, '_billing_tax_office');
    $tax_number        = polaris_checkout_get_order_meta($order, '_billing_tax_number');

    if ('' !== (string) $tc_number) {
        $fields['billing_tc_kimlik_no'] = [
            'label' => __('T.C. Kimlik No', 'polaris'),
            'value' => $tc_number,
        ];
    }

    if ('' !== (string) $billing_district) {
        $fields['billing_district'] = [
            'label' => __('Fatura İlçe', 'polaris'),
            'value' => $billing_district,
        ];
    }

    if ('' !== (string) $shipping_phone) {
        $fields['shipping_phone'] = [
            'label' => __('Teslimat Telefon', 'polaris'),
            'value' => $shipping_phone,
        ];
    }

    if ('' !== (string) $shipping_district) {
        $fields['shipping_district'] = [
            'label' => __('Teslimat İlçe', 'polaris'),
            'value' => $shipping_district,
        ];
    }

    if ('1' === (string) $corporate_invoice) {
        $fields['corporate_invoice'] = [
            'label' => __('Kurumsal Fatura', 'polaris'),
            'value' => __('Evet', 'polaris'),
        ];

        if ('' !== (string) $tax_office) {
            $fields['billing_tax_office'] = [
                'label' => __('Vergi Dairesi', 'polaris'),
                'value' => $tax_office,
            ];
        }

        if ('' !== (string) $tax_number) {
            $fields['billing_tax_number'] = [
                'label' => __('Vergi Numarası', 'polaris'),
                'value' => $tax_number,
            ];
        }
    }

    return $fields;
}, 20, 3);

add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }

    $script_path = get_template_directory() . '/assets/js/checkout.js';
    if (!file_exists($script_path)) {
        return;
    }

    $dependencies   = ['polaris-main', 'wc-checkout'];
    $google_api_key = polaris_checkout_get_google_places_api_key();

    if ('' !== $google_api_key) {
        $google_places_url = add_query_arg(
            [
                'key'       => $google_api_key,
                'libraries' => 'places',
                'language'  => 'tr',
                'region'    => 'TR',
            ],
            'https://maps.googleapis.com/maps/api/js'
        );

        wp_enqueue_script(
            'polaris-google-places',
            $google_places_url,
            [],
            null,
            true
        );
        wp_script_add_data('polaris-google-places', 'defer', true);

        $dependencies[] = 'polaris-google-places';
    }

    wp_enqueue_script(
        'polaris-checkout',
        get_template_directory_uri() . '/assets/js/checkout.js',
        $dependencies,
        (string) filemtime($script_path),
        true
    );
    wp_script_add_data('polaris-checkout', 'defer', true);

    wp_localize_script('polaris-checkout', 'polarisCheckoutConfig', [
        'cityStateMap'        => polaris_checkout_get_city_state_map(),
        'districtMap'         => polaris_checkout_get_district_map(),
        'googlePlacesEnabled' => '' !== $google_api_key,
        'messages'            => [
            'invalidPhone'    => __('Lütfen geçerli bir Türkiye telefon numarası girin.', 'polaris'),
            'invalidTC'       => __('T.C. Kimlik No 11 haneli sayısal değer olmalıdır.', 'polaris'),
            'invalidPostcode' => __('Posta kodu 5 haneli olmalıdır.', 'polaris'),
            'requiredField'   => __('Bu alan zorunludur.', 'polaris'),
        ],
    ]);
}, 40);
