<?php
/**
 * All functions for creating and managing Taxonomy Images
 *
 * Uses Genesis Term Meta functionality to store the image ID
 * in the Genesis Term meta array.
 *
 * @since 0.8.0
 *
 * @package genesis-taxonomy-images
 * @version 0.8.0
 * @author Ade Walker
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit( _( 'Sorry, you are not allowed to access this page directly.' ) );
}


add_action( 'init', 'gtaxi_add_taxonomy_image_hooks' );
/**
 * Add taxonomy image functionality to taxonomy terms admin screens.
 *
 * Hooked to 'init' action
 *
 * Loops through all registered taxonomies which have a public UI
 * and adds necessary filters to display and edit Taxonomy Images
 * in the taxonomies' terms admin screens.
 *
 * @since 0.8.0
 *
 * @see none
 * @link none
 * @global none.
 *
 * @return void.
 */
function gtaxi_add_taxonomy_image_hooks() {

	add_action( 'admin_enqueue_scripts', 'gtaxi_admin_scripts' );
	
	foreach ( get_taxonomies( array( 'show_ui' => true ) ) as $tax_name ) {
		add_filter( 'manage_edit-'.$tax_name.'_columns', 'gtaxi_taxonomy_image_column' );
		add_filter( 'manage_'.$tax_name.'_custom_column', 'gtaxi_taxonomy_image_column_content', 10, 3 );
		// Priority of 9 to insert this before Genesis term meta fields
		add_action( $tax_name.'_edit_form', 'gtaxi_add_edit_term_fields', 9, 2 );
	}
}


/**
 * Enqueue WP Media javascript.
 *
 * Hooked to 'admin_enqueue_scripts' action
 *
 * Loops through all registered taxonomies which have a public UI
 * and adds necessary filters to display Thumbnail in the taxonomies'
 * terms admin screens.
 *
 * @since 0.8.0
 *
 * @see gtaxi_add_taxonomy_image_hooks()
 * @link none
 * @global none.
 *
 * @return void.
 */
function gtaxi_admin_scripts() {
	
    $screen = get_current_screen();
    
    if ( in_array( $screen->id, array('edit-category') ) )
		wp_enqueue_media();
}


/**
 * Display image upload fields in taxonomy term edit screen.
 *
 * Hooked to {$tax_name}_edit_form filter
 *
 * Note that the Image attachment ID gets saved in the Genesis Term meta
 * array and this is handled automatically without this plugin having
 * to deal with Saving data.
 *
 * @since 0.8.0
 *
 * @see gtaxi_add_taxonomy_image_hooks()
 * @link none
 * @global none.
 *
 * @param mixed $term Term being edited
 * @param mixed $taxonomy Taxonomy of the term being edited
 * @return void
 */
function gtaxi_add_edit_term_fields( $term, $taxonomy ) {

	$image 			= '';
	$thumbnail_id 	= isset ( $term->meta['term_thumbnail_id'] ) ? absint( $term->meta['term_thumbnail_id'] ) : false;
	
	if ($thumbnail_id) {
		$image = wp_get_attachment_url( $thumbnail_id );
		$value = $thumbnail_id;
	} else {
		$image = gtaxi_placeholder_img_src();
		$value = '0';
	}
	?>
	<h3><?php _e( 'Term Image', 'gftw' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'gftw' ); ?></label></th>
				<td>
					<div id="term_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
					<div style="line-height:60px;">
						<input type="hidden" id="genesis-meta[term_thumbnail_id]" name="genesis-meta[term_thumbnail_id]" value="<?php echo $value; ?>" />
						<button type="submit" class="upload_image_button button"><?php _e( 'Upload/Add image', 'gftw' ); ?></button>
						<button type="submit" class="remove_image_button button"><?php _e( 'Remove image', 'gftw' ); ?></button>
					</div>
					<script type="text/javascript">

						// Only show the "remove image" button when needed
			 			if ( '0' == jQuery('#genesis-meta\\[term_thumbnail_id\\]').val() )
							jQuery('.remove_image_button').hide();
				
						// Uploading files
						var file_frame;

						jQuery(document).on( 'click', '.upload_image_button', function( event ){

							event.preventDefault();

							// If the media frame already exists, reopen it.
							if ( file_frame ) {
								file_frame.open();
								return;
							}

							// Create the media frame.
							file_frame = wp.media.frames.downloadable_file = wp.media({
								title: '<?php _e( 'Choose an image', 'gftw' ); ?>',
								button: {
									text: '<?php _e( 'Use image', 'gftw' ); ?>',
								},
								multiple: false
							});

							// When an image is selected, run a callback.
							file_frame.on( 'select', function() {
								attachment = file_frame.state().get('selection').first().toJSON();

								jQuery('#genesis-meta\\[term_thumbnail_id\\]').val( attachment.id );
								jQuery('#term_thumbnail img').attr('src', attachment.url );
								jQuery('.remove_image_button').show();
							});

							// Finally, open the modal.
							file_frame.open();
						});

						jQuery(document).on( 'click', '.remove_image_button', function( event ){
							jQuery('#term_thumbnail img').attr('src', '<?php echo gtaxi_placeholder_img_src(); ?>');
							jQuery('#genesis-meta\\[term_thumbnail_id\\]').val('');
							jQuery('.remove_image_button').hide();
							return false;
						});

					</script>
					<div class="clear"></div>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}


