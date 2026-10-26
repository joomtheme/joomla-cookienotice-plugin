# Changelog

## 1.1.4 - 2026-10-27

### Fixed

- Lower the settings launcher to 16 px from the viewport edge, with mobile safe-area spacing and existing CSS overrides retained.
- Keep the delayed banner hidden after a decision or while preferences are open.
- Keep forward and reverse Tab navigation inside preferences when the dialog container initially has focus.

### Compatibility

- Retain Joomla subscriber events, dependency injection, FileLayout and Web Asset Manager integration; no additional Bootstrap runtime.
- Confirmed working on Joomla 6.1.4 and 6.2.0 by the maintainer.
- Existing consent choices, plugin settings and CSS offset overrides are preserved.
