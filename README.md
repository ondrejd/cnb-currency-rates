# Cnb Currency Rates

Modul pro Magento 2, který přidává službu pro kurzy měn stahované z České národní banky ([odkaz](https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt)).

## Instalace

### Přes CLI

```bash
php bin/magento module:enable CnbCurrencyRates
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### Přes Composer ze ZIP archívu

- `CnbCurrencyRates.zip`

Aktualizujte `composer.json`:

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

Poté je třeba modul nainstalovat a povolit:

```bash
composer require cnbcurrencyrates/module-cnb-currency-rates:1.0.0
php bin/magento module:enable CnbCurrencyRates
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Použití v administraci

1. `Stores > Currency Rates`.
2. `Import Service`, vyberte `Czech National Bank (CNB)`.
3. Klikněte na `Import`.
4. Kliněte na `Save Currency Rates`.

Službu pro stahování kurzů lze nastavit i na Cron.
