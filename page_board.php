<?php
/**
 * Template Name: SCOT Board Page
 *
 * Child theme adaptation designed for board page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header('page'); ?>

<div class="wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
            <div class="board-bio-container">
                <?php
                while ( have_posts() ) :
                    the_post();

                    get_template_part( 'template-parts/page/content', 'page-board' );				

                endwhile; // End of the loop.
                ?>
            </div>                      
		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php
get_footer();
