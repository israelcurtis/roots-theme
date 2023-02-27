<?php
/* BULLETIN ITEM SINGLE TEMPLATE */

add_action( 'genesis_loop', 'roots_bulletinpage_loop' );
function roots_bulletinpage_loop() {
	global $post;
	echo "<div class='entry-content'>";
	$pdf = get_field('bulletin_pdf');
	if ( $pdf ) {
		echo '<h5><a href="'.wp_get_attachment_url( $pdf ).'" download>download PDF version</a></h5>';
	}
	$pages = get_field('pdf_pages');
	$size = 'full'; // (thumbnail, medium, large, full or custom size)
	if( $pages ): ?>
	    <ul class="alignfull">
	        <?php foreach( $pages as $image_id ): ?>
	            <li>
	                <?php echo wp_get_attachment_image( $image_id, $size ); ?>
	            </li>
	        <?php endforeach; ?>
	    </ul>
	<?php endif;
	echo "</div>";
	echo '<button id="reveal-raw" class="small">show raw text</button>';
	echo "<div id='bulletin-raw-text'>";
	echo the_content();
	echo '</div>';
	// metadata output
	roots_metadata_output( $post->ID );
	

	// $caption = get_field('description');
	// if (empty($caption)) {
	// 	echo "<h2>No Caption</h2>";
	// } else {
	// 	echo "<h2>$caption</h2>";
	// }


}


// eliminate default content rendering (so nothing from the page editor will appear)
remove_action( 'genesis_loop', 'genesis_do_loop' );
genesis();

?>