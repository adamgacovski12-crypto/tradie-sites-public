# Tradie Sites Co. — Blog Engine

AI-generated SEO blog at `/blog/`. Posts are markdown files with a one-line JSON frontmatter, rendered by `template.php` and listed by `index.php`.

## Directory layout

```
blog/
├── README.md          ← you are here
├── _topics.php        ← catalogue of 15 pre-planned topics (blocked from web)
├── generate.php       ← CLI: turn a topic into a .md file via Groq
├── index.php          ← listing page, 10 posts per page, newest first
├── template.php       ← single-post renderer
└── posts/             ← generated .md files, named YYYY-MM-DD-slug.md
```

## Generating a new post

Run from the project root on the production server (or anywhere `GROQ_API_KEY` is set):

```bash
php blog/generate.php "Your topic title exactly as written"
```

- If the title matches one in `_topics.php`, the slug and topic tag are pulled from there.
- Otherwise a slug is auto-generated from the title and the topic tag defaults to `general`.
- The generator makes one Groq call (llama-3.3-70b-versatile, JSON-object mode) and writes the post to `blog/posts/YYYY-MM-DD-slug.md`.
- On success: `Done — preview at /blog/your-slug`.

Examples:

```bash
php blog/generate.php "Why tradies need a website in 2026"
php blog/generate.php "How to get more leads as a plumber"
php blog/generate.php "SEO for tradies — the basics"
```

## What the LLM is asked for

`generate.php` sends a single JSON-mode call that returns:

- `meta_title` — under 60 chars
- `meta_description` — under 160 chars
- `body_markdown` — 800-1,200 words, H2/H3 structure, 2-3 internal `/trades/[slug]` links, 1 CTA at the end
- `faqs` — exactly 3 Q&A pairs (used for on-page FAQ block and FAQPage JSON-LD)

The system prompt includes the full list of 30 trade slugs from `trades/_trades.php` so the model can pick internal links that actually exist.

## Editing an existing post

Open the `.md` file in `blog/posts/` and edit it directly. The frontmatter is a single-line JSON object between `---` fences — don't split it across lines. Everything after the second `---` is the body (standard markdown, rendered by Parsedown v1.7.4).

## Deleting a post

Delete the `.md` file from `blog/posts/`. `index.php` and `sitemap.php` pick up the change on next request — no cache to clear.

## Recommended cadence

- **2 posts/week** is the sweet spot for Google indexing signals without burning the content well dry.
- Topics in `_topics.php` are pre-planned to cover the main SEO clusters: marketing, trade-specific, SEO, pricing, design, FAQ.
- Add new topics to `_topics.php` as you think of them — the generator will use them next time.

## Dependencies

- `lib/Parsedown.php` — markdown-to-HTML renderer (erusev/parsedown v1.7.4, single file, no composer needed).
- `GROQ_API_KEY` — must be set via `.htaccess SetEnv` on prod or `export GROQ_API_KEY=...` in the shell before running generate.php.

## Server-side URLs

- `/blog/` — listing
- `/blog/[slug]` — single post (rewritten to `/blog/template.php?slug=[slug]` by .htaccess)
- `/sitemap.xml` — dynamic sitemap that auto-includes every post in `posts/`

## Local dev

You can't run `generate.php` locally unless you've exported a Groq key in your shell. To test rendering only, hand-write a `.md` file in `blog/posts/` using the same frontmatter shape as the seed posts.
