# Logo flow (simple)

## Default logo

- **URL**: `GSLI_DEFAULT_AGENCY_LOGO` in `gsheet-listing-importer.php`  
  (`https://thebillsincluded.com/wp-content/uploads/2026/02/purple-house-clipart.jpg`)
- **Agency CPT**: Uses post meta `agency_logo` for display (not the CPT’s featured image).
- **Listing**: Uses post meta `company_logo` as fallback; when the listing has an agency, the single listing page shows the **agency’s** `agency_logo` instead.

## Where logos are set

| Source | Agency `agency_logo` | Listing `company_logo` |
|--------|----------------------|-------------------------|
| **GSheet import** | Set to default when **creating** a new agency. | Set to default for **every** listing. |
| **Re-scrape** | Existing agencies are **not** updated (we only create). Logo is never overwritten on re-scrape for existing agencies. | Listings get `company_logo` = default again when mapping is applied. |
| **Claim-your-agency edit form** | Owner can change the logo. When they save, we set `agency_logo_edited_by_owner = 1` so any future “sync agency from sheet” logic must **not** overwrite `agency_logo`. | — |

## Display (single listing page)

1. If the listing has `agency_post_id` and that agency has a non-empty `agency_logo` → show **agency’s** `agency_logo`.
2. Otherwise → show listing’s `company_logo` (or author profile pic / featured image per existing logic).

So: **one source for “company” logo on the listing page** – agency’s `agency_logo` when present, else listing’s `company_logo`.

## Re-scrape and claimed agencies

- The importer does **not** update existing agencies (it only finds or creates). So re-scrape never touches an existing agency’s meta, including `agency_logo`.
- If you later add “update existing agency from sheet” (e.g. sync name, phone, email), you must **skip** updating `agency_logo` when `get_post_meta( $agency_post_id, 'agency_logo_edited_by_owner', true ) === '1'`.
- New agencies created on import get `agency_logo` = default and `agency_logo_edited_by_owner = 0`.

## Summary

- **Import**: New agency → default logo; every listing → default `company_logo`.
- **Claimed agency**: Owner can change logo in the edit form; that sets `agency_logo_edited_by_owner = 1`.
- **Re-scrape**: Does not update existing agencies; if you add agency sync later, do not overwrite logo when `agency_logo_edited_by_owner` is set.
- **Display**: Single listing page uses agency’s `agency_logo` when the listing has an agency, else listing’s `company_logo`.
