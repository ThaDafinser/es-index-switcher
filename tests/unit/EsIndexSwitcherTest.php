<?php
namespace EsIndexSwitcherTest;

use PHPUnit\Framework\TestCase;
use EsIndexSwitcher\EsIndexSwitcher;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Elasticsearch\Namespaces\CatNamespace;
use ReflectionMethod;

/**
 * @covers EsIndexSwitcher
 */
final class EsIndexSwitcherTest extends TestCase
{
    public function testConstruct()
    {
        $client = ClientBuilder::create()->build();
        
        $es = new EsIndexSwitcher($client, 'some-alias', 'some-prefix');
        
        $this->assertInstanceOf(EsIndexSwitcher::class, $es);
        $this->assertInstanceOf(Client::class, $es->getClient());
        
        $this->assertEquals('some-alias', $es->getAlias());
        $this->assertEquals('some-prefix', $es->getIndexPrefix());
        $this->assertRegExp('/^[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}/', $es->getIndexSuffix());
        $this->assertRegExp('/^some-prefix_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}/', $es->getNewIndexName());
    }

    public function testCustomSuffix()
    {
        $client = ClientBuilder::create()->build();
        
        $es = new EsIndexSwitcher($client, 'some-alias', 'some-prefix', 'my-suffix');
        
        $this->assertInstanceOf(EsIndexSwitcher::class, $es);
        $this->assertInstanceOf(Client::class, $es->getClient());
        
        $this->assertEquals('my-suffix', $es->getIndexSuffix());
        $this->assertEquals('some-prefix_my-suffix', $es->getNewIndexName());
    }

    public function testCreateNewIndex()
    {
        $indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock->method('create')->willReturn([
            'acknowledged' => true,
            'shards_acknowledged' => true
        ]);
        
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('indices')->willReturn($indicesMock);
        
        $es = new EsIndexSwitcher($clientMock, 'some-alias', 'some-prefix');
        
        $result = $es->createNewIndex();
        
        $this->assertEquals([
            'acknowledged' => true,
            'shards_acknowledged' => true
        ], $result);
    }

    public function testCreateNewIndexWithCustomParameter()
    {
        $indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock->method('create')->willReturn([
            'acknowledged' => true,
            'shards_acknowledged' => true
        ]);
        
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('indices')->willReturn($indicesMock);
        
        $es = new EsIndexSwitcher($clientMock, 'some-alias', 'some-prefix');
        
        $result = $es->createNewIndex([
            'settings' => [
                'number_of_shards' => 2,
                'number_of_replicas' => 1
            ]
        ]);
        
        $this->assertEquals([
            'acknowledged' => true,
            'shards_acknowledged' => true
        ], $result);
    }

    public function testUpdateAlias()
    {
        $indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock->method('updateAliases')->willReturn([
            'acknowledged' => true
        ]);
        
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('indices')->willReturn($indicesMock);
        
        $es = new EsIndexSwitcher($clientMock, 'some-alias', 'some-prefix');
        
        $method = new ReflectionMethod(EsIndexSwitcher::class, 'updateAlias');
        $method->setAccessible(true);
        
        $this->assertEquals([
            'acknowledged' => true
        ], $method->invoke($es));
    }

    public function testFetchCurrentIndices()
    {
        $catMock = $this->getMockBuilder(CatNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catMock->method('indices')->willReturn([
            [
                'health' => 'yellow',
                'status' => 'open',
                'index' => 'some-prefix_2017-01-01_05-02-30',
                'uuid' => 'X18PeedNQzuQAskJ-5TEBA',
                'pri' => '5',
                'rep' => '1',
                'docs.count' => '0',
                'docs.deleted' => '0',
                'store.size' => '260b',
                'pri.store.size' => '260b'
            ]
        ]);
        
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('cat')->willReturn($catMock);
        
        $es = new EsIndexSwitcher($clientMock, 'some-alias', 'some-prefix');
        
        $method = new ReflectionMethod(EsIndexSwitcher::class, 'fetchCurrentIndices');
        $method->setAccessible(true);
        
        $this->assertEquals([
            [
                'health' => 'yellow',
                'status' => 'open',
                'index' => 'some-prefix_2017-01-01_05-02-30',
                'uuid' => 'X18PeedNQzuQAskJ-5TEBA',
                'pri' => '5',
                'rep' => '1',
                'docs.count' => '0',
                'docs.deleted' => '0',
                'store.size' => '260b',
                'pri.store.size' => '260b'
            ]
        ], $method->invoke($es));
    }

    public function testRemoveOldIndices()
    {
        $currentIndices = [
            [
                'health' => 'yellow',
                'status' => 'open',
                'index' => 'some-prefix_2017-01-01_05-02-30',
                'uuid' => 'X18PeedNQzuQAskJ-5TEBA',
                'pri' => '5',
                'rep' => '1',
                'docs.count' => '0',
                'docs.deleted' => '0',
                'store.size' => '260b',
                'pri.store.size' => '260b'
            ],
            [
                'health' => 'yellow',
                'status' => 'open',
                'index' => 'some-prefix_2017-01-02_05-02-30',
                'uuid' => 'X18PeedNQzuQAskJ-5TEBA',
                'pri' => '5',
                'rep' => '1',
                'docs.count' => '0',
                'docs.deleted' => '0',
                'store.size' => '260b',
                'pri.store.size' => '260b'
            ]
        ];
        
        $indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock->method('delete')->willReturn([
            'acknowledged' => true
        ], [
            'acknowledged' => true
        ]);
        
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('indices')->willReturn($indicesMock);
        
        $es = new EsIndexSwitcher($clientMock, 'some-alias', 'some-prefix');
        
        $method = new ReflectionMethod(EsIndexSwitcher::class, 'removeOldIndices');
        $method->setAccessible(true);
        
        $this->assertEquals([
            'some-prefix_2017-01-01_05-02-30' => [
                'acknowledged' => true
            ],
            'some-prefix_2017-01-02_05-02-30' => [
                'acknowledged' => true
            ]
        ], $method->invoke($es, $currentIndices));
    }

    public function testFinish()
    {
        $indicesMock = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $indicesMock->method('updateAliases')->willReturn([
            'acknowledged' => true
        ]);
        $indicesMock->method('delete')->willReturn([
            'acknowledged' => true
        ], [
            'acknowledged' => true
        ]);
        
        $catMock = $this->getMockBuilder(CatNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catMock->method('indices')->willReturn([
            [
                'health' => 'yellow',
                'status' => 'open',
                'index' => 'some-prefix_2017-01-01_05-02-30',
                'uuid' => 'X18PeedNQzuQAskJ-5TEBA',
                'pri' => '5',
                'rep' => '1',
                'docs.count' => '0',
                'docs.deleted' => '0',
                'store.size' => '260b',
                'pri.store.size' => '260b'
            ]
        ]);
        
        $clientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('indices')->willReturn($indicesMock);
        $clientMock->method('cat')->willReturn($catMock);
        $clientMock->method('delete')->willReturn($indicesMock);
        
        $es = new EsIndexSwitcher($clientMock, 'some-alias', 'some-prefix');
        
        $this->assertNull($es->finish());
    }
}
