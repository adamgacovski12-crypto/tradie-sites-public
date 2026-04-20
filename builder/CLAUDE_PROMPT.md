# Claude Code build prompt — {{REF}}

Copy everything below the `---` line and paste into a fresh Claude Code session pointed at `/builds/{{REF}}/`.

---

Build a 5-page tradie website for **{{BUSINESS_NAME}}**, a **{{TRADE}}** based in **{{SUBURB_PRIMARY}}** serving **{{SUBURBS_LIST}}**. They've been in business for **{{YEARS_IN_BUSINESS}}** years. ABN: **{{ABN}}**. Licence: **{{LICENCE}}**.

Their 3 core services:
1. {{SERVICE_1}}
2. {{SERVICE_2}}
3. {{SERVICE_3}}

Tagline they provided: "{{TAGLINE}}"

Trade-specific tone hint: {{TONE_HINT}}

Your working folder is `/builds/{{REF}}/`. Inside you'll find:
- `base.html` — starter template with data placeholders already filled ({{BUSINESS_NAME}}, {{ABN}}, etc.) and copy placeholders `[[IN_SQUARE_BRACKETS]]` remaining for you.
- `base.css` — starter stylesheet, accent colour pre-set to **{{ACCENT_COLOUR}}** for this trade.
- `photos/` — {{PHOTO_COUNT}} photos the tradie uploaded.
- `logo.{{LOGO_EXT}}` — their logo file (present: **{{HAS_LOGO}}**). If the value is `no`, there's no logo file to reference — hide the `<img class="brand-logo">` or use a text-only brand mark.
- `signup.json` — the full (sanitised) signup record for reference.

---

## YOUR TASK

### 1. Understand the structure

Read `base.html` first. It's a single-file scaffold showing every section type (hero, services, about, testimonial, gallery, contact, footer). Your job is to split it into **five separate HTML files** (`home.html`, `about.html`, `services.html`, `gallery.html`, `contact.html`) that all share the one `style.css` (rename `base.css` to `style.css` on the first write).

### 2. Copy the images

Create an `./images/` directory inside `/builds/{{REF}}/`. Copy every file from `photos/` into `images/`. Rename if the original filename is illegible — e.g. `IMG_2837.jpg` → `bathroom-renovation-01.jpg`. Keep the mapping in your head; you'll need it for alt text.

### 3. Write REAL Australian copy

- **Voice**: plain-spoken Aussie tradie expert. Confident, specific, no sales slop.
- **Australian English**: *colour*, *organise*, *specialise*, *licence* (noun). Use kilometres, suburbs by name.
- **Specific over generic**: "We cleared a blocked sewer in Parramatta at 9pm on a Sunday" beats "We offer emergency plumbing services".
- **Numbers where possible**: "{{YEARS_IN_BUSINESS}} years in the trade", "servicing {{PHOTO_COUNT}}+ local jobs", etc.
- **Suburb names naturally**: mention {{SUBURB_PRIMARY}} and a few from {{SUBURBS_LIST}} in body copy — not just the footer.

**FORBIDDEN PHRASES** — if you catch yourself writing any of these, stop and rewrite:
- "your trusted partner"
- "industry-leading"
- "cutting-edge"
- "seamless experience"
- "one-stop shop"
- "in today's digital age"
- "nice-to-have" / "must-have"
- "reliable and trustworthy" (too generic)
- "unparalleled"
- "state-of-the-art"
- "We pride ourselves on"
- "At [business], we believe"
- "Let us take care of your..."

### 4. Page-by-page content requirements

#### `home.html`
- **Hero H1**: short, trade-specific, locative. Examples:
  - "Licensed Plumbers Serving {{SUBURB_PRIMARY}}"
  - "24/7 Electricians — {{SUBURB_PRIMARY}} &amp; Around"
- **Hero subhead**: one sentence on what they do + service area.
- **Hero CTA**: primary `Call {{PHONE}}` (tel:), secondary `Get a Quote` (contact page).
- **Services preview**: 3 cards, ~30-word descriptions each, each linking to `/services.html#{{anchor}}` or `/contact.html`.
- **About snippet**: 2 paragraphs max, linking to full `about.html`.
- **Gallery preview**: 4 photos max + "See all our work →" tile linking to `gallery.html`.
- **Contact CTA**: short form or "Call / Email / Get a Quote" strip.

