<?php
// **********************************************************************// 
// ! Add brand description
// **********************************************************************//

if( !function_exists( 'et_brand_description' ) ) {
	function et_brand_description() {
		if(is_tax('brand') && term_description() != '') {
			echo '<div class="term-description">';
				echo do_shortcode(term_description());
			echo '</div>';
		}
	}
	//add_filter('woocommerce_archive_description', 'et_brand_description');
}

// **********************************************************************// 
// ! Remove Default STYLES
// **********************************************************************//

add_filter( 'woocommerce_enqueue_styles', '__return_false' );
add_filter( 'pre_option_woocommerce_enable_lightbox', 'return_no'); // Remove woocommerce prettyphoto 

function return_no($option) {
	return 'no';
}

// **********************************************************************// 
// ! Template hooks
// **********************************************************************// 

add_action('after_setup_theme', 'et_template_hooks'); 
if(!function_exists('et_template_hooks')) {
	function et_template_hooks() {
		add_action( 'woocommerce_before_shop_loop', 'woocommerce_pagination', 40 ); // add pagination above the products
		add_action( 'woocommerce_single_product_summary', 'et_size_guide', 26 );
		add_action( 'woocommerce_single_product_summary', 'et_email_btn', 36 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_cart_totals_after_shipping', 'woocommerce_shipping_calculator', 15 );
		remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );

		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

		$layout = ( etheme_get_custom_field( 'et_single_layout' ) != '' ) ? etheme_get_custom_field( 'et_single_layout' ) : etheme_get_option( 'single_product_layout' ) ;

		if( etheme_get_option( 'tabs_location' ) == 'after_image' && $layout != 'large') {
			//add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 31 );
		}

		if( etheme_get_option('single_product_layout') == 'fixed' ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		}

		if(etheme_get_option('reviews_position') == 'outside' ) {
			add_filter( 'woocommerce_product_tabs', 'et_remove_reviews_from_tabs', 98 );
			add_action( 'woocommerce_after_single_product_summary', 'comments_template', 30 );
			add_action('show_product_reviews', 'comments_template', 10); 
		}

		if(!etheme_get_option('show_product_title')) {
			if ( ! function_exists( 'et_hidden_title' ) ) {
				function et_hidden_title(){
					the_title( '<h1 itemprop="name" class="product_title entry-title hidden">', '</h1>' );
				}
			}

        	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
        	add_action( 'woocommerce_single_product_summary', 'et_hidden_title', 5 );
   		}
	}
}

if(!function_exists('et_remove_reviews_from_tabs')) {
	function et_remove_reviews_from_tabs( $tabs ) {
	    unset( $tabs['reviews'] ); 			// Remove the reviews tab
	    return $tabs;

	}
}


// **********************************************************************// 
// ! Define image sizes
// **********************************************************************//
if(!function_exists('etheme_woocommerce_image_dimensions')) {
	function etheme_woocommerce_image_dimensions() {
	  	$catalog = array(
			'width' 	=> '450',	// px
			'height'	=> '600',	// px
			'crop'		=> 0 		// true
		);
	 
		$single = array(
			'width' 	=> '555',	// px
			'height'	=> '741',	// px
			'crop'		=> 0 		// true
		);
	 
		$thumbnail = array(
			'width' 	=> '149',	// px
			'height'	=> '198',	// px
			'crop'		=> 0 		// false
		);
	 
		// Image sizes
		update_option( 'shop_catalog_image_size', $catalog ); 		// Product category thumbs
		update_option( 'shop_single_image_size', $single ); 		// Single product image
		update_option( 'shop_thumbnail_image_size', $thumbnail ); 	// Image gallery thumbs
	}
}

add_action('et_before_data_import', 'etheme_woocommerce_image_dimensions');

// **********************************************************************// 
// ! Hidden sidebar functionality
// **********************************************************************//

add_action('after_setup_theme', 'et_hidden_sidebar', 20); 

if(!function_exists('et_hidden_sidebar')) {
	function et_hidden_sidebar() {
		global $options;
		
		if(etheme_get_option('sidebar_hidden')) {
			add_action( 'woocommerce_before_shop_loop', 'et_hidden_sidebar_btn', 1 ); // add pagination above the products
			add_action( 'after_page_wrapper', 'et_hidden_sidebar_html', 35 ); // add pagination above the products

		}
	}
}


if(!function_exists('et_hidden_sidebar_html')) {
	function et_hidden_sidebar_html() {
		?>
			<div class="st-menu hide-filters-block">
				<div class="nav-wrapper">
					<div class="st-menu-content">
						<?php etheme_get_sidebar('shop'); ?>
					</div>
				</div>
			</div> 
		<?php
	}
}

if(!function_exists('et_hidden_sidebar_btn')) {
	function et_hidden_sidebar_btn() {
		?>
			<div id="st-trigger-effects" class="column pull-left">
				<button data-effect="hide-filters-block" class="btn filled medium"><?php esc_html_e('Show Filter', 'royal'); ?></button>
			</div>
		<?php
	}
}

// **********************************************************************// 
// ! Next and previous product links
// **********************************************************************//

