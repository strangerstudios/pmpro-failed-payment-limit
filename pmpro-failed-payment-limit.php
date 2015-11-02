<?php
/*
Plugin Name: PMPro Failed Payment Limit
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-failed-payment-limit/
Description: Cancel members subscriptions after X failed payments. Set X in plugin code.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

//define('PMPRO_FAILED_PAYMENT_LIMIT', 3);	//uncomment this or add a similar line to a custom plugin

/*
	If a user has X failed payments without a successful order in between, their subscription is cancelled.
*/
//handle payment failures
function pmprofpl_pmpro_subscription_payment_failed($order)
{
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
	if($count == PMPRO_FAILED_PAYMENT_LIMIT)
	{
		$old_level_id = pmpro_getMembershipLevelForUser($user->ID)->ID;
		
		//cancel subscription
		$worked = pmpro_changeMembershipLevel(false, $user->ID);					
		if($worked === true)
		{							
			//send an email to the member
			$myemail = new PMProEmail();
			$myemail->sendCancelEmail($user->ID);
			
			//send an email to the admin
			$myemail = new PMProEmail();
			$myemail->sendCancelAdminEmail($user, $old_level_id);			
			
			//update count in meta
			delete_user_meta($user->ID, "pmpro_failed_payment_count");
			
			//exit so we don't send failed payment email/etc
			exit;
		}
		else
		{
			//shouldn't get here, but keep track of count anyway
			update_user_meta($user->ID, "pmpro_failed_payment_count", $count);
		}
	}
	else
	{
		//update count in meta
		update_user_meta($user->ID, "pmpro_failed_payment_count", $count);
	}	
}
add_action('pmpro_subscription_payment_failed', 'pmprofpl_pmpro_subscription_payment_failed');

//update count on new orders
function pmprofpl_pmpro_added_order($order)
{
	//success?
	if($order->status == "success")
	{
		//remove any failed payment count they might have
		delete_user_meta($order->user_id, "pmpro_failed_payment_count");
	}	
}
add_action('pmpro_added_order', 'pmprofpl_pmpro_added_order');
add_action('pmpro_updated_order', 'pmprofpl_pmpro_added_order');	//update too for cases where a temp order is made at checkout then updated