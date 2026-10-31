# Phase 2: option inventory

Working document for the "single source of truth" refactor (branch `1.4.0`).
It lists every option the plugin knows, where each one is currently defined,
and the inconsistencies the registry has to resolve. Generated from the code
as of 1.3.0; the golden tests in `tests/Unit/GoldenTest.php` freeze the
behaviour described here.

## Where options are defined today

Each option is spelled out independently in all of these places. Any change
has to be repeated in each one, which is how the `s-wrap`, `participant`,
`lang` and `post_id` bugs happened.

| Place | Table | Matches |
|---|---|---|
| Renderer: styling map, flags, filters, defaults | `class-mtrn-table-renderer.php` | `class-mtrn-matches-renderer.php` |
| Shortcode: accepted attributes | `class-mtrn-table-shortcode.php` | `class-mtrn-matches-shortcode.php` |
| AJAX preview: POST fields, defaults, attribute build | `class-mtrn-table-ajax-handler.php` | `class-mtrn-matches-ajax-handler.php` |
| Meta box: defaults, form fields, preview attributes, generated shortcode, save list, checkbox list | `class-mtrn-table-admin-meta-boxes.php` | `class-mtrn-matches-admin-meta-boxes.php` |
| Block: meta to attribute mapping | `class-mtrn-table-block.php` | `class-mtrn-matches-block.php` |
| Widget: meta to attribute mapping | `class-mtrn-table-widget.php` | `class-mtrn-matches-widget.php` |
| Single post view: meta to attribute mapping, own colour math | `class-mtrn-single-content-filter.php` (both) | |
| Sanitising: field-name lists per type | `class-mtrn-admin-utilities.php` (both) | |
| JS shortcode updater: field ids, attribute names | `admin-table-meta-boxes.js` | `admin-matches-meta-boxes.js` |
| JS preview: POST fields, conditional rows | `admin-table-preview.js` | `admin-matches-preview.js` |

Line counts of the paired files: 4,883 in total, 54 to 93 percent similar
pairwise (measured with difflib in October 2026).

## The options

Columns: internal key (meta key is `_mtrn_` + key, admin field id is
`mtrn_` + key), who uses it, shortcode attribute, URL parameter sent to the
embed, renderer default, admin default, sanitising, admin label and group.

### Source and filters

| Key | Applies | Shortcode attr | URL | Renderer default | Admin default | Sanitise | Label / group |
|---|---|---|---|---|---|---|---|
| `tournament_id` | both | `id` | `id` | required | `''` | text | Tournament ID / Basic Settings |
| (none) | both | `post_id` | (selects the post whose meta is used) | | | | |
| `language` | both | `lang` (internally `setlang`) | `setlang`, omitted for `en`; also selects the service domain | `en` | from site/user locale | allow-list, else `en` | Language / Basic Settings |
| `group` | both | `group` | `gr` | `''` (service default) | `''` | text | Group / Display Options. Select filled from the API: 1-based group index, `90` = final round. Matches: `''` = all matches |
| `participant` | matches | `participant` | `tm` | `''` | `-1` | text | Participant / Display Options. `-1` and `''` both mean all |
| `match_number` | matches | `gamenumbers` (renderer also accepts `match_number`, used by the preview) | `mn` | `''` | `''` | text | Match number (from-to) / Display Options |
| `width`, `height` | both | `width`, `height` | iframe attributes | 300 / 200 | 300 / 152 | text | hidden fields, written back by the resize script |

### Display flags (checkboxes; meta `0`/`1`; attribute `1` enables; sent as bare URL flag)

| Key | Applies | Attr / URL | Conditional on API field | Label |
|---|---|---|---|---|
| `suppress_wins` | table | `sw` | | Suppress Num Wins, Losses, etc. |
| `suppress_logos` | table | `sl` | | Suppress Logos |
| `suppress_num_matches` | table | `sn` | | Suppress Num Matches |
| `projector_presentation` | both | `bm` | | Projector Presentation |
| `navigation_for_groups` | table | `nav` | | Navigation for Groups |
| `si` | matches | `si` | | Suppress Match Number |
| `sf` | matches | `sf` | `showCourts` | Suppress Court |
| `st` | matches | `st` | | Suppress Times |
| `sg` | matches | `sg` | `showGroups` | Suppress Group |
| `sr` | matches | `sr` | `showReferees` | Suppress Referee |
| `se` | matches | `se` | `finalMatches` present | Suppress Extra Time |
| `sp` | matches | `sp` | `finalMatches` present | Suppress Penalties |
| `sh` | matches | `sh` | `finalMatches` present | Suppress Headlines in Final Matches |
| `responsive` | both | `s-wrap` (`true`) | | Responsive Layout / Responsive. URL `s[wrap]` is always sent as `true`/`false`; `true` also wraps the iframe in `.mtrn-embed-responsive` |

