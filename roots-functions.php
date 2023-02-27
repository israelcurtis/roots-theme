<?php
// custom Favicon
add_filter( 'genesis_pre_load_favicon', 'roots_favicon' );
function roots_favicon( $favicon_url ) {
	return esc_url( get_stylesheet_directory_uri()."/images/roots-favicon.png");
}

// remove RSS feed links in head
add_action( 'feed_links_show_posts_feed', '__return_false', - 1 );
add_action( 'feed_links_show_comments_feed', '__return_false', - 1 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );


function roots_disable_feed() {
 wp_die( __( 'No feed available, please visit the <a href="'. esc_url( home_url( '/' ) ) .'">homepage</a>!' ) );
}
add_action('do_feed', 'roots_disable_feed', 1);
add_action('do_feed_rdf', 'roots_disable_feed', 1);
add_action('do_feed_rss', 'roots_disable_feed', 1);
add_action('do_feed_rss2', 'roots_disable_feed', 1);
add_action('do_feed_atom', 'roots_disable_feed', 1);
add_action('do_feed_rss2_comments', 'roots_disable_feed', 1);
add_action('do_feed_atom_comments', 'roots_disable_feed', 1);


// need to hook things last to override original essence theme stuff
add_action('after_setup_theme', 'roots_essence_reset');
function roots_essence_reset() {
	update_option( 'image_default_link_type', 'attachment' ); // we want the editor to default to this when placing images
	update_option( 'image_default_size', 'medium' ); // post editor image insertion
	update_option( 'image_default_align', 'center' ); // post editor image insertion
	remove_filter( 'genesis_post_info', 'essence_modify_post_info', 10);
	remove_filter( 'body_class', 'essence_half_width_entry_class' ); // kills the half-width-entries body class that essence injects
}

// kill the extra intro text and description fields, the SEO and layout options on each custom taxonomy edit screen
// genesis/lib/admin/term-meta.php
remove_action( 'admin_init', 'genesis_add_taxonomy_archive_options' );
remove_action( 'admin_init', 'genesis_add_taxonomy_seo_options' );
remove_action( 'admin_init', 'genesis_add_taxonomy_layout_options' );


// some single pages don't want the big header title
function roots_kill_hero_title() {
	remove_action( 'genesis_after_header', 'essence_header_title_wrap', 90 );
	remove_action( 'essence_entry_header', 'genesis_entry_header_markup_open' );
	remove_action( 'essence_entry_header', 'genesis_do_post_title' );
	remove_action( 'essence_entry_header', 'genesis_entry_header_markup_close' );
	remove_action( 'genesis_after_header', 'essence_header_title_end_wrap', 98 );
}

// get all the relevant terms for this single item minus excluded term id if supplied
function roots_metadata_output( $post_id = 0, $exclude_term_id = 0 ) {
	if ( empty( $post_id ) ) return;
	$columns = roots_fetch_tags( $post_id, "array", array('people', 'years', 'locations', 'projects'), $exclude_term_id );

	echo '<div class="roots-meta-table">';
	$i = 0;
	/* TAG RELATIONSHIP COLS */
	foreach ( $columns as $taxname => $terms ) {
		if ( $i == 0 ) {
			echo "<ul class='one-fourth first'>";
			$i++;
		} else {
			echo "<ul class='one-fourth'>";
			$i++;
		}
		// column title
		echo '<h3>'.$taxname.'</h3>';
		if ( empty( $terms ) ) {
			echo "<li>—</li>";
		} else {
			foreach ( $terms as $term_id ) {
				echo '<li><a href="'.get_term_link($term_id).'">' . get_term($term_id)->name . '</a></li>';
			}
		}
		echo '</ul>';
	}
	echo '<div class="clearfix"></div>'; // cleanup columns
	/* POST RELATIONSHIP COLS */
	// relationships handled differently for images (FPUA class) vs all other post types (ACF fields)
	if (get_post_type( $post_id ) == "attachment") {
		// use FPUA class to fetch all the posts this image is used in (thumbnails and body content)
		$find_ids = Find_Posts_Using_Attachment::get_posts_by_attachment_id($post_id);
		$found = array_merge( $find_ids['thumbnail'], $find_ids['content'] );
		$found = array_unique( $found );
		if (empty($found)) return;
		echo '<ul>';
		echo "<h3>Related Stories</h3>";
		foreach ($found as $id) {
			echo '<li><a href="'.get_permalink( $id ).'">'.get_the_title( $id ).'</a></li>';
		}
		echo '</ul>';
		// IMAGES ARE THE ONLY TYPE THAT DOESNT HAVE ALL THE OTHER RELATIONSHIPS, ONLY STORY?
		return;
	} else {
		echo '<div class="roots-related-columns">';
		roots_related_posts_output( get_field("related_bulletins"), 'Bulletins');
		roots_related_posts_output( get_field("related_stories"), 'Stories');
		roots_related_posts_output( get_field("related_videos"), 'Videos');
		echo '</div>';
	}
	echo '</div>';
}


