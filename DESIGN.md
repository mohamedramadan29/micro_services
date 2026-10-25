---
name: نفّذها (Nafizha)
description: Arabic-first services marketplace drawn as an execution drawing set — ink lines on grid paper, a green stamp for approval.
colors:
  ink: "#2B3038"
  ink-deep: "#1D2127"
  ink-2: "#50545B"
  ink-3: "#60646C"
  green: "#3FB699"
  green-ink: "#287A66"
  green-deep: "#1C5A4B"
  green-wash: "#E3F4EE"
  green-mist: "#F1FAF7"
  paper: "#F4F7F6"
  sheet: "#FFFFFF"
  line: "rgba(43, 48, 56, .14)"
  line-2: "rgba(43, 48, 56, .26)"
  line-3: "rgba(43, 48, 56, .55)"
  alert-red: "#D93025"  # only for counters: `.rf-acc-badge` (account menu) and the navbar bell/messages `.counter` (user 2026-10-20)
typography:
  display:
    fontFamily: "Alexandria, Readex Pro, Segoe UI, Tahoma, sans-serif"
    fontSize: "clamp(1.55rem, 1rem + 1.45vw, 2.3rem)"
    fontWeight: 800
    lineHeight: 1.6
    letterSpacing: "0"
  headline:
    fontFamily: "Alexandria, Readex Pro, Segoe UI, Tahoma, sans-serif"
    fontSize: "clamp(1.5rem, 1.1rem + 1.3vw, 2.1rem)"
    fontWeight: 700
    lineHeight: 1.45
    letterSpacing: "0"
  title:
    fontFamily: "Alexandria, Readex Pro, Segoe UI, Tahoma, sans-serif"
    fontSize: "1.05rem"
    fontWeight: 700
    lineHeight: 1.45
  body:
    fontFamily: "Readex Pro, Segoe UI, Tahoma, sans-serif"
    fontSize: "15.5px"
    fontWeight: 400
    lineHeight: 1.75
  label:
    fontFamily: "Readex Pro, Segoe UI, Tahoma, sans-serif"
    fontSize: "13.5px"
    fontWeight: 500
    lineHeight: 1.5
  dimension:
    fontFamily: "Readex Pro, Segoe UI, Tahoma, sans-serif"
    fontSize: "12.5px"
    fontWeight: 400
    fontFeature: "\"tnum\" 1"
rounded:
  pill: "4px"
  sm: "6px"
  lg: "10px"
  round: "50%"
spacing:
  gutter: "16px"
  cell: "24px"
  section: "72px"
  grid-minor: "24px"
  grid-major: "120px"
components:
  button-primary:
    backgroundColor: "{colors.green}"
    textColor: "{colors.sheet}"
    rounded: "{rounded.sm}"
    height: "46px"
    padding: "0 20px"
  button-primary-hover:
    backgroundColor: "#2F9C81"
    textColor: "{colors.sheet}"
  button-outline:
    backgroundColor: "{colors.sheet}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    height: "46px"
    padding: "0 20px"
  button-approve:
    backgroundColor: "{colors.green}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
  input:
    backgroundColor: "{colors.sheet}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    height: "44px"
  pill-ok:
    backgroundColor: "{colors.green-wash}"
    textColor: "{colors.green-deep}"
    rounded: "{rounded.pill}"
  pill-warn:
    backgroundColor: "{colors.sheet}"
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
  pill-info:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
  pill-stamp:
    backgroundColor: "{colors.green-mist}"
    textColor: "{colors.green-deep}"
    rounded: "999px"
    padding: "5px 12px"
  sheet-card:
    backgroundColor: "{colors.sheet}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
  stamp:
    backgroundColor: "{colors.green-mist}"
    textColor: "{colors.green-deep}"
    rounded: "{rounded.round}"
    size: "84px"
  footer:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.sheet}"
---

# Design System: نفّذها (Nafizha)

