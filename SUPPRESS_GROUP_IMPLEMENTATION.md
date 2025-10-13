# Suppress Group Field Implementation

## Overview
Added a new conditional "Suppress Group" field for Matches that appears only when the tournament's JSON data has `showGroups` set to `true`.

## Implementation Details

### 1. Admin Meta Boxes (`class-mtp-matches-admin-meta-boxes.php`)
- **Added conditional checkbox field** after the "Suppress Court" field in the Display Options section
- Field name: `mtp_sg`
- Label: "Suppress Group"
- Description: "Enable suppression of group information in the matches table."
- **Conditional display**: Only shown when `showGroups` is `true` in tournament JSON

### 2. JavaScript Conditional Logic
Updated the `checkConditionalFields()` function to:
- Check the tournament JSON for `showGroups` property via AJAX
- Show/hide the `#mtp_sg_row` element based on the `showGroups` value
- Hide the field by default if no tournament ID is present

### 3. Parameter Flow
When the "Suppress Group" checkbox is checked, the system adds `sg` parameter to:
- **Preview iframe**: Included in `build_preview_attributes()` method
- **Generated shortcode**: Added in `generate_shortcode()` method  
- **Frontend shortcode**: Processed in shortcode attributes with default value `'0'`

### 4. Supporting Files
The following files already had full `sg` parameter support:
- `class-mtp-matches-ajax-handler.php` - Handles AJAX preview requests with `sg` parameter
- `class-mtp-matches-renderer.php` - Adds `sg` to iframe URL parameters
- `class-mtp-matches-shortcode.php` - Accepts `sg` attribute in shortcode

## Usage

### In Admin
1. Create/edit a Match List post
2. Enter a Tournament ID
3. If the tournament has `showGroups: true` in its JSON data, the "Suppress Group" checkbox will appear
4. Check the box to suppress group information in the matches display

### In Shortcode
```
[mtp-matches id="1753883027" sg="1" ...]
```

### In iframe URL
The parameter is passed as:
```
https://www.meinturnierplan.de/displayMatches.php?id=1753883027&sg&...
```

## Testing
To test the implementation:
1. Use a tournament ID that has `showGroups: true` in its JSON response
2. Verify the "Suppress Group" field appears in the admin
3. Check the checkbox and verify the preview updates
4. Verify the generated shortcode includes `sg="1"`
5. Test the shortcode on a page to ensure groups are suppressed

## Date Implemented
October 13, 2025
