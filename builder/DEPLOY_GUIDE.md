# Deploy Guide — from paid signup to live site

**Audience**: Adam. This is the per-client playbook, roughly **20–30 minutes end to end**.

## Pre-requisites (one-off per machine)

- [ ] Claude Code CLI installed and logged in
- [ ] Cloudflare account with Pages enabled
- [ ] Formspree account (or equivalent) for contact-form endpoints
- [ ] SSH access to the server where signups land
- [ ] `/builds/` folder writable by the web user (for the admin-leads prep button to work)

---

## Per-client workflow

### 1. Confirm payment (`/admin-leads/`)

- Log in to `https://site.tradiebud.tech/admin-leads/` (admin / Tsc2026Admin).
- When the $200 bank transfer lands in CommBank, find the row by reference, click **Mark paid**.
- Customer gets the "payment received" email automatically.

### 2. Prep the build folder (`/admin-leads/` → **Prep build**)

- On the same row (now status `PAID`), click **Prep build**.
- Behind the scenes: `php builder/prep.php TSC-XXXXX-XXXX` runs, which:
  - Copies signup data + photos + logo into `/builds/{REF}/`
  - Pre-fills data placeholders in `base.html`/`base.css`
  - Writes `CLAUDE_BUILD_PROMPT.md` populated with this client's specifics
  - Flips status → `PREPPED`
- Green flash confirms: "prepped. Open /builds/{REF}/ in Claude Code."

### 3. Open a fresh Claude Code session in `/builds/{REF}/`

On the server (or on a machine with the repo synced):

```bash
cd /var/www/site.tradiebud.tech/builds/TSC-MIKESP-A3K9
claude
```

Inside Claude Code:

```bash
cat CLAUDE_BUILD_PROMPT.md
```

- **Copy the entire contents below the `---` divider** and paste as your first message to Claude Code.
- **Do not** reuse an old session — start fresh so there's no bleed-over from a previous client's context.
- **Flag `--dangerously-skip-permissions` is optional** if you trust the prompt; without it you'll approve each file write.

### 4. Wait 5–10 minutes

Claude Code will:
1. Read `base.html`, `base.css`, `signup.json`, `photos/`.
2. Write `home.html`, `about.html`, `services.html`, `gallery.html`, `contact.html`.
3. Write `style.css` (renamed from `base.css`).
4. Copy `photos/` → `images/` with better filenames.
5. Write `CHANGES.md` with decisions + Adam's checklist.

### 5. Spot-check

- Open `/admin-leads/` → **View build** on the client row.
- iframe preview shows `home.html`. Images + CSS may 404 in this admin preview (paths are relative; iframe doesn't resolve them) — **this is expected**. Check:
  - Hero headline reads well
  - No `{{...}}` or `[[...]]` stragglers in the visible text
  - Australian English (no "center", "organize", "-ize" endings)
- Open `CHANGES.md` inside the build folder. Read the "Decisions to verify" section and the "Placeholders left for Adam" list.

### 6. Fill Adam-only placeholders

Open `/builds/{REF}/*.html` in your editor. Search-and-replace these across every file:

| Placeholder | Fill with |
|---|---|
| `{{PHONE}}` | The client's public phone (often same as signup, sometimes different — confirm with them) |
| `{{EMAIL}}` | The client's public email |
| `{{FORM_ENDPOINT}}` | A new Formspree endpoint you create for this client — `https://formspree.io/f/xxxxxxx` |
| `{{STATE}}` | Australian state if Claude couldn't infer (NSW/VIC/QLD etc.) |

Grep one more time: `grep -rn "{{" /builds/{REF}/ | grep -v .log`. Should be zero hits.

### 7. Download the deploy ZIP

- `/admin-leads/` → **Download ZIP** on the client row.
- ZIP excludes `signup.json`, `build.log`, `CLAUDE_BUILD_PROMPT.md`, `CHANGES.md`, `base.html`, `photos/` — only the files Cloudflare needs.

### 8. Deploy to Cloudflare Pages

- Cloudflare dashboard → **Workers & Pages** → **Create** → **Pages** → **Upload assets**.
- Project name: `tsc-{ref-lowercase}` (e.g. `tsc-mikesp-a3k9`).
- Drag the ZIP or extract and upload the folder contents.
- First deploy URL will be `tsc-mikesp-a3k9.pages.dev` — test it loads before setting the custom domain.

### 9. Set custom domain

- Pages project → **Custom domains** → **Set up a custom domain**.
- Enter the client's `.com.au` (they must own it; if not, register on their behalf, costs ~$20–30/yr, charge them direct or absorb).
- Cloudflare will show the CNAME / AAAA records to add.

### 10. DNS setup

If the client's domain is already on Cloudflare: one-click.
If not:
- The client needs to change their nameservers to Cloudflare's OR add a CNAME record at their existing DNS host.
- Send them the instructions from Cloudflare. Usually 5–30 minutes to propagate.

### 11. Test live

- Hit the live URL on mobile + desktop.
- Test tel: link works on mobile.
- Submit a test form — Formspree should email you the submission.
- Check every internal nav link (no broken home → about → services chain).

### 12. Mark deployed (`/admin-leads/` → **Mark deployed**)

- Paste the live URL. Status flips to `DEPLOYED`.
- Client gets the "your site is live" email with the URL.

### 13. First-hosting-invoice reminder

For Hosted plan clients only: the recurring invoice cron will fire on signup-date + 1 month. Confirm the cron is running on the server (`crontab -l` should show an entry pointing to `/admin-leads/action.php?action=send_invoice&reference=...` or similar — TODO: wire this up in a later build).

---

## Gotchas

- **Stale Claude session**: always start fresh per client. Cross-client context bleed is the #1 risk.
- **Bad photos**: if `photos/` has only 1–2 usable images, the gallery page looks thin. Ask the client for 3–5 more before prep.
- **Licence number missing**: for plumber/electrician/gas-fitter/builder, prep.php warns if licence is empty but still runs. Remind the client that a licensed-trade site without a licence number loses trust and they should supply one.
- **Generic suburbs**: if the client wrote "Sydney" or "everywhere", nudge them to specify 3–5 specific suburbs for local SEO. A vague service area is one of the biggest drivers of poor Google ranking.
- **ZIP too big**: if over 25MB (Cloudflare Pages upload limit), check the photos folder — they may have uploaded 10MB JPGs. Compress to 500KB each before uploading.

---

## Rollback / re-build

If a client hates their site:
1. Delete `/builds/{REF}/` contents.
2. Re-run `php builder/prep.php {REF}` — fresh start.
3. Open new Claude Code session, refine the prompt with the client's feedback, paste.
4. Re-review, re-zip, re-deploy (Cloudflare lets you upload a new version over the existing project).

---

See `README.md` for architecture overview and placeholder conventions.
