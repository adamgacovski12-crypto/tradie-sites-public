# /builder/ — Auto-Build pipeline

Tradie Sites Co.'s client-site builder. **Claude Code** is the build engine — not Groq, not any other AI service. This folder contains the plumbing that preps everything so Adam can open a fresh Claude Code session and generate a client's 5-page site in 5–10 minutes.

## What lives here

```
builder/
├── README.md                 ← you are here
├── CLAUDE_PROMPT.md          ← reusable prompt template (gets filled per client)
├── DEPLOY_GUIDE.md           ← step-by-step for Adam from signup → live URL
├── prep.php                  ← CLI: turns a paid signup into a ready /builds/{REF}/ folder
└── templates/
    ├── base.html             ← master 5-page scaffold (single file, all sections)
    ├── base.css              ← master stylesheet, accent colour is a CSS custom property
    └── config.json           ← trade → accent colour + tone hint map
```

## Pipeline, end to end

```
   Signup form (/signup/)                    ← Phase 4 onboarding
         │
         ▼
   /signups/records/{REF}.json
         │  Adam marks as paid in /admin-leads/
         ▼
   /admin-leads/ → [Prep build] button       ← runs php builder/prep.php {REF}
         │
         ▼
   /builds/{REF}/                            ← NEW folder, gitignored
     ├─ signup.json             (sanitised — no phone/email/IP)
     ├─ photos/                 (client uploads)
     ├─ logo.{ext}              (if present)
     ├─ base.html               (data placeholders filled, copy [[placeholders]] remain)
     ├─ base.css                (accent colour pre-set for the trade)
     ├─ CLAUDE_BUILD_PROMPT.md  (fully populated — ready to paste)
     └─ build.log
         │
         ▼
   Adam opens a FRESH Claude Code session in /builds/{REF}/,
   pastes CLAUDE_BUILD_PROMPT.md contents.
         │
         ▼
   Claude Code generates:
     home.html · about.html · services.html · gallery.html · contact.html
     style.css (renamed from base.css)
     images/ (copied + renamed from photos/)
     CHANGES.md
         │
         ▼
   Adam spot-checks via /admin-leads/ → [View build] (iframe preview).
         │
         ▼
   /admin-leads/ → [Download ZIP]             excludes signup.json, build.log, photos/, base.html, CLAUDE_BUILD_PROMPT.md
         │
         ▼
   Cloudflare Pages → Create Project → Direct Upload → drag ZIP
   Custom domain = client.com.au. DNS per Cloudflare instructions.
         │
         ▼
   /admin-leads/ → [Mark deployed] + paste live URL
         │
         ▼
   Status = deployed, live_url recorded, client emailed "your site is live".
```

## Running prep.php

```bash
php builder/prep.php TSC-MIKESP-A3K9
```

- Refuses to run via the web (CLI-only guard).
- Idempotent — running again on the same REF will overwrite files in the build folder but not the signup record.
- Exits non-zero on any error; stderr carries the reason.
- Appends to `/builds/{REF}/build.log`.

## Placeholder conventions

Two kinds of placeholder show up in `base.html`/`base.css`:

| Syntax | Filled by | Purpose |
|---|---|---|
| `{{DATA_LIKE_THIS}}` | prep.php | Known facts from the signup — business name, ABN, licence, suburbs, accent colour, services, etc. |
| `[[COPY_LIKE_THIS]]` | Claude Code | Body copy, headlines, alt text, captions — anything requiring judgement or real Australian voice |

Three `{{...}}` placeholders are **deliberately left for Adam** at the end — prep.php doesn't fill them, and Claude Code is told to leave them as-is:

- `{{PHONE}}` — Adam confirms client's public phone post-build
- `{{EMAIL}}` — same
- `{{FORM_ENDPOINT}}` — Adam creates a Formspree endpoint per client
- `{{STATE}}` — optional, only if Claude can't infer from suburbs

## Why Claude Code, not Groq?

- Groq (llama-3.3-70b) is great for the marketing-site chatbot and blog generator — short-form, structured JSON, high throughput.
- Full 5-page sites with real per-client voice need planning, filesystem operations, split-file output, validation, and iteration — all of which Claude Code does natively and Groq doesn't.
- The marginal cost of Adam's Claude Code minutes per build (~5–10 minutes of tokens) is well below the margin on a $200 setup fee.
- Quality control: Adam eyeballs the CHANGES.md before deploying. A hallucinated field is caught in minutes, not after launch.

## When to bypass this pipeline

Don't run prep.php if:
- The signup hasn't been marked `paid` yet (defeats the funnel gate)
- The client's photos look unusable — ask them to re-send first
- The signup was bot spam — cancel via /admin-leads/ instead

## Troubleshooting

**`prep.php` errors out with "signup record not found"** — the REF doesn't match a file in `/signups/records/`. Check spelling (case-insensitive).

**"ZipArchive not available"** — PHP's zip extension isn't loaded on the host. SFTP the folder manually or enable `php-zip` on the server.

**`/admin-leads/` action.php `prep_build` fails silently** — check `/builds/{REF}/build.log` for the underlying error. The admin endpoint runs prep.php via `exec()` so any stderr goes into the flash message (first 12 lines).

**Preview iframe in `view_build` shows 404s for CSS/images** — expected. The iframe serves files via `/admin-leads/action.php?action=preview_build` which only resolves same-folder files; relative paths in `home.html` (`./style.css`, `./images/x.jpg`) won't resolve in the iframe. Deploy to Cloudflare Pages for a real preview.

---

See `DEPLOY_GUIDE.md` for the step-by-step Adam follows once the build folder is ready.