// generate our own byline
add_filter('genesis_post_info', 'roots_byline_output');
function roots_byline_output( $post_info ) {
	global $post;
	$post_info = ''; 	// reset genesis default string
	$credit = ''; 		// init credit
	if ($post->post_type == 'stories') $credit = roots_credit_link( 'writer', $post->ID );
	if ($post->post_type == 'attachment') $credit = roots_credit_link( 'photographer', $post->ID );
	if ($post->post_type == 'videos') $credit = null;
	if ($post->post_type == 'bulletins') $credit = null;
	if ( !empty( $credit ) ) $post_info = '<i class="byline">by</i> '.$credit['link'];
	if ( is_single() ) { // only shown on single story pages
		return $post_info;
	} else {
		// multi-post archive lists
		$post_info .= roots_story_tax_list($post->ID, 'people', $credit['id']);
		$post_info .= roots_story_tax_list($post->ID, 'projects');
		$post_info .= roots_story_tax_list($post->ID, 'locations');
		$post_info .= roots_story_tax_list($post->ID, 'years');
	}
	return $post_info;
}


// NOT USED ANYMORE, WAS FOR ARCHIVES OF STORIES
/**
 * Generates HTML spans of comma-separated taxonomy terms
 *
 * @param int $post_id
 * @param string $tax_slug
 * @param int $exclude_term_id - optional term to be excluded from the query results
 * (e.g. to prevent writer/photographer from simultaneously showing up under "people")
 * @return string HTML
 */
// returns text string because it's being included in the genesis byline, echoing would output out of order
function roots_story_tax_list ( $post_id = 0, $tax_slug = '', $exclude_term_id = 0 ) {
	// get all the terms for this post minus excluded term id if supplied
	$terms = wp_get_object_terms( $post_id, $tax_slug, array( 'exclude' => $exclude_term_id ) );
	if ( empty($terms) ) return;
	$tax = get_taxonomy( $tax_slug );
	$output = "<span class='entry-terms'>".$tax->label.": ";
	$numterms = count($terms);
	$i = 0;
	foreach ( $terms as $term ) {
		$output .= '<a href="'.get_term_link($term).'">' . $term->name . '</a>';
		if( ++$i != $numterms) {
			$output .= ', ';
		}
	}
	$output .= '</span>';
	return $output;
}

/**
 * Generates HTML <a> for writer/photographer byline link
 *
 * @param string $field - ACF field name
 * @param int $post_id
 * @return array( link, id ) - link for html output, id for passing term_id to roots_story_tax_list or roots_image_tax_column for exclusion
 */

function roots_credit_link ( $field = '', $post_id = 0 ) {
	$value = get_field( $field, $post_id );
	if ( empty( $value ) ) return null;
	$termobj = get_term_by( 'name', $value, 'people' );
	if ( empty( $termobj ) ) return null;
	$output = '<a href="'.get_term_link( $termobj ).'" class="credit">'.$value.'</a>';
	return array( 'link' => $output, 'id' => $termobj->term_id );
}