### Styling (attribute `s-*`, URL `s[*]`)

| Key | Applies | Attr | URL | Default | Field | Group / label |
|---|---|---|---|---|---|---|
| `font_size` | both | `s-size` | `size` | 9 | number 6-24 | Typography / Content Font Size (pt) |
| `header_font_size` | both | `s-sizeheader` | `sizeheader` | 10 | number 6-24 | Typography / Header Font Size (pt) |
| `logo_size` | table | `s-logosize` | `logosize` | 20 | number 6-24 | Typography / Logo Size (pt) |
| `ehrsize` | matches | `s-ehrsize` | `ehrsize` | 10 | number 6-24 | Typography / Headlines Font Size (pt) |
| `table_padding` | both | `s-padding` | `padding` | 2 | number 0-50 | Spacing & Layout / Table Padding (px) |
| `inner_padding` | both | `s-innerpadding` | `innerpadding` | 5 | number 0-20 | Spacing & Layout / Inner Padding (px) |
| `ehrtop` | matches | `s-ehrtop` | `ehrtop` | 9 | number 0-20 | Spacing & Layout / Headlines Top Padding (px) |
| `ehrbottom` | matches | `s-ehrbottom` | `ehrbottom` | 3 | number 0-20 | Spacing & Layout / Headlines Bottom Padding (px) |
| `bsizeh` | both | `s-bsizeh` | `bsizeh` | 1 | number 1-10 | Border Settings / Border Horizontal Size (px) |
| `bsizev` | both | `s-bsizev` | `bsizev` | 1 | number 1-10 | Border Settings / Border Vertical Size (px) |
| `bsizeoh` | both | `s-bsizeoh` | `bsizeoh` | 1 | number 1-10 | Border Settings / Table Top and Bottom Border Size (px) |
| `bsizeov` | both | `s-bsizeov` | `bsizeov` | 1 | number 1-10 | Border Settings / Table Left and Right Border Size (px) |
| `bbsize` | both | `s-bbsize` | `bbsize` | 2 | number 1-10 | Border Settings / Table Head Border Bottom Size (px) |
| `text_color` | both | `s-color` | `color` | `000000` | colour | Colors / Text Color |
| `main_color` | both | `s-maincolor` | `maincolor` | `173f75` | colour | Colors / Link & Navigation Color |
| `border_color` | both | `s-bcolor` | `bcolor` | `bbbbbb` | colour | Colors / Border Color |
| `head_bottom_border_color` | both | `s-bbcolor` | `bbcolor` | `bbbbbb` | colour | Colors / Table Head Bottom Border Color |
| `bg_color` + `bg_opacity` | both | `s-bgcolor` | `bgcolor` | `00000000` (admin `000000` + 0 %) | colour + opacity | Background Colors / Background Color |
| `head_bg_color` + `head_bg_opacity` | both | `s-bghead` | `bghead` | `eeeeffff` (`eeeeff` + 100 %) | colour + opacity | Background Colors / Head Background Color |
| `even_bg_color` + `even_bg_opacity` | both | `s-bgeven` | `bgeven` | `f0f8ffb0` (`f0f8ff` + 69 %) | colour + opacity | Background Colors / Even Rows Background Color |
| `odd_bg_color` + `odd_bg_opacity` | both | `s-bgodd` | `bgodd` | `ffffffb0` (`ffffff` + 69 %) | colour + opacity | Background Colors / Odd Rows Background Color |
| `hover_bg_color` + `hover_bg_opacity` | both | `s-bgover` | `bgover` | `eeeeffb0` (`eeeeff` + 69 %) | colour + opacity | Background Colors / Row Hover Background Color |

