<?php
/**
 * Checkout billing information form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.9
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="woocommerce-billing-fields">

	<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

		<h3 class="step-title"><span><?php esc_html_e( 'Billing &amp; Shipping', 'royal' ); ?></span></h3>

	<?php else : ?>

		<h3 class="step-title"><span><?php esc_html_e( 'Billing Details', 'royal' ); ?></span></h3>

	<?php endif; ?>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>
	<div class="woocommerce-billing-fields__field-wrapper">
		<?php $fields = $checkout->get_checkout_fields( 'billing' );
			foreach ( $fields as $key => $field ) {
				if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
					$field['country'] = $checkout->get_value( $field['country_field'] );
				}
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			}
		?>
	</div>
	<?php do_action('woocommerce_after_checkout_billing_form', $checkout ); ?>

	<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
		
		<div class="woocommerce-account-fields">

			<?php if (! $checkout->is_registration_required() ) : ?>

				<p class="form-row form-row-wide create-account">
					<label  for="createaccount" class="checkbox"><input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true) ?> type="checkbox" name="createaccount" value="1" /> <?php esc_html_e( 'Create an account?', 'royal' ); ?></label>
				</p>

			<?php endif; ?>

			<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

			<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

				<div class="create-account">

					<p><?php esc_html_e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'royal' ); ?></p>
					<div class="create-account">
						<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>

						<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
					
				<?php endforeach; ?>
					</div>
					<div class="clear"></div>

				</div>

			<?php endif; ?>

			<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
		</div>
	<?php endif; ?>
</div>