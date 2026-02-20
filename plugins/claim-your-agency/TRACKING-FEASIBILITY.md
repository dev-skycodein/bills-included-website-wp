# Core tracking & data collection – feasibility vs current codebase

**Context:** GSheet Listing Importer (listings + `gsli_agency`), Claim Your Agency (`cya_claim`, agency_owner), ListingHub (listings CPT, search, contact popup, messages, views).  
**Completed column:** Treat as “to be done”; mark completed only after implementation.

---

## 1. Renting behaviour

| Data needed | Doable? | Notes | What we need from you |
|-------------|--------|-------|------------------------|
| **Listing views & visits (per listing, per session)** | Partially | **Per listing total:** already stored as `listing_views_count` on each listing (incremented in `single-listing.php` and `listing_detail_shortcode.php`). **Per session:** not stored; would need a new event log (e.g. custom table or options) keyed by session/cookie + listing ID. | Confirm if “per session” is required (e.g. unique visitors per listing). If yes, we add server-side or JS-based session tracking. |
| **Search queries & filters used** | Yes | Search runs via `listinghub_get_search_args()`; all params come from `$_REQUEST` (e.g. `sf*` fields: keyword, category, tag, locations, sort, price, etc.). No logging yet. | None. We can log search/filter params on the archive/search template or via a filter when the main query runs. |
| **Contact agent clicks** | Yes | Contact button calls `listinghub_call_popup(listingid)` then form submit. We can add a tracking event when the popup is opened (or when “Send” is clicked) and store listing ID + timestamp. | Decide: track “popup opened” only, or “Send clicked” only, or both. |
| **Lead submissions (completed enquiries)** | Yes | Leads are stored as `listinghub_message` posts with `user_to`, `dir_url`, `from_*`. Listing ID is **not** stored as meta (only in `dir_url`). We can add `dir_id` (or `listing_id`) meta when `dir_id` is in the form and then report leads per listing. | None for storage. Optional: confirm you want `dir_id` meta on `listinghub_message` for easier reporting. |
| **Save / shortlist listing (future feature)** | Yes | ListingHub already has favorites: `listinghub_save_favorite` / `_favorites` (and user meta `_dir_favorites`). We can log “save” events and count per listing / per user if you want analytics. | Confirm if you want a dedicated analytics log for save events or are fine with deriving from existing meta. |

---

## 2. Listing performance

| Data needed | Doable? | Notes | What we need from you |
|-------------|--------|-------|------------------------|
| **Views per listing** | Yes | Already implemented: `listing_views_count` on each listing. | None. |
| **View-to-lead conversion rate** | Yes (after small change) | Views = `listing_views_count`. Leads = count of `listinghub_message` where we can associate to listing. Today we need to add `dir_id` (or `listing_id`) to message meta when form has `dir_id` so we can count leads per listing. | Add and persist `dir_id` (or `listing_id`) on `listinghub_message` when contact form is listing-specific. |
| **Time spent on each listing & profile** | Yes (new) | Not present. Need JS on single listing (and agency profile) to send “time on page” (e.g. on unload or heartbeat) to an AJAX endpoint that stores listing_id / agency_id + duration. | Define: store per-session only or also aggregate “total minutes” per listing/agency. |
| **Time to first enquiry** | Yes (after small change) | First enquiry = min `post_date` of `listinghub_message` for that listing. Requires listing ID on message (see above). Listing publish date = `post_date` of listing. Difference = time to first enquiry. | Same as above: store listing ID on messages. |
| **Listing completeness score** | Yes (new) | Not present. We can define a score from existing data: e.g. has featured image, has rent/price meta, has description (post_content), has inclusions/features (terms or meta). Store as computed meta or on-demand. | Define which fields count (e.g. images, rent, description, bills-included type) and weight/ formula (e.g. 0–100). |

---

## 3. Agency engagement

