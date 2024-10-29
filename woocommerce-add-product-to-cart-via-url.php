<?php
/*
Plugin Name:  Add Product To Cart Via URL
Plugin URI:   https://betterdeveloperdocs.com/woocommerce-add-product-to-cart-via-url/
Description:  Allows a CMS users (eg shop admin) to create a URL (for WooCommerce only) with specific product(s) and quantity info. When clicked by a user this URL will load those products into the users cart and take them to the checkout page automatically. Works for simple, grouped and variable products.
Version:      2.0
Author:       Better Developer Docs 
Author URI:   https://betterdeveloperdocs.com/about/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  WooCommerce
Domain Path:  /languages
*/ 

define('WCAD_PLUGIN_DIR', plugin_dir_url(realpath(__FILE__)));
// include_once(WCAD_PLUGIN_DIR . 'includes/utilityClasses.php');

$allowedElements = array(
	'a' => array(
		'href' => array(),
		'title' => array(),
		'class' => array(),
		'id' => array(),
		'data-index' => array(),
	),
	'select' => array(),
	'div' => array(
		'class' => array(),
		'id' => array(),
	),
	'form' => array(
		'class' => array(),
		'id' => array(),
		'autocomplete' => array(),
	),
	'p' => array(
		'class' => array(),
		'id' => array(),
	),
	'select' => array(
		'class' => array(),
		'id' => array(),
		'data-search' => array(),
	),
	'option' => array(
		'class' => array(),
		'id' => array(),
		'value' => array(),
		'data-children' => array(),
		'data-variations' => array(),
		'data-name' => array(),
		'data-attributevalue' => array(),
		'data-variationid' => array(),
	),
	'input' => array(
		'class' => array(),
		'id' => array(),
		'placeholder' => array(),
		'placeholder' => array(),
		'type' => array(),
		'value' => array(),
	),
	'h2' => array()
);
define('WCAD_ALLOWED_ELEMENTS', $allowedElements);

function wcad_custom_register_scripts() {
	wp_enqueue_style( 'wcad-css', WCAD_PLUGIN_DIR .'assets/css/wcad.css', '', '1.0', false );
}
add_action( 'wp_enqueue_scripts', 'wcad_custom_register_scripts' );

// Add the shortcode function so it recognised by the WP System
add_shortcode('wc-cart-url-form', 'wcad_product_url_form');
	
// this is the shortcode function
function wcad_product_url_form() {

  	// lets do some basic checks first
  	if (!is_user_logged_in()) {
	    $output = '<p>You need to be logged into the WP CMS to view this page</p>';
	    return wp_kses($output, WCAD_ALLOWED_ELEMENTS);
	}

	if (!class_exists('WooCommerce')) {
	    $output = '<p>WooCommerce is not installed AND activated - install and create some products before attempting to use.</p>';
	    return wp_kses($output, WCAD_ALLOWED_ELEMENTS);
	}

	$output = adtcView::productForm('simple');
	$output .= adtcView::productForm('grouped');
	$output .= adtcView::productForm('variable');

	return wp_kses($output, WCAD_ALLOWED_ELEMENTS);
}

// Include WP Code to support AJAX
add_action( 'wp_enqueue_scripts', 'wcad_producturlform_ajax_enqueue' );
	
function wcad_producturlform_ajax_enqueue() {
	// Enqueue javascript on the frontend.
	wp_enqueue_script(
		'wcad_producturlform-ajax-script',
		WCAD_PLUGIN_DIR . 'assets/js/wcad.js',	
		array('jquery')
	);

	// The wp_localize_script allows us to output the ajax_url path for our script to use.
	wp_localize_script(
		'wcad_producturlform-ajax-script',
		'wcad_producturlform_ajax_obj',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce('ajax-nonce')
		)
	);
}

add_action( 'wp_ajax_wcad_producturlform_ajax_request', 'wcad_producturlform_ajax_request' );
   	
