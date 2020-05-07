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

                $front_page_query_args = array(
                    'posts_per_page' => 3,
                );
                $front_page_posts = new WP_Query( $front_page_query_args );
                $front_page_post_ids = array();
                
                if( $front_page_posts->have_posts() ):
                    while( $front_page_posts->have_posts() ):
                        $front_page_posts->the_post();
                        array_push( $front_page_post_ids, get_the_ID() );
                    endwhile;
                endif;
                wp_reset_postdata();

                $latest_post = get_post( $front_page_post_ids[0] );
                $latest_post_ID = $latest_post->ID;
                $latest_post_title = $latest_post->post_title;
                $latest_post_link = get_permalink($latest_post_ID);
            ?>

            <article id="post-<?php echo $latest_post_ID; ?>"
            <?php echo post_class('recent-post-1', $latest_post_ID); ?>>
                <h3>
                    <a href="<?php echo $latest_post_link; ?>"><?php echo $latest_post_title; ?></a>
                </h3>
                <figure>
                <?php echo get_the_post_thumbnail($latest_post_ID, 'full'); ?>      
                </figure>
                <p>
                <?php if(empty($latest_post->post_excerpt)):
                    echo wp_trim_words($latest_post->post_content, 20).'<a class="scot-read-more-link" href="'.$latest_post_link.'">Read more</a>';
                else:
                    echo $latest_post->post_excerpt;
                endif; ?>
                </p>
            </article>

            <div class="older-articles">
            
            <?php
            for( $i = 1; $i < sizeof( $front_page_post_ids ); $i++ ):
                $current_post = get_post( $front_page_post_ids[$i] );
                $rpost_title = $current_post->post_title;
                $rpost_link = get_permalink($current_post->ID);
                $h3_class = str_word_count($rpost_title) > 5 ? ' class="recent-post-long-title"' : '';
                ?>
                <article id="post-<?php echo $current_post->ID;?>" <?php echo post_class('recent-post-'.($i+1), $current_post->ID); ?>>
                    <h3<?php echo $h3_class; ?>>
                        <a href="<?php echo $rpost_link; ?>"><?php echo $rpost_title; ?></a>
                    </h3>
                    <figure>          
                        <?php echo get_the_post_thumbnail($current_post->ID,'medium'); ?>
                    </figure>
                </article>
            <?php endfor; ?>
            </div>
        </section>

        <section class="home-action">
            <h2>Get Involved</h2>
            <div class="first-action"><a href="about-scot/become-a-member/" role="button">Become a Member</a></div>
            <div><a href="education/scholarships/" role="button">Apply for Scholarships</a></div>
            <div><a href="education/cary-indoor-competition/" role="button">Experience Cary Indoor Competition</a></div>
            <div><a href="events/" role="button">Attend an Event</a></div>
        </section>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer();
