# Changelog

## 1.1.1 - 2026-08-27

### Added

- Language-specific privacy/cookie policy URL mappings for multilingual Joomla sites.
- Exact Joomla language-tag matching with optional base-language fallback.

### Changed

- The existing privacy/cookie policy URL is now used as a backward-compatible fallback when no language-specific mapping matches.
- Updated Web Asset Manager metadata to 1.1.1.

### Security and privacy

- Language-specific policy URLs use the same HTTP/HTTPS and relative-URL validation as the global policy URL. Invalid mappings fall back safely.

## 1.1.0 - 2026-08-26

### Added

- Equal first-layer accept and reject actions.
- Category preferences for necessary, preferences, analytics and marketing services.
- Consent settings launcher and withdrawal flow.
- Structured consent record with revision and timestamp.
- Opt-in category snippet injection.
- `data-cookie-category` activation for scripts, iframes and images.
- Configurable cookie cleanup patterns when consent is withdrawn.
- Public JavaScript API and consent lifecycle events.
- German and French language files.
- Keyboard focus management, focus trapping, RTL switches and reduced-motion support.

### Changed

- Moved banner markup from the extension class to a Joomla layout.
- Replaced the placeholder namespace with the Joomtheme namespace.
- Made runtime default text language-driven.
- Always render the lightweight controller so accepted-user page caches cannot suppress the banner for new visitors.
- Updated Web Asset Manager metadata to 1.1.0.

### Security and privacy

- Non-essential categories default to denied.
- Unsafe privacy-policy URL schemes remain blocked.
- Cookie names and consent revisions are validated.
- Control-character URL validation avoids hexadecimal notation that triggers a JAMSS false positive.
- Consent withdrawal reloads the page after cleaning configured first-party cookies.

## 1.0.21 - 2026-06-01

- Cleaned up the Support & Contact backend note.

## 1.0.20

- Added JavaScript-side cookie detection.
- Hardened the privacy-policy URL.
- Added visible keyboard focus styles.
- Documented the notice-only legal scope.
