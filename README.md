# MageMatch_Email

Magento 2 module providing custom email templates and notifications — overrides core transactional emails (account, password, order, shipment, newsletter) with branded templates, adds account-change and revamped-website-password notifications, and injects order formatted-date formatting into email variables.

## Features

- Branded HTML email templates for account, password, order, shipment and newsletter flows
- `EmailNotification` model — sends account-changed and revamped-website-password emails
- `OrderFormattedDate` observer — adds `d/m/y` formatted date to order/shipment email transport
- `AccountManagementPlugin` — fires account-changed email after password change via API
- `UpdateCustomerAccountPlugin` (GraphQL) — fires account-changed / email-changed notifications after GraphQL account update
- `ProductImage` view model — resolves product image URL (with configurable fallback) for use in email templates

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.1 \|\| ^8.2 \|\| ^8.3 \|\| ^8.4` |
| `magento/framework` | `^103.0` |
| `magento/module-email` | `^101.0` |
| `magento/module-customer` | `^103.0` |
| `magento/module-sales` | `^103.0` |

## Installation

```bash
composer require arjundhi/magento2-email
bin/magento module:enable MageMatch_Email
bin/magento setup:upgrade
bin/magento cache:flush
```

## License

MIT — see [LICENSE](LICENSE).
