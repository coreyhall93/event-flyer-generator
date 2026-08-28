# Event Flyer Generator

[![CI](https://github.com/coreyhall93/event-flyer-generator/actions/workflows/ci.yml/badge.svg)](https://github.com/coreyhall93/event-flyer-generator/actions/workflows/ci.yml)

A small WordPress plugin that lets anyone fill out a front-end form and get a
printable, one-page flyer for 1-4 upcoming events — no design tools required.

Built for community programs (meetups, workshops, recurring event series) that
need a quick, consistent flyer without opening Canva or Figma every time.

## Try it live

**[Open the demo in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/coreyhall93/event-flyer-generator/master/blueprint.json)** — boots a throwaway WordPress site in your browser with the plugin installed and the form on the front page. Nothing to install, nothing persists.

## What it does

- Adds an `[event_flyer_form]` shortcode. Drop it on any page.
- Visitors fill in a program name, 1-4 events (date, time, title, description,
  venue, address, icon), and a footer line.
- Submitting generates a print-ready flyer page (816×1056, letter size) with a
  "Print / Save as PDF" button — no server-side PDF library, just the
  browser's native print-to-PDF.
- Flyer data is stored in a WordPress transient for one hour. That means it
  lives in your site's database (or object cache) for that window, and expired
  rows can linger until WordPress's daily cleanup runs. It is never attached to
  a post, a user, or an export. Treat a flyer as public-ish: don't put personal
  data in one.

## Installation

1. Download this repository as a ZIP (`Code` → `Download ZIP`).
2. In your WordPress admin: **Plugins → Add New → Upload Plugin**, and upload
   the ZIP.
3. Activate **Event Flyer Generator**.
4. Create a page, add the `[event_flyer_form]` shortcode to its content,
   publish.
5. Visit that page to use the form.

## Requirements

- WordPress 6.4+
- PHP 7.4+
- No database tables, no external services, no build step.

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
