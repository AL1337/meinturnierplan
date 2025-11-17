=== MeinTurnierplan ===
Contributors: romanperevala
Tags: tournament, sports, table, matches, standings
Requires at least: 6.3
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display tournament tables and match lists using custom post types, supporting Gutenberg blocks, widgets, and shortcodes.

== Description ==

MeinTurnierplan is a comprehensive WordPress plugin for displaying tournament tables and match lists. Perfect for sports clubs, leagues, and tournament organizers who want to showcase standings, rankings, and match schedules on their WordPress website.

= Key Features =

**Two Custom Post Types:**

* **Tournament Tables** - Display standings, rankings, and statistics
* **Match Lists** - Display scheduled matches and results

**Multiple Display Methods:**

* **Gutenberg Blocks** - Native block editor support for both tables and matches
* **Shortcodes** - `[mtp-table]` and `[mtp-matches]` with extensive customization options
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
* Internationalization - Multi-language support (English, German, Spanish, French, Croatian, Italian, Polish, Slovenian, Turkish)
* Automatic display on single custom post type pages

= Usage =

After activation, navigate to **Tournament Tables** or **Matches** in the admin menu to create your first content. You can then display your content using:

1. **Gutenberg Blocks** - Add the Tournament Table or Matches block to any post or page
2. **Shortcodes** - Use `[mtp-table id="123"]` or `[mtp-matches id="456"]`
3. **Widgets** - Add the Tournament Table or Matches widget to any widget area
4. **Automatic Display** - Visit single tournament table or match list pages directly

= Shortcode Examples =

**Tournament Table:**
`[mtp-table id="external-id"]`
`[mtp-table post_id="123"]`
`[mtp-table id="external-id" lang="de" group="A"]`

**Matches:**
`[mtp-matches id="external-id"]`
`[mtp-matches post_id="456"]`
`[mtp-matches id="external-id" lang="de" group="A"]`

= Links =

* [GitHub Repository](https://github.com/danfisher85/meinturnierplan)
* [Plugin Website](https://www.meinturnierplan.de)

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "MeinTurnierplan"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin through the 'Plugins' menu in WordPress

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
2. Use the shortcode: `[mtp-table post_id="123"]` (replace 123 with your table's post ID)
3. Use the widget: Go to Appearance > Widgets and add the "Tournament Table" widget
4. Visit the single post page directly - content displays automatically

= How do I customize the appearance? =

Each tournament table and match list has extensive customization options in the admin area:
* Configure colors (text, background, borders, hover states)
* Adjust font sizes for headers and content
* Control spacing (padding, margins)
* Toggle visibility of specific columns or information
* Use the live preview to see changes instantly

= Can I integrate with external tournament systems? =

Yes! The plugin supports integration with external tournament management systems. Simply enter your external tournament ID in the settings, and the plugin will fetch and display the data with your custom styling applied.

= What languages are supported? =

The plugin is fully internationalized and includes translations for:
* English (default)
* German (de_DE)
* Spanish (es_ES)
* French (fr_FR)
* Croatian (hr)
* Italian (it_IT)
* Polish (pl_PL)
* Slovenian (sl_SI)
* Turkish (tr_TR)

= Can I customize the plugin with CSS? =

Yes! The plugin generates inline styles based on your settings, but you can override them with custom CSS. Main classes include:
* `.mtp-tournament-table` - Main table container
* `.mtp-matches-container` - Matches container
* `.mtp-match-row` - Individual match row

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

1. Tournament table display with customized styling
2. Match list display showing scheduled games
3. Admin interface for tournament table settings
4. Admin interface for match list settings
5. Gutenberg block editor integration
6. Live preview in admin area
7. Widget configuration
8. Responsive mobile view

== Changelog ==

= 1.0.0 =
* Initial release

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
