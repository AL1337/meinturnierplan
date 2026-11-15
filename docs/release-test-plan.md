# Manual release test plan (1.4.0)

Automated checks cover markup, URLs and the resize script. This plan covers
what only a person in a browser can judge: the admin screens, the editor, the
real embeds, and the visual result. Allow about 45 minutes.

## Setup

- Local site running, `WP_DEBUG` and `WP_DEBUG_LOG` on, `debug.log` emptied.
- Browser developer tools open on the Console tab for every page you visit;
  there must be no plugin errors (anything mentioning `mtrn`).
- Public tournaments found on meinturnierplan.de on 2026-11-01 (they can
  disappear; if one does, take any current one from the homepage):
  - `1447850327`: 4 groups, courts, group column
  - `1447257223`: 2 groups with final round, courts, group column
  - `06ik9vnyqf`: final round only (12 final matches), courts
  - `11xu7j8c2z`: referees
  - `0cb4feoy6h`: plain single group, nothing optional
- One Tournament Table post and one Match List post, both saved.

## 1. Tournament Table edit screen

1. Open the post. Settings form on the left in this order: Basic Settings,
   Display Options, Responsive, Typography, Spacing & Layout, Border Settings,
   Colors, Background Colors. Preview on the right, Shortcode Generator in the
   sidebar.
2. Enter `1447850327`. The Group select fills with Group A to D within a
   second; the preview shows the table in the selected language.
3. Change Language to German: the preview reloads from meinturnierplan.de
   with German headers. Pick Group B: the preview shows group B.
4. Tick "Suppress Num Wins, Losses, etc.": the W/D/L columns disappear in
   the preview. Tick "Navigation for Groups": arrows and the group name appear
   above the table.
5. Change Link & Navigation Color to red with the colour picker: the arrows
   and the "Show Full Tournament" link turn red. Move an opacity slider: the
   percentage label follows and the preview updates.
6. Tick "Responsive Layout": the preview gains a horizontal scrollbar when the
   table is wider than the column; the generated shortcode shows
   `s-wrap="true"`.
7. Read the generated shortcode: it starts with `[mtrn-table id="1447850327"
   post_id="…" lang="de" group="2"`, then the enabled flags, `s-wrap`, the
   styling attributes, and ends with `width` and `height`. Click Copy; the
   success message appears.
8. Click "Update", reload: every value persists, the preview matches.
9. Enter a wrong tournament ID: the Group select shows the placeholder, the
   preview shows the service's error page, no console error.

## 2. Match List edit screen

1. Enter `1447257223`. Group select shows All Matches, Group A, Group B,
   Final Round. Participant select lists the eight teams. The rows Suppress
   Court and Suppress Group are visible; Suppress Referee is hidden; the
   three final-round rows (Extra Time, Penalties, Headlines) are visible.
2. Switch to `11xu7j8c2z`: Suppress Referee appears, the final-round rows
   hide. Switch to `0cb4feoy6h`: all conditional rows hide.
3. Back to `1447257223`. Pick a participant: the preview lists only that
   team's matches. Enter `1-5` as match number: only matches 1 to 5 remain.
   Tick Suppress Times: the start column disappears.
4. Pick Final Round as group: the preview shows the knockout matches.
5. Update, reload, values persist. Generated shortcode contains
   `participant="…"` and `gamenumbers="1-5"`.

## 3. Block editor

1. New page, insert "Tournament Table" and "Matches" blocks. Each shows a
   placeholder with a select listing your saved posts.
2. Select a post in each, publish, view the page: both embeds render and
   resize to their content; nothing is clipped at 200 px.
3. With the site language set to German (Settings, General), the block names
   and "Select a Tournament Table:" labels appear in German in the inserter.

## 4. Widgets

1. Appearance, Widgets: add "Tournament Table" and "Matches Table" widgets to
   a sidebar, choose the posts, save.
2. View a page with that sidebar: both widgets render their embeds with the
   posts' settings, including Responsive if enabled on the post.

## 5. Shortcodes on a page

Create one page with these shortcodes, one below the other, and view it:

1. The full shortcode copied from the Tournament Table post: identical to the
   admin preview.
2. `[mtrn-table post_id="X"]`: identical to 1, which proves language,
   colours, flags, group and saved size come from the post.
3. `[mtrn-matches post_id="Y" participant="1" gamenumbers="1-3" lang="en"]`:
   English, one team, matches 1 to 3, other settings from the post.
4. `[mtrn-table id="1447850327" lang="fr" group="90" sw="1"]` and
   `[mtrn-matches id="06ik9vnyqf" group="90" se="1" sp="1" sh="1"]`: French
   final ranking without W/D/L; French final matches without extra-time and
   penalty columns.
5. `[mtrn-table id="doesnotexist"]`: the service error text, and after about
   five seconds the iframe settles at 200 px high.

## 6. Single views

Open each post's own URL (View link in the admin): the embed appears above
the (empty) content and matches the admin preview.

## 7. Resize and responsive behaviour

1. On the shortcode page, open DevTools, toggle the device toolbar, choose a
   phone width. Embeds with Responsive on scroll horizontally inside their
   box; the page itself has no horizontal scrollbar.
2. If the site has a cookie banner or chat widget that loads later, wait ten
   seconds after load: the embeds must keep their size.
3. Reload the page several times: no embed stays at its initial 300 x 200.

## 8. Lifecycle

1. Deactivate and reactivate the plugin: both post types and their single
   views still work, the service notice behaves as before.
2. Only on a throwaway site: delete the plugin. Posts, meta, transients and
   the notice option are removed.

## 9. Wrap-up

- `debug.log` is still empty.
- Nothing in the browser console mentions `mtrn`.
- The admin and public pages render the same as with 1.3.0 except for the
  documented changes: no black backgrounds for posts without saved colours,
  embeds start at the saved size, shortcode attribute order.