function next_post_link_product() {
	$product = get_adjacent_post_product(true, '', false);
	if(empty($product)) return;
	$product_obj = new WC_Product( $product->ID );
	$image = get_the_post_thumbnail( $product_obj->get_id(), apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
	?>
		 <div class="next-product" onclick="window.location='<?php echo get_permalink( $product->ID ); ?>'">
		 	<div class="hide-info">
		 		<?php echo $image; ?>
                                <div>
                                    <span><?php echo $product->post_title; ?></span>
                                    <span class="price"><?php echo $product_obj->get_price_html(); ?></span>
                                </div>
		 	</div>
		 </div>
	<?php
}

function previous_post_link_product() {
	$product = get_adjacent_post_product(true, '', true);
	if(empty($product)) return;
	$product_obj = new WC_Product( $product->ID );
	$image = get_the_post_thumbnail( $product_obj->get_id(), apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
	?>
		<div class="prev-product" onclick="window.location='<?php echo get_permalink( $product->ID ); ?>'">
		 	<div class="hide-info">
		 		<?php echo $image; ?>
                                <div>
                                    <span><?php echo $product->post_title; ?></span>
                                    <span class="price"><?php echo $product_obj->get_price_html(); ?></span>
                                </div>
		 	</div>
		 </div>
	<?php
}

function adjacent_post_link_product( $format, $link, $in_same_cat = false, $excluded_categories = '', $previous = true ) {
    if ( $previous && is_attachment() )
        $post = get_post( get_post()->post_parent );
    else
        $post = get_adjacent_post_product( $in_same_cat, $excluded_categories, $previous );

    if ( ! $post ) {
        $output = '';
    } else {
        $title = $post->post_title;

        if ( empty( $post->post_title ) )
            $title = $previous ? esc_html__( 'Previous Post', 'royal' ) : esc_html__( 'Next Post', 'royal' );

        $title = apply_filters( 'the_title', $title, $post->ID );
        $date = mysql2date( get_option( 'date_format' ), $post->post_date );
        $rel = $previous ? 'prev' : 'next';

        $string = '<a href="' . get_permalink( $post ) . '" rel="'.$rel.'">';
        $inlink = str_replace( '%title', $title, $link );
        $inlink = str_replace( '%date', $date, $inlink );
        $inlink = $string . $inlink . '</a>';

        $output = str_replace( '%link', $inlink, $format );
    }

    $adjacent = $previous ? 'previous' : 'next';

    echo apply_filters( "{$adjacent}_post_link", $output, $format, $link, $post );
}

function get_adjacent_post_product( $in_same_cat = false, $excluded_categories = '', $previous = true ) {
    global $wpdb;

    if ( ! $post = get_post() )
        return null;

    $current_post_date = $post->post_date;

    $join = '';
    $posts_in_ex_cats_sql = '';
    if ( $in_same_cat || ! empty( $excluded_categories ) ) {
        $join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";

        if ( $in_same_cat ) {
            if ( ! is_object_in_taxonomy( $post->post_type, 'product_cat' ) )
                return '';
            $cat_array = wp_get_object_terms($post->ID, 'product_cat', array('fields' => 'ids'));
            if ( ! $cat_array || is_wp_error( $cat_array ) )
                return '';
            $join .= " AND tt.taxonomy = 'product_cat' AND tt.term_id IN (" . implode(',', $cat_array) . ")";
        }

        $posts_in_ex_cats_sql = "AND tt.taxonomy = 'product_cat'";
        if ( ! empty( $excluded_categories ) ) {
            if ( ! is_array( $excluded_categories ) ) {
                // back-compat, $excluded_categories used to be IDs separated by " and "
                if ( strpos( $excluded_categories, ' and ' ) !== false ) {
                    _deprecated_argument( __FUNCTION__, '3.3', sprintf( esc_html__( 'Use commas instead of %s to separate excluded categories.', 'royal' ), "'and'" ) );
                    $excluded_categories = explode( ' and ', $excluded_categories );
                } else {
                    $excluded_categories = explode( ',', $excluded_categories );
                }
            }

            $excluded_categories = array_map( 'intval', $excluded_categories );

            if ( ! empty( $cat_array ) ) {
                $excluded_categories = array_diff($excluded_categories, $cat_array);
                $posts_in_ex_cats_sql = '';
            }

            if ( !empty($excluded_categories) ) {
                $posts_in_ex_cats_sql = " AND tt.taxonomy = 'product_cat' AND tt.term_id NOT IN (" . implode($excluded_categories, ',') . ')';
            }
        }
    }

    $adjacent = $previous ? 'previous' : 'next';
    $op = $previous ? '<' : '>';
    $order = $previous ? 'DESC' : 'ASC';

    $join  = apply_filters( "get_{$adjacent}_post_join", $join, $in_same_cat, $excluded_categories );
    $where = apply_filters( "get_{$adjacent}_post_where", $wpdb->prepare("WHERE p.post_date $op %s AND p.post_type = %s AND p.post_status = 'publish' $posts_in_ex_cats_sql", $current_post_date, $post->post_type), $in_same_cat, $excluded_categories );
    $sort  = apply_filters( "get_{$adjacent}_post_sort", "ORDER BY p.post_date $order LIMIT 1" );

    $query = "SELECT p.id FROM $wpdb->posts AS p $join $where $sort";
    $query_key = 'adjacent_post_' . md5($query);
    $result = wp_cache_get($query_key, 'counts');
    if ( false !== $result ) {
        if ( $result )
            $result = get_post( $result );
        return $result;
    }

    $result = $wpdb->get_var( $query );
    if ( null === $result )
        $result = '';

    wp_cache_set($query_key, $result, 'counts');

    if ( $result )
        $result = get_post( $result );

    return $result;
}

// **********************************************************************// 
// ! Product Video
// **********************************************************************//

add_action('admin_init', 'et_product_meta_boxes');

function et_product_meta_boxes() {
	add_meta_box( 'woocommerce-product-videos', esc_html__( 'Product Video', 'royal' ), 'et_woocommerce_product_video_box', 'product', 'side' );
}

if(!function_exists('et_woocommerce_product_video_box')) {
	function et_woocommerce_product_video_box() {
		global $post;
		?>
		<div id="product_video_container">
			<?php esc_html_e('Upload your Video in 3 formats: MP4, OGG and WEBM', 'royal') ?>
			<ul class="product_video">
				<?php
					
					$product_video_code = get_post_meta( $post->ID, '_product_video_code', true );


					if ( metadata_exists( 'post', $post->ID, '_product_video_gallery' ) ) {
						$product_image_gallery = get_post_meta( $post->ID, '_product_video_gallery', true );
					} 
					
					$video_attachments = false;
					
					if(isset($product_image_gallery) && $product_image_gallery != '') {
						$video_attachments = get_posts( array(
							'post_type' => 'attachment',
							'include' => $product_image_gallery
						) ); 
					}
					
					
					
					//$attachments = array_filter( explode( ',', $product_image_gallery ) );
	
					if ( $video_attachments )
						foreach ( $video_attachments as $attachment ) {
							echo '<li class="video" data-attachment_id="' . $attachment->id . '">
								Format: ' . $attachment->post_mime_type . '
								<ul class="actions">
									<li><a href="#" class="delete" title="' . esc_html__( 'Delete image', 'royal' ) . '">' . esc_html__( 'Delete', 'royal' ) . '</a></li>
								</ul>
							</li>';
						}
				?>
			</ul>
	
			<input type="hidden" id="product_video_gallery" name="product_video_gallery" value="<?php echo esc_attr( $product_image_gallery ); ?>" />
	
		</div>
		<p class="add_product_video hide-if-no-js">
			<a href="#"><?php esc_html_e( 'Add product gallery video', 'royal' ); ?></a>
		</p>
		<p>
			<?php esc_html_e('Or you can use YouTube or Vimeo iframe code', 'royal'); ?>
		</p>
		<div class="product_iframe_video">
			
			<textarea name="et_video_code" id="et_video_code" rows="7"><?php echo esc_attr( $product_video_code ); ?></textarea>
			
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($){
	
				// Uploading files
				var product_gallery_frame;
				var $image_gallery_ids = $('#product_video_gallery');
				var $product_images = $('#product_video_container ul.product_video');
	
				jQuery('.add_product_video').on( 'click', 'a', function( event ) {
	
					var $el = $(this);
					var attachment_ids = $image_gallery_ids.val();
	
					event.preventDefault();
	
					// If the media frame already exists, reopen it.
					if ( product_gallery_frame ) {
						product_gallery_frame.open();
						return;
					}
	
					// Create the media frame.
					product_gallery_frame = wp.media.frames.downloadable_file = wp.media({
						// Set the title of the modal.
						title: '<?php esc_html_e( 'Add Images to Product Gallery', 'royal' ); ?>',
						button: {
							text: '<?php esc_html_e( 'Add to gallery', 'royal' ); ?>',
						},
						multiple: true,
						library : { type : 'video'}
					});
	
					// When an image is selected, run a callback.
					product_gallery_frame.on( 'select', function() {
	
						var selection = product_gallery_frame.state().get('selection');
	
						selection.map( function( attachment ) {
	
							attachment = attachment.toJSON();
	
							if ( attachment.id ) {
								attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;
	
								$product_images.append('\
									<li class="video" data-attachment_id="' + attachment.id + '">\
										Video\
										<ul class="actions">\
											<li><a href="#" class="delete" title="<?php _e( 'Delete video', 'royal' ); ?>"><?php _e( 'Delete', 'royal' ); ?></a></li>\
										</ul>\
									</li>');
							}
	
						} );
	
						$image_gallery_ids.val( attachment_ids );
					});
	
					// Finally, open the modal.
					product_gallery_frame.open();
				});
	
				// Image ordering
				$product_images.sortable({
					items: 'li.video',
					cursor: 'move',
					scrollSensitivity:40,
					forcePlaceholderSize: true,
					forceHelperSize: false,
					helper: 'clone',
					opacity: 0.65,
					placeholder: 'wc-metabox-sortable-placeholder',
					start:function(event,ui){
						ui.item.css('background-color','#f6f6f6');
					},
					stop:function(event,ui){
						ui.item.removeAttr('style');
					},
					update: function(event, ui) {
						var attachment_ids = '';
	
						$('#product_video_container ul li.video').css('cursor','default').each(function() {
							var attachment_id = jQuery(this).attr( 'data-attachment_id' );
							attachment_ids = attachment_ids + attachment_id + ',';
						});
	
						$image_gallery_ids.val( attachment_ids );
					}
				});
	
				// Remove images
				$('#product_video_container').on( 'click', 'a.delete', function() {
	
					$(this).closest('li.video').remove();
	
					var attachment_ids = '';
	
					$('#product_video_container ul li.video').css('cursor','default').each(function() {
						var attachment_id = jQuery(this).attr( 'data-attachment_id' );
						attachment_ids = attachment_ids + attachment_id + ',';
					});
	
					$image_gallery_ids.val( attachment_ids );
	
					return false;
				} );
	
			});
		</script>
		<?php
	}
}

add_action( 'woocommerce_process_product_meta', 'et_save_video_meta' );

if(!function_exists('et_save_video_meta')) {
	function et_save_video_meta($post_id) {
		// Gallery Images
		$video_ids =  explode( ',',  $_POST['product_video_gallery']  ) ;
		update_post_meta( $post_id, '_product_video_gallery', implode( ',', $video_ids ) );
		update_post_meta( $post_id, '_product_video_code',  $_POST['et_video_code']  );
	}
}

if(!function_exists('et_get_external_video')) {
	function et_get_external_video($post_id) {
		if(!$post_id) return false;
		$product_video_code = get_post_meta( $post_id, '_product_video_code', true );
		
		return $product_video_code;
	}
}

if(!function_exists('et_get_attach_video')) {
	function et_get_attach_video($post_id) {
		if(!$post_id) return false;
		$product_video_code = get_post_meta( $post_id, '_product_video_gallery', false );
		
		return $product_video_code;
	}
}

// **********************************************************************// 
// ! Product brand label
// **********************************************************************//

add_action( 'admin_enqueue_scripts', 'et_brand_admin_scripts' );
if(!function_exists('et_brand_admin_scripts')) {
    function et_brand_admin_scripts() {
        $screen = get_current_screen();
        if ( in_array( $screen->id, array('edit-brand') ) )
		  wp_enqueue_media();
    }
}
if(!function_exists('et_product_brand_image')) {
	function et_product_brand_image() {
		global $post, $wpdb, $product;
        $terms = wp_get_post_terms( $post->ID, 'brand' );

        if(count($terms)>0) {
        	?>
			<div class="sidebar-widget product-brands">
				<h4 class="widget-title"><span><?php esc_html_e('Product brand', 'royal') ?></span></h4>
	        	<?php
			        foreach($terms as $brand) {
			            $image 			= '';
			        	$thumbnail_id 	= absint( get_woocommerce_term_meta( $brand->term_id, 'thumbnail_id', true ) );
			        	?>
	                	<a href="<?php echo get_term_link($brand); ?>">
				        	<?php
				        	if ($thumbnail_id) :
				        		$image = etheme_get_image( $thumbnail_id );
				                ?>
				                		<?php if($image != ''): ?>
				                    		<img src="<?php echo $image; ?>" title="<?php echo $brand->name; ?>" alt="<?php echo $brand->name; ?>" class="brand-image" />
				                    	<?php else: ?>
				                    		<?php echo $brand->name; ?>
				                    	<?php endif; ?>
				                <?php
				                
				            else : 
				            	echo $brand->name;
				        	endif; ?>
		        	
	                	</a>
	                	<?php
			        }
	        	?>
			</div>
        	<?php
        }
        

        
	}
}

add_action( 'init', 'et_create_brand_taxonomies', 0 );
if(!function_exists('et_create_brand_taxonomies')) {
	function et_create_brand_taxonomies() {
		$labels = array(
			'name'              => _x( 'Brands', 'royal' ),
			'singular_name'     => _x( 'Brand', 'royal' ),
			'search_items'      => esc_html__( 'Search Brands', 'royal' ),
			'all_items'         => esc_html__( 'All Brands', 'royal' ),
			'parent_item'       => esc_html__( 'Parent Brand', 'royal' ),
			'parent_item_colon' => esc_html__( 'Parent Brand:', 'royal' ),
			'edit_item'         => esc_html__( 'Edit Brand', 'royal' ),
			'update_item'       => esc_html__( 'Update Brand', 'royal' ),
			'add_new_item'      => esc_html__( 'Add New Brand', 'royal' ),
			'new_item_name'     => esc_html__( 'New Brand Name', 'royal' ),
			'menu_name'         => esc_html__( 'Brands', 'royal' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
            'capabilities'			=> array(
            	'manage_terms' 		=> 'manage_product_terms',
				'edit_terms' 		=> 'edit_product_terms',
				'delete_terms' 		=> 'delete_product_terms',
				'assign_terms' 		=> 'assign_product_terms',
            ),
			'rewrite'           => array( 'slug' => 'brand' ),
		);

		register_taxonomy( 'brand', array( 'product' ), $args );
	}
}

add_action( 'brand_add_form_fields', 'et_brand_fileds' );
if(!function_exists('et_brand_fileds')) {
	function et_brand_fileds() {
		global $woocommerce;
		?>
		<div class="form-field">
			<label><?php esc_html_e( 'Thumbnail', 'royal' ); ?></label>
			<div id="brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo wc_placeholder_img_src(); ?>" width="60px" height="60px" /></div>
			<div style="line-height:60px;">
				<input type="hidden" id="brand_thumbnail_id" name="brand_thumbnail_id" />
				<button type="submit" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'royal' ); ?></button>
				<button type="submit" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'royal' ); ?></button>
			</div>
			<script type="text/javascript">

				 // Only show the "remove image" button when needed
				 if ( ! jQuery('#brand_thumbnail_id').val() )
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
						title: '<?php _e( 'Choose an image', 'royal' ); ?>',
						button: {
							text: '<?php _e( 'Use image', 'royal' ); ?>',
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						attachment = file_frame.state().get('selection').first().toJSON();

						jQuery('#brand_thumbnail_id').val( attachment.id );
						jQuery('#brand_thumbnail img').attr('src', attachment.url );
						jQuery('.remove_image_button').show();
					});

					// Finally, open the modal.
					file_frame.open();
				});

				jQuery(document).on( 'click', '.remove_image_button', function( event ){
					jQuery('#brand_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
					jQuery('#brand_thumbnail_id').val('');
					jQuery('.remove_image_button').hide();
					return false;
				});

			</script>
			<div class="clear"></div>
		</div>
		<?php
	}
}


