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
This enables you to create "special" forms e.g. to collect leads from dedicated marketing channels and sales channels.

Within SalesKing you can then professionally work with the collected leads like e.g. follow-up by mail or phone, create estimates and so on.

We suggest using the contact form 7 capabillitys like e.g. radio-buttons and selections to collect leads and dynamically tag them.
This will give your sales team great insight by filtering the leads within SalesKing and use SalesKing as CRM.


= Okay, sounds great so far: How to pimp your wordpress contact form 7 =

If you already use [Contact Form 7](http://wordpress.org/plugins/contact-form-7/), capturing leads from your site is just two simple steps away:

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

Besides the SK-fields above, we support form fields for phone_office and organisation it's up to you to fill the mentioned fields - be creative by using the contactform7 capabilitys in combination with SalesKing.

If you need more fields or any help just drop us a mail at support@salesking.eu.
You can see all possibly available contact fields in our [API Browser](http://sk-api-browser.herokuapp.com/#contact)

= Available languages =
* english (standard)

= Requirements =
* WordPress 3.2 and PHP 5.2.6+ required!
* curl is needed.


== Frequently Asked Questions ==

= Why is my user looged out when i use this addon? =

Because of security SalesKing has an advanced session-handling which causes the log-out. If you use the contact form addon you might consider to activate another user in your SalesKing-Account which then "works" as your API-User. Just get in contact with the SalesKing-Team.

= I do not get how to work with contact form 7 - is there a documentation? =

Contact-Form 7 is on of the most used contact-form plugins for wordpress and therefore it is very well documented.
You can find the [docs](http://contactform7.com/docs/), [FAQ](http://contactform7.com/faq/) and more detailed information about Contact Form 7 on [contactform7.com](http://contactform7.com/).

= SalesKing is great and does provide so many other usefull features - is there a documentation as well? =

Yep, we just released our new support-center(german) - if you need answers which are not provided in our [support-center](https://hilfe.salesking.eu/) just drop us a mail support@salesking.eu we love to help, promise!


== Screenshots ==

1. Activate the SalesKing integration on a form
2. Map fields collected by a form


== Installation ==

1. Download the plugin.
2. Decompress the ZIP file and upload the contents of the archive into `/wp-content/plugins/`.
3. Activate the plugin through the 'Plugins' menu in WordPress

= If you want to enhance the plugin itself, feel free to grab it on github =

Download [for developers](https://github.com/salesking/wp-contact-form-7-salesking-addon) who want to open-source enhancements.


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
