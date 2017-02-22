
# Elasticsearch index switcher

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