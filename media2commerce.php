<?php

/*
Plugin Name: Media2Commerce
Plugin URI: http://8mediacentral.com/developments/
Description: Automatically creates WooCommerce products from media images
Author: 8MediaCentral
Version: 1.0
Author URI: http://8MediaCentral.com
*/

/**
* Creates a product from media that is uploaded
* adds a new meta field to the media called product_link, which can be used by theme
* syncs categories from the post the media is attached to (though currently commented out for compatibility)
*
*/

//a default fallback price, do not set here set in Settings->Media2Commerce Settings
define( 'M2C_DEFAULT_PRICE', '999.99' );
define( 'M2C_DEFAULT_SCALING', '100' );
define( 'M2C_DEFAULT_QUALITY', '100' );


DEFINE('M2C_PLUGIN_TEXTDOMAIN', 'media2commerce');


DEFINE('M2C_PLUGIN_WEBSITE', 'http://8mediacentral.com/developments/plugins/media2commerce/');

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	exit;
}

//load the settings file
require_once('php/m2c-settings.php');


class media2commerce {

	//constructor
	function media2commerce(){
		add_action('admin_init', array($this, 'run_media2commerce'));
		//register taxonomies
		add_action('init', array($this, 'register_media2commerce_taxonomies'));
		register_activation_hook( __FILE__,  array( 'm2c_settings', 'm2c_add_options'));
	}

	/**
	* run the plugin
	*/
	function run_media2commerce(){
		if(is_plugin_active('woocommerce/woocommerce.php')){
			//add action for when media is uploaded
			add_action( 'add_attachment', array( $this, 'create_product_from_post_id' ) );
			//add action for when media is updated
			add_action( 'edit_attachment', array( $this, 'create_product_from_post_id' ) );
			//add action on post/post to update attached media
			add_action ( 'save_post', array( $this, 'update_post_media' ) );	
        	//add the filter for the settings link on plugin display
        	add_filter( 'plugin_action_links', array( $this, 'wpipp_plugin_settings_link'), 10, 2 );
		} else {
			add_action('admin_notices', array( $this, 'woocommerce_required_message'));
		}
	}


	/**
	* register required taxonomies
	*/
	function register_media2commerce_taxonomies(){
		if(post_type_exists( 'product' ) ){
			register_taxonomy( 'pa_type', 'product' );
		}
	}

	/**
	* display a message if woocommerce is not installed
	*/
	function woocommerce_required_message(){
		?>
		<div id = "message" class = "error">
			<p>The Media2Commerce plugin requires WooCommerce to work, ensure WooCommerce is installed and activated in the plugins list</p>
		</div><!-- end message -->
		<?php
	}


	/**
	* update the media on a post if it's not a revision
	*/
	function update_post_media ( $post_id ){

		//check not post revision
		if ( !wp_is_post_revision( $post_id ) ) {
			$args = array(
				'post_type' => 'attachment',
				'numberposts' => null,
				'post_status' => null,
				'post_mime_type' => 'image',
				'post_parent' => $post_id
			); 
			$attachments = get_posts($args);

			foreach( $attachments as $image ){
				$this->create_product_from_post_id($image->ID);
			} 

		}
					
	}


