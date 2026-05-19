# CnbCurrencyRates

Magento 2 module adding a new currency import service based on Czech National Bank (CNB) daily rates.

## What it does

- Registers a new import service `cnb` in Magento currency import services.
- Downloads and parses CNB daily rates from:
  - https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt
- Converts CNB values to Magento currency-rate matrix format.
- Makes the service available in Admin currency rates import UI.

## Module structure

- `registration.php`
- `etc/module.xml`
- `etc/di.xml`
- `etc/config.xml`
- `etc/adminhtml/system.xml`
- `Model/Currency/Import/Cnb.php`

## Configuration

Admin path:

- `Stores > Configuration > Currency Setup > Czech National Bank (CNB)`

Fields:

- `Rates Source URL`
- `Connection Timeout in Seconds`

Default values are provided in `etc/config.xml`.

## Enable module

Run in Docker PHP container:

```bash
docker compose exec phpfpm php bin/magento module:enable CnbCurrencyRates
docker compose exec phpfpm php bin/magento setup:upgrade
docker compose exec phpfpm php bin/magento cache:flush
```

## Install via Composer

This module is Composer-ready as package:

- `cnbcurrencyrates/module-cnb-currency-rates`

For other Magento projects, add repository and require the package.

Example for VCS repository:

```bash
composer config repositories.cnbcurrencyrates-cnb-currency-rates vcs <git-repository-url>
composer require cnbcurrencyrates/module-cnb-currency-rates
php bin/magento module:enable CnbCurrencyRates
php bin/magento setup:upgrade
php bin/magento cache:flush
```

Example for local path repository:

```bash
composer config repositories.cnbcurrencyrates-cnb-currency-rates path ./app/code/CnbCurrencyRates/CnbCurrencyRates
composer require cnbcurrencyrates/module-cnb-currency-rates:*
php bin/magento module:enable CnbCurrencyRates
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Install from ZIP via Composer

Composer can also install this module directly from a ZIP file.

Given ZIP file in project root:

- `CnbCurrencyRates.zip`

Add repository definition in target Magento project `composer.json`:

```json
{
  "repositories": {
    "cnbcurrencyrates-zip": {
      "type": "package",
      "package": {
        "name": "cnbcurrencyrates/module-cnb-currency-rates",
        "version": "1.0.0",
        "type": "magento2-module",
        "dist": {
          "url": "file:///absolute/path/to/CnbCurrencyRates.zip",
          "type": "zip"
        },
        "autoload": {
          "files": ["registration.php"],
          "psr-4": {
            "CnbCurrencyRates\\CnbCurrencyRates\\": ""
          }
        }
      }
    }
  }
}
```

Then install and enable:

```bash
composer require cnbcurrencyrates/module-cnb-currency-rates:1.0.0
php bin/magento module:enable CnbCurrencyRates
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Use in Admin UI

1. Open `Stores > Currency Rates`.
2. In `Import Service`, select `Czech National Bank (CNB)`.
3. Click `Import`.
4. Click `Save Currency Rates`.

## Notes

- CNB rates are published against CZK and module computes cross-rates for Magento currencies.
- If a currency is not present in CNB feed, the rate is returned as empty and warning messages are collected.