add_action( 'brand_edit_form_fields', 'et_edit_brand_fields', 10,2 );
if(!function_exists('et_edit_brand_fields')) {
    function et_edit_brand_fields( $term, $taxonomy ) {
    	global $woocommerce;
    
    	$image 			= '';
    	$thumbnail_id 	= absint( get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true ) );
    	if ($thumbnail_id) :
    		$image = wp_get_attachment_thumb_url( $thumbnail_id );
    	else :
    		$image = wc_placeholder_img_src();
    	endif;
    	?>
    	<tr class="form-field">
    		<th scope="row" valign="top"><label><?php esc_html_e( 'Thumbnail', 'royal' ); ?></label></th>
    		<td>
    			<div id="brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
    			<div style="line-height:60px;">
    				<input type="hidden" id="brand_thumbnail_id" name="brand_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
    				<button type="submit" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'royal' ); ?></button>
    				<button type="submit" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'royal' ); ?></button>
    			</div>
    			<script type="text/javascript">
    
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
    						title: '<?php _e( 'Choose an image', 'royal' ); ?>',
    						button: {
    							text: '<?php _e( 'Use image', 'royal' ); ?>',
    						},
    						multiple: false
    					});
    
    					// When an image is selected, run a callback.
    					file_frame.on( 'select', function() {
    						attachment = file_frame.state().get('selection').first().toJSON();
    
    						jQuery('#brand_thumbnail_id').val( attachment.id );
    						jQuery('#brand_thumbnail img').attr('src', attachment.url );
    						jQuery('.remove_image_button').show();
    					});
    
    					// Finally, open the modal.
    					file_frame.open();
    				});
    
    				jQuery(document).on( 'click', '.remove_image_button', function( event ){
    					jQuery('#brand_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
    					jQuery('#brand_thumbnail_id').val('');
    					jQuery('.remove_image_button').hide();
    					return false;
    				});
    
    			</script>
    			<div class="clear"></div>
    		</td>
    	</tr>
    	<?php
    }
}

