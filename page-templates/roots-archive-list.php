<?php
/**
 * Displays custom asset archive lists (simulates top-level tax archive)
 *
 * Template Name: Archive Listing
 * Template Post Type: page
 *
 * @package Roots
 * @author  Israel Curtis
 * @license GPL-2.0-or-later
 */

// inject additional class
// add_filter( 'body_class', function( $classes ) {
// 	$classes[] = 'roots-archive-list';
// 	return $classes;
// 	}
// );


// replacement loop, render functions depending on which kind of content (cpt,tax)
add_action( 'genesis_loop', 'roots_archive_listing', 1 );
function roots_archive_listing() {
	/*
	/* USING PAGE SLUG TO CONTROL POSTTYPE/TAX SELECTION! SO PAGE SLUG AND POSTTYPE/TAX SLUG MUST MATCH!
	/* since it's nearly impossible to create a template and endpoint that gets called to list taxonomy terms,
	/* it's waaaaay easier to just create a page whose slug owns the endpoint, and populate it with this custom page template
	/* same goes for making an archive list of attachment types. And to make this process consistent, we also disabled archive
	/* functionality for custom post types so they can also be handled here
	*/
	global $post;
	$page_slug = $post->post_name;
	echo '<div class="roots-archive-list '.$page_slug.'">';
	switch ($page_slug) {
		case 'images':
			roots_image_archive();
			break;
		case 'videos':
			roots_video_archive();
			break;
		case 'bulletins':
			roots_bulletin_archive();
			break;
		default:
			roots_tax_archive( $page_slug );
			break;
	}
	echo "</div>";
}



/*
// TAXONOMY LISTING
*/
// Grab all the terms for the taxonomy represented by this page's slug
function roots_tax_archive($page_slug = null) {
	$args = array(
		'taxonomy'        => $page_slug,
		'orderby'         => 'name',
		'order'           => 'ASC',
		'hide_empty'      => true, // without this won't include media attachments! (attachments don't count towards 'empty') DOESNT SEEM TO BE TRUE ANYMORE!
	);
	$archive = new WP_Term_Query($args);
	if ( empty( $archive->get_terms() ) ) {
		echo "<h3>Sorry, no ".$page_slug." found!</h3>";
		return;
	}
	// Loop through terms, output list of tagged assets for each
	foreach ( $archive->get_terms() as $term ) {
		echo '<h2 class="term-title"><a href="'.get_term_link($term).'">' . $term->name . '</a></h2>';
	}
	echo "</div>";
}


function roots_bulletin_archive() {
	$args = array(
		'posts_per_page'=> -1,
		'post_type' => 'bulletins',
		'post_status' => 'publish',
		// sorting with values saved to meta via the years taxonomy
		'meta_key'       => 'chronology',
		'meta_type'      => 'NUMERIC',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
	);
	$archive = new WP_Query( $args );
	if ( $archive->have_posts() ) {
		echo '<ul class="roots-archive-list">';
		while ( $archive->have_posts() ) {
			$archive->the_post();
			echo '<h2><a href="'.get_the_permalink().'">'.get_the_title().'</a></h2>';
		}
		echo '</ul>';
	} else {
		echo "<h3>Sorry, no bulletins found!</h3>";
		return;
	}
}



// image archive render
function roots_image_archive() {
	$args = array(
	// get everything
	'posts_per_page'=> -1,
	'post_type' => 'attachment',
	'post_mime_type' => 'image/jpeg',
	'post_status' => 'any',
	// sorting with values saved to meta via the years taxonomy
	'meta_key'       => 'chronology',
	'meta_type'      => 'NUMERIC',
	'orderby'        => 'meta_value',
	'order'          => 'ASC',
	// filter out unlisted pics, either because false or has no meta yet
	'meta_query' => array(
		'relation' => 'OR',
		array(
			'key' => 'unlisted',
			'compare' => 'NOT EXISTS'
			),
		array(
			'key' => 'unlisted',
			'value' => '0',
			'type' => 'numeric'
			)
		)
	// do we need to also exclude bulletin scans? they're marked unlisted by default but could have a fallback filter
	);
	$allthepics = new WP_Query( $args );
	if ( $allthepics->have_posts() ) {
		roots_related_posts_output( $allthepics->posts, "", true );
	}
}

// video archive render
function roots_video_archive() {
	$args = array(
		'posts_per_page'=> -1,
		'post_type' => 'videos',
		'post_status' => 'publish',
		// sorting with values saved to meta via the years taxonomy
		'meta_key'       => 'chronology',
		'meta_type'      => 'NUMERIC',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
	);
	$archive = new WP_Query( $args );
	if ( $archive->have_posts() ) {
		echo '<ul class="roots-video-grid">';
		while ( $archive->have_posts() ) {
			$archive->the_post();
			echo '<li><a class="videolink" href="'.get_the_permalink().'">'.get_the_title().'</a><img src="'.get_field('video_thumb_url').'" title="'.get_the_title().'"></li>';
		}
		echo '</ul>';
	}
}



// eliminate default content rendering (so nothing from the page editor will appear)
remove_action( 'genesis_loop', 'genesis_do_loop' );
// Runs the Genesis loop.
genesis();
