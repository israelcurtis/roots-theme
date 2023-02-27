<?php

// inject additional class
add_filter( 'body_class', function( $classes ) {
	$classes[] = 'roots-query-results';
	return $classes;
	}
);

add_action( 'genesis_meta', 'roots_kill_hero_title', 20 );

add_action( 'genesis_loop', 'roots_search_content', 1);
function roots_search_content() {

	echo '<h1 class="entry-title">Search Results: <em>'.get_search_query().'</em></h1>';
	global $wp_query;
	if ( empty( $wp_query->found_posts ) ) echo "<h4><em>No results found for these search terms</em></h4>";
	roots_query_output( $wp_query );
}


// eliminate default loop content
remove_action( 'genesis_loop', 'genesis_do_loop' );
// init output
genesis();