if(!function_exists('et_brands_fields_save')) {
    function et_brands_fields_save( $term_id, $tt_id, $taxonomy ) {
        
    	if ( isset( $_POST['brand_thumbnail_id'] ) )
    		update_woocommerce_term_meta( $term_id, 'thumbnail_id', absint( $_POST['brand_thumbnail_id'] ) );
    
    	delete_transient( 'wc_term_counts' );
    }
}

add_action( 'created_term', 'et_brands_fields_save', 10,3 );
add_action( 'edit_term', 'et_brands_fields_save', 10,3 );

// **********************************************************************// 
// ! AJAX Quick View
// **********************************************************************//

add_action('wp_ajax_et_product_quick_view', 'et_product_quick_view');
add_action('wp_ajax_nopriv_et_product_quick_view', 'et_product_quick_view');
if(!function_exists('et_product_quick_view')) {
	function et_product_quick_view() {
		if(empty($_POST['prodid'])) {
			echo 'Error: Absent product id';
			die();
		}

		$args = array(
			'p'=>$_POST['prodid'],
			'post_type' => array('product', 'product_variation')
		);

		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) : $the_query->the_post();
				wc_get_template('product-quick-view.php');
			endwhile;
			wp_reset_query();
			wp_reset_postdata();
		} else {
			echo 'No posts were found!';
		}
		die();
	}
}

