</main>

<footer class="footer">
  <div class="container">
    <div class="footer-columns">
      <div>
        <h4><?php echo esc_html(get_bloginfo('name')); ?></h4>
        <p><?php echo esc_html__('Premium surf kurşun ürünleri ve aksesuarları.', 'polaris'); ?></p>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>
    </div>
  </div>
</footer>

<?php get_template_part('template-parts/navigation/bottom-nav'); ?>

<?php wp_footer(); ?>
</body>
</html>
