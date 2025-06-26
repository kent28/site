# PKO Site

This repository contains a simple PHP based website for a private server. It uses Microsoft SQL Server for account and game databases as configured in `includes/config.php`.

## Donation Currency

Donation currency is stored in the `account_login` table of the account database. The value is kept in the `donat` column. New accounts created via the site automatically set this field to `0`.


