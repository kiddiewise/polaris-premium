<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="container polaris-content">
  <h1>
    <?php
    printf(
      esc_html__('Search results for: %s', 'polaris'),
      '<span>' . esc_html(get_search_query()) . '</span>'
    );
    ?>
  </h1>

  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article <?php post_class('polaris-post-card'); ?>>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div><?php the_excerpt(); ?></div>
      </article>
    <?php endwhile; ?>

    <?php the_posts_pagination(); ?>
  <?php else : ?>
    <p><?php echo esc_html__('No results found.', 'polaris'); ?></p>
  <?php endif; ?>
</section>
<?php
get_footer();
