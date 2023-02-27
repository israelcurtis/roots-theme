<?php
/* MEDIA IMAGE SINGLE TEMPLATE */

// get rid of hero title on certain pages, STILL NEED TO PUT ELSEWHERE?????
add_action( 'genesis_meta', 'roots_kill_hero_title', 20 );

add_action( 'genesis_loop', 'roots_videopage_loop' );
function roots_videopage_loop() {

	global $post;
	echo '<h1>'.$post->post_title.'</h1>';
	echo '<div class="embed-container">';
	the_field('video_url');
	echo '</div>';

	$caption = get_field('description');
	if (empty($caption)) {
		echo "<h2>No Caption</h2>";
	} else {
		echo "<h2>$caption</h2>";
	}
	// metadata output
	roots_metadata_output( $post->ID );
}


// eliminate default content rendering (so nothing from the page editor will appear)
remove_action( 'genesis_loop', 'genesis_do_loop' );
genesis();

?>