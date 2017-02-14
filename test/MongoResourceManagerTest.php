<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache\Storage\Adapter;

use Zend\Cache\Storage\Adapter\MongoResourceManager;

/**
 * @group      Zend_Cache
 * @covers Zend\Cache\Storage\Adapter\MongoResourceManager
 */
class MongoResourceManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoResourceManager
     */
    protected $object;

    public function setUp()
    {
        $this->object = new MongoResourceManager();
    }

    public function testSetResourceAlreadyCreated()
    {
        $this->assertAttributeEmpty('resources', $this->object);

        $id = 'foo';

        $clientClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';
        $client = new $clientClass(getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING'));
        $resource = $client->selectCollection(
            getenv('TESTS_ZEND_CACHE_MONGO_DATABASE'),
            getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION')
        );

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testSetResourceArray()
    {
        $this->assertAttributeEmpty('resources', $this->object);

        $id     = 'foo';
        $server = 'mongodb://test:1234';

        $this->object->setResource($id, ['server' => $server]);

        $this->assertSame($server, $this->object->getServer($id));
    }

    public function testSetResourceThrowsException()
    {
        $id = 'foo';
        $resource = new \stdClass();

        $this->setExpectedException('Zend\Cache\Exception\InvalidArgumentException');
        $this->object->setResource($id, $resource);
    }

    public function testHasResourceEmpty()
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));
    }

    public function testHasResourceSet()
    {
        $id = 'foo';

        $this->object->setResource($id, ['foo' => 'bar']);

        $this->assertTrue($this->object->hasResource($id));
    }

    public function testGetResourceNotSet()
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));

        $this->setExpectedException('Zend\Cache\Exception\RuntimeException');
        $this->object->getResource($id);
    }

    public function testGetResourceInitialized()
    {
        $id = 'foo';

        $clientClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';
        $client = new $clientClass(getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING'));
        $resource = $client->selectCollection(
            getenv('TESTS_ZEND_CACHE_MONGO_DATABASE'),
            getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION')
        );

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testGetResourceNewResource()
    {
        $id                = 'foo';
        $server            = getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING');
        $connectionOptions = ['connectTimeoutMS' => 5];
        $database          = getenv('TESTS_ZEND_CACHE_MONGO_DATABASE');
        $collection        = getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION');

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->assertInstanceOf('\MongoCollection', $this->object->getResource($id));
    }

    public function testGetResourceUnknownServerThrowsException()
    {
        $id                = 'foo';
        $server            = 'mongodb://unknown.unknown';
        $connectionOptions = ['connectTimeoutMS' => 5];
        $database          = getenv('TESTS_ZEND_CACHE_MONGO_DATABASE');
        $collection        = getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION');

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->setExpectedException('Zend\Cache\Exception\RuntimeException');
        $this->object->getResource($id);
    }

    public function testGetSetCollection()
    {
        $resourceId     = 'testResource';
        $collectionName = 'testCollection';

        $this->object->setCollection($resourceId, $collectionName);
        $this->assertSame($collectionName, $this->object->getCollection($resourceId));
    }

    public function testGetSetConnectionOptions()
    {
        $resourceId        = 'testResource';
        $connectionOptions = ['test1' => 'option1', 'test2' => 'option2'];

        $this->object->setConnectionOptions($resourceId, $connectionOptions);
        $this->assertSame($connectionOptions, $this->object->getConnectionOptions($resourceId));
    }

    public function testGetSetServer()
    {
        $resourceId = 'testResource';
        $server     = 'testServer';

        $this->object->setServer($resourceId, $server);
        $this->assertSame($server, $this->object->getServer($resourceId));
    }

    public function testGetSetDriverOptions()
    {
        $resourceId    = 'testResource';
        $driverOptions = ['test1' => 'option1', 'test2' => 'option2'];

        $this->object->setDriverOptions($resourceId, $driverOptions);
        $this->assertSame($driverOptions, $this->object->getDriverOptions($resourceId));
    }

    public function testGetSetDatabase()
    {
        $resourceId = 'testResource';
        $database   = 'testDatabase';

        $this->object->setDatabase($resourceId, $database);
        $this->assertSame($database, $this->object->getDatabase($resourceId));
    }
}