// If the user clicks the button with '+' symbol this function is called and adds another input and quantity set of fields for them to select another product.
function wcad_producturlform_ajax_request() {

	// lets sanitize and do some basic checks 
	$num_selects = sanitize_text_field($_REQUEST['num_selects']);
	$productType = sanitize_text_field($_REQUEST['productType']);
   	if(!isset($num_selects)){
    	print_r(wp_kses('<p>The number of select field (num_selects) is not set and therefore we cannot proceed with the AJAX call. Check the form is not mal formed.</p>', WCAD_ALLOWED_ELEMENTS));
    	die();
    }

    $args = array(
	    'type' => $productType,
	    'limit' => -1
	);
	$products = wc_get_products($args);

	$output = array();
	$output = '<div class="csetRow prod_qty_set" id="wcad_prod_qty_set_' . $num_selects . '"><select id="wcad_prod_url_select_' . $num_selects . '" class="prod_url_select" placeholder="Search for product of choice...">';
	$output .= '<option value="0">Search for product of choice...</option>';
	foreach( $products as $product ){
		
		if( $productType == 'simple' ){

			// /checkout/?productID=635:1,
            // /checkout/?productID=635:1,6452:2
			$output .= '<option value="' . esc_attr($product->get_id()) . '">' . esc_attr($product->name) . '</option>';
		
		}
		// elseif( $productType == 'grouped' ){

			// not allowing multiple rows for grouped products at this time
			// /checkout/?productID=6459&quantity[6452]=10&quantity[635]=10
			// $childProducts = '';
			// foreach($product->get_children() as $groupedProduct){
			// 	$childProducts .= $groupedProduct . ',';
			// }
			// $output .= '<option data-children='.$childProducts .' value="' . esc_attr($product->get_id()) . '">' . esc_attr($product->name) . '</option>';

		// }elseif( $productType == 'variable' ){

			// not allowing multiple rows for grouped products at this time
			// $variations = $product->get_available_variations('array');
			// $variationAttribute = '';
			// foreach($variations as $variation){
			// 	$variationAttribute .= $variation.',';
			// }
			
			// /checkout/?productID=10519&variation_id=10520&quantity_var=3&attribute_pa_colour=Blue&attribute_pa_size=L
			// $output .= '<option data-variations="'.$variationAttribute.'" value="' . esc_attr($product->get_id()) . '">' . esc_attr($product->name) . '</option>';
		// }
	}

	$output .= '</select><input placeholder="Quantity" type="number" class="prod_url_qty" value="1"><a href="'. '#'.'" class="wcad_remove_input" data-index="' . esc_attr($num_selects) . '">x</a>';
	
	print_r(wp_kses($output, WCAD_ALLOWED_ELEMENTS));
    die();
}

function wcad_add_multiple_products_to_cart( $url = false ) {

	// sanitize our request variables
	if(isset($_REQUEST['productID'])){
		$productID = sanitize_text_field($_REQUEST['productID']);
	}
	
	if(isset($_REQUEST['quantity'])){
		$groupedProducts = array_map('sanitize_text_field', $_REQUEST['quantity']);
	}

	if(isset($_REQUEST['variation_id'])){
		$variableProductID = sanitize_text_field($_REQUEST['variation_id']);
		$quantityVariableProduct = sanitize_text_field($_REQUEST['quantity_var']);
		$variableProductAttributes = array();
		foreach($_REQUEST as $key => $value){
			$containsAttributeValue = strpos('attribute', $key);
			if($containsAttributeValue !== false){
				// then we have some attributes
				$variableProductAttributes[$key] = $value;
			}
		}
	}

	// Make sure WC is installed, and productID query arg exists, and contains at least one comma.
	if ( 
		! class_exists( 'WC_Form_Handler' ) || 
		empty( $productID )
	){
		return;
	}

	// Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
	remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

	if(!empty($variableProductID)){
		// Process variable products url if it is present
		// https://yoursite.com/checkout/?productID=10519&variation_id=10520&quantity_var=3&attribute_pa_colour=Blue&attribute_pa_size=L
		WC()->cart->add_to_cart( $productID, $quantityVariableProduct, $variableProductID, $variableProductAttributes);
	}

	if(!empty($groupedProducts)){
		// Process grouped products url if it is present
		// https://yoursite.com/checkout/?productID=6459&quantity[6452]=10&quantity[635]=10
		foreach($groupedProducts as $key => $value){
			WC()->cart->add_to_cart( $key, $value );
		}
	}

	if(!empty($productID)){
		// process single and multiple regular products
		$product_ids = explode( ',', $productID );
		foreach ( $product_ids as $id_and_quantity ) {
			
			// single product and qty combo URL: https://yoursite.com/checkout/?productID=635:1,
			// multiple products and qty combo URL: https://yoursite.com/checkout/?productID=635:1,6452:2
			$id_and_quantity = explode( ':', $id_and_quantity );
			$product_id = $id_and_quantity[0];
			$quantity = $id_and_quantity[1];
			WC()->cart->add_to_cart( $product_id, $quantity );
		}
	}
	return;
}

// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action( 'wp_loaded', 'wcad_add_multiple_products_to_cart', 15 );

function addVariations_ajax_enqueue() {

	// Enqueue javascript on the frontend.
	wp_enqueue_script(
		'addVariations-ajax-script',
		WCAD_PLUGIN_DIR . 'assets/js/addVariation.js',	
		array( 'jquery' )
	);

	// The wp_localize_script allows us to output the ajax_url path for our script to use.
	wp_localize_script(
		'addVariations-ajax-script',
		'addVariations_ajax_obj',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'addVariations-nonce' )
		)
	);
}
add_action( 'wp_enqueue_scripts', 'addVariations_ajax_enqueue' );

function addVariations_ajax_request() {

    if ( isset( $_REQUEST ) ) {

        $productID = sanitize_text_field($_REQUEST['productID']);
        $product = wc_get_product($productID);
		$variations = $product->get_available_variations();
		$output .= '';
		foreach($variations as $variation){

			$output .= '<select>';
			$output .= '<option value="0">Select attribute</option>';
			$variationID = $variation['variation_id'];

			foreach($variation['attributes'] as $key => $value){
				$output .= '<option data-variationid="'.esc_attr($variationID).'" data-attributeValue="'.esc_attr($value).'" value="'.esc_attr($key).'">'.esc_attr($value).'</option>';
			}
			$output .= '</select>';
		}     
    }
    
    print_r(wp_kses($output, WCAD_ALLOWED_ELEMENTS));
    // Always die in functions echoing ajax content
   die();
}
 
add_action( 'wp_ajax_addVariations_ajax_request', 'addVariations_ajax_request' );
// If you wanted to also use the function for non-logged in users (in a theme for addVariations)
// add_action( 'wp_ajax_nopriv_addVariations_ajax_request', 'addVariations_ajax_request' );

	class adtcUtilities{

		// public static function debug($stuff){
			// echo '<pre>';
			// print_r($stuff);
			// echo '</pre>';
		// }
	}

	class adtcEngine{

	}

	class adtcView{

		public static function productForm($productType){

		    $args = array(
			    'type' => $productType,
			    'limit' => -1
			);

			$products = wc_get_products($args);

	    	$options = '';
		    foreach( $products as $product ){

		        if( $productType == 'simple' ){

					$options .= '<option value="' . esc_attr($product->get_id()) . '">' . esc_attr($product->name) . '</option>';
				
				}elseif( $productType == 'grouped' ){

					$childProducts = '';
					foreach($product->get_children() as $groupedProduct){
						$childProducts .= $groupedProduct . ',';
					}
					$options .= '<option data-children='.$childProducts .' value="' . esc_attr($product->get_id()) . '">' . esc_attr($product->name) . '</option>';
				}elseif( $productType == 'variable' ){

					$variations = $product->get_available_variations('array');
					$variationAttribute = '';
					foreach($variations as $variation){
						$variationAttribute .= $variation['variation_id'].',';
					}
					
					// /checkout/?productID=10519&variation_id=10520&quantity_var=3&attribute_pa_colour=Blue&attribute_pa_size=L
					$options .= '<option data-variations="'.esc_attr($variationAttribute).'" value="' . esc_attr($product->get_id()) . '">' . esc_attr($product->name) . '</option>';
				}
		    }

			$parameters = array (
				'dropDownOptions' => $options,
				'productType' => $productType 
			);

			$template = new adtcTemplate( __DIR__ . '/templates/', $parameters);
			return $template->render('productsForm.php', array());
		}		
	}

	/**
	 * Class Template
	 */
	class adtcTemplate
	{
		/**
		 * @var string
		 */
		private $path;

		/**
		 * @var array
		 */
		private $parameters = [];

		/**
		 * Template constructor.
		 * @param string $path
		 * @param array $parameters
		 */
		public function __construct(string $path, array $parameters = []){
			$this->path = rtrim($path, '/').'/';
			$this->parameters = $parameters;
		}

		/**
		 * @param string $view
		 * @param array $context
		 * @return string
		 * @throws \Exception
		 */
		public function render(string $view, array $context = []): string{
			if (!file_exists($file = $this->path.$view)) {
				throw new \Exception(sprintf('The file %s could not be found.', $view));
			}
			extract(array_merge($context, ['template' => $this]));
			ob_start();
			include ($file);
			return ob_get_clean();
		}

		/**
		 * @param string $key
		 * @return mixed
		 */
		public function get(string $key){
			return $this->parameters[$key] ?? null;
		}
	}