// **********************************************************************// 
// ! Wishlist
// **********************************************************************//

//add_action('woocommerce_after_add_to_cart_button', 'etheme_wishlist_btn', 20);
//add_action('woocommerce_after_shop_loop_item', 'etheme_wishlist_btn', 20);

if(!function_exists('etheme_wishlist_btn')) {
    function etheme_wishlist_btn() {
        if(class_exists('YITH_WCWL'))
            echo do_shortcode('[yith_wcwl_add_to_wishlist]');
    }
}


if(!function_exists('et_wishlist_btn')) {
    function et_wishlist_btn($label = '') {
        global $yith_wcwl, $product;
        if(!class_exists('YITH_WCWL') || !class_exists('YITH_WCWL_Shortcode')) return;

        return YITH_WCWL_Shortcode::add_to_wishlist(array());

        $html = '';
        if($label == '') {
            $label = esc_html__('Add to Wishlist', 'royal');
        }
        $exists = $yith_wcwl->is_product_in_wishlist( $product->get_id() );
        $url = $yith_wcwl->get_wishlist_url();

        $classes = 'class="add_to_wishlist"';

        $html  = '<div class="yith-wcwl-add-to-wishlist">';
        $html .= '<div class="yith-wcwl-add-button';  // the class attribute is closed in the next row

        $html .= $exists ? ' hide" style="display:none;"' : ' show"';

        $html .= '><a href="' . esc_url( $yith_wcwl->get_addtowishlist_url() ) . '" data-product-id="' . $product->get_id() . '" ' . $classes . ' >' . $label . '</a>';
        $html .= '<img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading" id="add-items-ajax-loading" alt="" width="16" height="16" style="visibility:hidden" />';
        $html .= '</div>';

        $html .= '<div class="yith-wcwl-wishlistaddedbrowse hide" style="display:none;"><a href="' . esc_url( $url ) . '">' . apply_filters( 'yith-wcwl-browse-wishlist-label', esc_html__( 'Browse Wishlist', 'yit' ) ) . '</a></div>';
        $html .= '<div class="yith-wcwl-wishlistexistsbrowse ' . ( $exists ? 'show' : 'hide' ) . '" style="display:' . ( $exists ? 'block' : 'none' ) . '"><a href="' . esc_url( $url ) . '">' . apply_filters( 'yith-wcwl-browse-wishlist-label', esc_html__( 'Browse Wishlist', 'yit' ) ) . '</a></div>';
        $html .= '<div style="clear:both"></div><div class="yith-wcwl-wishlistaddresponse"></div>';

        $html .= '</div>';

        return $html;
    }
}

if(!function_exists('et_email_btn')) {
    function et_email_btn($label = '') {
        global $post;
        $html = '';
        $permalink = get_permalink($post->ID);
        $post_title = rawurlencode(get_the_title($post->ID)); 
        if($label == '') {
            $label = esc_html__('Email to a friend', 'royal');
        }
        $html .= '
            <a href="mailto:enteryour@addresshere.com?subject='.$post_title.'&amp;body=Check%20this%20out:%20'.$permalink.'" target="_blank" class="email-link">'.$label.'</a>';
        echo $html;
    }
}