Scope: the public site and the client/freelancer account area (`resources/views/website/**`). The admin panel (`resources/views/admin/**`) runs a separate theme and is **not** part of this system; do not load `nafizha-world.css` there or copy admin styles into the site.

Where it lives:
- `public/assets/website/css/nafizha-world.css` is the design layer and the source of truth for tokens (`--nf-*` on `:root`). It loads after Bootstrap, plugins and the legacy theme, from `resources/views/website/layouts/header.blade.php`.
- `public/assets/website/css/styles.css` is the legacy theme. Its colours were mapped mechanically onto the ink/green scales (lightness kept). Its forced white button text (`button { color:#fff !important }`, line ~13305) skips any class containing `rf-` or `nf-`, plus `btn-outline*`, `.btn-light` and `.btn-link`. Name new buttons `nf-*` or `rf-*` and give them their own colours.
- Fonts: Alexandria (500–800) and Readex Pro (400–700) from Google Fonts with `display=swap`, in the header. The icon set is Bootstrap Icons 1.11.3 (`bi-*`).

## Overview

**Creative North Star: "الرسومات التنفيذية" (The Execution Drawing Set)**

The name «نفّذها» reads as "execute the drawing", so the site looks like an engineer's set of execution drawings. Pages sit on drafting-grid paper (24px minor grid, 120px major). Content sits on white sheets with crop marks at two corners. Headings carry a dimension line under them. Key facts sit in a ruled title block, and the one loud mark is a round green approval stamp. A project appears as a dimensioned plan whose stages are drawn in ink, and the last stage is closed in green.

The world is quiet and exact. The logo green is the primary colour (user decision 2026-10-18): actions, links, highlights and active states are green, always with charcoal text. Ink does text, borders and drawn lines; the green stamp still marks what is approved, held until approval, or complete. Density is moderate. Rows are separated by hairlines, not boxes inside boxes. Numbers are tabular, as on a drawing.

Motion is a plotter at work. Lines draw themselves once, the stamp lands once, and stations rise once as they scroll into view. Nothing lifts on hover. When motion does not run (page hidden at load, reduced motion, no IntersectionObserver), everything is simply drawn.

**Key Characteristics:**
- Two logo hues only: ink #2B3038 and green #3FB699, plus their tints and shades.
- Green = approval / held money / done; ink = everything you can press.
- Grid paper, sheets with crop marks, dimension lines, title blocks and a stamp make up the whole ornament vocabulary.
- Arabic-native RTL: Alexandria for display, Readex Pro for reading.
- Drawn once, never bouncing; hover lights a sheet from inside.

## Colors

A two-colour palette taken from the logo: logo green as the primary (actions, links, highlights) and charcoal ink for text and drawn lines, on cool paper.

### Primary
- **Logo Green** (`green`, CSS `var(--main-color, #3FB699)`; admins can override it in public settings via `settings.website_color`): every primary action fill (`.btn-primary` family, search button, nav login button, `.nf-link-btn.ink`, `.rf-acc-btn`), active-nav underline, selected states (account menu row, account type, checkboxes), badges, entry-card icon rings, CTA underlines, the stamp, the progress bar and done nodes. Text on it is always ink. Hover fill `--nf-green-hover` #36A88B.

### Secondary
- **Drafting Ink** (`ink`): body text, headings, drawn plan lines, title-block rules, sheet borders; `.btn-dark` is the charcoal solid button. `ink-deep` is the footer's bottom bar.
- **Green Text** (`green-ink`, 4.79:1 on paper, 5.16:1 on white): the only green used for text, the focus outline and the caret. Use it for success text (`.text-success`), the legend links and done-step icons.
- **Green Deep** (`green-deep`): text on green washes (ok pills, the stamp label, the held-money line in the title block) at 7.06:1 on `green-wash`.
- **Green Wash / Mist** (`green-wash`, `green-mist`): ok-pill and `.alert-success` backgrounds; `::selection`.

