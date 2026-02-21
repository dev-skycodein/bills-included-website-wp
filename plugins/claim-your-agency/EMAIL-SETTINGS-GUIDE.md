# Claim Your Agency – Email setup guide

There are **three** emails in the Claim My Agency flow. Two are configured in **WordPress admin**; one is configured in **Firebase**.

---

## 1. Agency Claim Approved (WordPress admin)

**When it sends:** After an admin clicks **Approve** on a claim.

**Where to configure:**  
**WP Admin → Agency Claims → Settings** (or **Claim Your Agency Settings**).

| Setting | Description |
|--------|-------------|
| **Enable approval email** | Check to send an email when a claim is approved. |
| **From name** | Sender name (e.g. "Bills Included"). Optional; falls back to site name. |
| **From email** | Sender address (e.g. `no-reply@yourdomain.com`). Optional; use your domain for better deliverability. |
| **Email subject** | Subject line of the approval email. |
| **Email body** | Body text. Line breaks are kept. |

**Placeholders in subject/body:**

- `{CLAIMANT_NAME}` – Claimant’s name  
- `{CLAIMANT_EMAIL}` – Claimant’s email  
- `{AGENCY_NAME}` – Name of the agency  
- `{DASHBOARD_URL}` – Link to the agency dashboard (use in body; e.g. “Log in here: {DASHBOARD_URL}”)  
- `{SITE_NAME}` – Your site name  

**Example subject:**  
`Your agency claim for {AGENCY_NAME} has been approved`

**Example body:**  
```
Hello {CLAIMANT_NAME},

Your agency claim for {AGENCY_NAME} on {SITE_NAME} has been approved.

You can access your agency dashboard here: {DASHBOARD_URL}

Thanks,
{SITE_NAME} team
```

Use **“Send test approval email”** (same page) to send a test to any email address.

---

## 2. Rejected Claim of Agency (WordPress admin)

**When it sends:** After an admin clicks **Reject** on a claim.

**Where to configure:**  
**WP Admin → Agency Claims → Settings** (same page as above).

| Setting | Description |
|--------|-------------|
| **Enable rejection email** | Check to send an email when a claim is rejected. |
| **Rejection email subject** | Subject line of the rejection email. |
| **Rejection email body** | Body text. |

Rejection emails use the **same From name and From email** as the approval email (so set those in the approval section).

**Placeholders:**

- `{CLAIMANT_NAME}`  
- `{CLAIMANT_EMAIL}`  
- `{AGENCY_NAME}`  
- `{SITE_NAME}`  

**Example subject:**  
`Your agency claim was not approved`

**Example body:**  
```
Hello {CLAIMANT_NAME},

Thank you for your interest. Unfortunately we are unable to approve your agency claim for {AGENCY_NAME} at this time.

If you have questions, please reply to this email.

Thanks,
{SITE_NAME} team
```

---

## 3. Verification / sign-in link email (Firebase)

**When it sends:** When the user submits the “Claim this agency” form (Firebase sends the “magic link” to verify their email).

**Where to configure:**  
**Firebase Console** → your project → **Authentication** → **Templates** (or **Email templates**).

| Setting | Description |
|--------|-------------|
| **Sender name** | Name shown as the sender (e.g. “Bills Included”). |
| **Reply-to** | Reply-to email address. |
| **Subject** | Subject of the Firebase email (e.g. “Sign in to [Your Site]”). |
| **Body** | Firebase allows customising the email body; the “Sign in” link is inserted by Firebase. |

Also set the **Action URL** (Continue URL) to your WordPress **callback page URL** (the page that contains the shortcode `[agency_login_callback]`). That way the link in the email opens your site and completes sign-in.

**Note:** This email is sent by Firebase’s servers, not by WordPress. For better deliverability, use a clear sender name and a proper reply-to in the Firebase template.

---

## Quick reference

| Email | Trigger | Configured in |
|-------|--------|----------------|
| **Agency Claim Approve** | Admin approves claim | WP Admin → Agency Claims → Settings |
| **Rejected Claim of Agency** | Admin rejects claim | WP Admin → Agency Claims → Settings |
| **Verification / sign-in link** | User submits claim form | Firebase Console → Authentication → Templates |
