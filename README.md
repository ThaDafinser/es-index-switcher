
# Elasticsearch index switcher

[![Build Status](https://travis-ci.org/ThaDafinser/es-index-switcher.svg)](https://travis-ci.org/ThaDafinser/es-index-switcher)
[![Code Coverage](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ThaDafinser/es-index-switcher/?branch=master)

[![Latest Stable Version](https://poser.pugx.org/thadafinser/es-index-switcher/v/stable)](https://packagist.org/packages/thadafinser/es-index-switcher)
[![Latest Unstable Version](https://poser.pugx.org/thadafinser/es-index-switcher/v/unstable)](https://packagist.org/packages/thadafinser/es-index-switcher) 
[![License](https://poser.pugx.org/thadafinser/es-index-switcher/license)](https://packagist.org/packages/thadafinser/es-index-switcher)
[![Total Downloads](https://poser.pugx.org/thadafinser/es-index-switcher/downloads)](https://packagist.org/packages/thadafinser/es-index-switcher) 

If you use Elasticsearch for a end user search, you never want to interrupt your service - even when you "reindex" your data from source.

This small scripts solves this issue. Your end users will always be able to search and you can reindex in parallel your updated data.

How does it work?
It's quiet simple! You just need to search over an alias which points to the current full index.
If you want to reindex your data, this script will change the alias when the indexing has finished and the user will search over the new data.

## Minimal example

### Index your data
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

$es = new EsIndexSwitcher($client, 'test_alias', 'testing');

/*
 * Create the index itself
 */
$result = $es->createNewIndex();

/*
 * Add your documents to the index!
 */
$params = [
    'index' => $es->getNewIndexName(),
    'type' => 'my_document',
    
    'body' => [
        'field1' => 'test'
    ]
];
$response = $client->index($params);

/*
 * Create/update alias and remove all old indices
 */
$es->finish();
```

### Search

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

$es = new EsIndexSwitcher($client, 'test_alias', 'testing');

/*
 * Add more documents to the old index (by using the alias)
 */
$params = [
    'index' => $es->getAlias(),
    'type' => 'my_document',
    'body' => [
    ]
];

$response = $client->search($params);

var_dump($response);

```

## Update document on the current used index

Maybe you don't want to create a new index on every small change.

Just add your document over the alias

```php
$client = ClientBuilder::create()->setHosts($hosts)->build();

$es = new EsIndexSwitcher($client, 'test_alias', 'testing');

/*
 * Add more documents to the old index (by using the alias)
 */
$params = [
    'index' => $es->getAlias(),
    'type' => 'my_document',
    
    'body' => [
        'field1' => 'test2'
    ]
];
$response = $client->index($params);

var_dump($response);
```