### Neutral
- **Paper** (`paper`): the page background, under the grid. Also the hover fill of rows and outline buttons, and the info pill.
- **Sheet** (`sheet`, #FFF): cards, the nav bar, inputs, alerts, the title block.
- **Ink 2** (`ink-2`, 7.06:1 on paper): paragraphs, nav links at rest, secondary labels.
- **Ink 3** (`ink-3`, 5.51:1 on paper, 5.94:1 on white): meta text, dimension labels, placeholders, `dt` cells, zero counts.
- **Line / Line 2 / Line 3** (14% / 26% / 55% ink): hairlines — sheet borders (`line`), input and outline-button borders and inner rules (`line-2`), crop marks, dimension lines and hover rings (`line-3`).
- **Grid** (`--nf-grid-minor` 4.5% ink, `--nf-grid-major` 8% ink): only inside `--nf-grid` paper.

### Named Rules
**The Green Primary Rule.** Logo green is the primary colour: actions, links (`green-ink` as text), highlights and active states. Approvals additionally get the stamp. Never introduce another hue.

**Direction.** The whole site is right-to-left: `<html lang="ar" dir="rtl">` in `layouts/header` (body `direction: rtl; text-align: right; overflow-x: clip`). The theme's navigation frame keeps `direction: ltr` so the logo stays on the right; its labels are rtl. Hide elements with clipping, never by pushing them `-9999px` aside (that becomes horizontal scroll in RTL). Latin-only fields (email, phone, URL) carry `dir="ltr"` themselves.

**The No White On Green Rule (text only).** White on #3FB699 is 2.51:1 and fails. Green surfaces always carry ink text (5.28:1) or `green-deep` text. Green as text on paper is `green-ink` only; raw `green` text is 2.33:1 and is limited to stars and icons on the ink footer and testimonials.

**The Logo-Only Rule.** No hue outside the ink and green scales: no red, amber or blue, not even for errors. Danger and invalid states use **ink + dashed line** (`.btn-danger`, `.is-invalid`, `.alert-danger`, `.rf-acc-pill.bad`). Warning is a solid ink outline. Info is paper.

## Typography

**Display Font:** Alexandria (fallback Readex Pro, Segoe UI, Tahoma)
**Body Font:** Readex Pro (fallback Segoe UI, Tahoma)

**Character:** Alexandria's geometric, heavy Arabic gives the sheet titles the weight of lettering on a drawing; Readex Pro is an open, even text face that stays readable at 13px in RTL. `letter-spacing: 0` everywhere (Arabic must never be tracked). Headings use `text-wrap: balance`.

### Hierarchy
- **Display** (800, `clamp(1.55rem, 1rem + 1.45vw, 2.3rem)`, lh 1.6, max 24ch, `white-space: pre-line` so admin line breaks are kept): the homepage hero `h1` only.
- **Headline** (700–800, `clamp(1.5rem, 1.1rem + 1.3vw, 2.1rem)`, lh 1.45): `.sec-heading h2`, the paths heading (800, up to 2.2rem).
- **Title** (700, 1.02–1.3rem): card, station, legend and account-card headings; primary entry cards use 1.28rem.
- **Body** (400, 15.5px, lh 1.75, `ink-2` for paragraphs): the hero lead is 1.12rem (1rem on phones), max 56ch; station text is max 30ch.
- **Label** (500–600, 13–14.5px): nav (14.5px/500), breadcrumbs (13.5px), title-block cells (13–13.5px), pills.
- **Dimension** (12.5px, `ink-3`, tabular numerals): dimension-line labels; the plan SVG uses 11px for dims and 12.5–13.5px for notes.

### Named Rules
**The Tabular Numbers Rule.** Every count, price and number cell uses `font-variant-numeric: tabular-nums` (tables, `.price_section strong`, `.nf-acc-tb dd.num`, `.nf-acc-line-item strong`, `.rf-acc-num`, station nodes). Counts set in the display face (`font-family: var(--nf-display)`) read as figures on a drawing.

**The Arabic Measure Rule.** Never track, uppercase-style or italicise Arabic. `.testimonial-text` resets the theme's italic for this reason.

## Layout

- **Paper and sheets.** Section backgrounds alternate between grid paper (`.nf-grid` or `background: var(--nf-grid), var(--nf-paper)`: hero, `.page-title` banners, `.rf-home-paths`, `.profile_page`), plain paper (`.home_services`), plain white bands (`.nf-how`, with `border-block: 1px solid line`) and the ink negative (footer, testimonials). Bands are separated by a 1px `line` border, never by a shadow.
- **Container.** Bootstrap `.container`; the theme forces `max-width: 1200px !important` on desktop. A narrower layout also needs `!important` (the homepage's `.rf-home-cards-secondary` is max 860px, centred with `margin-inline: auto`; `.rf-bl-article` and `.rf-st .container` use `!important`).
- **Rhythm.** Major bands use 72px vertical padding (hero 116/72, `.nf-how` 72, paths 72/40, testimonials 80). Inner page banners use 64/52. Sheet cells pad 22–26px × 24px (16–18px on phones). Section heading gap: 36px below `.sec-heading`. Inline gaps: 8–12px between buttons, 24px between grid columns.
- **Hero.** `.nf-hero-inner` is a 2-column grid (1.05fr / .95fr, gap 48px). The copy sits at the start (right in RTL) and the drawing (`.nf-plan`) at the end. At ≤991px it stacks to one column (gap 28px).
- **Breakpoints.** 991px (hero, legend stack) and 767px (tracks turn vertical, title block stacks, account header stacks, status-line items go 50% wide, tick separators hide, the search button keeps only its icon). Phone side gutter is Bootstrap's ~16px (`spacing.gutter`).
- **Phones.** The floating help buttons (`.help_center`) stay hidden until the visitor scrolls past 60% of the first screen (`body.nf-scrolled`, set by a passive scroll listener in the header), so the first screen belongs to the content.

### RTL notes
- Pages are `dir="rtl"`. Prefer logical properties (`margin-inline`, `inset-inline`, `border-block`) in new rules. Many incumbent rules use physical sides chosen for RTL: crop marks at the top-**right** and bottom-**left**, `dt` cells with a `border-left` separator, account-nav active bar `inset -3px 0 0` (the right edge), and the dimension line's tick on the right of `::before`. Mirror these deliberately if an LTR (`/en`) view ever needs them.
- The how-it-works rail draws from the right (`transform-origin: right`). On phones it becomes a vertical rail at `right: 27px` and draws from the top.
- The CTA underline on entry cards grows from the right (`transform-origin: right center`).

## Elevation & Depth

The system is flat paper. Depth comes from layering (paper → white sheet → hairline) and from ink rules, not from shadows. A sheet at rest has a 1px `line` border and **no shadow**. One soft shadow exists and appears only on hover or as a floating layer.

### Shadow Vocabulary
- **Sheet lift-light** (`--nf-shadow: 0 1px 2px rgba(29,33,39,.06), 0 8px 24px -12px rgba(29,33,39,.18)`): dropdown menus (profile, notifications, `.rf-acc-dropdown`) and, together with an inset ring, a hovered `.ser_110` card.
- **Inner ring** (`box-shadow: inset 0 0 0 1px <line-3 | ink>`): the hover and selected state of sheets: service cards and account stats/actions (`line-3`), primary entry cards and the selected account type (ink).
- **Ink-button underline** (`box-shadow: inset 0 -3px 0 green`): hover on every ink-filled action (`.btn-primary` family, `.nf-link-btn.ink`, `.nf-search button`, the nav CTA).
- **Focus halo** (`box-shadow: 0 0 0 3px rgba(63,182,153,.18)` with a `green-ink` border): focused inputs and `.nf-search:focus-within`. All other focus is a `:focus-visible` 2px `green-ink` outline, offset 2px.

### Named Rules
**The Lit-From-Inside Rule.** Hover never moves an element (`transform: none !important` on `.rf-home-card`, `.testimonial-card`). It darkens the border and adds an inner ring. A soft outer shadow may come with it. Only an image inside a card may scale (1.03, .6s).

## Shapes

- **Corners.** Small, drafted radii: 6px (`--nf-r`) for sheets, cards, buttons, inputs, alerts and breadcrumbs; 10px (`--nf-r-lg`) for dropdowns and the floating help buttons; 4px for pills, badges, the search button and account nav rows. Circles (50%) are reserved for drawn nodes: the stamp, station nodes, icon rings in the action strip and on entry cards, and plan nodes.
- **Title blocks** (`.nf-titleblock`, `.nf-acc-tb`) have **square corners** and a heavier 1.4px ink frame. They are the only heavy-framed element.
- **Crop marks.** `.nf-sheet` draws two 14px L-marks at the top-right and bottom-left corners, offset 7px outside the border (`.rf-home-card` uses 12px / 6px). They use `line-3`, are `pointer-events: none`, and need `position: relative` on the sheet (the class sets it).
- **Dimension line.** A 1px `line-3` line with 9px end ticks, drawn with gradients: `.nf-dim` (inline, with a centred label), `.sec-heading::after` (under headings, `min(320px, 70%)` wide) and `.rf-home-cards-primary::before` (spanning the primary card row).
- **Line weights.** 1px hairlines for structure, 1.4px for ink frames, plan lines and station nodes, 2–2.4px for green strokes (stamp ring, the plan's last segment, legend accents).
- **Dashed = attention.** A dashed ink line marks danger, invalid input or a failed state. A dashed `line-3` border marks a secondary or lighter sheet (the secondary homepage cards); it turns solid on hover.

## Components

### Buttons
Logo green for actions with **white bold** text (user decision 2026-10-18, taken knowing white on #3FB699 is 2.5:1).
- **Shape:** 6px corners, weight 600, no letter-spacing. The hero buttons are 46px tall.
- **Primary (green):** `.btn-primary`, `.btn-theme`, `.global_button`, `.btn-save`, `.rf-acc-btn`, and on the homepage `.nf-link-btn.ink` and `.nf-search button`. Green fill with white text, weight 700. The theme's other green-filled buttons get white text from the block at the end of `styles.css`.
- **Hover / Focus:** fill goes to `--nf-green-hover` (#2F9C81); 0.2s `--nf-ease`. Focus is the 2px `green-ink` outline.
- **Charcoal solid:** `.btn-dark` (white text).
- **Outline / Secondary:** `.btn-outline-*`, `.btn-light`, `.btn-secondary`, `.btn-info`, `.btn-warning`, and `.nf-link-btn`. White fill, 1px `line-2` border, ink text; on hover the border turns ink and the fill paper.
- **Approve:** `.btn-success` / `.btn-approve`. Green fill with **ink** text; on hover `#36a589`. Use it only for an approval act (approve a stage, accept).
- **Danger:** `.btn-danger` / `.btn-outline-danger` are the outline button with a **dashed** border. There is no red.
- **Disabled:** opacity .45, no shadow.

### Status pills
Built on the request system's `.rf-acc-pill` / `.rf-acc-tag` (4px radius, 600 weight). Pick the tone from the meaning, never from a colour:
- `ok`: green wash with green-deep text and an inset green ring — positive but not final (published, paid in).
- `warn`: white with a solid `line-3` ring — waiting on someone ("دورك").
- `bad`: white with a dashed ink outline — rejected, not completed, failed.
- `info`: paper — neutral progress (in progress, new).
- `stamp`: **the final approval mark** — a 999px pill with a double green ring (`inset 1.5px green, 3px white, 4px green`), green-deep display text, rotated −4°. Use it for completed requests and accepted offers (`$requestTone` / `$offerTone` maps at the top of `resources/views/website/user/dashboard.blade.php`).
- **Add a status:** map it to one of these five tones in the view's tone array. Do not add a new colour class; if none fits, it is `info` or no tone.

### Cards / Containers (sheets)
- **Sheet:** `.nf-sheet`. White, 1px `line` border, 6px corners, crop marks. Combine it with an existing component class (`class="rf-acc-hello nf-sheet nf-acc-head"`).
- **Service card:** `.ser_110` (shared by services, products, search and profiles). White, 1px `line`, no shadow at rest; on hover a `line-3` border, inner ring and `--nf-shadow`, and the thumbnail scales 1.03. Titles use the display face; stars are green; prices are ink and tabular. It fills its Bootstrap column (rules at the end of `styles.css`); never fix a card's height.
- **Entry cards (homepage paths):** `.rf-home-card` in `.rf-home-cards-primary` (3 cards, 236px min height, sheet with 12px crop marks, ink circle icon, a CTA whose ink underline draws in on hover) and `.rf-home-cards-secondary` (2 cards, transparent, dashed `line-3`, no crop marks, centred at max 860px).
- **Internal padding:** 22–26px on desktop, 16–18px on phones.

### Inputs / Fields
- **Style:** white, 1px `line-2` border, 6px corners, min height 44px, ink text, `ink-3` placeholders, `green-ink` caret. The same applies to Select2.
- **Focus:** the border turns `green-ink` with a 3px green halo at 18% opacity.
- **Error:** dashed ink border plus bold ink `.invalid-feedback`. Checked checkbox/radio: ink fill.
- **Search bar:** `.nf-search` is a white 5px-padded frame holding a borderless input and an ink button; the frame itself takes the focus halo (`:focus-within`).

### Alerts
White, 6px corners, `line-2` border, ink text. `.alert-success` has a green border on a mist background. `.alert-danger` / `.alert-warning` have a dashed ink border on white. Info, primary and secondary alerts sit on paper.

### Navigation
- **Bar:** white, 1px `line` bottom border, no shadow, with a ruler along its lower edge (`.header::after`: a tick every 8px and a taller tick every 48px).
- **Links:** Readex Pro 500, 14.5px, `ink-2` at rest and ink on hover. The active link gets a 2px ink underline inset 14px from each side.
- **CTA** (`li.add-listing`): ink fill, 6px corners; on hover the green inset underline appears.
- **Currency toggle** (`.rf-fx-seg`): the active segment is ink with white text.
- Logo max height 38px.

### Section heading
`.sec-heading` is centred, with an `h2` in the headline style and a dimension line drawn under it by `::after`. **New section:** wrap the heading in `<div class="sec-heading"><h2>…</h2><p>…</p></div>`. Add no kicker or eyebrow above it, and no coloured `span` (the `span` is forced to ink).

### Inner page banner
`.page-title`: grid paper, padding 64/52, 1px `line` bottom border, ink titles, and the breadcrumb as a small white sheet (`.bredcrumb_wrap .breadcrumb`, 13.5px, the active item ink 600).

### Account area (client and freelancer)
- **Frame:** `.profile_page` on grid paper. The side menu `.dashboard-navbar` and the content `._dashboard_content` are white sheets with no shadow. The active or hovered nav row is paper with a 3px ink bar on its right edge.
- **Header** `.nf-acc-head` (on `.rf-acc-hello nf-sheet`): a 2-column grid (greeting / `minmax(240px, 320px)` title block) that stacks at ≤767px.
- **Title block** `.nf-acc-tb`: a `<dl>` laid out as a 2-column grid with a 1.4px ink frame, `dt` in `ink-3` with a left rule, `dd` 600, and `dd.num` as a display-face tabular number. Use it for an account's key facts (balance, type, status). The homepage `.nf-titleblock` is the same pattern with a stamp cell.
- **Status line** `resources/views/website/user/partials/status-line.blade.php`: pass `items = [[value, label, url|null], …]`. It renders `.nf-acc-line.nf-sheet` as one row of counts separated by short ticks, like a dimension line. A `0` value gets `.zero` (muted). Each item is a link when it has a url. Use it instead of a row of number tiles.
- **Action strip** `.rf-acc-actions`: quick actions in **one** white strip (no tiles). Each link has a 38px icon in a drawn circle, a title and a hint, with ticks between links (hidden on phones); hover fills paper.
- **Account cards** `.rf-acc-card`: a sheet with a display-face `h3`, rows (`.rf-acc-row`) with tabular `.rf-acc-num` and a status pill, and an empty state `.rf-acc-empty` with underlined ink links. **New account card:** `<div class="rf-acc-card"><h3>…</h3>…</div>`, numbers in `.rf-acc-num`, status through the tone map, and links as underlined ink.
- **Progress:** `.rf-acc-progress span` is green (it measures completion). Done steps get a `green-ink` icon.

### Footer (ink title block)
`footer.dark-footer` is the drawing in negative: an ink sheet with a 24px white grid at 4.5%, a 6px `ink-deep` top rule, text at 78% white, white display-face widget titles over a 16% white rule, and the logo inverted to white. Social icons are outlined squares that turn green on hover. `.footer-bottom` is `ink-deep`. The floating help buttons are ink with a 10px radius.

### Signature: the drawing (`.nf-plan`, homepage hero)
An inline SVG plan (`viewBox 0 0 520 300`, `role="img"` with a `<title>`) inside `.nf-plan.nf-sheet`. Classes: `.ln` (1.4px ink), `.ln.thin` (.8px `line-3`, for frames and dimensions), `.ln.g` (2.4px green, only the closing segment), `.node` / `.node.done` (white or green), and `text`, `text.strong`, `text.dim`. Each path sets `style="--nf-len:<path length>; --d:<delay>s"` so the plotter can draw it. Below it, `.nf-titleblock` lists المشروع / المنفّذ / الدفع (the held-money line in `dd.held`, green-deep) next to the `.nf-stamp`.

### Signature: the stamp (`.nf-stamp`)
An 84px circle with a 2px green ring and an inner double ring, green-deep Alexandria 800 at 15px, a `small` sub-label, rotated −12° on an 8% green tint. It is the only loud green on any screen. It says «معتمد» and stands for an approval. Never use it as a badge for marketing claims.

### Legend (`.nf-legend`, homepage promises)
A sheet laid out like a drawing's key: a 270px title cell, then a 2×2 `<ol>` of rows with hairline rules between them. Each row has a 52px drawn SVG symbol (`.s` ink 1.5px, `.i` ink 2px, `.g` green 2.2px accents) and a title and text. It stacks to one column at ≤991px. Use drawn line symbols here, not icon-font glyphs.

### How it works (`.nf-track`)
One ink rail (`::before`) with numbered stations: `--n` sets the column count, `.nf-station-node` is a 54px ink-outlined circle with a tabular display number, and the **last** station is green with ink text (done). Station images are greyscale. On phones the rail becomes vertical with the nodes on the right.

### Payment methods
`.rf-pay-methods` is a white strip sheet and `.rf-pay-chip` is a paper chip with a `line` border. It shows EasyKash methods only.

### Motion grammar
- Keyframes: `nf-draw` (stroke-dashoffset from `--nf-len` to 0, 1.6s), `nf-stamp` (lands from 1.6× to 1×, .55s, after a 1.75s delay so it follows the lines), `nf-rise` (10px rise and fade, .7s), and `nf-line-in` (the rail scales in, 1.2s). Easing is always `--nf-ease: cubic-bezier(.16, 1, .3, 1)`.
- **Gate:** animations run only under `html.nf-drawing`. The header script adds that class on the next frame, and only if the page is visible and `prefers-reduced-motion` is not `reduce`. Rest state = fully drawn (`stroke-dashoffset: 0`). Scroll reveals add `.in` once through IntersectionObserver (homepage script); without IO everything gets `.in` immediately.
- `prefers-reduced-motion: reduce` collapses every animation and transition to 0.001ms.
- **Add motion:** write the drawn state as the default and put the animation under `.nf-drawing … .in`. Animate once, never loop, never on hover.

## Do's and Don'ts

### Do:
- **Do** read colours from `--nf-*` tokens; use `var(--nf-green)` (which follows the admin's `--main-color`) rather than a literal green.
- **Do** draw actions in logo green with ink text, and links in `--nf-green-ink`.
- **Do** put text on green in ink or `--nf-green-deep`; use `--nf-green-ink` (#287A66) for any green text on paper or white.
- **Do** say held money as «يبقى المبلغ لدى نفّذها حتى تعتمد العمل» and call the parties «عميل» and «مستقل».
- **Do** show counts as a status line (`partials/status-line`) or a title block (`.nf-acc-tb`), with tabular numbers.
- **Do** use dashed ink for danger, invalid and failed states.
- **Do** start a new section with `.sec-heading` and its dimension line, and place content on `.nf-sheet` over grid paper.
- **Do** design every animated element to be complete with no motion, and gate the animation behind `html.nf-drawing`.
- **Do** keep RTL first: logical properties for new rules, crop marks top-right / bottom-left.

### Don't:
- **Don't** use the word «ضمان» for held money, or «مشتري/بائع» for people.
- **Don't** introduce a hue outside the ink and green scales, including red/amber/blue for errors or warnings.
- **Don't** put white text on green (2.51:1), or make a link, CTA or heading green.
- **Don't** use emoji, gradient text, glassmorphism/`backdrop-filter` decoration, or coloured decorative gradients. Gradients exist only to draw grid paper, ruler ticks and dimension lines.
- **Don't** build page structure from grids of same-size icon cards, or a hero row of big metrics; never invent client counts, ratings or quotes (testimonials render only from approved reviews).
- **Don't** lift, bounce or translate elements on hover; light the sheet from inside instead.
- **Don't** loop animations or animate something that has no drawn rest state.
- **Don't** add a kicker or eyebrow line above headings.
- **Don't** fix card heights with `max-height` + `overflow: hidden`.
- **Don't** apply this system to the admin panel.

## Living homepage motion (2026-10-18)
The homepage keeps moving, always inside the drawing world and only under `html.nf-drawing` (page visible, motion allowed); with reduced motion the hero shows the approved end state.
- **Hero loop** (`[data-nf-plan]`, script in `index.blade.php`): the stages read right-to-left (طلبك → العروض → التنفيذ → تعتمده أنت). Every 2.6s a pen (`.nf-pen`) moves to the next station, the green `.nf-prog` line follows, the station gets `.is-on` (earlier ones `.is-past`), the title-block row «الحالة» (`.nf-status`, texts in `data-status`) changes, and on the last step `.nf-plan.is-approved` lands the stamp (it waits faintly otherwise). Hover, focus, click or Enter on a station shows it and holds the loop 6s. The loop pauses off-screen or in a hidden tab.
- **Drafting crosshair** (`.nf-cross`): green hairlines follow the pointer over the hero with a grid read-out (mouse/trackpad only, hidden on phones).
- **How it works**: a green dot travels the track (`.nf-track::after`, vertical on phones) and each station node pings in turn (`--i`, `--n`).
- **Hover**: legend icons redraw (`nf-redraw`), entry-card icon rings turn (`nf-spin`).
