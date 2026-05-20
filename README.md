# Cnb Currency Rates

Modul pro Magento 2, který přidává službu pro kurzy měn stahované z České národní banky ([odkaz](https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt)).

## Instalace

### Přes CLI

```bash
php bin/magento module:enable CnbCurrencyRates
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### Přes Composer

Aktualizujte `composer.json`:

```json
{
  "require": {
    "cnbcurrencyrates/module-cnb-currency-rates": "dev-main"
  }
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ondrejd/cnb-currency-rates"
    }
  ]
}
```

Poté je třeba modul nainstalovat a povolit:

```bash
composer require cnbcurrencyrates/module-cnb-currency-rates:dev-main
php bin/magento module:enable CnbCurrencyRates
php bin/magento setup:upgrade
php bin/magento cache:flush
```
