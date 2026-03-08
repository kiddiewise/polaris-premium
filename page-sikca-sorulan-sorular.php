<?php
if (!defined('ABSPATH')) {
    exit;
}

$whatsapp_message = __('Merhaba, sıkça sorulan sorular sayfanızdan ulaştım. Bilgi almak istiyorum.', 'polaris');
$whatsapp_url     = function_exists('polaris_get_whatsapp_url')
    ? polaris_get_whatsapp_url($whatsapp_message)
    : esc_url('https://wa.me/905462629002?text=' . rawurlencode($whatsapp_message));
$whatsapp_number  = function_exists('polaris_get_whatsapp_number')
    ? polaris_get_whatsapp_number()
    : '905462629002';
$whatsapp_display = '+' . ltrim((string) $whatsapp_number, '+');

$faq_sections = [
    [
        'title'  => __('Sipariş ve ödeme', 'polaris'),
        'kicker' => __('Sipariş adımları ve ödeme seçenekleri', 'polaris'),
        'icon'   => 'fa-solid fa-credit-card',
        'items'  => [
            [
                'question' => __('Siparişimi nasıl verebilirim?', 'polaris'),
                'answer'   => __('Ana sayfada görmek istediğiniz ürüne tıklayarak ürün sayfasına gidin. Satın almak istediğiniz adedi belirleyip Sepete Ekle seçeneğiyle ürünleri sepetinize ekleyebilirsiniz. Ürünleri sepete attıktan sonra Sepete Git sayfasından toplam tutarı ve kargo seçeneklerini görebilir, ardından ekrandaki adımları takip ederek siparişinizi tamamlayabilirsiniz.', 'polaris'),
            ],
            [
                'question' => __('Hangi ödeme yöntemlerini kabul ediyorsunuz?', 'polaris'),
                'answer'   => __('Sitemiz üzerinden yaptığınız alışverişlerde kredi kartı ile ödeme yapabilir veya hesabımıza EFT gönderebilirsiniz. Detaylar için sitedeki Hesap Bilgileri bölümünü inceleyebilirsiniz.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('Kargo ve teslimat', 'polaris'),
        'kicker' => __('Kargo bedeli, süreç ve teslimat bilgileri', 'polaris'),
        'icon'   => 'fa-solid fa-truck-fast',
        'items'  => [
            [
                'question' => __('Kargo ücreti ne kadar?', 'polaris'),
                'answer'   => __('1.000 TL ve üzeri alışverişlerde kargo ücretsizdir. Bu tutarın altındaki siparişlerde 150 TL kargo ücreti uygulanır.', 'polaris'),
            ],
            [
                'question' => __('Hangi kargo şirketiyle çalışıyorsunuz?', 'polaris'),
                'answer'   => __('Siparişlerin en güvenli ve hızlı şekilde ulaştırılması için anlaşmalı kargo şirketimiz DHL Kargo’dur.', 'polaris'),
            ],
            [
                'question' => __('Siparişim kaç günde kargoya verilir?', 'polaris'),
                'answer'   => __('Stoklar güncel tutulduğu için siparişler mümkün olan en kısa sürede kargoya verilir. Ancak üretim gerekebilecek durumlar veya sipariş yoğunluğu nedeniyle tedarik ve kargolama süresi 3-4 iş gününe kadar uzayabilir. Siparişinizin güncel durumunu Siparişlerim bölümünden takip edebilir veya bizimle iletişime geçebilirsiniz.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('İade ve satış politikası', 'polaris'),
        'kicker' => __('İade koşulları ve satış politikası', 'polaris'),
        'icon'   => 'fa-solid fa-rotate-left',
        'items'  => [
            [
                'question' => __('Ürün iadesi nasıl yapılır?', 'polaris'),
                'answer'   => __('Sitemizden aldığınız Polaris markalı tüm ürünleri koşulsuz olarak iade edebilirsiniz. Diğer marka ürünlerde ise Tüketicinin Korunması Hakkında Kanun kapsamında belirtilen şartları taşıyan iade talepleri için başvuruda bulunabilirsiniz. Ayrıntılar için İptal ve İade Koşulları bölümünü inceleyebilirsiniz.', 'polaris'),
            ],
            [
                'question' => __('Toptan satışınız var mı?', 'polaris'),
                'answer'   => __('Hayır, Polaris kurşun ürünlerinde toptan satış bulunmamaktadır.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('Ürünler hakkında', 'polaris'),
        'kicker' => __('Polaris ürün aileleri ve renk seçenekleri', 'polaris'),
        'icon'   => 'fa-solid fa-fish',
        'items'  => [
            [
                'question' => __('Polaris marka kurşunları neden tercih etmeliyim?', 'polaris'),
                'answer'   => __('Polaris kurşunları av verimliliği, görünürlük ve çevresel etki açısından avantaj sunar. Kaplama ve aerodinamik yapı atış performansını destekler. Renkli ve glowlu seriler su altında görünürlüğü artırarak hem balıkların dikkatini çeker hem de su altında kalan ürünlerin daha kolay fark edilmesini sağlar. Glowlu ürünlerde kullanımdan önce beyaz veya tercihen UV ışık tutulması daha parlak ve daha uzun süreli görünürlük sağlar. Ayrıca kaplama, çıplak kurşunun suyla doğrudan temasını azaltarak çevresel etkiyi ve kullanıcı temas riskini düşürmeye yardımcı olur.', 'polaris'),
            ],
            [
                'question' => __('Ürünlerinizde renk tercihi yapabiliyor muyuz?', 'polaris'),
                'answer'   => __('Glowlu fosforlu serilerde renk alternatifi yoktur; ürünler griye yakın beyaz renktedir. Renkli serilerde ise stok durumuna göre UV sarı, turuncu, yeşil, pembe ve diğer renkler karışık gönderilir. Mesaj yoluyla renk talebinizi iletebilirsiniz; stok uygunsa yardımcı olunur.', 'polaris'),
            ],
        ],
    ],
    [
        'title'  => __('Yardım ve iletişim', 'polaris'),
        'kicker' => __('Sorunuzun cevabı burada yoksa hızlı destek alın', 'polaris'),
        'icon'   => 'fa-solid fa-headset',
        'items'  => [
            [
                'question' => __('Aradığım sorunun cevabını bulamazsam ne yapabilirim?', 'polaris'),
                'answer'   => __('Aradığınız bilgi bu sayfada yer almıyorsa bizimle iletişime geçebilirsiniz. Size yardımcı olmaktan memnuniyet duyarız.', 'polaris'),
            ],
        ],
    ],
];

get_header();
?>

<section class="polaris-content polaris-faq-page">
  <div class="container">
    <header class="polaris-faq-hero polaris-surface fade-up active">
      <div class="polaris-faq-hero__copy">
        <p class="polaris-faq-kicker"><?php echo esc_html__('Yardım merkezi', 'polaris'); ?></p>
        <h1><?php echo esc_html__('Sıkça Sorulan Sorular', 'polaris'); ?></h1>
        <p class="polaris-faq-subtitle"><?php echo esc_html__('Sipariş, ödeme, kargo, iade ve ürün detaylarıyla ilgili en çok sorulan konuları tek ekranda toparladık.', 'polaris'); ?></p>
        <div class="polaris-faq-tags" aria-hidden="true">
          <span><?php echo esc_html__('Sipariş', 'polaris'); ?></span>
          <span><?php echo esc_html__('Kargo', 'polaris'); ?></span>
          <span><?php echo esc_html__('İade', 'polaris'); ?></span>
          <span><?php echo esc_html__('Ürün Bilgisi', 'polaris'); ?></span>
        </div>
      </div>
      <a class="btn btn-primary polaris-faq-hero__cta" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        <?php echo esc_html__('Bilgi almak istiyorum', 'polaris'); ?>
      </a>
    </header>

    <div class="polaris-faq-layout">
      <div class="polaris-faq-main">
        <?php foreach ($faq_sections as $section) : ?>
          <article class="polaris-faq-block polaris-surface fade-up active">
            <header class="polaris-faq-block__head">
              <span class="polaris-faq-block__icon" aria-hidden="true"><i class="<?php echo esc_attr($section['icon']); ?>"></i></span>
              <div>
                <h2><?php echo esc_html($section['title']); ?></h2>
                <p><?php echo esc_html($section['kicker']); ?></p>
              </div>
            </header>

            <div class="polaris-faq-items">
              <?php foreach ($section['items'] as $item_index => $item) : ?>
                <details class="polaris-faq-item"<?php echo 0 === $item_index ? ' open' : ''; ?>>
                  <summary>
                    <span class="polaris-faq-item__q"><?php echo esc_html($item['question']); ?></span>
                    <span class="polaris-faq-item__toggle" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
                  </summary>
                  <div class="polaris-faq-item__a">
                    <p><?php echo esc_html($item['answer']); ?></p>
                  </div>
                </details>
              <?php endforeach; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <aside class="polaris-faq-aside">
        <article class="polaris-faq-contact polaris-surface fade-up active">
          <h3><?php echo esc_html__('Hızlı destek', 'polaris'); ?></h3>
          <p><?php echo esc_html__('Destek ekibimize WhatsApp üzerinden hızlı şekilde ulaşabilirsiniz.', 'polaris'); ?></p>
          <a class="btn btn-primary" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
            <?php echo esc_html__('WhatsApp Destek Hattı', 'polaris'); ?>
          </a>
          <span class="polaris-faq-contact__number"><?php echo esc_html($whatsapp_display); ?></span>
        </article>

        <article class="polaris-faq-note polaris-surface fade-up active">
          <h3><?php echo esc_html__('Kısa notlar', 'polaris'); ?></h3>
          <ul>
            <li><?php echo esc_html__('1.000 TL ve üzeri siparişlerde kargo ücretsizdir.', 'polaris'); ?></li>
            <li><?php echo esc_html__('Sipariş yoğunluğuna göre kargoya veriliş süresi 3-4 iş gününe uzayabilir.', 'polaris'); ?></li>
            <li><?php echo esc_html__('Renk talepleri mesaj ile iletilebilir; stok uygunsa destek sağlanır.', 'polaris'); ?></li>
          </ul>
        </article>
      </aside>
    </div>
  </div>
</section>

<?php
get_footer();
