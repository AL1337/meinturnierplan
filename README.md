# MeinTurnierplan

A WordPress plugin to display tournament tables and match lists using custom post types, supporting Gutenberg blocks, widgets, and shortcodes.

## Features

- **Two Custom Post Types**:
  - **Tournament Tables** (`mtp_table`): Display standings, rankings, and statistics
  - **Match Lists** (`mtp_match_list`): Display scheduled matches and results
- **Multiple Display Methods**:
  - **Gutenberg Blocks**: Native block editor support for both tables and matches
  - **Shortcodes**: `[mtp-table]` and `[mtp-matches]` with extensive customization options
  - **Widgets**: Legacy widget support for both content types
- **Extensive Customization**: Control colors, fonts, borders, spacing, and more
- **External Integration**: Fetch data from external tournament management systems via IDs
- **Responsive Design**: Mobile-friendly styling with automatic adjustments
- **Real-time Preview**: AJAX-powered preview while editing in the admin area
- **Internationalization**: Multi-language support (English, German included)

## Installation

1. Upload the plugin folder to `/wp-content/plugins/meinturnierplan-wp/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Tournament Tables** or **Matches** in the admin menu to create your first content

## Usage

### Creating Content

#### Tournament Tables

1. Navigate to **Tournament Tables** → **Add New**
2. Enter a title for your tournament table
3. Configure settings in the **Table Settings** meta boxes:
   - Set external tournament ID (if using external data source)
   - Choose language, group, and display options
   - Customize colors, fonts, borders, and spacing
   - Toggle visibility of specific columns (wins, losses, logos, etc.)
4. Use the **Preview** section to see changes in real-time
5. Publish the table

#### Match Lists

1. Navigate to **Matches** → **Add New**
2. Enter a title for your match list
3. Configure settings in the **Matches Settings** meta boxes:
   - Set external tournament ID (if using external data source)
   - Choose language, group, and match number filters
   - Customize styling options (colors, fonts, spacing)
   - Toggle visibility of specific information (icons, flags, times, etc.)
4. Preview your changes
5. Publish the match list

### Displaying Content

#### Automatic Display on Single CPT Pages

**New in v0.3.1:** When you visit a single tournament table or match list page directly, the plugin automatically displays the content with all configured settings. No shortcode insertion needed!

Simply navigate to the permalink of your tournament table or match list (e.g., `/tournament-table/my-tournament/` or `/tournament-match-list/my-matches/`), and the content will be automatically rendered.

#### Using Gutenberg Blocks

1. Edit any post or page in the block editor
2. Add the **Tournament Table** or **Matches** block
3. Select your table or match list from the dropdown
4. The block will automatically inherit all settings from the post
5. Publish your page

#### Using Shortcodes

##### Tournament Table Shortcode

```
[mtp-table id="external-id"]
[mtp-table post_id="123"]
[mtp-table id="external-id" lang="de" group="A"]
```

**Common Attributes:**
- `id`: External tournament ID from tournament management system
- `post_id`: WordPress post ID (uses saved settings from the post)
- `lang`: Language code (`en`, `de`, etc.)
- `group`: Filter by group name
- `width`: Override table width
- `height`: Override table height

**Styling Attributes:**
- `s-size`: Font size (default: 9)
- `s-sizeheader`: Header font size (default: 10)
- `s-color`: Text color (hex without #)
- `s-maincolor`: Main/accent color (default: 173f75)
- `s-padding`: Table padding (default: 2)
- `s-innerpadding`: Inner cell padding (default: 5)
- `s-bgcolor`: Background color with opacity (8-char hex)
- `s-logosize`: Logo size (default: 20)
- `s-bcolor`: Border color (default: bbbbbb)
- `s-bsizeh`: Horizontal border size (default: 1)
- `s-bsizev`: Vertical border size (default: 1)
- `s-bbcolor`: Header bottom border color
- `s-bbsize`: Header bottom border size (default: 2)
- `s-bgeven`: Even row background color
- `s-bgodd`: Odd row background color
- `s-bgover`: Hover background color
- `s-bghead`: Header background color

**Display Options:**
- `sw`: Suppress wins/losses/draws (1 to hide)
- `sl`: Suppress logos (1 to hide)
- `sn`: Suppress number of matches (1 to hide)
- `bm`: Projector/presentation mode (1 to enable)
- `nav`: Enable group navigation (1 to enable)

##### Matches Shortcode

```
[mtp-matches id="external-id"]
[mtp-matches post_id="456"]
[mtp-matches id="external-id" lang="de" group="A" gamenumbers="1,2,3"]
```

**Common Attributes:**
- `id`: External tournament ID
- `post_id`: WordPress post ID
- `lang`: Language code
- `group`: Filter by group
- `gamenumbers`: Comma-separated list of match numbers to display

**Styling Attributes:** (Same as table shortcode, plus:)
- `s-ehrsize`: Extra header row size
- `s-ehrtop`: Extra header top margin
- `s-ehrbottom`: Extra header bottom margin

**Display Options:**
- `si`: Show icons (1 to show)
- `sf`: Show flags (1 to show)
- `st`: Show times (1 to show)
- `sg`: Show groups (1 to show)
- `sr`: Show rounds (1 to show)
- `se`: Show extra info (1 to show)
- `sp`: Show participants (1 to show)
- `sh`: Show headers (1 to show)
- `bm`: Projector/presentation mode (1 to enable)

#### Using Widgets

##### Tournament Table Widget

1. Go to **Appearance** → **Widgets**
2. Add the **Tournament Table** widget to any widget area
3. Enter a widget title (optional)
4. Select a tournament table from the dropdown
5. Save the widget

The widget automatically uses all settings configured in the selected tournament table post.

##### Matches Table Widget

1. Go to **Appearance** → **Widgets**
2. Add the **Matches Table** widget to any widget area
3. Enter a widget title (optional)
4. Select a match list from the dropdown
5. Save the widget

The widget automatically uses all settings configured in the selected match list post.

## File Structure

```
meinturnierplan-wp/
├── meinturnierplan-wp.php                    # Main plugin file
├── uninstall.php                             # Uninstall cleanup script
├── README.md                                  # This file
├── CHANGELOG.md                               # Version history
├── includes/
│   ├── class-mtp-plugin.php                  # Core plugin class
│   ├── class-mtp-installer.php               # Plugin activation/deactivation
│   ├── class-mtp-requirements-checker.php    # System requirements validation
│   ├── class-mtp-assets.php                  # Asset management
│   ├── class-mtp-admin-utilities.php         # Admin helper functions
│   ├── class-mtp-single-content-filter.php   # Auto-populate single CPT pages
│   ├── class-mtp-table-post-type.php         # Tournament table CPT
│   ├── class-mtp-table-admin-meta-boxes.php  # Table admin interface
│   ├── class-mtp-table-renderer.php          # Table HTML rendering
│   ├── class-mtp-table-shortcode.php         # Table shortcode handler
│   ├── class-mtp-table-widget.php            # Table widget
│   ├── class-mtp-table-block.php             # Table Gutenberg block
│   ├── class-mtp-table-ajax-handler.php      # Table AJAX preview
│   ├── class-mtp-matches-post-type.php       # Match list CPT
│   ├── class-mtp-matches-admin-meta-boxes.php # Matches admin interface
│   ├── class-mtp-matches-renderer.php        # Matches HTML rendering
│   ├── class-mtp-matches-shortcode.php       # Matches shortcode handler
│   ├── class-mtp-matches-widget.php          # Matches widget
│   ├── class-mtp-matches-block.php           # Matches Gutenberg block
│   └── class-mtp-matches-ajax-handler.php    # Matches AJAX preview
├── assets/
│   ├── css/
│   │   └── style.css                         # Frontend styles
│   └── js/
│       ├── admin.js                          # Admin scripts
│       ├── frontend.js                       # Frontend scripts
│       ├── tournament-table-block.js         # Table block editor
│       └── tournament-matches-block.js       # Matches block editor
├── blocks/
│   ├── tournament-table/
│   │   └── block.json                        # Table block configuration
│   └── tournament-matches/
│       └── block.json                        # Matches block configuration
└── languages/
    ├── meinturnierplan.pot                   # Translation template
    ├── meinturnierplan-de_DE.po              # German translations
    └── meinturnierplan-de_DE.mo              # Compiled German translations
