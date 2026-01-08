== WPS Core ==

== Translations ==
* English (100%): 162/162 lines translated
* French (100%): 162/162 lines translated
* German (100%): 162/162 lines translated
* Hindi (100%): 162/162 lines translated
* Italian (100%): 162/162 lines translated
* Portuguese (100%): 162/162 lines translated
* Russian (100%): 162/162 lines translated
* Spanish (100%): 162/162 lines translated

== Changelog ==
= 5.3.3 = 2025-12-18
* Fixed: Update alert message for site verification and improve styling
* Fixed: Remove issue that could delete the license key when verifying the site
* Fixed: Improve AJAX handling and refresh logic for AI content generation

= 5.3.2 = 2025-11-12
* Fixed: Fix https links

= 5.3.1 = 2025-10-13
* Updated: Updated wps/rest-api to latest version
* Updated: Update social media link from Twitter.com to X.com and add new SVG logo
* Fixed: Fix possible PHP error when using PHP 8.4 or lower

= 5.3.0 = 2025-09-25
* Added: Improve error handling for ai_job_status parameter and update AI job status correctly
* Added: Add webp extension support to all images options used by WP-Script themes
* Updated: Improve layout of AI status display in post columns
* Fixed: Append WP-Script logs to the logs page instead of overwriting them
* Fixed: Force sslverify option from API request arguments
* Fixed: Fix column display in WordPress videos listing table
* Fixed: Fix cron reschedule event error
* Fixed: Fix issues in translation files that could prevent the plugin to work properly in some languages

= 5.2.1 = 2025-04-14
* Updated: Update translation files for the plugin in Chinese, English, French, German, Hindi, Italian, Portuguese, Russian and Spanish
* Fixed: Adjust column widths for title and taxonomy in post edit screen
* Fixed: Fix text domain loading error
* Fixed: Replace wp_kses with wp_kses_post for improved output sanitization

= 5.2.0 = 2025-03-19
* Added: Add missing translations for new AI options and features

= 5.1.0 = 2025-03-11
* Added: Refactor all links to wp-script.com
* Fixed: Fix changelog release date for version 5.0.0
* Fixed: Update PHP min version to 7.2 in plugin declaration
* Fixed: Fix minor issues

= 5.0.0 = 2025-03-03
* Added: New engine to generate AI Content for videos
* Added: New column in posts listing to manage AI Content Generation for videos
* Added: You can now rewrite titles and generate description for adult videos directly from the posts listing
* Added: You can track AI Content Generation for each video post title and description
* Added: Status of AI Content Generation for videos are refreshed every 5 seconds automatically
* Added: New dropdown filters to select videos depending on their AI content generation status for their title and description (processing, success, error)
* Added: A new cron job will send requests to process AI content generation on titles and descriptions every 5 minutes
* Added: A new AI box is available in the Core dashboard to get more information about the AI Content Generation
* Added: You can also see your WPS Credits balance in the AI box
* Updated: Dropping support for PHP 5.6 to 7.1
* Updated: PHP ^7.2 or PHP 8.x is now required
* Updated: PHP ^7.4 or PHP 8.x is recommended
* Updated: Vue.js v2.6.12 to v2.7.16
* Fixed: Clean up the codebase to prevent a lot of potential php errors
* Fixed: Hide plugins dropdown menu when no plugin is installed
* Fixed: Fix PHP Deprecated strip_tags() issue on options pages of all WP-Script plugins
* Fixed: Fix all possible issues with function parameters types in options system

= 4.2.3 = 2024-04-02
* Fixed: Revert back to original xbox_get_field_value() implementation using default value from options setup can end up to unwanted behaviore like displaying default banners even when we don't want them to be displayed

= 4.2.2 = 2024-04-02
* Fixed: Fix default value option that could return empty data that could remove logo images and break other html elements from options

= 4.2.1 = 2024-04-02
* Updated: Update xbox options system to return default values set in options of each product when no default value is set