	/**
	* create a product using the media post (attachment) id
	*/
	function create_product_from_post_id($post_id){		

		if(class_exists('m2c_settings')){
			$m2c_settings_init = new m2c_settings();
			$variations_arr = $m2c_settings_init->get_types_as_array();
		}
		else {
			$variations_arr = array( array( "type_name"=> "Standard", "type_price"=> "9.99", "scaling"=> "20", "quality"=>   "100" ),
		 							array(  "type_name"=> "Large", "type_price"=> "29.99", "scaling"=> "50", "quality"=> "100" ),
									array( "type_name"=> "Super XL", "type_price"=>"49.99", "scaling"=> "100", "quality"=> "100" ) );
		}


		if(count($variations_arr)>0){
			//the first variation price will be product price, scaling, and quality
			$type_name = array_key_exists('type_name', $variations_arr[0])? $variations_arr[0]['type_name']: 'default';
			$product_price = array_key_exists('type_price', $variations_arr[0])? $variations_arr[0]['type_price']: M2C_DEFAULT_PRICE;
			$type_scaling = array_key_exists('scaling', $variations_arr[0])? $variations_arr[0]['scaling']: M2C_DEFAULT_SCALING;
			$type_quality = array_key_exists('quality', $variations_arr[0])? $variations_arr[0]['quality']: M2C_DEFAULT_QUALITY;
		} else {
			$product_price = M2C_DEFAULT_PRICE;
		}


		//get the attachment post
		$attachment_post = get_post($post_id);


		//don't do anything if we can't get that post
		if(is_null($attachment_post)) return;


		//don't do anything if the post's MIME type isn't image/jpeg or image/png
		if( $attachment_post->post_mime_type != 'image/jpeg' && $attachment_post->post_mime_type != 'image/png' ) return;


		//assign the product name
		if($attachment_post->post_title != ""){
			$product_name = $attachment_post->post_title;
		} else {
			// assign a generic name
			$product_name = 'Product '.rand(1,1000); 
		}


		//assign the product caption/product excerpt
		if($attachment_post->post_excerpt != ""){
			$product_excerpt = $attachment_post->post_excerpt;
		} else {
			// assign a generic excerpt
			$product_excerpt = 'No product excerpt entered'; 
		}


		//assign the product content
		if($attachment_post->post_content != ""){
			$product_content = $attachment_post->post_content;
		} else {
			// assign a generic content
			$product_content = 'No product content entered'; 
		}	

		//assign the author
		if($attachment_post->post_author != 0){
			$product_author = $attachment_post->post_author;
		} else {
			// assign a generic author
			$product_author = 1; 
		}	


		//attach the url
		if($attachment_post->guid != ""){
			$download_url = $attachment_post->guid;
		} else {
			// assign no url
			$download_url = ''; 
		}	

		//don't do this step if no variations, i.e. variations ==  1)
		if(count($variations_arr)==1){

			//the stored image reutnrs the download url
			$download_url = $this->store_image($attachment_post->guid, $type_name, $type_scaling, $type_quality);
		}

		//set the download link - query what happens when domain changes?
		$download_url_md5 = md5($download_url);
		$download = array($download_url_md5=>$download_url);	

		

		$product = array(
			'post_title' => $product_name,
			'post_excerpt' => $product_excerpt,
			'post_content' => $product_content,
			'post_status' => 'publish',
			'post_author' => $product_author,
			'post_type'		=> 'product'
		);

		$existing_product_link = get_post_meta($post_id, 'product_link', true);



		
		if(empty($existing_product_link)){
			//add the product to catalog
			$new_product_id = wp_insert_post( $product );	
			add_post_meta($post_id, 'product_link', $new_product_id);
			$product_id = $new_product_id;
		} else {
			//update the product catalog
			$product['ID'] = $existing_product_link;
			wp_update_post ($product);
			$product_id = $existing_product_link;
		}
		
		
		//update the orginal post meta data
		
		// Add meta
		//update_post_meta( $product_id, '_edit_last', '');
		//update_post_meta( $product_id, '_edit_lock', '');
		update_post_meta( $product_id, '_thumbnail_id', $post_id);
		update_post_meta( $product_id, '_visibility', 'visible');
		update_post_meta( $product_id, '_stock_status', 'instock');
		//update_post_meta( $product_id, 'total_sales', '');
		update_post_meta( $product_id, '_downloadable', 'yes');
		update_post_meta( $product_id, '_virtual', 'yes');
		//update_post_meta( $product_id, '_product_image_gallery', '');
		update_post_meta( $product_id, '_regular_price', $product_price);
		//update_post_meta( $product_id, '_sale_price', '');
		//update_post_meta( $product_id, '_tax_status', '');
		//update_post_meta( $product_id, '_tax_class', '');
		//update_post_meta( $product_id, '_purchase_note', '');
		//update_post_meta( $product_id, '_featured', '');
		//update_post_meta( $product_id, '_weight', '');
		//update_post_meta( $product_id, '_length', '');
		//update_post_meta( $product_id, '_width', '');
		//update_post_meta( $product_id, '_height', '');
		//update_post_meta( $product_id, '_sku', '');
		//update_post_meta( $product_id, '_product_attributes', '');
		//update_post_meta( $product_id, '_sale_price_dates_from', '');
		//update_post_meta( $product_id, '_sale_price_dates_to', '');
		update_post_meta( $product_id, '_price', $product_price);
		update_post_meta( $product_id, '_sold_individually', 'yes');
		//update_post_meta( $product_id, '_stock', '');
		//update_post_meta( $product_id, '_backorders', '');
		//update_post_meta( $product_id, '_manage_stock', '');
		update_post_meta( $product_id, '_file_paths', $download);
		//update_post_meta( $product_id, '_download_limit', '');


		/* do product variations */

		//if more than one variation
		if(count($variations_arr)>1){
				//santise variaton names
				
				$variations_santized = array();
				$variations_unsantized = array();
				foreach ($variations_arr as $variation_key=>$variation_value) {
					
					array_push($variations_santized, sanitize_title($variation_value['type_name']));
					array_push($variations_unsantized, $variation_value['type_name']);
					//not required unless taxonomy
					//wp_insert_term( $variation, 'type' );	
				}


				//add them as terms
				if(count($variations_santized)>0){
					//not required unless taxonomy
					//wp_set_object_terms( $product_id, $variations_santized , 'pa_type');
					wp_set_object_terms ($product_id, 'variable', 'product_type');
				}
				


				$prices = array();

				$names = array();

				$counter = 1;
				foreach ($variations_arr as $variation_key=>$variation_value) {


					array_push($prices, $variation_value['type_price']);

					$product_variation = array(
						'post_title' => $product_name,
						'post_excerpt' => $product_excerpt,
						'post_content' => $product_content,
						'post_status' => 'publish',
						'post_author' => $product_author,
						'post_type'		=> 'product_variation',
						'menu_order'		=> $counter,
						'post_parent'	=> $product_id
					);

					$variation_id = null; 
					
					//test that variation doesn't exist before creating it otherwise update it
					$existing_variations = get_posts(array('post_parent'=>$product_id));

					


					if(isset($existing_variations)){
						foreach ($existing_variations as $variation_name) {
							$product_variation_meta = get_post_meta($variation->ID, 'attribute_pa_type', true);
							if(isset($product_variation_meta) and $product_variation_meta = $variation_name){
								$variation_id = $variation->ID;
							}
						}
					}
					

					//if the variation_id isn't set, create the variation
					if(!isset($variation_id)){
						$variation_id = wp_insert_post($product_variation);
					}

					//update post
					$product_variation_update = array(
						'ID' => $variation_id,
						'post_title' => "Variation #" . $variation_id . " of " . $product_name,
						'post_name' =>	"product-" . $product_id . "-variation-" . $counter,

						
					);

					wp_update_post ($product_variation_update);


					//set the values of the variation for the different types
					$type_name = array_key_exists('type_name', $variation_value)? $variation_value['type_name']: 'default';
					$type_scaling = array_key_exists('scaling', $variation_value)? $variation_value['scaling']: M2C_DEFAULT_SCALING;
					$type_quality = array_key_exists('quality', $variation_value)? $variation_value['quality']: M2C_DEFAULT_QUALITY;

					

					//set the download urls
					$download_url = $this->store_image($attachment_post->guid, $type_name, $type_scaling, $type_quality);

					//set the download link - query what happens when domain changes?
					$download_url_md5 = md5($download_url);
					$download = array($download_url_md5=>$download_url);


					//update post meta
					//update_post_meta( $variation_id, '_edit_last', '');
					//update_post_meta( $variation_id, '_edit_lock', '');
					//update_post_meta( $variation_id, '_thumbnail_id', $post_id);
					update_post_meta( $variation_id, '_visibility', 'visible');
					update_post_meta( $variation_id, '_stock_status', 'instock');
					//update_post_meta( $variation_id, 'total_sales', '');
					update_post_meta( $variation_id, '_downloadable', 'yes');
					update_post_meta( $variation_id, '_virtual', 'yes');
					//update_post_meta( $variation_id, '_product_image_gallery', '');
					update_post_meta( $variation_id, '_regular_price', $variation_value['type_price']);
					//update_post_meta( $variation_id, '_sale_price', '');
					//update_post_meta( $variation_id, '_tax_status', '');
					//update_post_meta( $variation_id, '_tax_class', '');
					//update_post_meta( $variation_id, '_purchase_note', '');
					//update_post_meta( $variation_id, '_featured', '');
					//update_post_meta( $variation_id, '_weight', '');
					//update_post_meta( $variation_id, '_length', '');
					//update_post_meta( $variation_id, '_width', '');
					//update_post_meta( $variation_id, '_height', '');
					//update_post_meta( $variation_id, '_sku', '');
					//update_post_meta( $variation_id, '_product_attributes', '');
					//update_post_meta( $variation_id, '_sale_price_dates_from', '');
					//update_post_meta( $variation_id, '_sale_price_dates_to', '');
					update_post_meta( $variation_id, '_price', $variation_value['type_price']);
					update_post_meta( $variation_id, '_sold_individually', 'yes');
					//update_post_meta( $variation_id, '_stock', '');
					//update_post_meta( $variation_id, '_backorders', '');
					//update_post_meta( $variation_id, '_manage_stock', '');
					update_post_meta( $variation_id, '_file_paths', $download);
					//update_post_meta( $variation_id, '_download_limit', '');
					update_post_meta( $variation_id, 'attribute_type', sanitize_title($variation_value['type_name']));


					//array_push ($names, sanitize_title($variation_value['type_name']));
					array_push ($names, $variation_value['type_name']);


					$counter++;
				}

				//update the original parent product

				$product_attributes = get_post_meta($product_id, '_product_attributes', true);
					if(!isset($product_attributes) or $product_attributes==""){
						$product_attributes = array();
					}

				$names_str = implode(' | ', $names);
				$product_attributes['type'] = array(
																'name' => "Type",
																'value' => $names_str,
																'position' => "0",
																'is_visible' => 0,
																'is_variation' => 1,
																'is_taxonomy' => 0
														);

				update_post_meta( $product_id, '_product_attributes', $product_attributes);

				//test
				//wp_set_object_terms( $post_id, 'XL', 'pa_size' ); 

				//lowest variation price
				//$lowest_price = min(array_values($variations));
				$lowest_price = min(array_values($prices));
				update_post_meta ($product_id, '_min_variation_price', $lowest_price);

				/* end product variations */
		}		

		
		//get the attached post
		$parent = $attachment_post->post_parent;
		/* skip this for the time being - todo - implement category copying if required
		if($parent>0){
			//get the terms 
			$terms = wp_get_post_terms($parent, 'category');
			
			foreach ($terms as $term) {

				//create the new taxonomy for product category (or just get it if already created)				
				$parent_term = term_exists('photography', 'product_cat');				
			
				if(term_exists($term->name, 'product_cat') > 0)	{
					$new_taxonomy = term_exists($term->name, 'product_cat');
				} else {
					$args = array('parent'=>$parent_term, 'description'=>$term->description);
					$new_taxonomy = wp_insert_term($term->name, 'product_cat', $args);	
				}

				$new_terms = wp_set_post_terms( $product_id,   $term->term_id, 'product_cat', false );
			}

		}
		*/		

	}

