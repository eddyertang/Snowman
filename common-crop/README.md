# Common Crop

A farm-to-neighbour buying club for Nottinghamshire. Members join a local **pot**, put money on the food they want, and every pot buys together at the best bulk price the combined money reaches. Unused money carries over, and parcels are collected by QR code at a local hub.

- **[PLAN.md](PLAN.md)**: legality, structure, business model, suppliers, crowdfunding, 12-week pilot, and (§10) the pot model and its legal implications.
- **`site/`**: working demo (no build step).
- **`backend/schema.sql`**: Postgres/Supabase data model.
- **`tests/`**: engine tests.

## Run it

```sh
cd common-crop/site && python3 -m http.server 8000
# landing page:  http://localhost:8000/
# member app:    http://localhost:8000/app.html
```

Opening the files directly also works. Demo state is kept in your browser; use "Reset demo" in the app to start over.

```sh
node --test common-crop/tests/*.test.js   # engine tests
```

### Try this in the member app

1. **Choose items:** add money to strawberries and watch your share, carry-over and saving update.
2. **Pickup:** accept Sam's request to collect their parcel.
3. **Pot:** press **Close orders & buy**.
4. **Hub scanner:** scan your code. It releases your parcel *and* Sam's. Then scan Sam's code and see it refused.
5. **Pot:** start the next cycle. Priya didn't collect, so her parcel is forfeited. Your leftovers are still on each item under **Credit**.

## Structure

```
site/
  index.html   public landing page
  app.html     member app: Pot · Choose items · Credit · Pickup · Group · Rules · Hub scanner
  styles.css   design tokens on :root (light + dark), then components
  engine.js    THE RULES: tier pricing, fair allocation, ledger, QR tokens, delegation.
               Pure functions, no DOM. Runs in the browser and in Node (tests, server).
  data.js      sample hubs, pots, items + price tiers, synthetic network demand
  store.js     the only data layer; demo state in localStorage, swap for API calls
  landing.js   landing page rendering
  member.js    member app rendering and events
backend/schema.sql   tables that mirror the engine (append-only ledger etc.)
tests/engine.test.js
```

Pages only talk to `store.js`, and `store.js` only applies rules through `engine.js`. Going live means swapping `store.js` internals for API calls, while the server runs the same `engine.js`, so browser previews and real allocations always agree.

## Growth path

| Stage | What |
|---|---|
| **0. Now** | Static site + founding-member signup (point the join form at Tally/Formspree). WhatsApp Community per pot for the group chat. |
| **1. Pilot** | Run orders on Open Food Network UK, or a spreadsheet plus Stripe payment links. Run `engine.js` by hand at close to produce packing lists. |
| **2. Own platform** | Supabase (auth, Postgres from `schema.sql`, row-level security per member and pot) plus Stripe (Customer balance or a separate account for credit, refunds to card for withdrawals). A scheduled Edge Function runs `engine.settleCycle` at close and writes `commit` events. Signed per-cycle QR tokens (HMAC, server-side). A hub scanner PWA using the camera (`html5-qrcode`). |
| **3. Scale** | Real map (Leaflet + OpenStreetMap; hubs already have lat/lng). postcodes.io for pot assignment. In-app chat on Supabase Realtime, once Online Safety Act duties are in place. Farmer dashboard (orders, invoices). Delivery routing. |
