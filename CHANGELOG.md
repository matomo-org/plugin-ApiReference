## Changelog

6.0.1 - 2026-08-31
- Hardened OpenAPI example generation so that it only ever queries read-only endpoints, and only anonymously
- Hardened generation of the API annotations file so untrusted characters in example responses cannot alter its structure
- Removed the deprecated `ApiReference.shouldAllowLocalRequests` event, which no longer affected generation

6.0.0 - 2026-08-10
- Compatibility with Matomo 6

5.0.5 - 2027-07-06
- Updated symfony/yaml to v7.4.14

5.0.4 - 2026-06-29
- Removed eager fetching of Matomo URL 

5.0.3 - 08/06/2026
- Improved wording of authorization buttons and help text
- Cleaned up styling on input fields

5.0.2 - 25/05/2026
- Updated FAQ

5.0.1 - 25/05/2026
- Added fix for endpoints that don't return valid API responses

5.0.0
- Initial release of ApiReference plugin