```

## Development

The plugin follows WordPress coding standards and best practices:

- **Security**: Proper sanitization, validation, and nonce verification
- **Internationalization**: Full i18n support with text domain `meinturnierplan`
- **Modern WordPress**: Support for Gutenberg blocks and REST API
- **Clean Architecture**: Separation of concerns with dedicated classes for each feature
- **Object-Oriented**: Class-based structure with singleton pattern for main plugin class
- **AJAX Integration**: Real-time preview functionality without page reloads
- **Responsive Design**: Mobile-first approach with CSS breakpoints

## Customization

### Adding New Options

To add new customization options:

1. Add the meta field to the appropriate admin meta box class (`class-mtp-table-admin-meta-boxes.php` or `class-mtp-matches-admin-meta-boxes.php`)
2. Handle saving in the `save_meta_boxes()` method
3. Update the renderer class to use the new option
4. Add support in shortcode attributes if needed
5. Update widget class to pass the new option
6. Update block registration if the option should be available in blocks

### Styling

The plugin generates inline styles based on user settings, but you can override with custom CSS targeting these classes:

**Tournament Tables:**
- `.mtp-tournament-table`: Main table container
- `.tdRank`, `.tdRankTeamName`, `.tdWins`, etc.: Table cells
- Custom data attributes for advanced targeting

**Matches:**
- `.mtp-matches-container`: Matches container
- `.mtp-match-row`: Individual match row
- Custom data attributes for styling specific elements

### Extending Functionality

The plugin architecture supports extension through:

- Custom renderers (extend `MTP_Table_Renderer` or `MTP_Matches_Renderer`)
- Additional shortcode attributes
- Custom post meta fields
- WordPress hooks and filters (action/filter hooks throughout the codebase)

## API Integration

The plugin supports integration with external tournament management systems:

1. Store your external tournament ID in the post meta
2. The renderer fetches data via iframe embedding
3. All styling and display options are applied to the embedded content
4. Supports both tournament tables and match lists

## Requirements

- **WordPress**: 6.3 or higher (required for Block API v3)
- **PHP**: 7.4 or higher
- **Browser**: Modern browsers with JavaScript enabled for admin features

The plugin will automatically check these requirements on activation and display a friendly error message if your system doesn't meet them.

## Changelog

### Version 0.3.2
- **Requirements Checker**: Added system requirements validation (PHP 7.4+, WordPress 6.3+)
- **Uninstall Hook**: Proper cleanup when plugin is deleted
- Improved code organization and documentation
- Enhanced security and WordPress.org compliance

### Version 0.3.1
- **Auto-populate Single CPT Pages**: Single tournament table and match list pages now automatically display their content without manual shortcode insertion
- The content filter retrieves all saved settings and renders the appropriate table or match list on single CPT pages

### Version 0.3.0
- Added Gutenberg block support for both tables and matches
- Enhanced admin interface with better organization
- Improved AJAX preview functionality

### Version 0.2.0
- Added Matches custom post type
- Added Matches Gutenberg block
- Added Matches widget
- Added Matches shortcode
- Extensive styling customization options
- AJAX-powered live preview
- Multi-language support

### Version 0.1.0
- Initial release
- Tournament Tables custom post type
- Basic shortcode and widget support

## License

GPL v2 or later

## Author

Roman Perevala

## Support

For issues, feature requests, and contributions, please visit:
https://github.com/danfisher85/meinturnierplan-wp