Colour + opacity pairs are stored as two meta values and combined into an
8-digit hex (`combine_color_opacity`) for the attribute and URL. The embed URL
also carries the fixed `s[wrap]` and the flags listed above.

### Type configuration (the part that differs between the two embeds)

| | Table | Matches |
|---|---|---|
| Post type | `mtrn_table` | `mtrn_match_list` |
| Shortcode | `mtrn-table` | `mtrn-matches` |
| Block | `meinturnierplan/tournament-table` | `meinturnierplan/matches-table` |
| Widget id | `mtrn_table_widget` | `mtrn_matches_widget` |
| Endpoint | `displayTable.php` | `displayMatches.php` |
| Iframe id prefix (the resize script matches on it) | `mtrn-table-` | `mtrn-matches-` |
| Size message type | `iframeSizeMtpTable` | `iframeSizeMtpMatches` |
| Flags | `sw sl sn bm nav` | `bm si sf st sg sr se sp sh` |
| Filters | `gr` | `gr tm mn` |
| Extra styling | `logosize` | `ehrsize ehrtop ehrbottom` |
| AJAX actions | `mtrn_preview_table`, `mtrn_get_groups`, `mtrn_refresh_groups`, `mtrn_get_tables` | `mtrn_preview_matches`, `mtrn_get_matches_groups`, `mtrn_refresh_matches_groups`, `mtrn_get_matches_teams`, `mtrn_refresh_matches_teams`, `mtrn_check_tournament_option`, `mtrn_get_matches` |
| Meta box ids | `mtrn_table_settings`, `mtrn_table_shortcode` | `mtrn_matches_settings`, `mtrn_matches_shortcode` |
| Nonces | `mtrn_table_meta_box` | `mtrn_matches_meta_box` |
| Localized JS config | `mtrnTablePreviewConfig`, `mtrnTableMetaBoxConfig`, `mtrnBlock` | `mtrnMatchesPreviewConfig`, `mtrnMatchesMetaBoxConfig`, `mtrnMatchesBlock` |

## Inconsistencies the registry has to settle

Default position: preserve behaviour (the golden tests enforce it). Items
marked "change" are deliberate small corrections to make during the refactor.

1. **Height default** is 152 in the admin, AJAX preview and generated
   shortcode, but 200 in the renderer and widgets. Only the pre-resize iframe
   height is affected. Keep: one default (152) in the registry, renderer
   fallback 200 stays for shortcodes without a height.
2. **Conditional matches flags** (`sf sg sr se sp sh`): the preview only
   sends them when the tournament has the feature, the generated shortcode,
   the JS updater, block and widget send them regardless. Keep: the embed
   ignores flags that do not apply.
3. **`s-logosize`** is accepted by the matches shortcode and the single-post
   view passes `20`, but the matches renderer never sends it. Change: table
   only in the registry; the attribute is silently ignored for matches as
   today.
4. **`gamenumbers` versus `match_number`**: the shortcode attribute is
   `gamenumbers`, the renderer and preview also accept `match_number`. Keep
   both as accepted spellings of one option.
5. **Sanitising gaps**: `ehrsize`, `ehrtop`, `ehrbottom`, `width`, `height`
   are saved with `sanitize_text_field`, not `absint`, because they are
   missing from the number list. Change: the registry type decides, so they
   become integers like the other numeric fields.
6. **`participant`** default is `-1` in the admin and `''` in the shortcode;
   both mean "all". Keep both; the registry documents `''`/`-1` as "all".
7. **Colour + opacity math** exists three times: `combine_color_opacity`,
   `get_bg_color_with_opacity`, and a private copy in the single-post filter
   (`round($opacity * 2.55)`). The results were identical on PHP 8.3 but not
   on PHP 8.4, where `round()` no longer pre-rounds: 50 % and 90 % came out
   one step lower in the single-post view. Done during preparation: one
   implementation with integer arithmetic, pinned for 0-100 by a unit test.
8. **Matches block** passes neither `participant` nor `gamenumbers` and
   relies on the renderer's meta fallback; the matches widget passes both.
   Keep the result (identical output), one code path.
9. **Checkbox sanitising**: `sanitize_meta_value` only knows the table
   checkboxes; the matches checkboxes never reach it because the save handler
   handles checkboxes first. Registry type `bool` covers both.
