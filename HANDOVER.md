# Handover

Kadence child theme `simali-kadence-child` and plugin `simali-core`. WooCommerce stays. Elementor is not part of the front end.

## Photographs

`wp-content/uploads/simali/` holds 260 optimised photographs copied from simalistone.co.uk (product shots, installation photos, the showroom and the dog). They are committed with the code so that staging and production start with the same media.

`Simali_Media` (`includes/class-simali-media.php`) turns a file in that folder into a WordPress attachment the first time it is needed and remembers it in `_simali_file` meta, so re-running the seed never duplicates an image. If a file is missing on the server it is downloaded from the `source` URL recorded in the data files.

Theme helpers in `inc/template-tags.php`:

- `simali_image( $file, $alt, $ratio, $args )` prints a lazy `<img>` with width and height.
- `simali_hero_url()` is the homepage hero: option `simali_hero_image` (a URL) or the Ludlow living-room photograph.
- `simali_placeholder()` is a quiet grey block with an `aria-label`, used only when a product has no image.

WordPress writes resized copies next to the originals. `.gitignore` keeps those out of the repository.

## Edit a product

In WooCommerce, edit the product. The price is per square metre, inc VAT. Leave the price empty when it is not confirmed: the page then says “Price on request” and the basket button is replaced by the quote form. Quantity is square metres, in steps of 0.1.

Optional product meta:

- `_simali_pack_m2` — square metres in a pack. The calculator rounds up to whole packs.
- `_simali_weight_per_m2` — kilograms per square metre. Pallet size becomes 1000 kg divided by this, otherwise 15 m².
- `_simali_m2_per_pallet` — override the pallet size.
- `_simali_price_max` — when set above the price, the label says “from”.
- `_simali_matching_outdoor` — product ID of the outdoor match.
- `_simali_origin` — country or quarry, shown in Specifications.
- `_simali_meta_description` — `<meta name="description">`; falls back to the short description.

Finish, thickness, size, colour, collection, location (indoor/outdoor), room and underfloor heating are product attributes. Filters only list terms that exist in the category. Care and sealing copy is chosen by material (limestone, porcelain, sandstone; indoor or outdoor).

### The seed data

`data/catalog-snapshot.json` is the 39 live products with prices, descriptions, attributes and image files. `data/case-seeds.json` is 34 case studies built from the South West installation page and the product galleries. `wp simali seed` reads both, so the staging copy matches the live catalogue without copying the database.

## Testimonials

Custom post type Testimonials. The three homepage quotes are seeded verbatim with five stars. Set `_simali_source` and `_simali_date` when you know the platform; until then the card says “Simali Stone customer”. Every testimonial block links to Google reviews and Houzz. Aggregate review schema is not output, because these quotes have no platform source.

## Trust badges

Settings → Trust badges. A badge is shown in the footer only when `confirmed` is ticked. The CA' Pietra logo, insurance and a workmanship guarantee start unticked. The four homepage trust points (over 30 years, supply and install, CA' Pietra stockist, reviews) are stated facts from the live site and are not badges.

## Case studies

Our Work is the `simali_case` archive, filterable by room and material. Each case has town, room, material, a featured image, optional `_simali_gallery` (attachment IDs), `_simali_before` (attachment ID), `_simali_m2` and a linked product. One case (the Shaftesbury bathroom) has a before photograph. The `[simali_work limit="3" material="limestone" exclude="123"]` shortcode places related cases on product and case pages.

## Forms

Quotes, sample requests and contact messages are stored as the private post type Enquiry and emailed to sales@simalistone.co.uk and to the customer. Spam uses a honeypot and a three-second time trap. Photographs: up to 5 files, 10 MB, jpg, png, heic or pdf.

The sample basket is a 30-day cookie, not a WooCommerce line. The maximum is the option `simali_sample_max`. Until that is set, the page invites people to add every stone they are deciding between.

## Showroom hours

Option `simali_opening_hours` (plain text) is printed on the showroom page, the contact page and in the footer. Until it is set, the copy says to call 01747 852557 before setting off.

## Postcodes

`simali-core/data/outward-centroids.json` is outward-code centroids (Royal Mail © 2021, ONS Open Government Licence). The check is distance from SP7, 40 miles, with no live API. Shaftesbury is in area. A Manchester postcode is outside, and the message still offers nationwide delivery.

## Permalinks and redirects

`/limestone/`, `/porcelain/` and `/outdoor/` are the shop categories; `Simali_Catalog::category_url()` gives the canonical URL and the filter form posts to it. These 301, keeping any query string:

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

`wp simali seed` creates pages, the menu, testimonials, cases, products, attributes and attachments. It does not duplicate anything that already exists. It sets the store to GBP and United Kingdom, refreshes specification attributes from the snapshot, and turns WooCommerce coming-soon mode off. Landing pages that already have content are left alone; a landing page with empty content is refilled (so clearing the Timeless Stone Collection page and re-seeding regenerates it from the catalogue).

## Fonts

Inter and Cormorant Garamond, Latin WOFF2, `font-display: swap`. The Inter licence text is in `assets/fonts/OFL.txt`. Cormorant Garamond is also used under the SIL Open Font License.

## Local QA

`qa/` holds full-page screenshots at 1440 and 390 px of every template, taken on a local PHP + SQLite WordPress with the seed applied. Nothing on the site prints `TODO(client)`; the remaining unknowns are in `TODO-CLIENT.md` and have neutral customer-facing copy.
