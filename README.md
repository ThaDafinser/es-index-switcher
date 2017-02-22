
# Elasticsearch index switcher

[![Build Status](https://travis-ci.org/ThaDafinser/es-index-switcher.svg)](https://travis-ci.org/ThaDafinser/es-index-switcher)
[![Code Coverage](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/?branch=master)

[![Latest Stable Version](https://poser.pugx.org/thadafinser/es-index-switcher/v/stable)](https://packagist.org/packages/thadafinser/es-index-switcher)
[![Latest Unstable Version](https://poser.pugx.org/thadafinser/es-index-switcher/v/unstable)](https://packagist.org/packages/thadafinser/es-index-switcher) 
[![License](https://poser.pugx.org/thadafinser/es-index-switcher/license)](https://packagist.org/packages/thadafinser/es-index-switcher)
[![Total Downloads](https://poser.pugx.org/thadafinser/es-index-switcher/downloads)](https://packagist.org/packages/thadafinser/es-index-switcher) 

## Usage
```php
$hosts = [
    [
        'host' => '...',
        'port' => '9200',
        'scheme' => 'http',
        'user' => '...',
        'pass' => '...'
    ]
];

$client = ClientBuilder::create()->setHosts($hosts)->build();

$es = new EsIndexSwitcher($client, 'testing', 'test_alias');

/*
 * Create the index
 */
$indexBody = [];
$result = $es->createNewIndex($indexBody);

/*
 * Add your data to index!
 */
$params = [
    'index' => $es->getNewIndexName(),
    'body' => [
        'field1' => 'test'
    ]
];
$response = $this->getClient()->index($params);

/*
 * Update alias + remove old indices
 */
$es->finish();

```