= 4.2.0 = 2024-03-20
* Added: Add some WordPress filters to allow WP Script plugins to plug some features easily in the future
* Added: New WordPress filter in admin videos lists to filters videos posts by partner
* Added: New WordPress filter in admin videos lists to display partner image
* Fixed: Fix thumb and partner image that could be displayed side by side on large screens in admin videos lists
* Fixed: Fix small issues

= 4.1.1 = 2024-03-07
* Added: Add PHP version used in the site to call the API for better support and compatibility with the products
* Added: Add max width 1500px to the dashboard to prevent the dashboard to prevent unreadable layout on large screens
* Updated: Merge thumbnails and partners image columns to gain visual space in the admin videos listings (used by WP-Script themes and plugins)
* Updated: Fix thumbnails sizes on small screen in the admin videos listings (used by WP-Script themes and plugins)
* Fixed: Fix small issues

= 4.1.0 = 2023-12-20
* Added: Add compatibility with new theme TikSwipe

= 4.0.3 = 2023-11-23
* Added: Add admin notice for site verification
* Added: Link to documentation for site verification
* Updated: Update translation for the plugin in all languages
* Fixed: Clear wpscript.log when updating the plugin

= 4.0.2 = 2023-10-16
* Fixed: Fix data types issues that prevented the verification box to be displayed when there was 0 days left to verify the site
* Fixed: Fix issue that prevented to see up to date data about site verification

= 4.0.1 = 2023-10-13
* Added: New translation for the plugin in Hindi
* Updated: Update translation for the plugin in Chinese, English, French, German, Hindi, Italian, Portuguese, Russian and Spanish
* Fixed: Fix verification system displayed by default on PHP7 that prevented the CORE to work properly

= 4.0.0 = 2023-10-09
* Added: Added verification system to verify that the user is the owner of the license key to prevend license key stealing
* Added: New toast notification on dashboard when some actions are performed lije connecting a product
* Added: New small success label on the dashboard when the site is verified

= 3.5.8 = 2023-09-19
* Added: Add message with a reload button when no data from api to retry
* Fixed: Fix php8 warning
* Fixed: Fix css issues in options (themes and plugins)
* Fixed: Fix hide core version when not available

= 3.5.7 = 2023-02-28
* Fixed: Fix css issues in options (themes and plugins)

= 3.5.6 = 2023-02-22
* Added: PHP Tests to cover PHP 5.6 to 8.2
* Fixed: Fixed PHP code to be compatible with PHP 5.6, 7.x and 8.x

= 3.5.5 = 2022-05-31
* Fixed: Fix xbox import class that could prevent the niche options from Retrotube theme to be imported

= 3.5.4 = 2022-02-24
* Fixed: Fix access to dashboard on multisites

= 3.5.3 = 2022-02-23
* Updated: Update enhance the error message when auto installation is not working
* Updated: No more technical error message when auto installation is not working
* Updated: Add links to documentation when auto installation is not working
* Fixed: Installation of products that failed when using the automatic installation from the Core

= 3.5.2 = 2022-02-21
* Fixed: Conflicts with WP Legal Pages plugin that prevented the dashboard page to load properly
* Fixed: Installation of products that failed when the product directory already existed

= 3.5.1 = 2022-01-20
* Updated: Change Full Access box color from gold to purple
* Fixed: Products Purchase buttons that were redirecting to a 404 page

= 3.5.0 = 2022-01-13
* Added: Add Child Themes compatibility for child themes based on WP-Script themes
* Fixed: Compatibility issues caused by PHP bellow 7.2

= 3.4.1 = 2021-12-17
* Fixed: No more PHP warning because of cron

