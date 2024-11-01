=== EeroPay Payment Gateway ===

Contributors: aeydev
Plugin Name: EeroPay
Tags: mobile payments, EeroPay, eeropay, online payments, M-PESA, mpesa, woocommerce, payment gateway, e-commerce, safaricom, daraja, payments, online shop
Author: Aey Dev
Requires at least: 2.2
Tested up to: 6.2
Stable tag: 1.0.1
License: GNU General Public License v2.0
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: #

== Description ==
Over the last few years, merchants in Kenya and across Africa have moved to conduct business online via different platforms and mainly e-commerce websites. WordPress and WooCommerce are at the top of the list when it comes to the most used CMS and merchant store platforms for E-commerce sites globally.

However, all these merchants face countless problems when accepting payments from their customers. With the penetration of mobile money across Africa, it’s become paramount for merchants to link their online stores to mobile money payment gateways.

MPESA is a mobile money fintech product from Safaricom LTD that is available in Kenya, Tanzania, South Africa, Afghanistan, Lesotho, DRC, Ghana, Mozambique, Egypt, and Ethiopia.

This plugin enables customers to pay for products using M-PESA mobile money service to any WordPress site with the WooCommerce plugin installed.

The plugin adds an option on the checkout section for paying through M-PESA.


#### Plugin features: ####

Free Plugin features
    1. MPESA PayBill or Till STK Push 
    2. 2D checkout
    3. Custom setup (Daraja Credentials)
    4. Cart clearing

Paid Plugin features
    1. MPESA PayBill or Till STK Push 
    2. 2D checkout
    3. Custom setup (Daraja Credentials)
    4. Callbacks
    5. Auto update of Order Statuses on payment complete.
    6. You can customize the statuses the orders become once payment is complete.
    7. Cart clearing
    8. Stock reducing


#### How to use: ####
1. To use the plugin, one must get an MPESA PayBill or MPESA Till number, a unique number that will act as your merchant account where the payments from the customer will be debited.

2. Once you have a PayBill or Till Number, head to Safaricom’s Daraja Portal and link your Paybill or Till number to the developer account created on the Daraja portal.

3. From your Daraja Portal, get the following credentials:

    Consumer Key

    Consumer Secret

    Passkey

    URL Endpoints for Sandbox/Production for authentication and payment request.

4.Activate EeroPay plugin, then fill in the credentials you got from Daraja Portal.

We will store the credentials on your website to ensure the site owner has full control over the payment details of the PayBill or Till number and the plugin. We do not collect or store any of these sensitive credentials.

#### Payment Instructions ####

When the customer clicks on the Pay button on the payment page, the plugin will initiate a payment authentication request to the customer.

The customer will then accept or decline the payment from the personal mobile phone and the callback will be sent from the portal with details of the customer’s action.

== Disclaimer ==

The plugin’s purpose is to help you receive M-Pesa payments on your WooCommerce Website. 


== Installation ==

1. Go to the plugins tab on your wordpress admin website.

2. Click on the add new plugin button.

3. Search on the online store for EeroPay

4. Install the EeroPay plugin that is from Aey-Group

5. Activate the plugin after installation.

6. Navigate to woocommerce / Settings / Payments / Manage 

7. Add your daraja credentials and save.


== Upgrade Notice ==

This is the first version.

== Changelog ==

= 1.0.1 =
1. Customer UI
2. Admin settings page
3. M-Pesa STK Push
4. Environments

