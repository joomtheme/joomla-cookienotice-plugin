# Smart Cookie Consent for Joomla 6

Smart Cookie Consent is a lightweight consent-management foundation for Joomla 6.1.x. It provides equal first-layer choices, category preferences, consent withdrawal and opt-in activation of managed scripts and embeds.

## Current release: 1.1.3

[Download Smart Cookie Consent 1.1.3](https://github.com/joomtheme/joomla-cookienotice-plugin/releases/download/v1.1.3/plg_system_cookienotice_1.1.3.zip) · [Release notes](https://github.com/joomtheme/joomla-cookienotice-plugin/releases/tag/v1.1.3)

Version 1.1.3 introduces an accessible cookie icon launcher with configurable bottom-left/bottom-right positioning and CSS spacing overrides. The consent engine, saved state, cookie cleanup and category behavior are unchanged.

The maintainer confirmed that the release passed JED Checker and site testing, and that updates were received through Joomla's update system. The published ZIP's SHA-256 and all packaged files were also verified against the release commit.

```text
SHA-256: 4d914503356c03fec279c57af874b7f66b57ceb24d1e415b5713b02f0f137a06
```

## Highlights

- Joomla subscriber events, dependency injection and Web Asset Manager
- Layout-based frontend markup
- Accept all, reject all and granular preferences
- Necessary, preferences, analytics and marketing categories
- No non-essential category is enabled by default
- Accessible inline cookie icon to reopen settings and withdraw consent
- Configurable launcher corner and CSS x/y offsets
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

1. Download the installable `plg_system_cookienotice_1.1.3.zip` release asset and install it from Joomla Administrator.
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

## Settings launcher (1.1.3)

After a saved choice, the 48 px cookie icon opens the existing preferences dialog. The icon is embedded from [Bootstrap Icons](https://icons.getbootstrap.com/icons/cookie/) (MIT licensed), with no external dependency. **Cookie Settings Accessible Name** retains the existing `launcher_text` parameter and its language fallback; it now supplies the button's `aria-label` instead of visible text. The saved consent format and categories are unchanged.

Select **Bottom left** (default) or **Bottom right** under **Cookie Settings Icon Position**. The default bottom offset is 72 px to leave space for common fixed accessibility controls. Templates may adjust horizontal and vertical spacing independently:

```css
:root {
    --jt-cookie-launcher-x: 20px;
    --jt-cookie-launcher-y: 96px;
}
```

The x offset is measured from the selected physical edge, including on RTL pages; the y offset is measured from the bottom. Check the actual accessibility widget and other fixed controls on both desktop and mobile, and increase offsets as needed. The icon remains keyboard focusable and has a visible focus outline; forced-colors mode uses system colors.

### Manual Joomla 6.1.x release checklist

- Install 1.1.3 over 1.1.2 on a staging site; confirm existing consent records and plugin settings persist, and confirm the CSS asset loads at 1.1.3.
- Test a first-time visitor, then accept, reject, save category preferences, reopen by mouse and Enter/Space, withdraw consent and reload. Verify category activation and cookie cleanup still behave as in 1.1.2.
- Set a custom `launcher_text` in each relevant language; inspect the button's accessible name with a screen reader or accessibility inspector. Verify the translated default when the field is blank.
- Switch both launcher corners; test LTR and RTL pages, 320 px mobile width, zoom at 200%, landscape and a page with Joomla Accessibility or another fixed widget. Adjust x/y CSS overrides in the template where required.
- Check Tab focus outline, dialog focus return and Escape, forced-colors/high-contrast mode and reduced-motion preference.
- Run JED Checker on the installable ZIP and test installation/update plus frontend behavior on a real Joomla 6.1.x demo site before publishing.

## Updating from 1.1.2

Use Joomla's extension update system or install the 1.1.3 release ZIP over the existing installation. Existing plugin settings and valid consent choices are retained. The existing `launcher_text` value now supplies the icon button's accessible name. Clear page/CDN caches after updating and confirm the CSS/JS assets load with version 1.1.3.

## Updating from 1.0.x

Version 1.1.0 stores structured, category-based consent. The old acknowledgement value is not treated as valid consent, so visitors will be asked to choose again. Existing plugin parameters are retained by Joomla during an upgrade.

## Compliance scope

This plugin supplies technical consent controls; installing it alone does not make a website legally compliant. The site owner remains responsible for classifying services, preventing every non-essential request before consent, maintaining accurate disclosures, choosing an appropriate consent lifetime and applying national rules. Server-side or third-party cookies that JavaScript cannot access may require service-specific deletion or an additional integration.

## Compatibility

- Designed for Joomla 6.1.x
- Vanilla JavaScript; no Bootstrap requirement
- GPL-2.0-or-later


## Cookie cleanup in 1.1.2

Withdrawal saves the new consent record before emitting the change event and cleaning accessible cookies in denied categories. The page then reloads. On initialization, a valid saved decision triggers another cleanup of denied categories before allowed content is activated. This second pass addresses accessible cookies that previously loaded services may write during unload. It also cleans leftovers when upgrading an existing denied decision from 1.1.1. Startup cleanup does not cause a reload loop.

Cleanup still relies on cookie names visible in document.cookie and configured category-specific patterns. Paths along the current URL are attempted with and without trailing slashes. The plugin's own consent cookie is excluded. HttpOnly cookies, other origins, inaccessible paths and other storage partitions remain outside this JavaScript cleanup. This is not a continuous blocker for scripts independently loaded outside consent control; all integrations must respect the saved choice. Avoid overlapping category patterns: a cookie matching a denied category is eligible for deletion even if an allowed category also uses it.

Use one integration method per service: either the category snippet field OR annotated template/module markup. Do not add the same GA loader to both. Google Analytics measurement configuration and loading should belong to the same consent-controlled integration. Removing an already executed script element does not stop its existing listeners; the reload and subsequent cleanup remain necessary.

Version 1.1.2 is a patch update: the consent schema and revision are unchanged. Existing valid choices are retained. The reset() API continues to remove the consent record and reopen the choice on reload; use the preference controls to withdraw consent and invoke the withdrawal cleanup flow.

### Historical validation: 1.1.2

The 1.1.2 development checks reported 17/17 controlled JavaScript scenarios passing with a simulated cookie jar and DOM/API stubs, including the late-write cleanup regression. XML/JSON, manifest paths, language key parity and asset versions were checked. These results describe the 1.1.2 cleanup work; they are not a claim that every browser or integration has been tested.

For site-specific regression testing, accept Analytics, then withdraw consent and reload. Verify that accessible `_ga` and `_ga_*` cookies are removed, consent remains denied, and allowed categories retain their cookies. Repeat on non-trailing-slash paths and in Firefox, using a single consent-controlled analytics integration.
