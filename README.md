# SalesKing Wordpress Contact Form 7 Addon

## Turn your Wordpress into a lead capture CRM machine

Get your website's Contact Form 7 Data straight into SalesKing.

Capture Lead's like a pro, without touching any data.
=== Contact Form 7 - SalesKing CRM Addon ===
Contributors: killer-g, frank b
Tags: CRM, salesking, contact-form-7, german, leads, form, feedback, contact-form, contact
Requires at least: 3.2
Tested up to: 3.5.2
Stable tag: 1.0.0
License: GPL
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Get your Contact Form 7 data straight into SalesKing CRM.

== Description ==
The plugin creates a lead with the contact information captured in any Contact-Form-7 form.
You can add as many forms as you like and map arbitrary fields to advanced contact properties e.g tags, notes, lead_reference.

= How to pimp your wordpress contact form 7 =

If you already have [Contact Form 7](http://wordpress.org/plugins/contact-form-7/), capturing Leads from your site is just two simple steps:

1. Get a [SalesKing account](https://app.salesking.eu/signup/wpcf)
2. Install and activate contact form 7 + this plugin
3. Configure a contact form with SalesKing integration (see screenshots)

= Advanced Usage =

On new forms we enter some fail-safe defaults:

    Form-field      -> SalesKing Contact field
    email           -> email
    name            -> last_name
    subject+message -> notes
    form-url        -> lead_ref
    some-tag        -> tag_list

Besides the SK-fields above, we support form fields for phone_office and organisation.

If you need more fields or any help just mail us at support@salesking.eu.
You can see all contact fields in our [API Browser](http://sk-api-browser.herokuapp.com/#contact)

= Available languages =
* english (standard)

= Requirements =
* WordPress 3.2 and PHP 5.2.6 required!
* curl is needed.

Get the [BackWPup Pro](http://marketpress.com/product/backwpup-pro/) Version with more features on [MarketPress.com](http://marketpress.com/product/backwpup-pro/)

== Frequently Asked Questions ==


== Screenshots ==

1. SalesKing Settings on the form

== Installation ==

1. Download the plugin.
2. Decompress the ZIP file and upload the contents of the archive into `/wp-content/plugins/`.
3. Activate the plugin through the 'Plugins' menu in WordPress


== Changelog ==

= Version 1.0.0 =
* Fixed: simplified code move to OO-Structure
* Fixed: align naming of fields with SalesKIng Contact fields
* Added: test salesKing connection via AJAX on admin form

= Version 0.0.4 =
* Added: additional data tag field

= Version 0.0.3 =
* Added: note about field limitations
* Added:initialize form only if user or password or subdomain not present

## How to pimp your wordpress contact form 7

If you already have [Contact Form 7](http://wordpress.org/plugins/contact-form-7/), capturing Leads from your site is just two simple steps:

1. Get a [SalesKing account](https://app.salesking.eu/signup/wpcf)
2. Install and activate contact form 7 + this plugin
3. Configure a contact form with SalesKing integration (see picture settings.png)

### Advanced Usage

On new forms we enter some fail-safe defaults:

    Form-field      -> SalesKing Contact field
    email           -> email
    name            -> last_name
    subject+message -> notes
    form-url        -> lead_ref
    some-tag        -> tag_list

Besides the SK-fields above, we support form fields for phone_office and organisation.

If you need more fields or any help just mail us at support@salesking.eu.
You can see all contact fields in our API Browser: http://sk-api-browser.herokuapp.com/#contact



### New to [Contact Form 7](http://wordpress.org/plugins/contact-form-7/) ?

Go ahead install it, as it is by far the most complete free contact form plugin out there.



## System requirements
Wordpress 3.5.x
PHP 5.3.x+
PHP cURL extension
