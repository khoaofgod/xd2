<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/new-product.php
 *
 * @author 		WC Marketplace
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
if ( !defined( 'ABSPATH' ) ) exit; 
global  $WCMp;

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( "Hi there! This is to notify that a new product has been submitted in %s.",  'dc-woocommerce-multi-vendor' ), get_option( 'blogname' ) ); 
echo '\n'; 
echo sprintf(  __( "Product title: %s",  'dc-woocommerce-multi-vendor' ), $product_name ); 
echo '\n'; 
echo sprintf(  __( "Submitted by: %s",  'dc-woocommerce-multi-vendor' ), $vendor_name ); 
echo '\n'; 
echo sprintf(  __( "Edit product: %s",  'dc-woocommerce-multi-vendor' ), admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); 
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
do_action( 'woocommerce_email_footer' );

?>