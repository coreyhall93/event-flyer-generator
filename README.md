# Event Flyer Generator

A small WordPress plugin that lets anyone fill out a front-end form and get a
printable, one-page flyer for 1-4 upcoming events — no design tools required.

Built for community programs (meetups, workshops, recurring event series) that
need a quick, consistent flyer without opening Canva or Figma every time.

## Try it live

**Demo site:** https://coreyhall93-yedwm-studio.wp.build

## What it does

- Adds an `[event_flyer_form]` shortcode. Drop it on any page.
- Visitors fill in a program name, 1-4 events (date, time, title, description,
  venue, address, icon), and a footer line.
- Submitting generates a print-ready flyer page (816×1056, letter size) with a
  "Print / Save as PDF" button — no server-side PDF library, just the
  browser's native print-to-PDF.
- Flyer data is held in a short-lived WordPress transient (1 hour), not saved
  permanently anywhere.

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
- Icons are drawn from [`@wordpress/icons`](https://github.com/WordPress/gutenberg/tree/trunk/packages/icons)
  (GPLv2+), the open-source icon set that ships with the WordPress block
  editor.
- Print output forces `print-color-adjust: exact` so background colors (the
  black header band) survive printing — browsers strip backgrounds from
  print output by default.

## License

GPL v2 or later, same as WordPress itself. See [LICENSE](LICENSE).
