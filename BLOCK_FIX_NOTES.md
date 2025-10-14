# Block Click Issue - Fix Applied (v0.2.2)

## Problem
Both the **Matches** and **Tournament Table** blocks could not be selected by clicking on them in the WordPress Block Editor. They could only be selected from the Navigator panel.

Symptoms:
- Block does not become active when clicked
- No selection border appears
- No context menu (move, delete, etc.)
- No block toolbar appears

## Root Cause
The blocks were returning `Placeholder` or `Fragment` components directly without a wrapping clickable `div` element. The `Placeholder` component doesn't properly capture click events for block selection in the Gutenberg editor.

## Solution
Wrapped all return statements with a `div` element that has:
- A class name for styling
- Inline styles including `minHeight` to ensure it's clickable
- Visual borders to indicate it's a block

## Changes Made

### 1. `/assets/js/tournament-matches-block.js`
- Removed `useBlockProps` import (not needed for this approach)
- Wrapped all three return scenarios with clickable `div` elements:
  - Loading state: dashed border, min height 100px
  - Table selected: solid blue border, highlighted background
  - No selection: dashed border, min height 100px
- Added better visual feedback when a table is selected

### 2. `/assets/js/tournament-table-block.js`
- Applied the same fix as matches block
- Wrapped all return statements with clickable `div` elements
- Added conditional rendering to show selected table name
- Improved visual hierarchy and styling

### 3. `/includes/class-mtp-matches-block.php`
- Kept dependencies as `'wp-editor'` (no change needed)

### 4. `/meinturnierplan-wp.php`
- Bumped plugin version from 0.2.0 → 0.2.2
- Forces WordPress to reload JavaScript files

## How to Test

1. **Go to WordPress Admin → Plugins**
2. **Deactivate** and then **Reactivate** the MeinTurnierplan plugin
3. **Hard refresh** your browser (Cmd+Shift+R on Mac, Ctrl+Shift+R on Windows)
4. **Open any page/post** in the Block Editor
5. **Add a Matches or Tournament Table block**
6. **Click directly on the block** in the editor

### Expected Results:
- ✅ Block becomes selected/active when clicked
- ✅ Blue selection border appears around the block
- ✅ Block toolbar appears at the top
- ✅ Context menu (three dots) appears with move, delete, etc.
- ✅ You can drag and move the block
- ✅ Selected table name is displayed inside the block
- ✅ Dropdown allows changing the selection

## Technical Details

### Before (Not Working):
```javascript
return el(Placeholder, {...}, el(SelectControl, {...}));
// Placeholder doesn't capture clicks properly
```

### After (Working):
```javascript
return el('div', 
  { 
    className: 'mtp-matches-block-editor',
    style: { minHeight: '100px', padding: '20px', border: '1px dashed #ddd' }
  },
  el(Placeholder, {...}, el(SelectControl, {...}))
);
// Outer div captures clicks and makes block selectable
```

## Block Structure Now

Both blocks now follow this structure:

```
Clickable DIV wrapper (100px min height, border)
  └─ If loading:
      └─ Placeholder with Spinner
  └─ If table selected:
      ├─ "Selected [Type]:" label (bold)
      ├─ Table name (larger font)
      └─ Dropdown to change selection
  └─ If nothing selected:
      └─ Placeholder with Selector dropdown
```

## Troubleshooting

If blocks still don't work:
1. Check browser console (F12) for JavaScript errors
2. Try in incognito/private window
3. Disable other plugins temporarily to rule out conflicts
4. Clear WordPress object cache if using caching plugins
5. Verify plugin version shows 0.2.2 in WordPress admin
