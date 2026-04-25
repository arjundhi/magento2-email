# Changelog

All notable changes to `arjundhi/magento2-email` are documented here.

## [1.0.0] - 2024-04-16

### Added
- Initial release as `Rameera_Email` with standardized module identity.
- Branded transactional email templates (account, password, order, shipment, newsletter)
- `EmailNotification` model for account-changed and revamped-website-password emails
- `OrderFormattedDate` observer for d/m/y date formatting in email vars
- `AccountManagementPlugin` — post-password-change email notification
- `UpdateCustomerAccountPlugin` — GraphQL account-update email notification
- `ProductImage` view model for email templates
- MIT licence, CI workflow, standard packaging