if(!function_exists('et_size_guide')) {
    function et_size_guide() {
	    if ( etheme_get_custom_field('size_guide_img') ) : ?>
	    	<?php $lightbox_rel = (get_option('woocommerce_enable_lightbox') == 'yes') ? 'prettyPhoto' : 'lightbox'; ?>
	        <div class="size_guide">
	    	 <a rel="<?php echo $lightbox_rel; ?>" href="<?php etheme_custom_field('size_guide_img'); ?>"><?php esc_html_e('SIZING GUIDE', 'royal'); ?></a>
	        </div>
	    <?php endif;	
    }
}


// **********************************************************************// 
// ! Product Labels
// **********************************************************************// 

if(!function_exists('etheme_wc_product_labels')) {
	function etheme_wc_product_labels( $product_id = '' ) { 
	    echo etheme_wc_get_product_labels($product_id);
	}
}


if(!function_exists('etheme_wc_get_product_labels')) {
	function etheme_wc_get_product_labels( $product_id = '' ) {
		global $post, $wpdb,$product;
	    $count_labels = 0; 
	    $output = '';

	    if ( etheme_get_option('sale_icon') ) : 
	        if ($product->is_on_sale()) {$count_labels++; 
	            $output .= '<span class="label-icon sale-label">'.esc_html__( 'Sale!', 'royal' ).'</span>';
	        }
	    endif; 
	    
	    if ( etheme_get_option('new_icon') ) : $count_labels++; 
	        if(etheme_product_is_new($product_id)) :
	            $second_label = ($count_labels > 1) ? 'second_label' : '';
	            $output .= '<span class="label-icon new-label '.$second_label.'">'.esc_html__( 'New!', 'royal' ).'</span>';
	        endif;
	    endif; 
	    return $output;
	}
}

// **********************************************************************// 
// ! Get list of all product images
// **********************************************************************// 

if(!function_exists('get_images_list')) {
	function get_images_list() {
		global $post, $product, $woocommerce;
		$images_string = '';
		
		$attachment_ids = $product->get_gallery_image_ids();
			
		$_i = 0;
		if(count($attachment_ids) > 0) {
			$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'shop_catalog');
			$images_string .= $image[0];
			foreach($attachment_ids as $id) {
				$_i++;
				$image = wp_get_attachment_image_src($id, 'shop_catalog');
				if($image == '') continue;
				if($_i == 1)
					$images_string .= ',';
				
				
				$images_string .= $image[0];
				
				if($_i != count($attachment_ids)) 
					$images_string .= ',';
			}
		
		}

		return $images_string;
	}
}

// **********************************************************************// 
// ! Is product New
// **********************************************************************// 

if(!function_exists('etheme_product_is_new')) {
	function etheme_product_is_new( $product_id = '' ) {
		global $post, $wpdb;
	    $key = 'product_new';
		if(!$product_id) $product_id = $post->ID;
		if(!$product_id) return false;
	    $_etheme_new_label = get_post_meta($product_id, $key);
	    if(isset($_etheme_new_label[0]) && $_etheme_new_label[0] == 'enable') {
	        return true;
	    }
	    return false;	
	}
}

// **********************************************************************// 
// ! Grid/List switcher
// **********************************************************************// 

add_action('woocommerce_before_shop_loop', 'etheme_grid_list_switcher',35);
if(!function_exists('etheme_grid_list_switcher')) {
	function etheme_grid_list_switcher() {
		?>
		<?php $view_mode = etheme_get_option('view_mode'); ?>
		<?php if($view_mode == 'grid_list'): ?>
			<div class="view-switcher hidden-tablet hidden-phone">
				<label><?php esc_html_e('View as:', 'royal'); ?></label>
				<div class="switchToGrid"><i class="icon-th-large"></i></div>
				<div class="switchToList"><i class="icon-th-list"></i></div>
			</div>
		<?php elseif($view_mode == 'list_grid'): ?> 
			<div class="view-switcher hidden-tablet hidden-phone">
				<label><?php esc_html_e('View as:', 'royal'); ?></label>
				<div class="switchToList"><i class="icon-th-list"></i></div>
				<div class="switchToGrid"><i class="icon-th-large"></i></div>
			</div>
		<?php endif ;?> 
		

		<?php
	}	
}

// **********************************************************************// 
// ! Catalog Mode
// **********************************************************************// 

function etheme_remove_loop_button(){
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
	remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
	remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
	remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
}


add_action( 'after_setup_theme', 'et_catalog_setup', 18 );

if(!function_exists('et_catalog_setup')) {
	function et_catalog_setup() {
		$just_catalog = etheme_get_option('just_catalog');

		if($just_catalog) {
		    add_action('init','etheme_remove_loop_button');
		}
		// **********************************************************************// 
		// ! Set number of products per page
		// **********************************************************************// 
		$products_per_page = etheme_get_option('products_per_page');
		add_filter( 'loop_shop_per_page', create_function( '$cols', 'return '.$products_per_page.';' ), 20 );
	}
}

// **********************************************************************// 
// ! Category thumbnail
// **********************************************************************// 
if(!function_exists('etheme_category_header')){
	function etheme_category_header() {
		if(function_exists('et_get_term_meta')){
			global $wp_query;
			$cat = $wp_query->get_queried_object();
			if(!property_exists($cat, "term_id") && !is_search()){
				echo '<div class="category-description">';
			    	echo do_shortcode(etheme_get_option('product_bage_banner'));
				echo '</div>';
			}else{
			    $image = etheme_get_option('product_bage_banner');
				$queried_object = get_queried_object(); 
				
				if (isset($queried_object->term_id)){
			
					$term_id = $queried_object->term_id;  
					$content = et_get_term_meta($term_id, 'cat_meta');
			
					if(isset($content[0]['cat_header']) && !empty($content[0]['cat_header'])){
						echo '<div class="category-description">';
						echo do_shortcode($content[0]['cat_header']);
						echo '</div>';
					}
				}
			}
		}
	}
}
        