10. **Language default**: admin uses the site or user locale, renderer uses
    `en`. Keep: the admin default is a UI convenience, the renderer default
    is the service default.

11. **Attribute order** of the generated shortcode differs between the PHP
    generator (`group` after the styling attributes) and the JS updater
    (`group` after the flags). Shortcode attribute order has no meaning.
    Change: one order, produced by one generator.

12. **Black row backgrounds for posts without saved colours.** When a post
    had no colour meta (created programmatically or before a field existed),
    the renderer sent `000000b0` for the even, odd, hover and head
    backgrounds through `post_id`-only shortcodes, blocks and widgets,
    while explicit shortcodes and the single-post view sent the documented
    defaults. Change (done in step 4): the registry falls back to the
    option default when the meta is empty. Verified against the goldens:
    only those four parameters changed, only in those three channels.
13. **URL parameter order.** The embed URL now lists parameters in the
    registry's order (the admin form order) instead of the old renderer
    mapping order. The service reads parameters by name; the golden
    regeneration for step 4 was checked parameter by parameter.
14. **Iframe id suffix.** The 8-character suffix is now derived from the
    resolved values instead of the raw attribute array, so a block, a
    widget and a shortcode for the same post get the same id. The resize
    script only matches the prefix; the golden comparison masks the suffix.

15. **Width and height fallback.** The single-post view applied the size
    saved by the admin preview, blocks, widgets and `post_id`-only
    shortcodes did not (they started at 300x200 until the resize message
    arrived). Change (step 5): the resolver falls back to the saved size for
    every channel, which reduces the initial layout jump.

## Refactor log

- **Step 4 (registry):** `includes/class-mtrn-options.php` holds every
  option and both type configurations; `includes/class-mtrn-embed-renderer.php`
  renders both embeds from it. The old renderer classes are thin wrappers
  until their callers are migrated. Items 7, 12, 13, 14 resolved.
- **Step 5 (channels):** `class-mtrn-shortcodes.php`, `class-mtrn-blocks.php`,
  `class-mtrn-embed-widget.php` (one class, registered per type) and the
  rewritten single-post filter all call `MTRN_Embed_Renderer::render()` with
  their type and post id. Deleted: both shortcode classes, both block
  classes, both widget classes, both renderer wrappers. Goldens: identical
  except item 15. Item 8 resolved.
- **Step 6 (admin):** `class-mtrn-admin-meta-boxes.php` (one class per type:
  form, preview attributes, generated shortcode and save all from the
  registry), `class-mtrn-ajax-handler.php` (all actions, names unchanged),
  `assets/js/admin-meta-boxes.js`, `assets/js/admin-preview.js` and
  `assets/js/blocks.js` driven by configuration localized from the registry.
  Deleted: both meta box classes, both AJAX handlers, both preview scripts,
  both shortcode updaters, both block editor scripts. Goldens: admin form
  byte-identical; generated shortcodes identical as attribute sets (item 11,
  one order now); saves identical except item 5 (numeric sanitising); the
  preview request no longer carries the separate opacity values, which the
  server never read. Items 5, 9, 11 resolved; item 2 kept.

## Golden tests

`tests/Unit/GoldenTest.php` renders 12 cases (`tests/golden/cases.php`)
through every channel and compares with `tests/golden/expected/`:
shortcode with attributes, `post_id`-only shortcode, block, widget,
single-post view, admin form, admin shortcode box, live-preview AJAX and
the meta keys a save writes. `tests/e2e/admin-js-golden.spec.js` loads the
captured admin forms with the real admin scripts and freezes the generated
shortcode, the rebuilt group / participant selects, conditional row
visibility, the preview payload and the AJAX contract. Regenerate the PHP
goldens first, then the JS goldens, because the latter read the former.

## Compatibility contract (must not change)

Shortcode names and attributes, meta keys, block names and attributes, widget
ids, iframe id prefixes and size message types, AJAX action names, transient
names (`mtrn_groups_*`, `mtrn_teams_*`, `mtrn_data_*`), the
`mtrn_service_notice_dismissed` option, and every translatable string.
Class and file names may change (decided 2026-10-31); the two post types stay
separate.
