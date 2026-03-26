# Tradie Sites Co. — Public Website

Public-facing sales site for Tradie Sites Co. at `site.tradiebud.tech`.

## Features

- Sales/marketing landing page
- Groq AI chatbot (answers questions, captures leads)
- Lead enquiry form → email notification + CSV log
- Mobile-first responsive design
- Pure HTML/CSS/JS frontend, PHP backend

## Tech Stack

- HTML5 + CSS3 + Vanilla JS
- PHP 8.x (chat endpoint + lead handler)
- Groq API (llama-3.1-70b-versatile)
- Google Fonts (Barlow + Barlow Condensed)

## File Structure

```
index.html          # Main sales page with chatbot UI
chat.php            # Groq AI chat API endpoint
submit-lead.php     # Lead form handler → email + CSV
leads.csv           # Auto-created lead log (gitignored)
.htaccess           # Rewrites, security headers, env vars
```

## Deployment (VentraIP)

### 1. Create Subdomain
- cPanel → Subdomains → create `site.tradiebud.tech`
- Document root: `public_html/site`

### 2. Upload Files
```bash
# Option A: Git
git clone https://github.com/YOUR_USER/tradie-sites-public.git public_html/site

# Option B: FTP
# Upload all files to public_html/site/
```

### 3. Configure Environment
Edit `.htaccess` and replace:
- `[GROQ_API_KEY]` → your Groq API key (get from console.groq.com)
- `[ADMIN_EMAIL]` → notification email address

### 4. Set Permissions
```bash
chmod 755 .
chmod 644 *.html *.php .htaccess
```

### 5. Test
- Visit `https://site.tradiebud.tech`
- Test chatbot (sends to Groq API)
- Test contact form (check email + leads.csv creation)

## Environment Variables

Set in `.htaccess` via `SetEnv`:

| Variable | Description |
|----------|-------------|
| `GROQ_API_KEY` | Groq API key for chatbot |
| `ADMIN_EMAIL` | Email address for lead notifications |

## Rate Limiting

Chat endpoint limited to 30 requests per IP per hour (file-based counter in system temp directory).
