<?php

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );

function enqueue_parent_styles() {
   wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
   wp_enqueue_style('child-style', get_stylesheet_directory_uri().'/style.css', array('parent-style'), wp_get_theme()->get('Version'));
}

add_action('wp_enqueue_scripts','enqueue_ms_fix');

function enqueue_ms_fix(){
	wp_enqueue_script('ms_fix',get_stylesheet_directory_uri().'/assets/js/msFix.js',array(),'1.0',true);
}

function wp_nav_menu_searchBar( $args = array() ) {
	static $menu_id_slugs = array();

	$defaults = array( 'menu' => '', 'container' => 'div', 'container_class' => '', 'container_id' => '', 'menu_class' => 'menu', 'menu_id' => '',
	'echo' => true, 'fallback_cb' => 'wp_page_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>', 'item_spacing' => 'preserve',
	'depth' => 0, 'walker' => '', 'theme_location' => '' );

	$args = wp_parse_args( $args, $defaults );

	if ( ! in_array( $args['item_spacing'], array( 'preserve', 'discard' ), true ) ) {
		// invalid value, fall back to default.
		$args['item_spacing'] = $defaults['item_spacing'];
	}

	/**
	 * Filters the arguments used to display a navigation menu.
	 *
	 * @since 3.0.0
	 *
	 * @see wp_nav_menu()
	 *
	 * @param array $args Array of wp_nav_menu() arguments.
	 */
	$args = apply_filters( 'wp_nav_menu_args', $args );
	$args = (object) $args;

	/**
	 * Filters whether to short-circuit the wp_nav_menu() output.
	 *
	 * Returning a non-null value to the filter will short-circuit
	 * wp_nav_menu(), echoing that value if $args->echo is true,
	 * returning that value otherwise.
	 *
	 * @since 3.9.0
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string|null $output Nav menu output to short-circuit with. Default null.
	 * @param stdClass    $args   An object containing wp_nav_menu() arguments.
	 */
	$nav_menu = apply_filters( 'pre_wp_nav_menu', null, $args );

	if ( null !== $nav_menu ) {
		if ( $args->echo ) {
			echo $nav_menu;
			return;
		}

		return $nav_menu;
	}

	// Get the nav menu based on the requested menu
	$menu = wp_get_nav_menu_object( $args->menu );

	// Get the nav menu based on the theme_location
	if ( ! $menu && $args->theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->theme_location ] ) )
		$menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );

	// get the first menu that has items if we still can't find a menu
	if ( ! $menu && !$args->theme_location ) {
		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu_maybe ) {
			if ( $menu_items = wp_get_nav_menu_items( $menu_maybe->term_id, array( 'update_post_term_cache' => false ) ) ) {
				$menu = $menu_maybe;
				break;
			}
		}
	}

	if ( empty( $args->menu ) ) {
		$args->menu = $menu;
	}

	// If the menu exists, get its items.
	if ( $menu && ! is_wp_error($menu) && !isset($menu_items) )
		$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

	/*
	 * If no menu was found:
	 *  - Fall back (if one was specified), or bail.
	 *
	 * If no menu items were found:
	 *  - Fall back, but only if no theme location was specified.
	 *  - Otherwise, bail.
	 */
	if ( ( !$menu || is_wp_error($menu) || ( isset($menu_items) && empty($menu_items) && !$args->theme_location ) )
		&& isset( $args->fallback_cb ) && $args->fallback_cb && is_callable( $args->fallback_cb ) )
			return call_user_func( $args->fallback_cb, (array) $args );

	if ( ! $menu || is_wp_error( $menu ) )
		return false;

	$nav_menu = $items = '';

	$show_container = false;
	if ( $args->container ) {
		/**
		 * Filters the list of HTML tags that are valid for use as menu containers.
		 *
		 * @since 3.0.0
		 *
		 * @param array $tags The acceptable HTML tags for use as menu containers.
		 *                    Default is array containing 'div' and 'nav'.
		 */
		$allowed_tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
		if ( is_string( $args->container ) && in_array( $args->container, $allowed_tags ) ) {
			$show_container = true;
			$class = $args->container_class ? ' class="' . esc_attr( $args->container_class ) . '"' : ' class="menu-'. $menu->slug .'-container"';
			$id = $args->container_id ? ' id="' . esc_attr( $args->container_id ) . '"' : '';
			$nav_menu .= '<'. $args->container . $id . $class . '>';
		}
	}

	// Set up the $menu_item variables
	_wp_menu_item_classes_by_context( $menu_items );

	$sorted_menu_items = $menu_items_with_children = array();
	foreach ( (array) $menu_items as $menu_item ) {
		$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
		if ( $menu_item->menu_item_parent )
			$menu_items_with_children[ $menu_item->menu_item_parent ] = true;
	}

	// Add the menu-item-has-children class where applicable
	if ( $menu_items_with_children ) {
		foreach ( $sorted_menu_items as &$menu_item ) {
			if ( isset( $menu_items_with_children[ $menu_item->ID ] ) )
				$menu_item->classes[] = 'menu-item-has-children';
		}
	}

	unset( $menu_items, $menu_item );

	/**
	 * Filters the sorted list of menu item objects before generating the menu's HTML.
	 *
	 * @since 3.1.0
	 *
	 * @param array    $sorted_menu_items The menu items, sorted by each menu item's menu order.
	 * @param stdClass $args              An object containing wp_nav_menu() arguments.
	 */
	$sorted_menu_items = apply_filters( 'wp_nav_menu_objects', $sorted_menu_items, $args );

	$items .= walk_nav_menu_tree( $sorted_menu_items, $args->depth, $args );
    unset($sorted_menu_items);
    
    //TRC: Form Item
    $formItem_unique_id = esc_attr( uniqid( 'search-form-' ) );
    $formItem = '<li class="nav-search menu-item"><form role="search" method="get" class="search-form" action="'.esc_url( home_url( '/' ) ).'"><label for="'.$formItem_unique_id.'">
    <span class="screen-reader-text">'._x( 'Search for:', 'label', 'twentyseventeen' ).'</span>
	</label><input type="search" id="'.$formItem_unique_id.'" class="search-field" placeholder="'.esc_attr_x( 'Search &hellip;', 'placeholder', 'twentyseventeen' ).'" value="'.get_search_query().'" name="s" /><button type="submit" class="search-submit">'.twentyseventeen_get_svg( array( 'icon' => 'search' ) ).'<span class="screen-reader-text">'._x( 'Search', 'submit button', 'twentyseventeen' ).'</span></button>
    </form></li>';

    $items .= $formItem;

	// Attributes
	if ( ! empty( $args->menu_id ) ) {
		$wrap_id = $args->menu_id;
	} else {
		$wrap_id = 'menu-' . $menu->slug;
		while ( in_array( $wrap_id, $menu_id_slugs ) ) {
			if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) )
				$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
			else
				$wrap_id = $wrap_id . '-1';
		}
	}
	$menu_id_slugs[] = $wrap_id;

	$wrap_class = $args->menu_class ? $args->menu_class : '';

	/**
	 * Filters the HTML list content for navigation menus.
	 *
	 * @since 3.0.0
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string   $items The HTML list content for the menu items.
	 * @param stdClass $args  An object containing wp_nav_menu() arguments.
	 */

	$items = apply_filters( 'wp_nav_menu_items', $items, $args );

	/**
	 * Filters the HTML list content for a specific navigation menu.
	 *
	 * @since 3.0.0
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string   $items The HTML list content for the menu items.
	 * @param stdClass $args  An object containing wp_nav_menu() arguments.
	 */
	$items = apply_filters( "wp_nav_menu_{$menu->slug}_items", $items, $args );

	// Don't print any markup if there are no items at this point.
	if ( empty( $items ) )
		return false;

	$nav_menu .= sprintf( $args->items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $items );
	unset( $items );

	if ( $show_container )
		$nav_menu .= '</' . $args->container . '>';

	/**
	 * Filters the HTML content for navigation menus.
	 *
	 * @since 3.0.0
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string   $nav_menu The HTML content for the navigation menu.
	 * @param stdClass $args     An object containing wp_nav_menu() arguments.
	 */
	$nav_menu = apply_filters( 'wp_nav_menu', $nav_menu, $args );

	if ( $args->echo )
		echo $nav_menu;
	else
		return $nav_menu;
}

