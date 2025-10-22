<?php
/**
 * Plugin Name: WooCommerce VATID FREE (Block & Classic)
 * Description: Adds a VAT Number field to WooCommerce checkout (Classic & Block) and stores it in order meta as "vat_number". Includes debug logs, admin display, and emails.
 * Version: 0.0.1
 * Author: Janko Metelko
 * License: GPL2
 * Text Domain: wc-vatid-free
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enable debug logging helper
if ( ! function_exists( 'wc_vat_debug_log' ) ) {
    function wc_vat_debug_log( $message ) {
        if ( defined('WP_DEBUG') && WP_DEBUG ) {
            error_log( '[WC VAT Field] ' . $message );
        }
    }
}

class WC_VATID_free {
    public function __construct() {
        wc_vat_debug_log('Plugin initialized.');
        
        // Classic checkout: filter checkout fields
        add_filter( 'woocommerce_checkout_fields', [ $this, 'add_vat_field_to_classic_checkout' ] );
        
        // Block checkout: register custom field
        add_action( 'woocommerce_blocks_loaded', [ $this, 'register_block_checkout_field' ] );
        
        // Save VAT to order meta
        add_action( 'woocommerce_checkout_create_order', [ $this, 'save_vat_number_to_order_meta' ], 10, 2 );
        
        // Display in admin order
        add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'display_vat_in_admin_order' ] );
        
        // Add to emails
        add_filter( 'woocommerce_email_order_meta_fields', [ $this, 'add_vat_to_order_emails' ], 10, 3 );
        
        // Detect checkout type
        add_action( 'wp', [ $this, 'detect_checkout_type' ] );
    }

    // -----------------------
    // Debug: detect classic vs block checkout
    // -----------------------
    public function detect_checkout_type() {
        if ( function_exists( 'is_checkout' ) && is_checkout() ) {
            if ( has_block( 'woocommerce/checkout' ) ) {
                wc_vat_debug_log('Detected BLOCK checkout.');
            } else {
                wc_vat_debug_log('Detected CLASSIC checkout.');
            }
        }
    }

    // -----------------------
    // Classic checkout: PHP filter
    // -----------------------
    public function add_vat_field_to_classic_checkout( $fields ) {
        wc_vat_debug_log('Adding VAT field to classic checkout fields.');
        $fields['billing']['vat_number'] = [
            'type'        => 'text',
            'label'       => __( 'VAT Number', 'wc-vatid-free' ),
            'placeholder' => __( 'Enter your VAT Number', 'wc-vatid-free' ),
            'required'    => false,
            'class'       => [ 'form-row-wide' ],
            'priority'    => 120,
        ];
        return $fields;
    }

    // -----------------------
    // Block checkout: register field using Checkout Block API
    // -----------------------
    public function register_block_checkout_field() {
        if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
            wc_vat_debug_log('woocommerce_register_additional_checkout_field not available. WooCommerce Blocks extension required.');
            return;
        }

        wc_vat_debug_log('Registering VAT field for block checkout.');

        woocommerce_register_additional_checkout_field(
            [
                'id'            => 'namespace/vatid-free',
                'label'         => __( 'VAT Number', 'wc-vatid-free' ),
                'location'      => 'address',
                'type'          => 'text',
                'required'      => false,
                'attributes'    => [
                    'placeholder' => __( 'Enter your VAT Number', 'wc-vatid-free' ),
                ],
            ]
        );
    }

    // -----------------------
    // Save VAT number to order meta
    // -----------------------
    public function save_vat_number_to_order_meta( $order, $data ) {
        // Check for classic checkout POST data
        if ( isset($_POST['vat_number']) && ! empty($_POST['vat_number']) ) {
            $vat = sanitize_text_field($_POST['vat_number']);
            wc_vat_debug_log('Saving VAT number from classic checkout: ' . $vat);
            $order->update_meta_data( 'vat_number', $vat );
        }
        // Check for block checkout data
        elseif ( isset($_POST['extensions']['namespace/vatid-free']) ) {
            $vat = sanitize_text_field($_POST['extensions']['namespace/vatid-free']);
            wc_vat_debug_log('Saving VAT number from block checkout: ' . $vat);
            $order->update_meta_data( 'vat_number', $vat );
        } else {
            wc_vat_debug_log('No VAT number found in POST data.');
        }
    }

    // -----------------------
    // Display in admin order
    // -----------------------
    public function display_vat_in_admin_order( $order ) {
        $vat_number = $order->get_meta( 'vat_number' );
        wc_vat_debug_log('Admin order view — VAT: ' . ( $vat_number ?: 'none' ));
        if ( $vat_number ) {
            echo '<p><strong>' . __( 'VAT Number', 'wc-vatid-free' ) . ':</strong> ' . esc_html( $vat_number ) . '</p>';
        }
    }

    // -----------------------
    // Add VAT to order emails
    // -----------------------
    public function add_vat_to_order_emails( $fields, $sent_to_admin, $order ) {
        $vat_number = $order->get_meta( 'vat_number' );
        wc_vat_debug_log('Adding VAT to email meta: ' . ( $vat_number ?: 'none' ));
        if ( $vat_number ) {
            $fields['vat_number'] = [
                'label' => __( 'VAT Number', 'wc-vatid-free' ),
                'value' => $vat_number,
            ];
        }
        return $fields;
    }
}

new WC_VATID_free();
