<?php get_header();?><main id="main"><?php while(have_posts()):the_post();get_template_part('template-parts/page');endwhile;?></main><?php get_footer();?>
