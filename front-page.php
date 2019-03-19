<?php
/**
 *  Modified for SCOT child theme
 * 
 *  The front page template file
 *
 * If the user has selected a static page for their homepage, this is what will
 * appear.
 * Learn more: https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 * 
 * MODIFIED VERSION FOR CHILD THEME
 */

get_header('front'); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">      

        <section class="home-intro">
        <?php
                while ( have_posts() ) :
                    the_post();

                    get_template_part( 'template-parts/page/content', 'page-front' );				

                endwhile; // End of the loop.
                ?>
        </section>

        <section class="home-recent-posts">
            <h2>Happenings</h2>

        <?php // Show the selected frontpage content.
        
        //my "loop"

        $recent_post_args = array('numberposts'=> 3);
        $recent_posts = get_posts($recent_post_args);

        $latest_post = $recent_posts[0];
        $latest_post_ID = $latest_post->ID;
        $latest_post_title = $latest_post->post_title;
        $latest_post_link = get_permalink($latest_post_ID);

        echo '<article id="post-'.$latest_post_ID.'"';
        post_class('recent-post-1', $latest_post_ID);
        echo '><h3><a href="'.$latest_post_link.'">'.$latest_post_title.'</a></h3><figure>';
        echo get_the_post_thumbnail($latest_post_ID, 'full');
        ?>
        </figure>
        <p>

        <?php if(empty($latest_post->post_excerpt)){
            echo wp_trim_words($latest_post->post_content, 20).'<a class="scot-read-more-link" href="'.$latest_post_link.'">Read more</a>';
        } else {
            echo $latest_post->post_excerpt;
        }
        ?>
        </p>
        </article>

        <div class="older-articles">
        
        <?php
        $long_title = false;

        for($i = 1; $i<sizeof($recent_posts); $i++){
            if(!$long_title){
                if(str_word_count($recent_posts[$i]->post_title)>5){
                    $long_title = true;
                }
            }            
        }        

        for($i = 1; $i<sizeof($recent_posts); $i++) {
            $rpost_title = $recent_posts[$i]->post_title;
            $rpost_link = get_permalink($recent_posts[$i]->ID);
            echo '<article id="post-'.$recent_posts[$i]->ID.'"';
            post_class('recent-post-'.($i+1), $recent_posts[$i]->ID);
            if(!$long_title){
                echo '><h3><a href="'.$rpost_link.'">'.$rpost_title.'</a></h3><figure>';
            } else {
                echo '><h3 class="recent-post-long-title"><a href="'.$rpost_link.'">'.$rpost_title.'</a></h3><figure>';
            }            
            echo get_the_post_thumbnail($recent_posts[$i]->ID,'medium');
            echo '</figure></article>';  
        }

        ?>
        </div>
        </section>

        <section class="home-action">
            <h2>Get Involved</h2>
            <div class="first-action"><a href="about-scot/become-a-member/" role="button">Become a Member</a></div>
            <div><a href="education/scholarships/" role="button">Apply for Scholarships</a></div>
            <div><a href="education/cary-indoor-competition/" role="button">Experience Cary Indoor Competition</a></div>
            <div><a href="events/" role="button">Attend an Event</a></div>
        </section>

        <?php
        // Get each of our panels and show the post data.
        if ( 0 !== twentyseventeen_panel_count() || is_customize_preview() ) : // If we have pages to show.

            /**
             * Filter number of front page sections in Twenty Seventeen.
             *
             * @since Twenty Seventeen 1.0
             *
             * @param int $num_sections Number of front page sections.
             */
            $num_sections = apply_filters( 'twentyseventeen_front_page_sections', 4 );
            global $twentyseventeencounter;

            // Create a setting and control for each of the sections available in the theme.
            for ( $i = 1; $i < ( 1 + $num_sections ); $i++ ) {
                $twentyseventeencounter = $i;
                twentyseventeen_front_page_section( null, $i );
            }

    endif; // The if ( 0 !== twentyseventeen_panel_count() ) ends here. ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer();
