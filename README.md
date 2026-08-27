# Smart Cookie Consent for Joomla 6

Smart Cookie Consent is a lightweight consent-management foundation for Joomla 6.1.x. It provides equal first-layer choices, category preferences, consent withdrawal and opt-in activation of managed scripts and embeds.

## Highlights

- Joomla subscriber events, dependency injection and Web Asset Manager
- Layout-based frontend markup
- Accept all, reject all and granular preferences
- Necessary, preferences, analytics and marketing categories
- No non-essential category is enabled by default
- Reopenable settings and consent withdrawal
- Consent revision control and timestamped browser record
- Cache/CDN-safe client-side state handling
- Configurable category snippets
- Script, iframe and image activation through `data-cookie-category`
- Cookie cleanup patterns when a category is withdrawn
- Keyboard-accessible preferences dialog and reduced-motion support
- English, Turkish, German and French language files
- Language-specific privacy/cookie policy URLs with a global fallback
- No external JavaScript dependency

## Installation

1. Install the ZIP from Joomla Administrator.
2. Enable **System - Smart Cookie Consent**.
3. Set the fallback privacy policy URL. For multilingual sites, add language-specific mappings such as `de-DE=/datenschutz` or `fr-FR=/politique-de-confidentialite`.
4. Move every non-essential service into the matching category snippet field, or annotate its markup as described below.
5. Verify the site with a clean browser profile before release.

## Blocking contract

Code pasted into **Preferences**, **Analytics** or **Marketing snippets** is not inserted into the page until that category has consent.

Existing template or extension markup can be held with an explicit annotation:

```html
<script type="text/plain" data-cookie-category="analytics" data-cookie-src="https://example.com/analytics.js"></script>
<iframe data-cookie-category="marketing" data-cookie-src="https://example.com/embed"></iframe>
```

Supported category values are `preferences`, `analytics` and `marketing`. For images, `data-cookie-src` and `data-cookie-srcset` are supported.

The plugin intentionally does not guess whether arbitrary third-party markup is essential. Automatic HTML rewriting is unreliable across Joomla templates and extensions; site owners must classify and route every non-essential service.

## JavaScript integration

```js
window.JTCookieConsent.hasConsent('analytics');
window.JTCookieConsent.openPreferences();
window.JTCookieConsent.getState();
window.JTCookieConsent.reset();
```

The document emits `jt:cookie-consent:ready` and `jt:cookie-consent:change` events. The event `detail` is the current consent state, or `null` before a choice.

## Multilingual policy links

The policy link text follows Joomla language strings when its override field is left empty. For the URL, the global **Privacy / Cookie Policy URL** remains the fallback. Optional language-specific mappings can be added one per line using `language-tag=URL`, for example:

```text
en-GB=/privacy-policy
de-DE=/datenschutz
fr-FR=/politique-de-confidentialite
tr-TR=/gizlilik-politikasi
```

The active Joomla site language is matched first. A base-language mapping such as `de=/datenschutz` is also accepted when no exact `de-DE` mapping exists. Invalid or unsafe URL schemes are ignored and the validated fallback URL is used instead.

## Updating from 1.0.x

Version 1.1.0 stores structured, category-based consent. The old acknowledgement value is not treated as valid consent, so visitors will be asked to choose again. Existing plugin parameters are retained by Joomla during an upgrade.

## Compliance scope

This plugin supplies technical consent controls; installing it alone does not make a website legally compliant. The site owner remains responsible for classifying services, preventing every non-essential request before consent, maintaining accurate disclosures, choosing an appropriate consent lifetime and applying national rules. Server-side or third-party cookies that JavaScript cannot access may require service-specific deletion or an additional integration.

## Compatibility

- Designed for Joomla 6.1.x
- Vanilla JavaScript; no Bootstrap requirement
- GPL-2.0-or-later
