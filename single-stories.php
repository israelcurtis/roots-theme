<?php
/* STORY POST SINGLE TEMPLATE */

// add the metadata output after post content gets rendered
add_action('genesis_after_loop', 'roots_story_tags' );
function roots_story_tags() {
	global $post;
	roots_metadata_output( $post->ID );
}


// eliminate default content rendering (so nothing from the page editor will appear)
// remove_action( 'genesis_loop', 'genesis_do_loop' );
genesis();

?>