= 3.4.0 = 2021-12-02
* Added: New infos when calling init (user_ip, user_email, method (cron / dashboard)
* Added: Prevent non admin users to access the dashboard
* Updated: Remove Discord link
* Fixed: Fix __wakeup and __clone php warning on xbox class
* Fixed: Remove auto connect from dashboard to prevent security issues
* Fixed: Html rendering on error messages in dashboard

= 3.3.6 = 2021-06-02
* Added: Display message from API call if there are some

= 3.3.5 = 2021-05-25
* Fixed: Youtube video preview when a youtube url is entered in the video url field in a video post

= 3.3.4 = 2021-05-21
* Fixed: Fix minor issue

= 3.3.3 = 2021-05-18
* Fixed: Fix minor issue

= 3.3.2 = 2021-05-12
* Updated: License key is now hidden on dashboard
* Fixed: License input field issue on small screens

= 3.3.1 = 2021-04-29
* Fixed: Log system errors that prevented cron tasks to work properly

= 3.3.0 = 2021-04-20
* Added: Italian translation of the plugin
* Added: Chinese translation of the plugin
* Updated: Lodash from v4.17.20 to v4.17.21
* Updated: JS and CSS are now versioned based on files modification date to prevent caching issues on products updates
* Updated: Console.log hash in options
* Updated: Refactoring WP-Script Log File Managemer class
* Fixed: Lodash.js and underscore.js conflicts in plugin options pages
* Fixed: Fatal error when cUrl is not installed on the server
* Fixed: Video preview in video url metabox on WP-SCRIPT themes
* Fixed: Missing license_key warning in ajax check account
* Fixed: Unused import dummy content JavaScript warning
* Fixed: JqColorPicker map JavaScript warning

= 3.2.2 = 2021-01-07
* Fixed: Fix admin pages issue with some 3rd party themes

= 3.2.1 = 2021-01-06
* Fixed: Possible plugins pages loading issues caused by JS files loading issues

= 3.2.0 = 2020-11-09
* Added: Translations of the Dashboard and Log pages
* Added: Available new languages codes are DE, ES, FR, PT, RU, ZH (more to come)
* Added: WordPress locale auto dectection to load the best language for you

= 3.1.1 = 2020-11-05
* Fixed: Php warnings on license activation
* Fixed: Logs folder with htaccess that prevented logs to be saved

= 3.1.0 = 2020-11-04
* Added: Use cache to call WP-Script API (~3x faster)
* Added: Saving changes in options now keeps track of the current option tab
* Updated: Use vue.min.js instead of vue.js for production for better performances
* Updated: Vue.js v2.6.10 to v2.6.12
* Updated: Vue-resource.js v1.0.3 to v1.5.1
* Updated: Lodash v4.17.4 to v4.17.20
* Updated: Clipboard.js v1.6.0 to v2.0.6
* Fixed: JavaScript onload conflict with third party plugins that could prevent WP-Script dashboard to load
* Fixed: Links to products in learn more buttons
* Fixed: WP-Script menu is now displayed to admin only
* Fixed: Theme options page is now accessible to admin only
* Fixed: Products updates notices are now displayed for admin only
* Fixed: Logs table display issue on mobile
* Fixed: WP-Script tabs display issue on mobile
* Fixed: Logo and social icons display issues on mobile

= 3.0.7 = 2020-08-14
* Fixed: Fix cURL error 28 that prevented the WP-Script dashboard to load properly

= 3.0.6 = 2020-08-12
* Fixed: Fix jQuery().live is not a function since WordPress 5.5

= 3.0.5 = 2020-08-11
* Added: Twitter + Discord next to WP-Script logo
* Fixed: Plugin dropdown menu displayed behind some elements

= 3.0.4 = 2020-06-26
* Fixed: Fix 403 errors with admin-ajax.php when Wordfence is activated

= 3.0.3 = 2020-04-14
* Updated: Remove SSL verification on init
* Fixed: Remove dependencies for plugins icons

= 3.0.2 = 2019-11-25
* Updated: Thumbs and Partners columns are back in admin posts listings
* Fixed: WP_Filesystem() PHP Fatal Error

= 3.0.1 = 2019-11-21
* Fixed: Issues to update products data which prevented some installation and update features to work properly

= 3.0.0 = 2019-11-19
* Added: New WPSCORE_Api class
* Added: New WPSCORE_Log class
* Added: New WPSCORE_Exception class
* Updated: Minimal PHP version 5.6.20 compatibiliy
* Fixed: Header already sends issue on plugin activation/deactivation
* Fixed: PHP Warning caused by WordPress 5.3
* Fixed: PHP Warning on product connexion
* Fixed: WP-Script Logs loading issue
* Fixed: Fix minor bugs

= 2.6.0 = 2019-08-27
* Added: Wordfence compatibilty on ajax requests for all WP-Script products
* Added: Visual improvements on Activate / Deactivate buttons
* Updated: Update notification for plugins and themes is now visible only when the current version is lower (no more different) than the latest
* Fixed: Use gmdate() instead of date() to prevent durations issues on some servers configuration
* Fixed: Visuals bug on the dashboard page
* Fixed: Curl settings in Xbox options framework that prevented Niches to be activated with Retrotube in some cases
* Fixed: Warning Req. icon that wasn't displayed when all required PHP elements are not installed

= 2.5.0 = 2019-07-26
* Added: New plugin WPS Disclaimer compatibility
* Fixed: Fix minor bugs

= 2.4.5 = 2019-06-26
* Updated: Niches displaying in theme options

= 2.4.4 = 2019-06-14
* Added: New niches import system for themes

= 2.4.3 = 2019-06-12
* Fixed: Fix default options on plugins activation
* Fixed: Fix minor bugs

= 2.4.2 = 2019-06-10
* Updated: CSS style
* Fixed: The site is experiencing technical difficulties issue

= 2.4.1 = 2019-06-05
* Fixed: Warnings on plugin activation with some PHP versions

= 2.4.0 = 2019-05-22
* Updated: Plugin Updater Class methods to be able to upgrade future versions of WP-Script plugins

= 2.3.0 = 2019-05-10
* Updated: New look to stick to the new wp-script.com design
* Updated: Vue.js version to v2.6.10
* Updated: Javascript code refactored
* Updated: CSS refactored to use the BEM methodology
* Updated: CSS splitting for faster loading
* Fixed: Products options that didn't work on localhost

= 2.2.0 = 2019-03-04
* Updated: Vue.js version to v2.6.2
* Fixed: Options pages that don't load when domain path contains themes or plugins strings (extremely rare)
* Fixed: All Bootstrap JS loading collisions that could provide some issues
* Fixed: Xbox library to be 100% compatible with PHP 7.0

= 2.1.0 = 2019-01-29
* Updated: Vue.js version to v2.5.21
* Fixed: Fix minor bugs

= 2.0.2 = 2018-11-28
* Updated: Code updated to manage more than 10 sub-menus and prepare the future
* Fixed: Fix minor bugs

= 2.0.1 = 2018-11-15
* Fixed: Gutenberg lodash.js collision with WP-Script Core lodash.js that prevented to download a logo in the theme options
* Fixed: Fix minor bugs

= 2.0.0 = 2018-10-29
* Added: WP-Script GOLD manager box and features
* Updated: Webm video format compatibility for themes video information metabox
* Fixed: Products update links fixed on the Theme Options page

= 1.0.24 = 2018-09-28
* Updated: Products update links removed from the notice in the dashboard page. To update a product, just click on the Update green buttons in the updatable products

= 1.0.23 = 2018-08-03
* Updated: Better error detection with some servers configuration when saving license key or creating an account

= 1.0.22 = 2018-08-01
* Fixed: Products installation/update issues with some servers configuration

= 1.0.21 = 2018-07-04
* Updated: Theme options compatibility
* Fixed: Fix minor bugs

= 1.0.20 = 2018-06-27
* Fixed: API calls errors when SERVER_NAME is not detected
* Fixed: HTTP / HTTPS server misconfiguration that can prevent assets (js/css) to be loaded
* Fixed: Fix minor bugs

= 1.0.19 = 2018-06-15
* Fixed: Saving options that doesn't work in some cases
* Fixed: Fix minor bugs

= 1.0.18 = 2018-06-11
* Fixed: Fix cUrl errors
* Fixed: Fix missing data in the dashboard in some cases after updating the Core
* Fixed: Fix minor bugs

= 1.0.17 = 2018-06-08
* Updated: Update cUrl to v7.34.0 requirement doesn't block products anymore if not installed
* Fixed: Modalbox position issue when clicking on a button in the Tools options of WP-Script themes
* Fixed: Fix minor bugs

= 1.0.16 = 2018-06-06
* Added: Add cUrl and cUrl to v7.34.0 requirements detection, preventing random issues
* Fixed: SERVER_ADDR Issues for local servers
* Fixed: Fix minor bugs

= 1.0.15 = 2018-05-25
* Fixed: HTTP / HTTPS server misconfiguration that can prevent the Core to work properly
* Fixed: Fix minor bugs

= 1.0.14 = 2018-05-07
* Added: Message in a modal box when there is a server error while installing/updating a product
* Fixed: All products reset button in options tab that didn't work

= 1.0.13 = 2018-04-13
* Updated: Product activation is no longer possible if all required PHP elements are not installed. This prevents products side effects
* Updated: Product updates message and links (Update link redirects to the dashboard | Changelog link redirects to wp-script.com changelog page)
* Fixed: Fatal error Class SimpleXMLElement not found on some products activation
* Fixed: PHP notices when WP_DEBUG is activated
* Fixed: Fix minor bugs

= 1.0.12 = 2018-04-09
* Added: Product name column in logs to filter logs by product
* Added: Link to product details on products images in dashboard
* Updated: WP-Script admin pages logo
* Updated: WP-Script menu logo
* Updated: All WP-Script plugins tabs are now collapsed in one tab with sub menus
* Fixed: Dropdown options that didn't work anymore because of bootstrap conflict
* Fixed: Fix minor bugs

= 1.0.11 = 2018-03-20
* Added: Namespace has been added to Bootstrap to prevent conflicts with other plugins
* Updated: Compatibility with themes and plugins new versions
* Fixed: Google Font in Options pages are now loaded over HTTPS

= 1.0.10 = 2018-02-28
* Fixed: API calls errors when SERVER_NAME is empty
* Fixed: Fix minor bugs

= 1.0.9 = 2018-02-14
* Fixed: Empty thumbnail in the Video Information metabox

= 1.0.8 = 2018-01-16
* Updated: Improvement of the video preview under the video URL field in the Video Information metabox. Displays now videos from YouTube, Google Drive and the most popular adult tubes. The old version displayed only MP4 videos.
* Fixed: Fix minor bugs

= 1.0.7 = 2017-12-13
* Fixed: Error logs removed

= 1.0.6 = 2017-12-05
* Fixed: Loading submenu issue

= 1.0.5 = 2017-12-02
* Fixed: Thumbnails displaying issue in admin posts

= 1.0.4 = 2017-12-01
* Added: Prevent any third party plugins scripts and css conflict on WP-Script pages
* Fixed: Displaying issues with some WP-Script themes options
* Fixed: Fix minor bugs

= 1.0.3 = 2017-11-21
* Fixed: Fatal error when activating wp-script core on PHP bellow 3.5.0
* Fixed: Fatal error when activating Retro Tube Theme manually and wp-script core is not installed

= 1.0.2 = 2017-11-13
* Fixed: Admin displaying issue when using Cloudflare or CDN service
* Fixed: Fix minor bugs

= 1.0.1 = 2017-11-08
* Fixed: Core Auto-update issue (please replace WP-Script Core Plugin 1.0.0 by 1.1. manualy)
* Fixed: Core Upload issue when PHP allowed memory size si too small (less than 2MB)
* Fixed: RetroTube Theme installation / activation issues
* Fixed: Fix minor bugs

= 1.0.0 = 2017-11-02
* Added: Initial release

