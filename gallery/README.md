# /gallery — design samples

Interactive preview page at `/gallery.php` that swaps 18 standalone
HTML mockups in an iframe.

## File layout

```
/gallery.php                    ← the filter + iframe page
/gallery/mockups/*.html         ← 18 standalone mockups
/gallery/README.md              ← this file
/assets/gallery.css             ← page-specific styles
/assets/gallery.js              ← filter + URL sync
```

## Adding a new mockup

There's no registry. The filesystem IS the registry — `gallery.php`
only knows about trade/style combinations that match the hard-coded
`$trades` and `$styles` arrays in its header.

### Adding a new STYLE

1. Write a new mockup file per trade: `gallery/mockups/{trade}-{newstyle}.html`
   for every trade in `$trades` (you need all six, otherwise visitors
   picking the new style for a trade you haven't done see a broken iframe).
2. Add the style slug to the `$styles` array in `gallery.php`:
   ```php
   $styles = ['modern','classic','bold','your-new-style'];
   ```
3. That's it — the pill button renders automatically.

### Adding a new TRADE

1. Write three mockups for the new trade (one per style).
2. Add the trade slug to `$trades` in `gallery.php`:
   ```php
   $trades = ['plumber','electrician','builder','landscaper','painter','concreter','your-new-trade'];
   ```
3. No navigation update needed. Pills render from the array.

## Mockup file requirements

Each file MUST:

- Be a valid standalone HTML5 document (nothing that depends on
  server-side PHP).
- Stay under 30KB so the iframe swap feels instant — Tailwind CDN +
  Lucide + Unsplash hotlinks is plenty.
- Use ONLY internal `#anchor` nav inside the mockup. No links to
  external sites except Unsplash + the Tailwind/Lucide/Fonts CDNs.
- Have `form action="#" onsubmit="return false"` on every form.
- Show invented business names clearly prefixed `Sample`.
- Use `0400 000 000` for phone (Australian reserved-for-fiction prefix).
- Use `sample@tradiebud.tech` for email.
- Tag testimonials as `Sample review —` or similar.
- Include a footer line: `Sample design by Tradie Sites Co. — not a real business`.

## The iframe sandbox

Parent page loads each mockup via:

```html
<iframe sandbox="allow-same-origin allow-scripts">
```

We deliberately DON'T allow `allow-top-navigation`, so a mockup
cannot escape the iframe to navigate the parent page. If a future
mockup needs to submit forms to third-party origins you'll need to
add `allow-forms` — but Phase 1 never needs real submissions.

## Trade + style slugs

Current inventory:

| Trade        | Modern | Classic | Bold |
|--------------|:------:|:-------:|:----:|
| plumber      | ✓      | ✓       | ✓    |
| electrician  | ✓      | ✓       | ✓    |
| builder      | ✓      | ✓       | ✓    |
| landscaper   | ✓      | ✓       | ✓    |
| painter      | ✓      | ✓       | ✓    |
| concreter    | ✓      | ✓       | ✓    |

Style signatures you can copy when adding a new trade:

- **Modern** — Inter font, `bg-paper #f7f7f5`, one brand accent colour
  per trade, rounded-2xl cards, aspect-[4/5] hero photo on the right.
- **Classic** — Playfair Display serif + Inter body, `bg-cream #fdfcf8`,
  deep heritage accent (navy / burgundy / forest / steel), centred hero
  with a full-width photo below, 3-col bordered services.
- **Bold** — Archivo Black display + Space Grotesk body, `bg-black`,
  one electric accent (lime / yellow / fuchsia / neon / volt), numbered
  services in a coloured grid, rotated hero photo.
