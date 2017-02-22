<?php
namespace EsIndexSwitcher;

use Elasticsearch\Client;
use DateTime;

class EsIndexSwitcher
{

    /**
     *
     * @var Client
     */
    private $client;

    /**
     *
     * @var string
     */
    private $alias;

    /**
     *
     * @var string
     */
    private $indexPrefix;

    /**
     *
     * @var string
     */
    private $indexSuffix;

    public function __construct(Client $client, string $alias, string $indexPrefix, string $indexSuffix = null)
    {
        $this->client = $client;
        
        $this->alias = $alias;
        
        if ($indexSuffix === null) {
            $indexSuffix = new DateTime();
            $indexSuffix = $indexSuffix->format('Y-m-d_H-i-s');
        }
        
        $this->indexPrefix = $indexPrefix;
        $this->indexSuffix = $indexSuffix;
    }

    /**
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     *
     * @return string
     */
    public function getIndexPrefix()
    {
        return $this->indexPrefix;
    }

    /**
     *
     * @return string
     */
    public function getIndexSuffix()
    {
        return $this->indexSuffix;
    }

    /**
     *
     * @return string
     */
    public function getNewIndexName()
    {
        return $this->getIndexPrefix() . '_' . $this->getIndexSuffix();
    }

    /**
     *
     * @param array $indexBody
     * @return array
     */
    public function createNewIndex(array $indexBody = [])
    {
        $client = $this->getClient();
        
        $params = [
            'index' => $this->getNewIndexName(),
            'body' => $indexBody
        ];
        return $client->indices()->create($params);
    }

    /**
     *
     * @return void
     */
    public function finish()
    {
        /*
         * Step 2) Update now the alias
         */
        $response = $this->updateAlias();
        
        /*
         * Step 3) list all indices with the given prefix
         */
        $indices = $this->fetchCurrentIndices();
        
        /*
         * Step 3) Remove old indices
         */
        $response = $this->removeOldIndices($indices);
    }

    /**
     *
     * @return array
     */
    private function updateAlias()
    {
        $client = $this->getClient();
        
        $params = [
            'body' => [
                'actions' => [
                    [
                        'add' => [
                            'index' => $this->getNewIndexName(),
                            'alias' => $this->getAlias()
                        ]
                    ]
                ]
            ]
        ];
        
        return $client->indices()->updateAliases($params);
    }

    /**
     *
     * @return array
     */
    private function fetchCurrentIndices()
    {
        $client = $this->getClient();
        
        return $client->cat()->indices([
            'index' => $this->getIndexPrefix() . '*'
        ]);
    }

    /**
     *
     * @param array $indices
     * @return array
     */
    private function removeOldIndices(array $indices)
    {
        $client = $this->getClient();
        
        $response = [];
        foreach ($indices as $index) {
            // do not delete the just new created index
            if ($index['index'] === $this->getNewIndexName()) {
                continue;
            }
            
            // but all other
            $response[$index['index']] = $client->indices()->delete([
                'index' => $index['index']
            ]);
        }
        
        return $response;
    }
}
