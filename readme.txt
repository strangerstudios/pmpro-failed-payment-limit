=== Paid Memberships Pro - Failed Payment Limit ===
Contributors: strangerstudios
Tags: pmpro, paid memberships pro, recurring, failed payments
Requires at least: 4.0
Tested up to: 6.3
Stable tag: .3

Cancel members subscriptions after 1-3 failed payments.

== Description ==

This plugin will keep track of the number of failed payments coming in for a user. When that count reaches the set failed payment limit, the user's membership is cancelled and the subscription at the gateway will be cancelled. For most sites, we recommend you cancel after the first failed payment. Automatic retries from the gateway rarely work. Some users do update their billing information, which would fix things for second and third attempts, but it's not much less work than simply checking out again.

== Installation ==

1. Upload the `pmpro-failed-payment-limit` directory to the `/wp-content/plugins/` directory of your site.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit Memberships -> Advanced Settings to change the "Failed Payment Limit" setting.
4. Alternatively, you can set a PMPRO_FAILED_PAYMENT_LIMIT constant in your wp-config.php file.

== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the issues section of GitHub and we'll fix it as soon as we can. Thanks for helping. https://github.com/strangerstudios/pmpro-failed-payment-limit/issues

== Changelog ==
= 0.3 - 2023-10-12 =
* BUG FIX/ENHANCEMENT: Marking plugin as incompatible with Multiple Memberships Per User for the PMPro v3.0 update. #14 (@dparker1005)
* BUG FIX: Now passing user object to `sendCancelEmail()` instead of just the user ID. #10 (@mircobabini)
* REFACTOR: Now using `get_option()` instead of `pmpro_getOption()`. #15 (@dwanjuki)

= .2 =
* ENHANCEMENT: Added a setting to the advanced settings page.
* ENHANCEMANT: Added readme.