// divide the relevant query results grouped by type on the archive/search pages
function roots_query_output( $query = null ) {
	if ( empty( $query ) ) return;
	// search results don't have related fields to grab, otherwise fill buckets from relationships
	$projects = is_search() ? null : get_field( 'related_projects', get_queried_object());
	$locations = is_search() ? null : get_field( 'related_locations', get_queried_object());
	$people = is_search() ? null : get_field( 'related_people', get_queried_object());
	// init buckets for sorting
	$images = array();
	$stories = array();
	$videos = array();
	$bulletins = array();
	// sort posts into buckets
	$results = $query->posts;
	foreach ($results as $post) {
		if ($post->post_type == 'attachment') $images[] = $post;
		if ($post->post_type == 'stories') $stories[] = $post;
		if ($post->post_type == 'videos') $videos[] = $post;
		if ($post->post_type == 'bulletins') $bulletins[] = $post;
	}
	/* list container */
	echo '<div class="roots-query-list">';
	/* list taxonomies first */
	echo '<div class="roots-related-columns">';
	if ( is_search() ) {
		roots_search_taxes_output( get_search_query(), 'people' );
		roots_search_taxes_output( get_search_query(), 'locations' );
		roots_search_taxes_output( get_search_query(), 'projects' );
		roots_search_taxes_output( get_search_query(), 'years' );
	}
	if ( is_archive() && is_tax()) {
		roots_related_taxes_output( $projects, 'Projects');
		roots_related_taxes_output( $locations, 'Locations');
		roots_related_taxes_output( $people, 'People');
	}
	echo "</div>";
	/* list post types last */
	roots_related_posts_output( $images, 'Images', true );
	echo '<div class="roots-related-columns">';
	roots_related_posts_output( $bulletins, 'Bulletins');
	roots_related_posts_output( $stories, 'Stories');
	roots_related_posts_output( $videos, 'Videos');
	echo "</div>";
	echo "</div>";
}

// output lists for related taxonomy terms
function roots_related_taxes_output( $relatives = array(), $title = '' ) {
	if ( empty( $relatives ) ) return;
	if ( is_array( $relatives ) ) {
		echo '<ul>';
		echo "<h3>$title</h3>";
		foreach ( $relatives as $relative ) {
			if ( is_object( $relative ) ) {
				echo '<li><a href="'.get_term_link( $relative->term_id ).'">'.$relative->name.'</a></li>';
			}

		}
		echo '</ul>';
	}
}


// output lists for search results on taxonomy terms
function roots_search_taxes_output($search = null, $tax = null) {
	if ( empty( $search) ) return null;
	if ( empty( $tax ) ) return null;
	$args = array(
	    'taxonomy'      => array( $tax ),
	    'orderby'       => 'name',
	    'order'         => 'ASC',
	    'hide_empty'    => true,
	    'fields'        => 'all',
	    'name__like'    => $search
	);
	$terms = get_terms( $args );
	$taxobj = get_taxonomy( $tax );
	if ( !empty( $terms ) ) {
		echo "<ul>";
		echo '<h3>'.$taxobj->labels->name.'</h3>';
		foreach ($terms as $term) {
			echo "<li><a href='".get_term_link( $term )."'>".$term->name."</a></li>";
		}
		echo "</ul>";
	}
}

// output lists for related posts, also used for the image archives grid
function roots_related_posts_output( $relatives = array(), $title = '', $images = false ) {
	if ( empty( $relatives ) ) return;
	if ( !is_array( $relatives ) ) return;
	if ( $images ) {
		// image grid links
		echo '<ul class="roots-thumb-grid">';
		echo empty( $title ) ? null : "<h3>$title</h3>";
		foreach ( $relatives as $relative ) {
			echo '<li><a href="'.get_permalink( $relative->ID ).'" title="'.wp_get_attachment_caption( $relative->ID ).'">'.wp_get_attachment_image( $relative->ID, 'thumbnail' ).'</a></li>';
		}
		echo '</ul>';
	} else {
		// default text links
		echo '<ul>';
		echo "<h3>$title</h3>";
		foreach ( $relatives as $relative ) {
			if ( is_object( $relative ) ) {
				echo '<li><a href="'.get_permalink( $relative->ID ).'">'.get_the_title( $relative->ID ).'</a></li>';
			}
		}
		echo '</ul>';
	}
}



// injecting search form to nav menu
add_filter( 'wp_nav_menu_items', 'roots_responsive_menu_search', 10, 2 );
/**
 * Filter menu items, appending either a search form or today's date.
 *
 * @param string   $menu HTML string of list items.
 * @param stdClass $args Menu arguments.
 *
 * @return string Amended HTML string of list items.
 */