// **********************************************************************// 
// ! Review form
// **********************************************************************//   
//add_action('after_page_wrapper', 'etheme_review_form');
if(!function_exists('etheme_review_form')) {
	function etheme_review_form( $product_id = '' ) {
		global $woocommerce, $product,$post;
		$title_reply = '';
	
		if ( have_comments() ) :
			$title_reply = esc_html__( 'Add a review', 'royal' );
	
		else :
	
			$title_reply = esc_html__( 'Be the first to review', 'royal' ).' &ldquo;'.$post->post_title.'&rdquo;';
		endif;
	
		$commenter = wp_get_current_commenter();
	
		echo '<div id="review_form">';
		
		echo '<h4>'.esc_html__('Add your review', 'royal').'</h4>';
	
		$comment_form = array(
			'title_reply' => '',
			'comment_notes_before' => '',
			'comment_notes_after' => '',
			'fields' => array(
				'author' => '<p class="comment-form-author">' . '<label for="author">' . esc_html__( 'Name', 'royal' ) . '</label> ' . '<span class="required">*</span>' .
				            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></p>',
				'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'royal' ) . '</label> ' . '<span class="required">*</span>' .
				            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></p>',
			),
			'label_submit' => esc_html__( 'Submit Review', 'royal' ),
			'logged_in_as' => '',
			'comment_field' => ''
		);
	
		if ( get_option('woocommerce_enable_review_rating') == 'yes' ) {
	
			$comment_form['comment_field'] = '<p class="comment-form-rating"><label for="rating">' . esc_html__( 'Rating', 'royal' ) .'</label><select name="rating" id="rating">
				<option value="">'.esc_html__( 'Rate&hellip;', 'royal' ).'</option>
				<option value="5">'.esc_html__( 'Perfect', 'royal' ).'</option>
				<option value="4">'.esc_html__( 'Good', 'royal' ).'</option>
				<option value="3">'.esc_html__( 'Average', 'royal' ).'</option>
				<option value="2">'.esc_html__( 'Not that bad', 'royal' ).'</option>
				<option value="1">'.esc_html__( 'Very Poor', 'royal' ).'</option>
			</select></p>';
	
		}
	
		$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your Review', 'royal' ) . '</label><textarea id="comment" name="comment" cols="25" rows="8" aria-required="true"></textarea></p>' . WC()->nonce_field('comment_rating', true, false);
		
		
			comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
			
		
	
		echo '</div>';
	}
}  

// **********************************************************************// 
// ! User area in account page sidebar
// **********************************************************************//   
add_action('etheme_before_account_sidebar', 'etheme_user_info',10);
if(!function_exists('etheme_user_info')) {
	function etheme_user_info() {
		global $current_user;
		get_currentuserinfo();
		if(is_user_logged_in()) {
			?>
				<div class="user-sidearea">
					<?php echo get_avatar( $current_user->ID, 50 ); ?>
					<?php echo '<strong>' . $current_user->user_login . "</strong>\n"; ?>
					<br>
					<a href="<?php echo wp_logout_url(home_url()); ?>"><?php esc_html_e('Logout', 'royal') ?></a>
				</div>
			<?php
		}
	}
}

// **********************************************************************// 
// ! Get account sidebar position
// **********************************************************************// 

if(!function_exists('etheme_account_sidebar')) {
    function etheme_account_sidebar() {

        $result = array(
            'responsive' => '',
            'span' => 9,
            'sidebar' => etheme_get_option('account_sidebar')
        );
        
        $result['responsive'] = etheme_get_option('shop_sidebar_responsive');   

        if(!$result['sidebar']) {
            $result['span'] = 12;
        }
        
        return $result;
    }
}
// **********************************************************************// 
// ! Login form popup
// **********************************************************************//  

add_action('after_page_wrapper', 'etheme_login_form_modal');
if(!function_exists('etheme_login_form_modal')) {
	function etheme_login_form_modal() {
		global $woocommerce;
		?>
			<div id="loginModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h3 class="title"><span><?php esc_html_e('Login', 'royal'); ?></span></h3>
					</div>
					<div class="modal-body">
						<?php do_action('etheme_before_login'); ?>
						<form method="post" class="login">
							<p class="form-row form-row-<?php if (get_option('woocommerce_enable_myaccount_registration')=='yes') : ?>wide<?php else: ?>first<?php endif; ?>">
								<label for="username"><?php esc_html_e( 'Username or email', 'royal' ); ?> <span class="required">*</span></label>
								<input type="text" class="input-text" name="username" id="username" />
							</p>
							<p class="form-row form-row-<?php if (get_option('woocommerce_enable_myaccount_registration')=='yes') : ?>wide<?php else: ?>last<?php endif; ?>">
								<label for="password"><?php esc_html_e( 'Password', 'royal' ); ?> <span class="required">*</span></label>
								<input class="input-text" type="password" name="password" id="password" />
							</p>
							<div class="clear"></div>

							<p class="form-row">
								<?php wp_nonce_field( 'woocommerce-login' ); ?>
								<input type="submit" class="button filled active" name="login" value="<?php esc_html_e( 'Login', 'royal' ); ?>" />
								<a class="lost_password" href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost Password?', 'royal' ); ?></a>
								<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="right"><?php esc_html_e('Create Account', 'royal') ?></a>
							</p>
						</form>
					</div>
				</div>
			</div>
		<?php
	}
}

 
// **********************************************************************// 
// ! Top Cart Widget
// **********************************************************************// 

