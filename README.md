# Smart Cookie Consent for Joomla 6

Smart Cookie Consent is a lightweight consent-management foundation for Joomla 6.1.x and 6.2.x. It provides equal first-layer choices, category preferences, consent withdrawal and opt-in activation of managed scripts and embeds.

## Current release: 1.1.4

[Download Smart Cookie Consent 1.1.4](https://github.com/joomtheme/joomla-cookienotice-plugin/releases/download/v1.1.4/plg_system_cookienotice_1.1.4.zip) · [Release notes](https://github.com/joomtheme/joomla-cookienotice-plugin/releases/tag/v1.1.4)

Version 1.1.4 lowers the settings launcher's default bottom spacing from 72 px to 16 px, adds mobile safe-area spacing, prevents delayed banner reappearance, and improves keyboard focus handling in the preferences dialog. Existing consent choices, plugin settings and CSS offset overrides are preserved.

The maintainer confirmed that the release works on Joomla 6.1.4 and 6.2.0. The published ZIP's SHA-256 was verified against the downloaded release asset.

```text
SHA-256: 381f43fbef8b8427ac53462c28c4813f0ec10e99d9039e59e65099fd5434243d
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

1. Download the installable `plg_system_cookienotice_1.1.4.zip` release asset and install it from Joomla Administrator.
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

## Settings launcher

After a saved choice, the 48 px cookie icon opens the existing preferences dialog. The icon is embedded from [Bootstrap Icons](https://icons.getbootstrap.com/icons/cookie/) (MIT licensed), with no external dependency. **Cookie Settings Accessible Name** retains the existing `launcher_text` parameter and its language fallback; it now supplies the button's `aria-label` instead of visible text. The saved consent format and categories are unchanged.

Select **Bottom left** (default) or **Bottom right** under **Cookie Settings Icon Position**. The default bottom offset is 16 px, plus the device safe-area inset. Templates may adjust horizontal and vertical spacing independently:

```css
:root {
    --jt-cookie-launcher-x: 20px;
    --jt-cookie-launcher-y: 96px;
}
```

The x offset is measured from the selected physical edge, including on RTL pages; the y offset is measured from the bottom. Check the actual accessibility widget and other fixed controls on both desktop and mobile, and increase offsets as needed. The icon remains keyboard focusable and has a visible focus outline; forced-colors mode uses system colors.

## Validation

- Maintainer-confirmed operation on Joomla 6.1.4 and 6.2.0.
- Eleven controlled Chromium browser checks passed, covering launcher positioning at desktop and 320 px widths, CSS offset overrides, RTL positioning, initial dialog focus navigation, delayed banner behavior, consent activation, withdrawal and cookie cleanup.
- PHP and JavaScript syntax checks, release metadata validation, ZIP integrity and deterministic package builds passed.
- JED Checker and the full cross-browser/accessibility checklist were not rerun for 1.1.4.

## Updating

Use Joomla's extension update system or install the 1.1.4 release ZIP over the existing installation. Existing plugin settings and valid category-based consent choices are retained. Clear Joomla, page and CDN caches after updating, and confirm the CSS/JS assets load at version 1.1.4.

The launcher's default bottom spacing is now 16 px plus the device safe-area inset. Existing `--jt-cookie-launcher-x` and `--jt-cookie-launcher-y` overrides still apply, with the corresponding safe-area inset added.

Legacy acknowledgement-only choices from 1.0.x do not count as category-based consent; those visitors will be asked to choose again.

## Compliance scope

This plugin supplies technical consent controls; installing it alone does not make a website legally compliant. The site owner remains responsible for classifying services, preventing every non-essential request before consent, maintaining accurate disclosures, choosing an appropriate consent lifetime and applying national rules. Server-side or third-party cookies that JavaScript cannot access may require service-specific deletion or an additional integration.

## Compatibility

- Designed for Joomla 6.1.x and 6.2.x; confirmed working on Joomla 6.1.4 and 6.2.0 by the maintainer.
- Atum handles administrator configuration; frontend rendering uses the plugin FileLayout and Web Asset Manager, independent of the site template.
- Compatible with a Bootstrap 5 site without loading a second Bootstrap library.
- Vanilla JavaScript; no Bootstrap requirement
- GPL-2.0-or-later


## Cookie cleanup

Withdrawal saves the new consent record before emitting the change event and cleaning accessible cookies in denied categories. The page then reloads. On initialization, a valid saved decision triggers another cleanup of denied categories before allowed content is activated. This second pass addresses accessible cookies that previously loaded services may write during unload. Startup cleanup does not cause a reload loop.

Cleanup still relies on cookie names visible in document.cookie and configured category-specific patterns. Paths along the current URL are attempted with and without trailing slashes. The plugin's own consent cookie is excluded. HttpOnly cookies, other origins, inaccessible paths and other storage partitions remain outside this JavaScript cleanup. This is not a continuous blocker for scripts independently loaded outside consent control; all integrations must respect the saved choice. Avoid overlapping category patterns: a cookie matching a denied category is eligible for deletion even if an allowed category also uses it.

Use one integration method per service: either the category snippet field OR annotated template/module markup. Do not add the same GA loader to both. Google Analytics measurement configuration and loading should belong to the same consent-controlled integration. Removing an already executed script element does not stop its existing listeners; the reload and subsequent cleanup remain necessary.

The consent schema and revision are unchanged in 1.1.4. Existing valid choices are retained. The reset() API continues to remove the consent record and reopen the choice on reload; use the preference controls to withdraw consent and invoke the withdrawal cleanup flow.

For site-specific regression testing, accept Analytics, then withdraw consent and reload. Verify that accessible `_ga` and `_ga_*` cookies are removed, consent remains denied, and allowed categories retain their cookies. Repeat on non-trailing-slash paths and in Firefox, using a single consent-controlled analytics integration.