function scot_header_args($args) {
	$args['height'] = 400;
	$args['video'] = false;
	return $args;
}

function scot_breadcrumb_menu($id, $type='') {
	
	if (get_option('show_on_front')==='page') {		
		$homeID = get_option('page_on_front');
	} else {
		$homeID = get_option('page_for_posts');
	}

	$blogID = get_option('page_for_posts');

	echo('<nav class="scot-breadcrumb-menu">');
	echo('<ul>');
	echo('<li><a href="'.get_permalink($homeID).'">Home</a></li>');

	if($type==='archive'){
		$secondLevelID = wp_get_post_parent_id($blogID);
		if ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		}
		echo('<li><a href="'.get_permalink($secondLevelID).'">'.get_the_title($secondLevelID).'</a></li>');
		echo('<li><a href="'.get_permalink($blogID).'">'.get_the_title($blogID).'</a></li>');
		echo('<li class="current">'.$title.'</li>');
	}else{
		if($type===''){
			$pageAncestors = get_ancestors($id, 'page');
			$pageAncestors = array_reverse($pageAncestors);
			foreach($pageAncestors as $ancestor) {
				echo('<li><a href="'.get_permalink($ancestor).'">'.get_the_title($ancestor).'</a></li>');
			}
		} elseif($type==='single'){
			$secondLevelID = wp_get_post_parent_id($blogID);
			$categories = get_the_category();
			$firstCategory = $categories[0];
			echo('<li><a href="'.get_permalink($secondLevelID).'">'.get_the_title($secondLevelID).'</a></li>');
			echo('<li><a href="'.get_permalink($blogID).'">'.get_the_title($blogID).'</a></li>');
			echo('<li><a href="'.get_category_link($firstCategory->cat_ID).'">'.$firstCategory->name.'</a></li>');
		}
		echo('<li class="current">'.get_the_title($id).'</li>');
	}	
	echo('</ul>');
	echo('</nav>');
}

