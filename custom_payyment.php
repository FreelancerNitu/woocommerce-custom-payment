<?php
/*
Plugin Name: Custom Payment Gateway
Description: Adds a custom payment gateway to WooCommerce
Version: 1.0
Author: Nitu Barmon
*/

add_action('plugins_loaded', 'init_custom_payment_gateway');

function init_custom_payment_gateway() {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class Custom_Payment_Gateway extends WC_Payment_Gateway {

        public function __construct() {
            $this->id = 'custom_payment_gateway';
            $this->method_title = __('Custom Payment Gateway', 'woocommerce');
            $this->method_description = __('This is a custom payment gateway for WooCommerce.', 'woocommerce');
            $this->title = __('Custom Payment Gateway', 'woocommerce');
            $this->icon = ''; // Set an image for the payment gateway if you want to.
            $this->has_fields = true; // Set it to true if your payment gateway has fields to capture payment information.

            $this->init_form_fields(); // Call the init_form_fields() method to initialize the payment gateway's fields.
            $this->init_settings(); // Call the init_settings() method to initialize the payment gateway's settings.

            // Define the payment gateway's configuration settings.
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            // Hook the payment gateway's process_payment() method to the checkout process.
            add_action('woocommerce_checkout_process', array($this, 'process_payment'));

            // Hook the payment gateway's receipt_page() method to the thank you page.
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'receipt_page'));
        }

        // Define the payment gateway's fields.
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable Custom Payment Gateway', 'woocommerce'),
                    'default' => 'no',
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Custom Payment Gateway', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Description', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Pay with your custom payment gateway.', 'woocommerce'),
                ),
            );
        }

        // Define the payment gateway's payment processing method.
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            // Mark the order as on-hold since payment is being processed.
            $order->update_status('on-hold', __('Payment is being processed using the Custom Payment Gateway.', 'woocommerce'));

            // Reduce stock levels for all products in the order.
            wc_reduce_stock_levels($order_id);

            // Empty the cart.
            WC()->cart->empty_cart();

            // Redirect the user to the thank you page.
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }

        // Define the payment gateway's thank you page display method.
        public function receipt_page($order_id) {
          $order = wc_get_order($order_id);
          echo '<p>' . __('Thank you for your order!', 'woocommerce') . '</p>';
          }
          
        }
}