#### `about.html`
- 2–3 paragraphs telling the business story. Use {{YEARS_IN_BUSINESS}} and suburbs naturally.
- **Licence prominent**: "Licensed {{TRADE}} — Licence #{{LICENCE}}" as its own card or callout.
- **ABN** displayed.
- **Suburbs served**: bulleted list.
- **Why us**: 3 bullets in tradie voice — concrete promises, not "unparalleled service". Examples: "On time or we ring you with a reason", "Quoted price is the price you pay", "Fully insured and compliant."

#### `services.html`
- Each of the 3 services expanded to **100–150 words**.
- Include trade-specific specifics (plumber → hot water, blocked drains, gas; electrician → switchboards, safety switches, LED).
- Each service section ends with a CTA (Call {{PHONE}} or `contact.html`).
- If trade is plumber / electrician / gas-fitter / builder, inline the licence number somewhere on this page too.

#### `gallery.html`
- Simple grid of **all** uploaded photos.
- Write a **one-line caption** per photo. Infer from filename or trade context (e.g. "Hot water install — Parramatta").
- **Alt text for every image** — describe the work, not the filename.

#### `contact.html`
- Full contact form (fields already in `base.html`: name, phone, email, suburb, message).
- Phone prominently as `tel:` link.
- **Service hours**: sensible defaults if none given — weekday 7am–5pm + emergency after hours for licensed trades.
- **Service areas**: bulleted list.
- **Response time promise**: "We ring back within 2 hours on a weekday" or similar, pick something realistic.

### 5. SEO / Schema

- **Unique `<title>`** per page. Format: `{Page} | {{BUSINESS_NAME}} — {{TRADE}} in {{SUBURB_PRIMARY}}`.
- **Unique meta description** per page (under 160 chars).
- **LocalBusiness schema.org** markup on all 5 pages (the scaffold in `base.html` shows the shape). Include `areaServed` as an array of suburb strings.
- **Service schema** for each of the 3 services on `services.html`.
- **BreadcrumbList schema** on every non-home page.

### 6. Technical

- **Single `style.css`** shared across all 5 pages. Rename `base.css` → `style.css` on first write.
- **All images** referenced from `./images/`.
- **All internal links** use relative paths (`./about.html`).
- **Mobile-first** — mentally test at 390px viewport.
- **No broken placeholders**. Before finishing, grep your output for `{{` and `[[` — none should remain.

### 7. Leave a few placeholders intentionally

A handful of fields are **Adam's job to fill** after your build is reviewed. Leave these as literal placeholders in the output — Adam search-and-replaces before deploying to Cloudflare:

- `{{PHONE}}` — Adam confirms the client's public phone (sometimes different from their signup phone)
- `{{EMAIL}}` — same reason
- `{{FORM_ENDPOINT}}` — Adam creates the Formspree endpoint per client
- `{{STATE}}` — if you can't infer from suburbs, leave it

### 8. CHANGES.md — always write this

At the end, create `/builds/{{REF}}/CHANGES.md`. Structure:

```markdown
# Build notes — {{REF}}

## Files generated
- [list every .html + .css + new image filenames]

## Decisions you should verify
- Primary suburb chosen: [...] (picked because [...])
- Trade slug mapping: [...]
- [anything else judgement-call]

## Placeholders left for Adam to fill
- {{PHONE}} — client's public phone
- {{EMAIL}} — client's public email
- {{FORM_ENDPOINT}} — Formspree endpoint to create
- [any others]

## Pre-deploy checklist
- [ ] Every {{ and [[ placeholder replaced
- [ ] All image files resolve (no 404s)
- [ ] Mobile viewport (390px) looks clean
- [ ] All internal nav links work
- [ ] tel: link dials correctly
- [ ] Licence + ABN appear in footer on all 5 pages
- [ ] Schema.org validates (use https://validator.schema.org/)
```

### 9. Don't commit anything

These files are deploy artefacts, not source code. They go into `/builds/{{REF}}/` which is gitignored. When you're done, stop — don't `git add` or `git commit`.

---

**When done, reply with**: (a) a one-line confirmation, (b) the file list from CHANGES.md, (c) anything you judgement-called that Adam should eyeball first.
