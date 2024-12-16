=== Barcode Generator for WooCommerce - Show barcodes on products, orders, invoices and other pages ===
Contributors: UkrSolution <https://profiles.wordpress.org/ukrsolution>
Tags: Embed barcode, barcode, barcode email, WooCommerce, barcode, SKU, EAN, UPC, Code128, Code39, QR Code, QRcode, inventory, bar code, barcode generator, Base64, ZATCA QR.
Requires at least: 4.0.1 
Tested up to: 6.4
Stable tag: trunk
Requires PHP: 5.6
License: GPLv2 or later 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

### Show Barcodes/QRCodes on WooCommerce & other pages

https://www.youtube.com/watch?v=ynFJQZhYijg

**✔ Generate barcodes for WooCommerce products, orders and emails**
**✔ Embed barcodes into invoices and third party plugins**
**✔ Support of CODE128, QRCode, UPC, EAN, Data Matrix, CODE39, Base64, ZATCA QRCode.**

This plugin allows you to extend functionality of your WooCommerce store by adding barcodes to product pages and any other places.
After plugin installation you need to enable a few settings to display barcodes in next places:

1. Product pages
2. Order emails
3. Admin product pages
4. Admin order pages

Size of the barcode can be adjusted in "px" or "%", so it can perfectly fit to website layout.
You can also select the position of the barcode where it should be displayed on "Product page" - you can find a list of positions in your template documentation.

This "Barcode Embedding" plugin also allows to add barcodes into invoices or any other third party plugins by using shortcodes.
Embedding barcodes with shortcodes might be a tricky task, so you may need help from your developer or just [contact us](https://www.ukrsolution.com/ContactUs) and we will help you with it.

Your customers can use a barcode scanner or app on your smartphone to read information from barcodes.

*Note: this is a demo version with some limitations.*
*You can upgrade to the full version on our [website](https://www.ukrsolution.com/Wordpress/Digital-Barcode-Generator-for-Embedding-into-Pages-and-Posts).*

### Integration with WooCommerce
 
Plugin fully supports WooCommerce and allows to display any data on barcodes from products and orders.
For example, you can add to barcode product name, price, attributes and any custom fields.
Or add extra text data for order barcode, like "order id", "date of creation", etc.
If you need any additional data on barcode simply [contact us](https://www.ukrsolution.com/ContactUs) and we will help you.

### Barcode templates

Plugin offers a few barcode templates that will allow you to display more data on it.
There are templates for barcode with a single line of text and up to 4 lines of text.
So, you can select a template based on how much text data you would like to display on barcodes.
If you are looking for some specific barcode template - [contact us](https://www.ukrsolution.com/ContactUs) and we will create it for you.

### Embedding barcodes using shortcodes

Shortcodes can be used for embedding barcodes into the most difficult parts on your website.
With a little help from a technical person you can add barcodes in almost any place on your website.

You can create a new shortcode on "Embed Barcode" tab where you can:

1. Select template
2. Select type of barcode (CODE128, QRCode, UPC, EAN, DataMatrix or CODE39)
3. Select field from which should be barcode image generated
4. Match text lines 1-4 with actual product or order fields.

### Installing Demo version
* Go to WordPress admin panel -> Plugins menu.
* Add New-> search for plugin by entering plugin name “Embedding barcodes” -> choose plugin  “Embed barcodes into product pages, orders, emails, invoices and any other pages” -> install now.
* Activate the plugin.

== Frequently Asked Questions == 

= Barcode is too big on product page, how can I reduce size of it ?=

You can specify size in "px" or % in plugin settings (on "Product page" tab).

= Is it possible to display barcodes on post or any other pages ? =

Yes, check out the "Embed Barcode" tab to find a shortcode which can be used for barcode embedding into third party plugins.

= How can I specify position of barcode on product page ?=

In the plugin's settings you can use the "Barcode position on page" option to specify the correct template position.

== Screenshots ==

1. Display barcode on Product page
2. Display barcode in order email
3. Display barcode in invoice
4. Barcode settings

== Changelog =

= 2.0.1 - 23 February 2023  =

* Feature: Added support of "Sequential Order Numbers for WooCommerce Pro" plugin
* Improvement: Added better support of the WooCommerce 7.4+
* Improvement: Minor UI changes
* Bug fix: Fix for barcode generation from a "Multiple Fields" at the same time.


= 2.0.0 - 23 September 2022 (Major update) =
Important: Lost compatibility with old 1.x.x versions.
After update from 1.x.x to 2.0.0 version, settings from 1.x.x won't be moved to 2.0.0, so you will need to configure version 2.0.0 again.
No problems for any new plugin installations.

* Changed core of the plugin
* Improved auto updater
* Improved support of WP 6.x
* Dozen of minor fixes & UI improvements.

= 1.2.5 - 15 December 2021 =
* Added support of the ZATCA QRCode type.
* Added support of the Base64 format.
* Added shortcode for order price.
* Added shortcode order's tax by name.

= 1.2.4 - 22 November 2021 =
* Added shortcodes for orders.
* Integration with the "Booster" plugin.

= 1.2.2 - 15 October 2021 = 
* Added support of the "WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels" plugin.
* Added possibility to add barcode for every item in an order.
* Added support of a custom product field.
* UI improvements. 

= 1.2.1 - 7 October 2021 = 
* Added integration with the "WooCommerce PDF Invoices and Packing Slips" plugin.
* Added barcode for product variations.

= 1.2.0 - 10 September 2021 =
* Embed barcode to order preview.
* Added possibility for combine fields to generate QRcode and barcode.
* Improvement for UI.

= 1.1.2 - May 21st, 2021 =
* UI improvements.

= 1.1.1 - May 20th, 2021 =
* Fixed issue with displaying barcodes on receipt for Woocommerce POS.

= 1.0.1 - March 1st, 2021 =
* First published version. Allows to display barcodes on product pages and in admin panel.




== Upgrade Notice ==
* First version.
