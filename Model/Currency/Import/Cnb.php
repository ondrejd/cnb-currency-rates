<?php
declare(strict_types=1);

namespace CnbCurrencyRates\CnbCurrencyRates\Model\Currency\Import;

use Laminas\Http\Request;
use Magento\Directory\Model\Currency\Import\AbstractImport;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Store\Model\ScopeInterface;

class Cnb extends AbstractImport
{
    private const DEFAULT_CNB_DAILY_RATES_URL =
        'https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt';

    private const DEFAULT_REQUEST_TIMEOUT_SECONDS = 30;

    private const XML_PATH_CNB_SOURCE_URL = 'currency/cnb/source_url';

    private const XML_PATH_CNB_TIMEOUT = 'currency/cnb/timeout';

    /**
     * @var LaminasClientFactory
     */
    private $httpClientFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CurrencyFactory $currencyFactory
     * @param LaminasClientFactory $httpClientFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        LaminasClientFactory $httpClientFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($currencyFactory);
        $this->httpClientFactory = $httpClientFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();
        $cnbRatesInCzk = $this->getCnbRatesInCzk();

        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = [];
            }

            foreach ($currencies as $currencyTo) {
                if ($currencyFrom === $currencyTo) {
                    $data[$currencyFrom][$currencyTo] = 1.0;
                    continue;
                }

                if (!isset($cnbRatesInCzk[$currencyFrom])) {
                    $this->_messages[] = __('CNB does not provide rate for currency "%1".', $currencyFrom);
                    $data[$currencyFrom][$currencyTo] = null;
                    continue;
                }

                if (!isset($cnbRatesInCzk[$currencyTo])) {
                    $this->_messages[] = __('CNB does not provide rate for currency "%1".', $currencyTo);
                    $data[$currencyFrom][$currencyTo] = null;
                    continue;
                }

                $data[$currencyFrom][$currencyTo] =
                    $this->_numberFormat($cnbRatesInCzk[$currencyFrom] / $cnbRatesInCzk[$currencyTo]);
            }

            ksort($data[$currencyFrom]);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        return 1;
    }

    /**
     * Returns rates normalized to "1 unit of currency = X CZK".
     *
     * @return array<string, float>
     */
    private function getCnbRatesInCzk(): array
    {
        /** @var LaminasClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setUri($this->getSourceUrl());
        $httpClient->setMethod(Request::METHOD_GET);
        $httpClient->setOptions(['timeout' => $this->getTimeout()]);

        try {
            $response = $httpClient->send();
        } catch (\Throwable $exception) {
            $this->_messages[] = __('Unable to fetch CNB rates: %1', $exception->getMessage());
            return [];
        }

        if (!$response->isSuccess()) {
            $this->_messages[] = __('Unable to fetch CNB rates. HTTP status: %1', $response->getStatusCode());
            return [];
        }

        $body = trim($response->getBody());
        if ($body === '') {
            $this->_messages[] = __('CNB rates response is empty.');
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $body) ?: [];
        if (count($lines) < 3) {
            $this->_messages[] = __('CNB rates response has unexpected format.');
            return [];
        }

        $ratesInCzk = ['CZK' => 1.0];

        foreach (array_slice($lines, 2) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = explode('|', $line);
            if (count($parts) < 5) {
                continue;
            }

            $amount = (int) trim($parts[2]);
            $currencyCode = strtoupper(trim($parts[3]));
            $rateRaw = trim($parts[4]);

            if ($amount <= 0 || $currencyCode === '') {
                continue;
            }

            $rate = (float) str_replace(',', '.', $rateRaw);
            if ($rate <= 0) {
                continue;
            }

            $ratesInCzk[$currencyCode] = $rate / $amount;
        }

        return $ratesInCzk;
    }

    /**
     * @return string
     */
    private function getSourceUrl(): string
    {
        $sourceUrl = (string) $this->scopeConfig->getValue(self::XML_PATH_CNB_SOURCE_URL, ScopeInterface::SCOPE_STORE);

        return $sourceUrl !== '' ? $sourceUrl : self::DEFAULT_CNB_DAILY_RATES_URL;
    }

    /**
     * @return int
     */
    private function getTimeout(): int
    {
        $timeout = (int) $this->scopeConfig->getValue(self::XML_PATH_CNB_TIMEOUT, ScopeInterface::SCOPE_STORE);

        return $timeout > 0 ? $timeout : self::DEFAULT_REQUEST_TIMEOUT_SECONDS;
    }
}
