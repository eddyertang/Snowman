# TODO(client)

Nothing on the site prints `TODO(client)` any more. Where the live site had no answer, the new site uses neutral copy (“call ahead on 01747 852557”) and the gap is listed here. Nothing in this list has been invented.

## One decision, used everywhere

- Confirm **30 or 35 years**. The live site says both; the new site says “over 30 years”, which is true either way. Change it in `front-page.php`, `inc/pages.php` (About) and the landing copy in `class-simali-setup.php` if you prefer 35.
- **Wincombe or Wincome.** The live site spells the business park “Wincome”; Dorset Council and Royal Mail use “Wincombe”. The new site uses Wincombe. Say if you want the other.

## Showroom

- Opening hours → Settings option `simali_opening_hours`. Until set, pages say to call before setting off.
- Parking. The showroom page says “parking outside the unit”, which is what the photographs show; confirm.
- Appointment policy. The copy currently says visits are by appointment.

## Samples

- Free or paid. The samples page does not state a price; sample requests go through the form, not the basket.
- Maximum number per order → option `simali_sample_max`. Until set, no limit is shown.
- Dispatch time. Not shown anywhere.

## People

- Full names, roles and photographs. Reviews name Stu only; the About page says “you will deal with Stu”.
- Permission to use the CA' Pietra logo. The words “CA' Pietra stockist” are a stated fact. The logo badge stays off until `confirmed` is ticked in Settings → Trust badges.
- The 07785 795596 “best price” mobile is back on the About page stockist panel because it is on the live home page. Remove it if it is no longer in use.

## Commercial

- Prices for the 13 products the live site lists without one (the Timeless Stone Collection stones, Marnhull and Epsom). They show “Price on request” with the quote form; add a regular price in WooCommerce and the basket appears.
- Reply time for “price on request” and for the thank-you page. No time is promised; the copy says “we will come back to you by phone or email”. Add one in `content-single-product.php` and `inc/pages.php` if you want to.
- Restocking and breakage fees. The returns page quotes the live site's supplier conditions and statutory rights; nothing else is stated.
- Care and sealing copy is written per material (`simali_care_copy()` in `inc/woocommerce.php`). Read it once.
- Missing product facts: origin for most ranges, exact edge finish for some porcelain. Add the attribute term or `_simali_origin` meta and it appears in Specifications.
- Hero photograph → option `simali_hero_image` (URL). Default is the Ludlow living room.

## Our work

- Square metres and before photographs for each case (only the Shaftesbury bathroom has a before shot).
- A testimonial only when you have one for that job.
- Source and date for Sarah, Michelle and Annette (`_simali_source`, `_simali_date`). There is no aggregate rating until a verifiable platform is the source.
- The Google reviews link is a search for the business; replace with the Google Business Profile short link when you have it (`simali_google_reviews_url()`).

## Trust

- Accreditations, insurance and a workmanship guarantee. The badges screen shows nothing until `confirmed` is true.

## Legal

- Privacy policy wording, including how long enquiries are kept.
- Statutory distance-selling wording on returns: the live site's text is reproduced; have it read by whoever does your terms.
- Governing-law wording. The old terms page said “laws of gb” and was a generator template. That template is not reproduced.