function roots_responsive_menu_search( $menu, $args ) {

	//* Change 'primary' to 'secondary' to add extras to the secondary navigation menu
	if ( 'primary' !== $args->theme_location )
		return $menu;

	//* Uncomment this block to add a search form to the navigation menu

	ob_start();
	get_search_form();
	$search = ob_get_clean();
	$menu  .= '<li class="search">' . $search . '</li>';

	return $menu;
}

// specify text in the search field
add_filter( 'genesis_search_text', 'roots_search_button_text' );
function roots_search_button_text( $text ) {
	return esc_attr( 'Search' );
}

/*--- CRUCIAL QUERY MODIFIERS ---*/

// modifies search results AND taxonomy archive listings output
add_action( 'pre_get_posts', 'roots_expand_query_results', 11);
function roots_expand_query_results( $query ) {
	// abort on anything but search and archive queries
	if ( is_admin() || is_page() || is_singular() ) return $query;
	// only execute on the main query, to avoid other queries for things like nav_menu_items and wp_global_styles which also return true for is_tax and is_archive!
	if ( $query->is_main_query() || is_search() ) {
	// if ( $query->is_search() || $query->is_tax('years') || $query->is_tax('people') || $query->is_tax('locations') || $query->is_tax('projects') ) {

		// kill pagination so we get all the results
		$query->query_vars['nopaging'] = 1;
		$query->query_vars['posts_per_page'] = -1;

		// include attachments AS WELL as other custom post types (doesn't usually do attachments)
		$query->set( 'post_type', array( 'attachment', 'stories', 'bulletins', 'videos' ) );
		$query->set( 'post_status', array( 'inherit', 'publish' ) );  // attachments have 'inherit' status, normal query would only allow 'publish'

		// order results by the chronology meta
		$query->set( 'meta_key', 'chronology' );
		$query->set( 'meta_type', 'NUMERIC' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'ASC' );
	}
	return $query;
}

/*--------------------------------*/


/* replace gutenberg image block output to force all images to be linked to their attachment page
* Detect the block you want and only render in the front-end (doesn't modify database)
* normally the prebuilt innerHTML string would just be rendered, we're ignoring that
* so this actually overrides not only the saved linking type, but will replace any custom link the editor may have specified with our own
* obviously this requires more queries on page render, would be more efficient to hook the save_post and commit this preference to the DB
* (would also avoid possible confusion for the editor wondering why their content isn't rendering exactly as they've saved it)
* DOES NOT WORK WITH GALLERIES OR MAYBE IMAGE BLOCK INSIDE COLUMNS OR GROUPS! how do we render as innerContent vs innerHTML?
* this method does have the advantage of being dynamic in the future, in case attachment image permalinks change. Very difficult to retroactively update the static html image objects for each post later. This way, the editor/writer does not have to bother with linking at all. Any and all images embedded in a story will get wrapped and alt tagged
*/
function roots_embedded_image_render_links ( $block_content, $block ) {
	// only front-end plz
	if ( !is_admin() && !wp_is_json_request() ) {
		// ignoring the saved innerHTML, build our own output from scratch
		$html = '';
		$align = empty( $block['attrs']['align'] ) ? 'center' : $block['attrs']['align'];  // default to center if not specified
		if ( isset($block['attrs']['sizeSlug'])) {
			$size = $block['attrs']['sizeSlug'];
		} else {
			$size = 'medium';
		}

		$caption = wp_get_attachment_caption( $block['attrs']['id'] );
		$html .= '<div class="wp-block-image">';
		if ( isset( $block['attrs']['classname'] ) ) {
			$html .= '<figure class="size-'.$size.' align'.$align.' '. $block['attrs']['className'] .'">';
		} else {
			$html .= '<figure class="size-'.$size.' align'.$align.'">';
		}
		// wrap the img tag with link (also nice because the img tag isn't hardcoded in the post, so filtering the img tag output is possible)
		$html .= '<a href="'.get_attachment_link( $block['attrs']['id'] ).'">'.wp_get_attachment_image( $block['attrs']['id'], $size ).'</a>';
		$html .= empty($caption) ? null : '<figcaption>'.$caption.'</figcaption>';
		$html .= '</figure>';
		$html .= '</div>';
		return $html;
	}
	// return original for admin and json contexts
	return $block_content;
}
add_filter('render_block_core/image', 'roots_embedded_image_render_links', 10, 2);