| Data needed | Doable? | Notes | What we need from you |
|-------------|--------|-------|------------------------|
| **Agency claimed, unclaimed & claim agency start** | Yes | **Claimed:** `gsli_agency` with `agency_owner` > 0. **Unclaimed:** `agency_owner` empty or 0. **Claim start:** `cya_claim` post created (status pending) with `agency_post_id`; already logged in CYA. We can add analytics events (or DB records) for “claim form opened” and “claim submitted”. | None for counts. Optional: confirm if you want explicit “claim start” (form opened) tracking. |
| **Number of listings per agency** | Yes | Listings have `agency_post_id`. Count by agency: `meta_key = 'agency_post_id', meta_value = <agency_id>`. | None. |
| **Agency logins / sessions** | Partially | WordPress/Firebase login exists; no session counter today. We can hook `wp_login` (and CYA’s `cya_agency_wp_login`) to log “agency owner login” (user_id, agency_id, timestamp) and then count logins per agency or per period. | Confirm: count “logins per agency” only, or also “sessions” (e.g. same day = one session). |
| **Response times from first message** | Partially | “First message” = first `listinghub_message` to that agency (user_to = agency owner). Response = e.g. “agency replied” or “listing updated”; no reply-tracking in current code. So we can store “time from first lead to first agency action” only if we define and record “agency action” (e.g. edit listing, reply). | Define what “response” means (e.g. reply to lead, edit listing, update profile) and whether you will add reply/action tracking. |
| **Actions taken by agency** | Partially | No central log. We can add hooks on: post update (listing), profile/agency update (CYA), and (if you add it) “reply to lead”. Then store event type + agency_id + timestamp. | Same as above: define which actions to count and whether you plan a “reply to lead” flow. |

---

## 4. Marketplace health

| Data needed | Doable? | Notes | What we need from you |
|-------------|--------|-------|------------------------|
| **Active listings count (weekly / monthly)** | Yes | Count `post_type = listing` (or `ep_listinghub_url`) `post_status = publish` with `post_modified` or `post_date` in range. | Define “active”: last modified in period, or published in period, or both. |
| **Active renters (MAU / WAU)** | Partially | “Renter” = user who viewed listings / searched / submitted a lead. Today we don’t have a clear “renter” identity: contact form can be used without login. So MAU/WAU is doable only for **logged-in** users who perform renter actions (e.g. search, contact, save). For anonymous, we’d need cookie/session IDs and a policy on privacy. | Decide: (1) MAU/WAU for logged-in renters only, or (2) also track anonymous (cookie/session) and how to store it. |
| **Renters & Agencies account created** | Partially | **Agencies:** Claimed agencies = count of `gsli_agency` with `agency_owner` > 0. “Account created” could be “user created on claim approval” (we have that in CYA). **Renters:** No dedicated “renter account” type; if you mean WP user registrations that are not agency owners, we can count by role or by “has no agency”. | Clarify: “Renters & Agencies account created” = (A) WP users created for claims + any other signups, or (B) only agency claims, or (C) separate renter signup flow to be built. |
| **Supply–demand ratio by area** | Yes | Supply = count of published listings per area (e.g. `listing-locations` term or address/postcode meta). Demand = e.g. count of searches or leads or views per area (from new or existing tracking). Ratio = supply / demand (or inverse) by area. | Define “area” (taxonomy term, postcode prefix, etc.) and “demand” (searches, leads, or views). |

---

## Summary

- **Already in place:** Listing views (total per listing), leads stored as `listinghub_message`, favorites/shortlist, claim CPT and agency_owner (claimed vs unclaimed), listings per agency.
- **Small changes:** Store listing ID on `listinghub_message` (for lead-based metrics); optionally add “contact click” and “claim start” events.
- **New but straightforward:** Search/filter logging, time on listing/agency, listing completeness score, agency login/session counts, active listings count, supply–demand by area (once “demand” and “area” are defined).
- **Needs product/UX decisions:** “Per session” view tracking, “response time” and “agency actions” (what counts as response/action), MAU/WAU for anonymous vs logged-in renters, and exact definition of “Renters & Agencies account created.”

Use the “What we need from you” column to confirm definitions and priorities; then we can implement in phases (e.g. first: message meta + search log + contact click + claim events; then time on page + completeness; then agency actions and MAU/WAU).
