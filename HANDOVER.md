# Handover

Kadence child theme `simali-kadence-child` and plugin `simali-core`. WooCommerce stays. Elementor is not part of the front end.

## Edit a product

In WooCommerce, edit the product. The price is per square metre, inc VAT. Leave the price empty when it is not confirmed: the page then says “Price on request” and the basket button is replaced by the quote form. Quantity is square metres, in steps of 0.1.

Optional product meta:

- `_simali_pack_m2` — square metres in a pack. The calculator rounds up to whole packs.
- `_simali_weight_per_m2` — kilograms per square metre. Pallet size becomes 1000 kg divided by this, otherwise 15 m².
- `_simali_m2_per_pallet` — override the pallet size.
- `_simali_price_max` — when set above the price, the label says “from”.
- `_simali_matching_outdoor` — product ID of the outdoor match.

Finish, thickness, size and underfloor heating come from product attributes. Filters only list terms that exist. Colour, edge, origin and indoor/outdoor stay as TODOs until you add the attribute.

## Testimonials

Custom post type Testimonials. The three homepage quotes are seeded verbatim. Set the source meta only when it is a real platform. Aggregate review schema is not output, because these quotes have no platform source.

## Trust badges

Settings → Trust badges. A badge is shown only when `confirmed` is ticked. Years, the CA' Pietra logo, insurance and a workmanship guarantee start unticked.

## Case studies

Our Work is the `simali_case` archive. Each case has town, room, material, image URL and optional square metres. Seeded titles and pictures come from the South West installation page. Stories, square metres and before photos are TODOs.

## Forms

Quotes, sample requests and contact messages are stored as the private post type Enquiry and emailed to sales@simalistone.co.uk and to the customer. Spam uses a honeypot and a three-second time trap. Photographs: up to 5 files, 10 MB, jpg, png, heic or pdf.

The sample basket is a 30-day cookie, not a WooCommerce line. The maximum is the option `simali_sample_max`. Until that is set, the page does not invent a number.

## Postcodes

`simali-core/data/outward-centroids.json` is outward-code centroids (Royal Mail © 2021, ONS Open Government Licence). The check is distance from SP7, 40 miles, with no live API. Shaftesbury is in area. A Manchester postcode is outside, and the message still offers nationwide delivery.

## Permalinks and redirects

`/limestone/`, `/porcelain/` and `/outdoor/` are the shop categories. These 301:

- `/contact-us/` → `/contact/`
- `/delivery-information/` → `/delivery/`
- `/refund_returns/` → `/returns/`
- `/product-category/limestone/` → `/limestone/`
- `/product-category/porcelain/` → `/porcelain/`
- `/product-category/timeless-stone-collection/` → `/limestone/?pa_collection=timeless-stone-collection`
- `/product-category/stone-flooring-guides/` → `/guides/`

Product slugs are unchanged. After moving the plugin to a host, open Settings → Permalinks and save once.

## Elementor

On the server that still has it:

`wp simali remove-elementor`

That deactivates and deletes Elementor and Elementor Pro. It does not delete this theme. Run it only after the new templates are the ones in use.

`wp simali seed` creates pages, the menu, testimonials, cases and products. It does not duplicate products, testimonials or cases if they already exist. It does refresh specification attributes from the short description, and it turns WooCommerce coming-soon mode off.

## Fonts

Inter and Cormorant Garamond, Latin WOFF2, `font-display: swap`. The Inter licence text is in `assets/fonts/OFL.txt`. Cormorant Garamond is also used under the SIL Open Font License.

## What this staging copy does not include

Product photographs were not copied off the live site. Case images still point at the live uploads. There is no hero photograph. See `TODO-CLIENT.md`.