/**
 * Add Taxonomy Image column to taxonomy admin screen.
 *
 * Hooked to 'manage_edit-{$tax_name}_columns' filter.
 *
 * @since 0.8.0
 *
 * @see gtaxi_add_taxonomy_custom_columns()
 * @link none
 * @global none.
 *
 * @param  array $columns Default columns displayed in tax terms screen.
 * @return array Default plus new columns to be displayed in tax terms screen.
 */
function gtaxi_taxonomy_image_column( $columns ) {
	
	$new_columns = array();
	$new_columns['cb'] = $columns['cb'];
	$new_columns['thumb'] = __( 'Image', 'gftw' );

	unset( $columns['cb'] );

	return array_merge( $new_columns, $columns );
}


/**
 * Populate new Taxonomy Image column in the taxonomy admin screen.
 *
 * Hooked to 'manage_{$tax_name}_custom_column' filter.
 *
 * @since 0.8.0
 *
 * @see gtaxi_add_taxonomy_custom_columns()
 * @link none
 * @global none.
 *
 * @param mixed $columns
 * @param mixed $column
 * @param mixed $id Term ID
 * @return string $columns Content for our new column
 */
function gtaxi_taxonomy_image_column_content( $columns, $column, $id ) {

	if ( $column == 'thumb' ) {
		
		$image = '';
		
		$taxonomy = isset( $_GET['taxonomy' ] ) ? $_GET['taxonomy' ] : false;
		
		if ( ! $taxonomy )
			return $columns;
		
		$term = get_term_by( 'id', $id, $taxonomy );
		
		if ( ! $term )
			return $columns;
		
		$thumbnail_id = isset( $term->meta['term_thumbnail_id'] ) ? absint( $term->meta['term_thumbnail_id'] ) : false;

		if ($thumbnail_id)
			$image = wp_get_attachment_url( $thumbnail_id );
		else
			$image = gtaxi_placeholder_img_src();

		$alt = esc_attr( $term->name ) . ' Term image';
		
		$columns .= '<img src="' . $image . '" alt="' . $alt . '" class="wp-post-image" height="48" width="48" />';

	}

	return $columns;
}


/**
 * URL of placeholder taxonomy term image.
 *
 * Helper function used by other functions in the plugin.
 * Includes a filter to allow users to override location of placeholder image.
 *
 * @since 0.8.0
 *
 * @see none
 * @link none
 * @global none.
 *
 * @return string URL of placeholder image.
 */
function gtaxi_placeholder_img_src() {
	return apply_filters('gtaxi_placeholder_img_src', GTAXI_URL . '/assets/images/placeholder.png' );
}


/**
 * Get the Taxonomy Image for a Term.
 *
 * Based heavily on Genesis genesis_get_image().
 * By default, this fetches the Taxonomy Image html or src for the relevant Term
 * or, if it doesn't exist, a placeholder image.
 * The $args array allows users to override the default args when calling
 * this function via functions.php, etc.
 * Includes a filter just before output to enable users to completely modify
 * this function if they wish to do so.
 *
 * @since 0.8.0
 *
 * @see Genesis lib/functions/image.php genesis_get_image()
 * @link none
 * @global object $wp_query.
 *
 * @param array $args
 * @return mixed HTML or src of this Term's image, or placeholder, or false
 */
function gtaxi_get_taxonomy_image( $args = array() ) {

	global $wp_query;

	if ( ! is_category() && ! is_tag() && ! is_tax() )
		return;

	$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : $wp_query->get_queried_object();

	if ( ! $term )
		return;
		
	$defaults = apply_filters( 'gtaxi_get_taxonomy_image_default_args', array(
		'format'   => 'html',
		'size'     => 'full',
		'num'      => 0,
		'attr'     => '',
		'fallback' => 'placeholder',
		'context'  => '',
	) );

	$args = wp_parse_args( $args, $defaults );

	$image = '';

	$thumbnail_id = isset( $term->meta['term_thumbnail_id'] ) ? $term->meta['term_thumbnail_id'] : false;
	
	if ( $thumbnail_id ) {
		$html = wp_get_attachment_image( $thumbnail_id, $args['size'], false, $args['attr'] );
		list( $url ) = wp_get_attachment_image_src( $thumbnail_id, $args['size'], false, $args['attr'] );
		
	} elseif ( 'placeholder' == $args['fallback'] ) {
		// @TODO Get selected image sizes and apply to placeholder
		$url = gtaxi_placeholder_img_src();
		$alt = esc_attr( $term->name ) . ' Term image';
		$html = '<img src="' . $url . '" alt="' . $alt . '" class="wp-post-image" height="48" width="48" />';
	
	} else {
		return false;
	}

	//* Source path, relative to the root
	$src = str_replace( home_url(), '', $url );

	//* Determine output
	if ( 'html' === mb_strtolower( $args['format'] ) )
		$output = $html;
	elseif ( 'url' === mb_strtolower( $args['format'] ) )
		$output = $url;
	else
		$output = $src;

	// Return false if $url is blank
	if ( empty( $url ) ) $output = false;

	//* Return data, filtered
	return apply_filters( 'gtaxi_get_taxonomy_image', $output, $args, $thumbnail_id, $html, $url, $src );
}