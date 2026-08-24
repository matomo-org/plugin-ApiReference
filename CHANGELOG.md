## Changelog

5.0.7 - 2026-08-25
- Hardened generation of the API annotations file so untrusted characters in example responses cannot alter its structure

5.0.6 - 2026-08-17
- Hardened OpenAPI example generation so that it only ever queries read-only endpoints, and only anonymously
- Deprecated the `ApiReference.shouldAllowLocalRequests` event, which no longer affects generation and will be removed in the next major version
- Updated nikic/php-parser to v5.8.0 and Vue to 3.5.41

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
