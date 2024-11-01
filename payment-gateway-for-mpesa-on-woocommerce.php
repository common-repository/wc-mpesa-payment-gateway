<?php

// In order to prevent direct access to the plugin
 defined('ABSPATH') or die("Goals are good for setting a direction. But I am better off not called directly!");

 /*
 Plugin Name: EeroPay
 Plugin URI: https://aey-group.com/
 Description: Payment Gateway For MPESA on WooCommerce plugin enables you to link your MPESA Till/Paybil and accept payments through MPESA in your WooCommerce Website.
 Version: 1.0.1
 License: GPL-2.0+
 Author: Aey Dev 
 Author URI: https://aey-group.com
 Text domain: aey-group.com
 */



//Add the css 
add_action( 'wp_enqueue_scripts', 'PGFMOW_Payment_Gateway_Load_Style' );
 
function PGFMOW_Payment_Gateway_Load_Style() {
    wp_enqueue_style( 'Comfortaa Font', 'https://fonts.googleapis.com/css?family=Comfortaa');
    wp_enqueue_style( 'Style', plugin_dir_url(__FILE__) . '/payment-gateway-for-mpesa-on-woocommerce.css',false,'1.0','all');

 }

add_action('plugins_loaded', 'PGFMOW_Payment_Gateway_Init');

add_action( 'init', function() {
    add_rewrite_rule( '^/scanner/?([^/]*)/?', 'index.php?scanner_action=1', 'top' );
});

function PGFMOW_Payment_Gateway_Init()
{
    if (!class_exists('WC_Payment_Gateway'))
        return;

    class WC_Gateway_PGFMOW extends WC_Payment_Gateway
    {
        /**
         *  Plugin constructor
         */

        public function __construct()
        {

            // Basic settings
            $this->id = 'pgfmow';
            $this->has_fields = false;

            $this->method_title = __('EeroPay', 'woocommerce');
            $this->method_description = __('Enable customers to make payments to your business through M-Pesa PayBill or Till Number');

            // load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Define variables set by the user in the admin section
            $this->title = 'EeroPay';
            $this->instructions = 'Connect your online business or e-commerce store with Safaricom MPESA';
            $this->mer = $this->get_option('mer');

            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            } else {
                add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));

            }

        }



        /**
         *Initialize form fields that will be displayed in the admin section.
         */
        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable EeroPay', 'woocommerce'),
                    'default' => 'yes'
                ),
                'api_token' => array(
                    'title' => __('EeroPay api token', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Your EeroPay api token', 'woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                ),

            );

        }


        /**
         * Generates the HTML for admin settings page
         */

        public function admin_options()
        {

            /** 
             *The heading and paragraph below are the ones that appear on the Payment Gateway settings
             */

            echo '<h3>EeroPay Gateway</h3>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';

        }


        public function process_payment($order_id)
        {
            global $woocommerce;

            $order = new WC_Order($order_id);

            $tell = sanitize_text_field($order->get_meta( 'pgfmow_phone_number' ));

            if($tell == ''){
                wc_add_notice(__("Error,M-Pesa phone number not found.", 'woocommerce'), 'error');
                return;
            }
        
            $tell = str_replace("-", "", $tell);
        
            $tell = str_replace( array(' ', '<', '>', '&', '{', '}', '*', "+", '!', '@', '#', "$", '%', '^', '&'), "", $tell );

            $updatedMpesaPhone = "254".substr($tell, -9);

            $amount = $order->get_total();
            // $phone_number = $order->get_billing_phone();
            $phone_number = sanitize_text_field($updatedMpesaPhone);
            $callback_url = $this->get_return_url($order);

            global $woocommerce;
            $items = $woocommerce->cart->get_cart();

            $cart_products_objects = [];
            $downloadable_product_url = "null";

            foreach($items as $item => $values) { 
                $_product =  wc_get_product( $values['data']->get_id()); 
                $cart_products_objects[] = (object) [
                    //Sanitize all cart data before creating an array
                    "name" => sanitize_text_field($_product->get_title()),
                    "units" => sanitize_text_field($values['quantity']),
                    "price" => sanitize_text_field(get_post_meta($values['product_id'] , '_price', true) ? get_post_meta($values['product_id'] , '_price', true) : (double)$order->total),
                    "downloadable" => true,
                    "downloadable_url" => "https://nfts.com/bored-ape.png"
                    
                    ];

                    //If product is downloadable show download button
                    if ($_product->is_downloadable('yes')) {
                        foreach( $_product->get_downloads() as $key_download_id => $download ) {
                            ## Using WC_Product_Download methods (since WooCommerce 3)
                            $download_link = $download->get_file(); // File Url
                            $download_id   = $download->get_id(); // File Id (same as $key_download_id)
                            $downloadable_product_url = $download_link;
                        }
                    } 
                    
            } 

            $payload = array(
                "customer" =>
                        array(
                                "name" =>  sanitize_text_field($order->billing_first_name ? $order->billing_first_name :null) ." ". sanitize_text_field($order->billing_last_name? $order->billing_last_name :null),
                                "location" =>  sanitize_text_field($order->billing_city ? $order->billing_city :null),
                                "email" =>  sanitize_email($order->billing_email ? $order->billing_email : null),
                                "phone_number" =>  wc_sanitize_phone_number($phone_number ? $phone_number : null),
                            ),
                            "products" => $cart_products_objects ? $cart_products_objects : array( array( "name" => 'Product Name',"units" => '1',"price" => '1',"downloadable" => true, "downloadable_url" => 'https://nfts.com/bored-ape.png')),
                            "order" =>
                            array(
                                "delivery_fee" => sanitize_text_field((double)$order->shipping_total? (double)$order->shipping_total :0),
                                "discount_fee" => sanitize_text_field((double)$order->discount_total? (double)$order->discount_total :0),
                                "total" => sanitize_text_field((double)$order->total),
                                "order_id" => sanitize_text_field($order_id)
                            ),
                            "website" => array(
                                "url"  => sanitize_url(get_site_url( null, '', null )),
                                "callback_url" => sanitize_url(get_site_url( null, '', null )."/?wc-api=callback")
                            ),
            );

            $url = sanitize_text_field('http://api.eeropay.com:8080/api/v1.0/woocommerce/payment/mpesa');
            $api_token = sanitize_text_field($this->get_option( 'api_token' ));
            $header = array("Content-Type"=> "application/json","Authorization" => "Bearer ".$api_token);
            
            $args = array(
                'method'      => 'POST',
                'timeout'     => 100,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     =>  $header,
                'body'        => json_encode($payload),
            );

            $response = wp_remote_post( $url, $args );
            $decodedResp = json_decode(wp_remote_retrieve_body( $response ));

            // Mark order as on-hold
            $order->update_status('wc-processing'); 

            if($decodedResp->status_code != 200){
                // Mark order as on-hold
                $order->update_status( 'failed' );
                wc_add_notice( $decodedResp->message, 'error' );
                return;
                
            }

            // Update order meta with checkout request ID
            update_post_meta( $order_id, '_mpesa_checkout_request_id', $order_id );
            
            $order->update_status('wc-completed'); 

            // Remove cart
            $woocommerce->cart->empty_cart();

            // Return thank you page redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );

        }
    }
}

