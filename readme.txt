=== MeinTurnierplan ===
Contributors: meinturnierplan, ramzesimus
Tags: tournament, sports, table, matches, standings
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display tournament tables and match lists using custom post types, supporting Gutenberg blocks, widgets, and shortcodes.

== Description ==

MeinTurnierplan allows you to display tournament tables and match schedules from meinturnierplan.de on your WordPress site. Perfect for sports clubs, leagues, and tournament organizers who want to showcase standings, rankings, and match schedules on their WordPress website.

== External Services ==

**MeinTurnierplan.de Service**

This plugin uses [MeinTurnierplan.de](https://www.meinturnierplan.de/) for both displaying tournament content and retrieving tournament configuration data.

**What it does:**

1. **Frontend Display (Public-Facing):**
   - Displays tournament tables and match schedules to site visitors via iframe embeds
   - **Endpoints Used:**
     * `https://www.meinturnierplan.de/displayTable.php` (for tournament standings)
     * `https://www.meinturnierplan.de/displayMatches.php` (for match schedules)
   - **When:** When a visitor loads a page with tournament content (shortcode, block, or widget)

2. **Admin Configuration (Admin Area Only):**
   - Provides tournament structure data via JSON API to help administrators configure displays
   - **Endpoint Used:**
     * `https://www.meinturnierplan.de/json/json.php` (tournament structure data)
   - **When:** Only in WordPress admin area when:
     * Administrator enters a Tournament ID in settings
     * Administrator clicks "Refresh Groups" or similar refresh buttons
     * Admin preview is loaded or refreshed
   - **What it retrieves:**
     * Tournament groups/divisions structure
     * Team lists and names
     * Tournament options (showCourts, showGroups, showReferees, finalMatches)
   - **Purpose:**
     * Auto-populate group selection dropdowns in admin interface
     * Determine which features are available for the tournament
     * Provide better admin user experience with automatic configuration
   - **Data cached:** Retrieved data is cached for 15 minutes to minimize API calls
   - **NOT used on frontend:** JSON API is only contacted from WordPress admin area, never from public-facing pages

* **Data Sent:** Tournament ID only (no personal data, no user information)
* [Privacy Policy](https://www.meinturnierplan.de/legal.php?t=privacy&v=2019-04-20&l=en)
* [Terms of Service](https://www.meinturnierplan.de/legal.php?t=tou&v=2019-04-20&l=en)

**What the Embedded Widgets Collect:**

The embedded widgets:

* **NO tracking scripts** - The embedded widgets do not use Google Analytics or any other analytics services
* **NO cookies** - The widgets do not set any cookies in users' browsers
* **NO third-party resources** - The widgets only load CSS styling from meinturnierplan.de (no Google Fonts, AdSense, or other external services)
* **Communication:** The widgets only use JavaScript to send iframe dimensions to your page for proper display sizing (via postMessage API)

**What Data May Be Collected:**

When users view embedded tournament content, meinturnierplan.de's web server may automatically log:

* IP addresses (standard web server logs)
* Browser type and version (from User-Agent header)
* Referrer URL (your website where the widget is embedded)
* Access timestamp

This is standard web server logging and does not involve cookies, tracking scripts, or persistent user identification.

== Privacy Notice ==

**This plugin itself does not:**

* Track users
* Collect personal data
* Use cookies or localStorage
* Send personal or sensitive data to any server

**Data Transmission:**
The only data sent by this plugin is the Tournament ID to meinturnierplan.de when you explicitly add tournament content (via shortcode, block, or widget) to display on your pages.

**Embedded Widget Behavior:**
The embedded widgets from meinturnierplan.de:

* Do NOT use tracking scripts (no Google Analytics in widgets)
* Do NOT set cookies
* Do NOT load third-party services (no Google Fonts, AdSense, etc.)
* Only communicate iframe dimensions back to your page for proper display

**Standard Web Server Logging:**
Like any web resource, meinturnierplan.de's servers may log standard HTTP request data (IP address, browser type, referrer, timestamp) when serving the embedded content. This is standard practice for all web servers and does not involve user tracking or cookies.

**No Consent Required:**
Because the embedded widgets do not use cookies, tracking scripts, or persistent user identification, no additional cookie consent is required beyond standard web server logging disclosure in your privacy policy.

= Available Languages =

The plugin is available in the following languages:

* English
* German (Deutsch)
* Spanish (Español)
* French (Français)
* Italian (Italiano)
* Polish (Polski)

= Key Features =

**Two Custom Post Types:**

* **Tournament Tables** - Display standings, rankings, and statistics
* **Match Lists** - Display scheduled matches and results

**Multiple Display Methods:**

* **Gutenberg Blocks** - Native block editor support for both tables and matches
* **Shortcodes** - `[mtrn-table]` and `[mtrn-matches]` with extensive customization options
* **Widgets** - Legacy widget support for both content types

**Extensive Customization:**

* Control colors, fonts, borders, and spacing
* Toggle visibility of specific columns (wins, losses, logos, etc.)
* Customize styling options (colors, fonts, spacing)
* Real-time preview while editing in the admin area

**Additional Features:**

* External integration with tournament management systems via IDs
* Responsive design - Mobile-friendly styling with automatic adjustments
* AJAX-powered live preview in admin area
* Automatic display on single custom post type pages

= Usage =

After activation, navigate to **Tournament Tables** or **Tournament Match Lists** in the admin menu to create your first content. You can then display your content using:

1. **Gutenberg Blocks** - Add the Tournament Table or Tournament Match List block to any post or page
2. **Shortcodes** - Use `[mtrn-table id="123"]` or `[mtrn-matches id="456"]`
3. **Widgets** - Add the Tournament Table or Tournament Match List widget to any widget area
4. **Automatic Display** - Visit single tournament table or match list pages directly

= Shortcode Examples =

**Tournament Table:**

* `[mtrn-table id="external-id"]`
* `[mtrn-table post_id="123"]`
* `[mtrn-table id="external-id" lang="de" group="A"]`

**Matches:**

* `[mtrn-matches id="external-id"]`
* `[mtrn-matches post_id="456"]`
* `[mtrn-matches id="external-id" lang="de" group="A"]`

= Links =

* [GitHub Repository](https://github.com/danfisher85/meinturnierplan)
* [Plugin Website](https://www.meinturnierplan.de)

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to **Plugins > Add New**
3. Search for "MeinTurnierplan"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New > Upload Plugin**
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin through the **Plugins** menu in WordPress

= After Activation =

1. Navigate to **Tournament Tables** or **Tournament Match Lists** in the admin menu
2. Click **Add New Tournament Table** or **Add New Tournament Match List** to create your first tournament table or match list
3. Configure settings and styling options
4. Use the preview section to see changes in real-time
5. Publish and display using blocks, shortcodes, or widgets

== Frequently Asked Questions ==

= How do I display a tournament table? =

You have several options:
1. Use the Gutenberg block: Add the "Tournament Table" block to any post or page
2. Use the shortcode: `[mtrn-table post_id="123"]` (replace 123 with your table's post ID)
3. Use the widget: Go to Appearance > Widgets and add the "Tournament Table" widget
4. Visit the single post page directly - content displays automatically

= How do I customize the appearance? =

Each tournament table and match list has extensive customization options in the admin area:
* Configure colors (text, background, borders, hover states)
* Adjust font sizes for headers and content
* Control spacing (padding, margins)
* Toggle visibility of specific columns or information
* Use the live preview to see changes instantly

= What are the shortcode attributes? =

**Tournament Table Shortcode Attributes:**

Common:
* `id` - External tournament ID
* `post_id` - WordPress post ID
* `lang` - Language code (en, de, etc.)
* `group` - Filter by group name
* `width` - Override table width
* `height` - Override table height

Styling:
* `s-size` - Font size (default: 9)
* `s-sizeheader` - Header font size (default: 10)
* `s-color` - Text color (hex without #)
* `s-maincolor` - Main/accent color (default: 173f75)
* `s-padding` - Table padding (default: 2)
* `s-innerpadding` - Inner cell padding (default: 5)
* `s-bgcolor` - Background color with opacity (8-char hex)
* `s-logosize` - Logo size (default: 20)
* `s-bcolor` - Border color (default: bbbbbb)
* `s-bsizeh` - Horizontal border size (default: 1)
* `s-bsizev` - Vertical border size (default: 1)

Display Options:
* `sw` - Suppress wins/losses/draws (1 to hide)
* `sl` - Suppress logos (1 to hide)
* `sn` - Suppress number of matches (1 to hide)
* `bm` - Projector/presentation mode (1 to enable)
* `nav` - Enable group navigation (1 to enable)

**Matches Shortcode Attributes:**

Common:
* `id` - External tournament ID
* `post_id` - WordPress post ID
* `lang` - Language code
* `group` - Filter by group
* `gamenumbers` - Comma-separated list of match numbers

Display Options:
* `si` - Show icons (1 to show)
* `sf` - Show flags (1 to show)
* `st` - Show times (1 to show)
* `sg` - Show groups (1 to show)
* `sr` - Show rounds (1 to show)
* `se` - Show extra info (1 to show)
* `sp` - Show participants (1 to show)
* `sh` - Show headers (1 to show)
* `bm` - Projector/presentation mode (1 to enable)

== Screenshots ==

1. Add a new Tournament Table.
2. Add a New Tournament Match List.
3. Preview of the Tournament Table in the backend.
4. Preview of the Tournament Match List in the backend.

== Changelog ==

= 1.0.3 =
* Fix - Update plugin version

= 1.0.2 =
* Localization - Update Spanish, French, Polish and Italian translations

= 1.0.1 =
* Localization - Add German, Spanish, French, Polish and Italian translations

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.3 =
* Fixes incorrect plugin version

= 1.0.2 =
* Updated Spanish, French, Polish and Italian translations

= 1.0.1 =
* Added translations for multiple languages

= 1.0.0 =
Initial release of MeinTurnierplan. Display tournament tables and match schedules from meinturnierplan.de on your WordPress site.

== Development ==

The plugin follows WordPress coding standards and best practices:

* **Security** - Proper sanitization, validation, and nonce verification
* **Internationalization** - Full i18n support with text domain `meinturnierplan`
* **Modern WordPress** - Support for Gutenberg blocks and REST API
* **Clean Architecture** - Separation of concerns with dedicated classes for each feature
* **Object-Oriented** - Class-based structure with singleton pattern
* **AJAX Integration** - Real-time preview functionality
* **Responsive Design** - Mobile-first approach with CSS breakpoints

== Support ==

For issues, feature requests, and contributions, please visit:
[GitHub Repository](https://github.com/danfisher85/meinturnierplan)

== Credits ==

Developed by Roman Perevala
