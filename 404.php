<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="container polaris-content">
  <h1><?php echo esc_html__('Sayfa bulunamadı', 'polaris'); ?></h1>
  <p><?php echo esc_html__('Aradığınız sayfa mevcut değil veya taşınmış olabilir.', 'polaris'); ?></p>
  <a class="btn btn-primary" href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Ana sayfaya dön', 'polaris'); ?></a>
</section>
<?php
get_footer();
