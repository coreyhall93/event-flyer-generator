# Event Flyer Generator

[![CI](https://github.com/coreyhall93/event-flyer-generator/actions/workflows/ci.yml/badge.svg)](https://github.com/coreyhall93/event-flyer-generator/actions/workflows/ci.yml)

A small WordPress plugin that turns your next few events into a printable,
one-page flyer. Tick up to four events, click a button, print it.

Built for community programs (meetups, workshops, recurring event series) that
need a quick, consistent flyer without opening Canva or Figma every time.

**It works on its own.** Install it, activate it, and add your events right in
the builder. There is nothing else to install and no account to create. This is
what most people will get, and it is the whole plugin, not a trial version of it.

**It also reads GatherPress, if you already run it.** When
[GatherPress](https://wordpress.org/plugins/gatherpress/) is active the builder
lists your existing GatherPress events instead, so you never retype an event you
already entered, and the plugin's own add-event form steps out of the way
because GatherPress owns events at that point.

GatherPress is entirely optional. **Installing this plugin will not install
GatherPress**, will not prompt you to, and does not need it to work.

## Try it live

Two demos, matching the two ways this works. Both boot a throwaway WordPress in
your browser. Nothing to install, nothing persists.

**1. [Standalone](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/coreyhall93/event-flyer-generator/master/blueprint-standalone.json)**
— no GatherPress. Add your own events right in the builder, then make a flyer.
This is the plugin on its own.

**2. [With GatherPress](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/coreyhall93/event-flyer-generator/master/blueprint.json)**
— GatherPress installed with six sample events. The builder reads them, so you
never retype an event you already have. The demo's Playground blueprint is what
installs GatherPress here; the plugin never installs it for you.

Both run the exact code on this repository's `master` branch, so what you try
there is what you get when you install it.

## What it does

- **`[event_flyer_picker]`** — the main one. A two-column builder: your events
  on the left, the flyer on the right. Tick up to four, or use "Flyer for just
  this" on a single event. Activating the plugin creates a **Flyer Builder**
  page carrying this shortcode, so there is nothing to wire up.
- **`[event_flyer_form]`** — the manual fallback. A blank form for one-off
  flyers, for events you do not want to store.
- Generates a print-ready page (816×1056, letter size) with a "Print / Save as
  PDF" button. No server-side PDF library, just the browser's print-to-PDF.
- The layout changes with the number of events: one event is a stacked poster,
  four is the tightest setting. Long content is scaled to fit rather than
  silently clipped.
- Flyer data is stored in a WordPress transient for one hour. That means it
  lives in your site's database (or object cache) for that window, and expired
  rows can linger until WordPress's daily cleanup runs. It is never attached to
  a post, a user, or an export. Treat a flyer as public-ish: don't put personal
  data in one.

## Installation

1. Download this repository as a ZIP (`Code` → `Download ZIP`).
2. In your WordPress admin: **Plugins → Add New → Upload Plugin**, and upload
   the ZIP.
3. Activate **Event Flyer Generator**. A **Flyer Builder** page is created for
   you, and a notice links straight to it.
4. Add some events on the builder page. If you happen to have GatherPress
   active, it lists your GatherPress events instead and you can skip this step.

## Requirements

- WordPress 6.4+
- PHP 7.4+
- GatherPress optional. No database tables, no external services, no build step.

## Notes

- The form is public by default — no login required to generate a flyer.
  Submissions are throttled to one flyer per IP every 15 seconds, and each
  field is length-capped, since the endpoint writes to the options table.
- Icons are drawn from [`@wordpress/icons`](https://github.com/WordPress/gutenberg/tree/trunk/packages/icons)
  (GPLv2+), the open-source icon set that ships with the WordPress block
  editor.
- Oswald and Inter are bundled locally under `assets/fonts/` (SIL OFL,
  licenses included). Nothing is fetched from Google Fonts, so no visitor
  IP addresses are sent to third parties.
- Print output forces `print-color-adjust: exact` so background colors (the
  black header band) survive printing — browsers strip backgrounds from
  print output by default.
- Uninstalling deletes the plugin's transient rows from the database. On a site
  with a persistent object cache those entries live in the cache instead and are
  left to expire on their own TTL, because clearing them would mean flushing the
  whole site's cache.

## Changelog

### 1.0.2
- Reads GatherPress events when GatherPress is active; the built-in add-event
  form is the no-GatherPress fallback.
- New `[event_flyer_picker]` two-column builder, and a **Flyer Builder** page is
  created on activation so there is nothing to wire up.
- A distinct layout per event count, and content is scaled to fit the page
  instead of being silently clipped.

### 1.0.1
- **Fixed:** flyers failed to load on any host with a persistent object cache.
  Tokens were generated mixed-case but read back lowercased, so the lookup
  missed unless the storage backend happened to be case-insensitive.
- **Fixed:** "Back to form" returned to the site home page instead of the form.
- Added rate limiting and field length caps on the public submit endpoint.
- Bundled fonts locally instead of hotlinking Google Fonts.
- Added `uninstall.php` to clean up transients.
- Added `Requires at least` / `Requires PHP` headers and loaded the text domain.

### 1.0.0
- Initial release.

## Development

Not needed to use the plugin — only to work on it.

Requires **PHP 8.1+** for the dev tooling (PHPUnit 10 needs it). The plugin
itself still runs on PHP 7.4 — CI verifies that separately.

```bash
composer install
composer run test   # PHPUnit
composer run lint   # PHPCS, WordPress coding standards
composer run lint:fix
```

CI runs the linter, the test suite on PHP 8.1 through 8.4, and parses every
shipped file on PHP 7.4 to verify the `Requires PHP` header.

## License

GPL v2 or later, same as WordPress itself. See [LICENSE](LICENSE).