	/**
	* manipulate and store an image and return the url of the newly created image
	*/
	function store_image($guid = null, $type_name = 'defaulttest', $scaling = '100', $quality = '100'){
		$args = array(
		    'mime_type' => 'image/png',
		    'methods' => array(
		        'rotate',
		        'resize',
		        'save'
		    )
		);



		//check everything is ok
		if($guid==NULL){
			echo "GUID URL is currently null";
			return false;
		}
		if(intval($scaling)>100){
			echo "Scaling cannot be larger than 100, currently " . $scaling;
			return false;
		}
		if(intval($scaling)<0){
			echo "Scaling cannot be smaller than 0, currently " . $scaling;
			return false;
		}
		if(intval($quality)>100){
			echo "Quality cannot be larger than 100, currently " . $quality;
			return false;
		}
		if(intval($quality)<0){
			echo "Quality cannot be smaller than 0, currently " . $quality;
			return false;
		}


		$img_editor_test = wp_image_editor_supports($args);
		if ($img_editor_test !== false) {
			//set up directories
			$upload_dir = wp_upload_dir();
 			$upload_basepath = $upload_dir['path']; 
 			$upload_baseurl = $upload_dir['baseurl']; 
 			$upload_url = $upload_dir['url']; 
 			$image_url = $guid ;
 			$img_path = $upload_basepath . '/' . basename($image_url);
 			$img_path_parts = pathinfo($img_path);
 			$image_name_without_ext = $img_path_parts['filename'];
 			$image_ext = $img_path_parts['extension'];
 			$safe_type_name = sanitize_title($type_name);

 			

 			//set new filename
 			$new_file_name = $upload_basepath . '/' . $image_name_without_ext . '-' . $safe_type_name . '.' . $image_ext;
 			$new_file_url =  $upload_url . '/' . $image_name_without_ext . '-' . $safe_type_name . '.' . $image_ext;

 			//check it doesn't exist
 			while(file_exists($new_file_name)){
 				$new_file_name  = $upload_basepath . '/' . $image_name_without_ext . '-' . $safe_type_name . '-' . $this->generate_random_string() . '.' . $image_ext;
 				$new_file_url =  $upload_url . '/' . $image_name_without_ext . '-' . $safe_type_name . '-' . $this->generate_random_string() . '.' . $image_ext;
 			}


 			//save with the new filename
 			//echo "type name -> " . $type_name . "<br/>";
 			//echo "new name -> " . $new_file_name . "<br/>";



 			//echo "imageurl->". $image_url;
 			//echo "<br/>";
 			//echo "imagepath->". $img_path;
 			//echo "<br/>";

 			//open the image
		    $image = wp_get_image_editor( $img_path);
		    if ( ! is_wp_error( $image ) ) {

		    	//for some reason this hasn't been implemented in the WordPress core yet, fallback to PHP
		    	//$image_dimensions = $image->get_dimensions();
		    	$size = getimagesize($img_path);

		    	if($size==FALSE) return false;

		    	$width = $size[0];
		    	$height = $size[1];

		    	$scaling_factor = intval($scaling) / 100;

		    	$new_width = $width * $scaling_factor;
		    	$new_height = $height * $scaling_factor;


			    $image->resize( $new_width, $new_height, false);
			    $image->set_quality(intval($quality));
			    $img_result = $image->save( $new_file_name );
			    //var_dump($img_result);	

			    //on successs return url
			    return $new_file_url;
			} else {
				echo "Error Saving Image";
				return false;
			}
		}
	}


	/**
	* generate random string
	*/
	function generate_random_string($lngth = 12) {
	    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $random_string = '';
	    for ($i = 0; $i < $lngth; $i++) {
	        $random_string .= $chars[rand(0, strlen($chars) - 1)];
	    }
    return $random_string;
}



	/**
    * Add the settings shortcut to plugin page
    */
    function wpipp_plugin_settings_link($links, $file) { 
        $plugin_name = plugin_basename(__FILE__); 
        if($file == $plugin_name){
            $settings_url = '<a href="options-general.php?page=m2c_display">' . __("Settings", M2C_PLUGIN_TEXTDOMAIN) . '</a>'; 
            //add link
            array_unshift($links, $settings_url);                
        }
        return $links; 
    } 


} //end class

$media2commerce_init = new media2commerce();

?>