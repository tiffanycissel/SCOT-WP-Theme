<?php
/**
 * Template Name: SCOT Landing Page
 *
 * Child theme adaptation designed for landing pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

<div class="wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/page/content', 'page-landing' );				

            endwhile; // End of the loop.
            ?>
            <section class="landing-page-children">
            <?php $page_id = $wp_query->get_queried_object_id();
            $page_children = get_children($page_id);
            $page_name = get_page($page_id)->post_name;
            $child_pages = array();

            foreach($page_children as $child) {
                if($child->post_status == 'publish') {
                    $child_pages[$child->ID] = $child->post_title;
                }
            }
            
            foreach(landingPageChildSort(landingPageNavChildren($page_name),$child_pages) as $key => $value){
                    $link = get_permalink($key);
                    echo '<article><figure><a href="'.$link.'">';
                    echo get_the_post_thumbnail($key, 'thumbnail');
                    echo '</a><figcaption><p><a href="'.$link.'">'.$value.'</a></p></figcaption></figure></article>';
                }                
             ?>
            </section>
            <?php
            $headline_args = array(
                'category_name' => $page_name,
                'numberposts' => 5
            );
            $headlines = get_posts($headline_args);

            if(sizeof($headlines)>0){
                echo '<section class="landing-headlines"><h3>Recent '.ucfirst($page_name).' News</h3><ul>';
                foreach ($headlines as $headline) {
                    echo '<li><a href="'.get_permalink($headline->ID).'">'.$headline->post_title.'</a></li>';              
                }
                echo '</ul>';
                echo '<a href="'.get_category_link(get_category_by_slug($page_name)).'" class="all-cat-news">See all '.ucfirst($page_name).' News</a>';
                echo '</section>';
            }

            ?>
		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php
get_footer();
