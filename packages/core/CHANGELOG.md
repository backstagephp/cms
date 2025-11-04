# Changelog

All notable changes to `backstage` will be documented in this file.

## v0.0.12 - 2025-02-14

- Changed debounce to onBlur
- Form actions with mail, template and s3 file upload

## v0.0.11 - 2025-02-14

- Repeater now uses slugs instead of name as key.

## v0.0.10 - 2025-02-14

-   Fixed unique validation for non-public content.
-   Reorder children content within the content edit page.
-   Content that is non-public now doesn't have a url.

## v0.0.9 - 2025-02-09

-   Added ulid to blocks table.
-   Removed configurable fields and moved it to a separate package (Fields).
-   Media custom field is moved to the Media package.
-   Uploadcare field is moved to the Uploadcare Field package.

## v0.0.8 - 2025-01-24

-   Components and views are now automaticly loaded for blocks.

## v0.0.7 - 2025-01-21

-   $content->field(...) can now return relations.
-   <x-form slug="..." /> will not render if the form is not found.
-   (Temporary) removed the name_field in content form and used content.name instead.

## v0.0.6 - 2025-01-17

-   Improved color dropdown for Toggle field.

## v0.0.5 - 2025-01-17

-   Added configurable Repeater field.

## v0.0.3 - 2025-01-17

-   Added option to add custom configurable fields. See [Field Documentation](docs/02-fields.md)

## v0.0.2 - 2024-12-31

-   Added option to select parent for content. See [Content Documentation](docs/01-content.md)
-   Removed Scope for languages in Domain settings and language overview
-   Changed selectablePlacholder default value to true

## v0.0.1 - 2024-12-31

-   initial release
-   added content url matching based on content path, site path, domain and language path. See [Content url Documentation](docs/04-urls.md)
