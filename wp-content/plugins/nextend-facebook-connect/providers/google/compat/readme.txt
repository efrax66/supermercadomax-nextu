=== Nextend Google Connect ===
Contributors: nextendweb
Tags: google, register, login, social connect, social, google connect
Donate link: https://www.facebook.com/nextendweb
Requires at least: 3.0
Tested up to: 4.7.0
Stable tag: 1.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

One click registration & login plugin for Google? Easy installation? Is it totally free and comes with support? Yeah!

== Description ==

Check the [DEMO](http://secure.nextendweb.com/) on our site.

Also we created a [Social Connect button generator](http://www.nextendweb.com/social-connect-button-generator) for this plugin. This allows you to create fancy login buttons. 

Personally, I hate to fill out registration forms, waiting for confirmation e-mails, so we designed this plugin for our website. Now, we want to share this very usable plugin with everyone, for free!
 
**Why should you choose Nextend Google Connect plugin from the many social plugins?**

* If your visitors have a Google profile, they can register your site with a single click, and later to log in too.
* The previously registered users can simply attach their existing Google profile to their account, so in the future, they can logging in with the one social button.
* The plugin has multiple desings, so it fits all kind of websites smoothly and elegantly. - Soon
* Very simple to use.
* Fast and helpful support.
* Totally free.

If you like our stuff donate a like to our [Facebook page](https://www.facebook.com/nextendweb) or follow us on [Twitter](https://twitter.com/nextendweb)!

#### Usage

After you activated the plugin, the plugin will autmatically 

* add the login buttons to the WordPress login page. See screenshot #1
* add the account linking buttons to the WordPress profile page. See screenshot #2


#### Advanced usage

**Simple link**

&lt;a href="*siteurl*/wp-login.php?loginGoogle=1&redirect=*siteurl*" onclick="window.location = \'*siteurl*/wp-login.php?loginGoogle=1&redirect=\'+window.location.href; return false;"&gt;Click here to login or register with Google&lt;/a&gt;

**Image button**

&lt;a href="*siteurl*/wp-login.php?loginGoogle=1&redirect=*siteurl*" onclick="window.location = \'*siteurl*/wp-login.php?loginGoogle=1&redirect=\'+window.location.href; return false;"&gt; &lt;img src="HereComeTheImage" /&gt; &lt;/a&gt;

== Installation ==

1.  Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.
2.  Follow the steps in the Nextend Google Connect settings page!


== Screenshots ==

1. Our Social Connect plugins on the main WP login page
2. Our Social Connect plugins in the profile page for account linking

== Changelog ==

= 1.6.1 =
* Fix: Missing check which resulted a notice - made by Alberto

= 1.6.0 =
* Fix: Redirect urls are well encoded - made by JRB

= 1.5.9 =
* Nonce added to backend

= 1.5.5 =
* Vulnerability fix

= 1.5.3 =
* XSS Vulnerability fix

= 1.5.1 =
* Security fix for XSS

= 1.5.0 =
* Security fix for redirects (Thanks to: Kacper Szurek and Elger Jonker)
* Avatar fix (Thanks to: Jamie Bainbridge)

= 1.4.58 =
* Avatar fix
* Buddypress avatar support. If Buddypress avatar not exists, then Google avatar used. If there is a BuddyPress avatar, that will be used.

= 1.4.56 =
* Fix: WordPress transient functions used to store the required session variables. $_SESSION fully removed. Beta!!!

= 1.4.55 =
* Fix: Now the plugin use cookies instead of PHP session. Maybe this fixes the problems.
* Fix: Now the plugin use wp transient for the admin messages
* NOTICE: If the 1.4.54 version work for you fine, do NOT update yet!


= 1.4.54 =
* Fix

= 1.4.53 =
* Fix for crash

= 1.4.52 =
* Avatar fix

= 1.4.51 =
* Redirection fix
* Some other bug fixes with account linking feature

= 1.4.50 =
* Avatar fix
* Changes in actions 

= 1.4.49 =
* Settings page fixed

= 1.4.48 =
* Redirection fix
* Optimalizations

= 1.4.45 =
* Feature: Account unlinking added

= 1.4.42 =
* Buddypress login widget support

= 1.4.38 =
* Added check for login inputs

= 1.4.36 =
* PHP notice fixes

= 1.4.33 =
* Typo fix, please update from 1.4.3x to this version!

= 1.4.32 =
* Double login button fix

= 1.4.31 =
* Callback url changed! if you used older version please repeat installation step #8
* Official SSL support added - Thanks for Chin for the help

= 1.4.27 =
* Important security fix

= 1.4.26 =
* Avatar support added
* Added e-mail notification on registration

= 1.4.25 =
* wp_login do_action fix

= 1.4.24 =
* new_google_is_user_connected() function now returns with the Google id if authenticated, null if not...

= 1.4.23 =
* Now the application will only request authorization for the register.

= 1.4.22 =
* Bugfix for Wordpress 3.5RC1

= 1.4.21 =
* Bugfix for Wordpress 3.5RC1

= 1.4.18 =
* Register redirect bugfix

= 1.4.17 =
* Bugfix

= 1.4.16 =
* Buttons added to registration form

= 1.4.15 =
* Added the option for different redirect for Login and Registration

= 1.4.14 =
* Login page jQuery fix

= 1.4.12 =
* Fixed session check

= 1.4.11 =
* Fixed wrong login urls on the settings page

= 1.4.10 =
* Added editProfileRedirect parameter for buddypress edit profile redirect. Usage: siteurl?editProfileRedirect=1

= 1.4.9 =
* https bugfix - author Michel Weimerskirch

= 1.4.8 =
* Added name, first name, last name and Google plus url support.
* 
= 1.4.4 =
* Modified login redirect issue for wp-login.php - author Michel Weimerskirch
* Added fix redirect url support. If you leave it empty or "auto" it will try to redirect back the user to the last visited page. 
 
= 1.1 =
* Added Social button generator support

= 1.0.1 =
* Added linking option to the profile page, so an already registered user can easily link the profile with a Facebook profile.