# Common Crop

A farm-to-neighbour buying club for Nottinghamshire. Neighbours pledge shares in bulk "crowd-buys", farms deliver to a local collection hub, and nobody is charged unless a crowd-buy hits its target.

- **[PLAN.md](PLAN.md)**: legality, structure, business model, supplier strategy, crowdfunding, 12-week pilot plan.
- **`site/`**: working shell website (no build step).
- **`backend/schema.sql`**: Postgres/Supabase data model for the real thing.

## Run the site

Open `site/index.html` in a browser, or serve the folder:

```sh
cd common-crop/site && python3 -m http.server 8000
```

It works as static hosting on GitHub Pages, Netlify or Cloudflare Pages (all free).

## How the shell is built to grow

```
site/
  index.html   markup only: sections map 1:1 to future pages/routes
  styles.css   design tokens on :root (light + dark), then components
  data.js      sample data, same shape as backend/schema.sql
  store.js     THE ONLY data layer: async methods, swap bodies for API calls
  app.js       rendering + events; reads only from Store
```

The rendering code never touches data directly, so going live means changing `store.js` and nothing else in the UI.

### Growth path

| Stage | What | Cost |
|---|---|---|
| **0. Now** | Static site + founding-member signup. Point the join form at a Tally/Google Form or Formspree. | £0 |
| **1. Pilot** | Run real orders on **Open Food Network UK** (open-source food-hub platform: ordering, payments, producer invoices). Keep this site as the front door and link to the OFN shop. | Low, turnover-based |
| **2. Crowd-buys** | Add Supabase (auth + Postgres from `backend/schema.sql`) and Stripe. Replace `store.js` methods with Supabase queries. Pledges save a card with a Stripe **SetupIntent**, and a scheduled function at close time charges pledges on buys where `target_met`. | ~£0–25/mo + Stripe fees |
| **3. Scale** | Move to a framework (Next.js/Astro) for per-farm and per-product pages (SEO), member accounts, hub-volunteer app (collection check-off, temperature logs), and farmer dashboard (orders, invoices). The token CSS and data layer carry across. | Dev time |

### Why SetupIntent instead of pre-authorising

Card authorisations expire after about 7 days. Saving the card and charging only when a target is met works for any length of order window, and nobody's money is tied up for a buy that doesn't happen.
