=== Plugin Name ===
Plugin Name: FeedBlitz Member Mail
Author URI: http://www.feedblitz.com
Plugin URI: http://www.feedblitz.com
Contributors: phollows, commentluv
Tags: FeedBlitz, newsletter, email newsletters, email marketing, email subscriptions, email lists, feedburner, feedburner alternative, blog subscriptions
Requires at least: 2.8.2
Tested up to: 3.5
Stable tag: 1.0.1

Build your FeedBlitz email newsletter subscription list faster with simple checkboxes on user registration and / or comment forms.

== Description ==

Get more WordPress email subscribers with this plugin from FeedBlitz. the goal is to accelerate list growth by placing a "subscribe me" checkbox into two existing user interactions: registering for the site and leaving a comment. 

The plugin does two things:

1. Adds a checkbox to the user registration screen (see screenshots)
1. Adds a similar check box to the comment form (see screenshots)

You can customize the text associated with the check box. When the check box is checked, FeedBlitz will email an opt in activation form to the user using the email address given in the relevant form. The user then clicks the link and completes a CAPTCHA in order to join your list. 

Because the opt-in is available during existing user interactions, it is very easy for a user to start the subscription process, accelerating your list's growth.

Dual opt-in, CAPTCHA codes and other techniques are used to minimize any potential risk of abuse. 

== Installation ==

1. Upload `feedblitzmembermail` directory and contents to the `/wp-content/plugins/` directory.
1. Configure and then activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Do I need a FeedBlitz account? =

Yes, you must have an active FeedBlitz account and an active mailing list. The plugin has links to set that up if necessary. A demo video is at http://www.feedblitz.com/help/tour.asp

= Does this plugin work with Feedblitz trial accounts? =

Yes. 

= Is this plugin dual opt-in? =

Yes. When the user checks the checkbox they will be required to activate their subscription by clicking a link in an email FeedBlitz sends to them.

= Can a user subscribe multiple times? =

If a user is already on the list FeedBlitz will de-duplicate them; they will not be required to opt in again and again.

= If the plugin fails to work, can users still register or comment? =

Yes. if for some reason the plugin can't reach FeedBlitz to add the user the normal comment or site registration process continues.

= Where else can I get help and support? =

Check out the [FeedBlitz Knowledge Base](http://kb.feedblitz.com). 


== Screenshots ==

1. Example of how the plug in adds a "subscribe by mail" option to the standard comment form.
2. Users can sign up for the email newsletter using the registration screen.

== Changelog ==

= 1.0.1 =
* update links and use new WP API for settings page.
* use wp_remote_get instead of cURL

= 0.93 =
* use class and wordpress settings api.
* pretty settings page
* allow choice of which forms to add checkbox to
* internationalization support

= 0.92 =
* First release to plugins repository at wordpress.org 

== Upgrade Notice ==

= 1.0.1 =
WP 3.5 compatibility