/**
 * Add Gateway to WooCommerce
 **/

function PGFMOW_Payment_Gateway_Store_Gateway_Class( $methods ) {
    $methods[] = 'WC_Gateway_PGFMOW';
    return $methods;
}

if(!add_filter( 'woocommerce_payment_gateways', 'PGFMOW_Payment_Gateway_Store_Gateway_Class' )){
    die;
}

add_action( 'woocommerce_gateway_description', 'PGFMOW_Payment_Gateway_Mpesa_Input' , 20, 2 );
function PGFMOW_Payment_Gateway_Mpesa_Input( $description, $payment_id ){

    if( 'pgfmow' === $payment_id ){
        ob_start();
        echo '
            <div class="pgfmow-form-container">
                <div id="pgfmow-mpesa-details">
                    <div class="pgfmow-field-container">
                        <div id="pgfmow-mpesa-dp">
                            <span>Phone Number</span>
                        </div>
                        <div  class="input" id="pgfmow-phonenumber-dp" >
                            <input class="input" id="pgfmow-phonenumber_input" value="254" type="number" name="pgfmow_phone_number" placeholder="254 700 000 000" isvalid="false" onKeyPress="if(this.value.length==12) return false;" required >
                        </div>
                    </div>
                </div>
            </div>';
    
        $description .= ob_get_clean(); 
        $required = esc_attr__( 'required', 'woocommerce' );
        ?>

        <script type="text/javascript">

            function isNumberKey(e) {
                var currentChar = parseInt(String.fromCharCode(e.keyCode), 10);
                if (!isNaN(currentChar)) {
                    var nextValue = $("#txthour").val() + currentChar; //It's a string concatenation, not an addition

                    if (parseInt(nextValue, 10) <= 12) return true;
                }

                return false;
            }
        </script>
        <?php 
    }

    return $description;
}

add_action( 'woocommerce_checkout_update_order_meta', 'PGFMOW_Payment_Gateway_Store_Mpesa_Field' );

function PGFMOW_Payment_Gateway_Store_Mpesa_Field( $order_id ){
    if( ! empty( $_POST[ 'pgfmow_phone_number' ] ) ) {
		update_post_meta( $order_id, 'pgfmow_phone_number', wc_clean( $_POST[ 'pgfmow_phone_number' ] ) );
	}
}

//Add x button that allows the user to remove items on the woocommerce checkout page
add_filter( 'woocommerce_cart_item_name', 'PGFMOW_Payment_Gateway_Remove_Item', 10, 3 );
function PGFMOW_Payment_Gateway_Remove_Item( $product_name, $cart_item, $cart_item_key ) {
    if ( is_checkout() ) {
        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

        $remove_link = apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
            '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">×</a>',
            esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
            __( 'Remove this item', 'woocommerce' ),
            esc_attr( $product_id ),
            esc_attr( $_product->get_sku() )
        ), $cart_item_key );
    
        return '<span>' .$remove_link . '</span> <span>' . esc_attr($product_name) . '</span>';
    }
    
    return $product_name;
}

/** 
*
* You are sitting in a shade today because someone
* decided to plant a tree a long time ago.
* Aey Dev
* dev@aey-group.com
*/

?>