# Mein Turnierplan WP

A WordPress plugin to display tournament tables using custom post types, supporting widgets, blocks, and shortcodes.

## Features

- **Custom Post Type**: Create and manage tournament tables (`mtp_table`)
- **Two Display Methods**:
  - **Shortcodes**: `[mtp-table id="123" width="300"]`
  - **Widgets**: Add tournament tables to widget areas
- **Customizable Width**: Set table width in pixels
- **Responsive Design**: Mobile-friendly styling
- **Admin Preview**: See changes in real-time while editing

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Tournament Tables' in the admin menu to create your first table

## Usage

### Creating a Tournament Table

1. Navigate to **Tournament Tables** → **Add New** in your WordPress admin
2. Enter a title for your tournament table
3. Set the table width in the **Table Settings** meta box
4. Preview your changes in real-time
5. Publish the table

### Displaying Tables

#### Using Shortcodes

```
[mtp-table id="123"]
[mtp-table id="123" width="400"]
```

**Attributes:**
- `id` (required): The ID of the tournament table post
- `width` (optional): Override the table width in pixels

#### Using Widgets

1. Go to **Appearance** → **Widgets**
2. Add the **Tournament Table** widget to any widget area
3. Select your tournament table from the dropdown
4. Optionally set a custom width
5. Save the widget

4. Save the widget

## Usage

## File Structure

```
meinturnierplan-wp/
├── meinturnierplan-wp.php          # Main plugin file
├── includes/
│   └── class-mtp-table-widget.php  # Widget class
├── assets/
│   └── css/
│       └── style.css               # Frontend styles
└── README.md                       # This file
```

## Development

The plugin follows WordPress coding standards and best practices:

- Proper sanitization and validation of user input
- Nonce verification for security
- Internationalization ready (text domain: `meinturnierplan-wp`)
- Responsive design with mobile breakpoints
- Clean separation of concerns

## Customization

### Adding New Options

To add new customization options:

1. Add the option to the meta box in the `meta_box_callback()` method
2. Handle saving in the `save_meta_boxes()` method
3. Update the `render_table_html()` method to use the new option
4. Add support in shortcode, widget, and block implementations

### Styling

The plugin uses CSS classes that can be customized:

- `.mtp-tournament-table`: Main table container
- `.tdRank`, `.tdRankTeamName`, etc.: Individual cell classes
- Responsive breakpoints at 768px and 480px

## Hooks and Filters

The plugin is designed to be extensible. Future versions may include:

- Action hooks before/after table rendering
- Filters for customizing table output
- Additional shortcode attributes
- More styling options

## Requirements

- WordPress 5.0+
- PHP 7.0+

## License

GPL v2 or later

## Support

For issues and feature requests, please use the GitHub repository.
