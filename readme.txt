=== Essentials ===

Contributors: bvandevliet
Tags: security, development, analytics, essentials
Requires at least: 4.9.6
Tested up to: 5.9
Requires PHP: 7.2
Stable tag: 2022.02.11
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl.html

Adds essential functionalities to your WordPress installation. Security and development options, Google Analytics and more.


== Description ==

<p>
  Adds essential functionalities to your WordPress installation.
</p>
<p>
  WordPress is a great CMS! But, in my opinion, it lacks a small number of essential functionalities. Of course, separate plugins can be found for that, but they often do much more than essentially required and, partly as a result, may adversely affect the security and speed of the website. That is why I have collected the below functionalities in one efficient and lightweight WordPress plugin.
</p>
<p>
  The following modules are available:
  <ul>

  <li>Security</li>
  Basic security options and brute-force protection.
  Only administrator users can see and manage this module.

  <li>Development Mode</li>
  Easy toggling WP_DEBUG or make your website accessible for logged-in users only.
  Only administrator users can see and manage this module.

  <li>Login Form</li>
  Change the WordPress logo shown above the login form to your own brand's logo.
  Only administrator users can see and manage this module.

  <li>Google Analytics</li>
  Implement Google Analytics using gtag.js.
  All users that can edit pages can see and manage this module.

  </ul>
</p>
<p>
  After activation of the plugin, each module is available in the admin dashboard as if it had always been there out of the box. You decide which modules to enable, which gives you the freedom to disable a module if you prefer to use another plugin for it instead.
</p>


== Installation ==

= Manually via FTP =

1. Download the plugin.
1. Unpack the .zip
1. Upload the `wpessentials` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin from within your WP Admin.

= Manually via WP Admin =

1. Download the plugin.
1. Goto `Plugins` > `Add New` > `Upload Plugin`.
1. Upload the .zip
1. Activate the plugin.

= Automatic via WP Admin =

1. Goto `Plugins` > `Add New`.
1. Search for `wpessentials`.
1. Find the plugin and click `Install Now`.
1. Activate the plugin.


== Screenshots ==

1. An overview of all available modules
2. Security module
3. Development module
4. Login Form module
5. Google Analytics module


== Changelog ==

= 2022.02.11 =
* Bugfixed missing reference to `dbDelta()` on plugin activation.
* Minor translation correction.

= 2021.12.17 =
* Bugfixed silly Javascript mistakes.

= 2021.10.20 =
* Improved and bugfixed Devmode disable front-end logic.

= 2021.10.02 =
* Fixed media uploader in Login Form module.

= 2021.09.17 =
* using gmdate() instead of date().
* ran codebase through wpcs with phpcs.

= 2021.07.26 =
* Not much, tested up to WordPress 5.8

= 2021.03.12 =
* Removed irrelevant "guest" role, if role was already created, it will not be removed.
* Removed analytics.js, now gtag.js only.
* Improved Force SSL feature using the new wp_update_urls_to_https() function if exists.
* Fixed bug where wrong setting was reversed on failure.
* Improved code readability and tested up to WordPress 5.7

= 2020.12.28 =
* Default charset and collate fix on database table creation.

= 2020.12.22 =
* jQuery bugfix.

= 2020.12.14 =
* Added force SSL option to security module.
* Added toggling SAVEQUERIES option to devmode module.
* Updated Javascript to ES6 standards.

= 2020.12.10 =
* Added various additional security options to protect system files using .htaccess tweaks.
* Added cronjob to remove outdated lockout records even when security module is disabled.
* The debug.log file contents are now shown within the devmode admin page.
* Added filter whether to print the analytics tag to support opt-out logic in custom scripts.
* Added analytics.js tag option besides gtag.js and made it the default setting.
* Added option to disable advertising features in Google Analytics tag.
* Dramatically improved file handling related logic and added .htaccess mod handling.
* Various bug fixes and improvements.

= 2020.06.17 =
* Javascript reviewed, improved scoping and coding standards.
* Removed Link Finder module, now available as separate plugin.

= 2020.03.30 =
* Support to easily update "display_name" for users where its value is equal to their "login_name".
* Disabling REST API improved.

= 2020.03.26 =
* Fixed a fatal error bug in active lockouts table for username entries.

= 2020.03.16 =
* Improved active lockouts table in the security module admin page.

= 2020.03.14 =
* The security module now detects users that publicly reveal their username and helps to fix that.
* Active lockouts can now be viewed and released from within the security module admin page.
* Added option to only send an email notification for lockouts if an existing username was locked out.
* Various bug fixes and improvements.

= 2020.03.05 =
* Link Finder module now updates internal hyperlinks as either absolute url (self-pings allowed) or as relative url (self-pings avoided).
* Fixed bug where home_url was used instead of site_url or vise versa.

= 2020.03.01 =
* Some performance efficiency fixes.
* Dutch translation.

= 2020.02.26 =
* Brute-force events are now stored for a minimum period of 4 weeks instead of the attempt or lockout period set.
* Added option to send an email if a lockout event occurs.

= 2020.02.24 =
* Toggling `WP_DEBUG` now also toggles `WP_DISABLE_FATAL_ERROR_HANDLER` and `ini_set('display_errors', 'On'|'Off')`.
* Improved error handling when toggling `WP_DEBUG`.
* Various performance improvements and bugfixes including a plugin activation bug.

= 2020.02.21 =
* First introduction, available modules:
* Security
* Development Mode
* Login Form
* Google Analytics
* Link Finder


== Upgrade Notice ==

= 2020.12.10 =
This is a major update. Please visit the changelog for details.

= 2020.06.17 =
Link Finder module removed, now available as a separate plugin.
