<?php
/* TAXONOMY ARCHIVE TEMPLATE */

// inject additional class
add_filter( 'body_class', function( $classes ) {
	$classes[] = 'roots-query-results';
	return $classes;
	}
);

add_action( 'genesis_meta', 'roots_kill_hero_title', 20 );

/* TAX TERM ARCHIVE CONTENT LOOP */
// note: content modified by roots_expand_query_results()

add_action( 'genesis_loop', 'roots_term_listing', 1);
function roots_term_listing() {
	$term = get_queried_object();
	$tax = get_queried_object()->taxonomy;
	$taxobj = get_taxonomy( $tax );
	echo '<h5><a href="'.get_site_url().'/'.$tax.'">'.$taxobj->labels->singular_name.'</a><h5>';
	echo '<h1>'.get_queried_object()->name.'</h1>';
	if ( !empty( $term->description ) ) echo '<p class="term-desc">'.$term->description.'</p>';

	global $wp_query;
	roots_query_output($wp_query);
}




// eliminate default loop content
remove_action( 'genesis_loop', 'genesis_do_loop' );
// init output
genesis();