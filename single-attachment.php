<?php
/* MEDIA ATTACHMENT IMAGE SINGLE TEMPLATE */

add_action( 'genesis_meta', 'roots_kill_hero_title', 20 );

add_action( 'wp_enqueue_scripts', 'image_enqueue_scripts_styles' );
function image_enqueue_scripts_styles() {
	wp_enqueue_style(
		'venobox',
		get_stylesheet_directory_uri() . '/lib/venobox/venobox.min.css',
		[ genesis_get_theme_handle() ],
		genesis_get_theme_version(), 'screen'
	);
	wp_enqueue_script(
		'venobox',
		get_stylesheet_directory_uri() . '/lib/venobox/venobox.min.js',
		[ 'jquery' ],
		genesis_get_theme_version(),
		true
	);
	$venoboxinit = <<<END
	<script>jQuery(function($) {
		if ( $( window ).width() > 640 ) {
			$('.venobox').venobox({
				spinner : 'three-bounce',
			});
		}
		/* on document loading for mobile widths, remove the <a> tag itself, as there's no point, cant view any bigger */
		if ( $( window ).width() <= 640 ) {
			$('body.single-attachment #genesis-content img').unwrap();
		}
	});
	</script>
	END;
	wp_add_inline_script('venobox', $venoboxinit, 'after');
}



add_action( 'genesis_loop', 'roots_imagepage_loop' );
function roots_imagepage_loop() {
	global $post;

	$att_meta = wp_get_attachment_metadata($post->ID);
    $img['title'] = $post->post_title;
    $img['description'] = $post->post_content;
    $img['caption'] = $post->post_excerpt;
    echo '<a href="'.wp_get_attachment_url().'" class="venobox" title="'.wp_get_attachment_caption( $post->ID ).'">';
	echo wp_get_attachment_image( $post->ID, 'large', false, array( 'class' => 'centered' ) ); // the alt tag is fetched and output by wp_get_attachment_image (which is being filtered dynamically to inject the taxonomy terms) we aren't storing it in _wp_attachment_image_alt postmeta!
	echo '</a>';
	// caption
	if (empty($img['caption'])) {
		echo '<h2>No Caption</h2>';
	} else {
		echo '<h2>'.$img['caption'].'</h2>';
	}
	$credit = roots_credit_link( 'photographer', $post->ID );
	if ( !empty( $credit ) ) {
		echo '<p class="photographer">Photographer: '.$credit['link'].'</p>';
		roots_metadata_output( $post->ID, $credit['id'] );
	} else {
		echo '<p class="photographer">Photographer: <em>Unknown</em></p>';
		roots_metadata_output( $post->ID, 0 );
	}
}


// eliminate default content rendering (so nothing from the page editor will appear)
remove_action( 'genesis_loop', 'genesis_do_loop' );
genesis();

?>