function scot_excerpt_length($length){
	return 30;
}

add_filter( 'body_class', function($classes) {
	foreach($classes as $key=>$class) {
		if($class=='home'){
			unset($classes[$key]);
		}
	}
	return $classes;
}, 1000);

add_filter( 'body_class', function($classes) {
	foreach($classes as $key=>$class) {
		if($class=='twentyseventeen-front-page'){
			unset($classes[$key]);
		}
	}
	return $classes;
}, 1000);


add_filter('excerpt_length', 'scot_excerpt_length');
add_filter('twentyseventeen_custom_header_args', 'scot_header_args');
add_action('after_setup_theme', 'scot_custom_image_sizes');

function scot_custom_image_sizes(){
	add_image_size('scot350', 350);
	add_image_size('scot400', 400);
	add_image_size('scot450', 450);
	add_image_size('scot500', 500);
	add_image_size('scot600', 600);
}

function scot_custom_logo_size(){
	add_theme_support('custom-logo', array(
		'height' => 84,
		'width' => 300, 
		'flex-width' => true,
	));
}

add_action('after_setup_theme', 'scot_custom_logo_size',100);

if(!is_registered_sidebar(4)){
	function scot_extra_footer_sidebar(){
		$sidebar_args = array(
			'name' => __( 'Footer 3', 'twentyseventeen' ),
			'id' => 'sidebar-4',
			'class' => '',
			'description' => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',		
		);
		register_sidebar($sidebar_args);
	}
	add_action( 'widgets_init', 'scot_extra_footer_sidebar' );
}

function landingPageNavChildren($page_name){
	$topNavMenu = wp_nav_menu(array(
		'menu' => 'top-menu',
		'depth' => 2,
		'echo' => false,
	));
	$start = strpos($topNavMenu,'<li',strpos($topNavMenu,'<ul',strpos($topNavMenu,strtolower($page_name))));
	$end = strpos($topNavMenu,'</ul>',$start);	
	$subMenu = html_entity_decode(substr($topNavMenu,$start,$end-$start));
	$subMenu = str_replace('</a></li>','</a></li>$',$subMenu);
	$subMenu = explode('$',$subMenu);

	$newMenu = array();

	foreach($subMenu as $item){
		$trimStart = strpos($item,'">',strpos($item,'"><a')+1)+2;
		$trimEnd = strpos($item,'</a>');
		$trimmedItem = substr($item,$trimStart,$trimEnd-$trimStart);
		if(strlen($trimmedItem)>=1){			
			array_push($newMenu,substr($item,$trimStart,$trimEnd-$trimStart));
		}			
	}
	return $newMenu;
}

function landingPageChildSort($menuArray,$childrenArray){
	$newArray = array();
	$extraKids = array();
	
	foreach($menuArray as $key=>$value){
		$origKey = array_search($value,$childrenArray);
		$newArray[$origKey] = $value;
	}

	asort($childrenArray);

	foreach($childrenArray as $key=>$value) {
		if(!array_key_exists($key,$newArray)){
			$newArray[$key] = $value;
		}
	}	
 return $newArray;
}