add_filter( 'woocommerce_widget_cart_is_hidden', '__return_false' );

if(!function_exists('etheme_cart_items')) {
	function etheme_cart_items ($limit = 3) { 
global $woocommerce;
		?>

		<div class="shopping-cart-widget" id='basket'>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-summ" data-items-count="<?php echo WC()->cart->cart_contents_count; ?>">
			<div class="cart-bag">

				<i class="<?php echo etheme_get_option( 'top_cart_icon' ) ? 'fa fa-' . esc_html( etheme_get_option( 'top_cart_icon' ) ) : 'ico-sum'; ?>" aria-hidden="true" style="font-size: <?php echo etheme_get_option( 'top_cart_icon_size' ) ? esc_html( etheme_get_option( 'top_cart_icon_size' ) ) : '18px'; ?>; color:<?php echo etheme_get_option( 'activecol' ); ?>;"></i>
				<span class="badge-number"><?php echo WC()->cart->cart_contents_count; ?></span>
			</div>

				<span class='shop-text'><?php esc_html_e('Cart', 'royal') ?>: <span class="total"><?php echo WC()->cart->get_cart_subtotal(); ?></span></span> 
				
			</a>
		</div>

		<div class="cart-popup-container">

		<div class="et_block"></div>

		<?php

        if ( ! WC()->cart->is_empty() ) {
          ?>
			<p class="recently-added"><?php esc_html_e('Recently added item(s)', 'royal'); ?></p>
			
			<ul class='order-list'>
          <?php
            $counter = 0;
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $counter++;
                if($counter > $limit) continue;
                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$thumbnail  = $_product->get_image( array( 70 ,200 ), array( 'class' => 'media-object' ) );
                if ( ! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) 
                    continue;
                
                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) 

                	$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ); 
                            
                    $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );   

					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                                
                ?>
					<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
						<?php 
                            echo apply_filters( 'woocommerce_cart_item_remove_link', 
							sprintf('<a href="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"class="close-order-li remove remove_from_cart_button" title="%s"></a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), esc_attr( $product_id ),esc_attr ( $cart_item_key ), esc_attr( $_product->get_sku() ),esc_html__('Remove this item', 'royal') ), 
                            	$cart_item_key ); 
                        ?>
						<div class="media">
							<a class="pull-left" href="<?php echo esc_url( $product_permalink ); ?>">
								<?php echo str_replace( array( 'http:', 'https:' ), '', $thumbnail ); ?>	
							</a>
							<?php $product_title = $_product->get_title();
							if (is_a($_product, 'WC_Product_Variation')) {
		                		$variation = wc_get_product($_product->get_id());
								$product_title = $variation->get_name();
		                	} ?>
							<div class="media-body">
								<h4 class="media-heading"><a href="<?php echo esc_url ( $product_permalink ); ?>"><?php echo apply_filters( 'woocommerce_cart_item_name', $product_title, $cart_item, $cart_item_key ); ?></a></h4>
								<div class="descr-box">
									<?php echo WC()->cart->get_item_data( $cart_item ); ?>
									<span class="coast"><?php echo $cart_item['quantity']; ?> x <span class='medium-coast'><?php echo $product_price; ?></span></span>
								</div>
							</div>
						</div>
					</li>
                <?php
                } ?>
				</ul>

        <?php   
        } else {
            echo '<p class="woocommerce-mini-cart__empty-message empty a-center">' . esc_html__('No products in the cart.', 'royal') . '</p>';
        }
        

        if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
            do_action( 'woocommerce_widget_shopping_cart_before_buttons' );
          ?>
			<p class="small-h pull-left"><?php echo esc_html__('Cart Subtotal', 'royal'); ?></p>
			<span class="big-coast pull-right">
				<?php echo WC()->cart->get_cart_subtotal(); ?>
			</span>
			<div class="clearfix"></div>
			<div class='bottom-btn'>
				<a href="<?php echo wc_get_cart_url(); ?>" class='btn text-center border-grey'><?php echo esc_html__('View Cart', 'royal'); ?></a>
				<a href="<?php echo wc_get_checkout_url(); ?>" class='btn text-center big filled'><?php echo esc_html__('Checkout', 'royal'); ?></a>
			</div>
			
            <?php

        }


?>
		</div>
<?php


	}
}

if(!function_exists('et_support_multilingual_ajax')) {
	add_filter('wcml_multi_currency_is_ajax', 'et_support_multilingual_ajax');
	function et_support_multilingual_ajax($functions) {
		$functions[] = 'et_woocommerce_add_to_cart';
		return $functions;
	}
}

// **********************************************************************// 
// ! New AJAX add to cart action
// **********************************************************************// 
add_action('wp_ajax_et_woocommerce_add_to_cart', 'et_woocommerce_add_to_cart');
add_action('wp_ajax_nopriv_et_woocommerce_add_to_cart', 'et_woocommerce_add_to_cart');

if(!function_exists('et_woocommerce_add_to_cart')) {
	function et_woocommerce_add_to_cart() {
		ob_start();

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$variation_id = $variation = '';
		if(isset($_POST['variation_id']) && $_POST['variation_id'] != '') {
			$variation_id = $_POST['variation_id'];
		}
		if(isset($_POST['variation']) && is_array($_POST['variation'])) {
			$variation = $_POST['variation'];
		}

		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			$data = array(
				'error' => false,
			);

		} else {

			header( 'Content-Type: application/json; charset=utf-8' );

			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error' => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
			);
		}

		echo json_encode( $data );

		die();
	}	
}
apply_filters('woocommerce_product_subcategories_hide_empty', false); 
