<?php
/*
Plugin Name: Paid Memberships Pro - Failed Payment Limit Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-failed-payment-limit/
Description: Cancel members subscriptions after 1-3 failed payments.
Version: .3
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

//define('PMPRO_FAILED_PAYMENT_LIMIT', 3);	//uncomment this or add a similar line to a custom plugin

/*
	If a user has X failed payments without a successful order in between, their subscription is cancelled.
*/
//add setting to advanced settings
function pmprofpl_pmpro_custom_advanced_settings($settings) {
	if(!is_array($settings))
		$settings = array();

	$settings[] =  array(
			'field_name' => 'pmpro_failed_payment_limit',
            'field_type' => 'select',
            'label' => 'Failed Payment Limit',
            'description' => '',
            'options' => array(''=>'None. Let the gateway handle it.', '1' => '1. Cancel after the first failed payment.', '2' => '2. Cancel after the second failed payment.', '3' => '3. Cancel after the third failed payment.')
        );

	return $settings;
}
add_filter('pmpro_custom_advanced_settings', 'pmprofpl_pmpro_custom_advanced_settings');

//helper function to get the limit
function pmprofpl_getLimit() {
	//defined constant overrides everything
	if(defined('PMPRO_FAILED_PAYMENT_LIMIT'))
		return PMPRO_FAILED_PAYMENT_LIMIT;
	else
		return get_option( 'pmpro_failed_payment_limit' );
}

//handle payment failures
function pmprofpl_pmpro_subscription_payment_failed($order) {
	//get user from order
	$user = get_userdata($order->user_id);
	
	//get their failed payment count
	$count = get_user_meta($user->ID, "pmpro_failed_payment_count", true);

	//increment it
	if(empty($count))
		$count = 1;
	else
		$count = $count + 1;
	
	//if we hit X, cancel the user
	if($count >= pmprofpl_getLimit()) {
		$old_level_id = pmpro_getMembershipLevelForUser($user->ID)->ID;
		
		//cancel subscription
		$worked = pmpro_changeMembershipLevel(false, $user->ID);						
		if($worked === true) {							
			//send an email to the member
			$myemail = new PMProEmail();
			$myemail->sendCancelEmail($user);

			
			//send an email to the admin
			$myemail = new PMProEmail();
			$myemail->sendCancelAdminEmail($user, $old_level_id);			
			
			//update count in meta
			delete_user_meta($user->ID, "pmpro_failed_payment_count");
			
			//exit so we don't send failed payment email/etc
			exit;
		} else {
			//shouldn't get here, but keep track of count anyway
			update_user_meta($user->ID, "pmpro_failed_payment_count", $count);
		}
	} else {
		//update count in meta
		update_user_meta($user->ID, "pmpro_failed_payment_count", $count);
	}	
}
add_action('pmpro_subscription_payment_failed', 'pmprofpl_pmpro_subscription_payment_failed');

//update count on new orders
function pmprofpl_pmpro_added_order($order) {
	//success?
	if($order->status == "success") {
		//remove any failed payment count they might have
		delete_user_meta($order->user_id, "pmpro_failed_payment_count");
	}	
}
add_action('pmpro_added_order', 'pmprofpl_pmpro_added_order');
add_action('pmpro_updated_order', 'pmprofpl_pmpro_added_order');	//update too for cases where a temp order is made at checkout then updated

/**
 * Mark the plugin as MMPU-incompatible.
 */
function pmprofpl_mmpu_incompatible_add_ons( $incompatible ) {
    $incompatible[] = 'PMPro Failed Payment Limit Add On';
    return $incompatible;
}
add_filter( 'pmpro_mmpu_incompatible_add_ons', 'pmprofpl_mmpu_incompatible_add_ons' );

/*
Function to add links to the plugin row meta
*/
function pmprofpl_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-failed-payment-limit.php') !== false) {
		$new_links = array(
			'<a href="' . esc_url('http://paidmembershipspro.com/support/') . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmprofpl_plugin_row_meta', 10, 2);
