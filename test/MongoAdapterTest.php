<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache\Mongo;

use Zend\Cache\Mongo\MongoAdapter;
use Zend\Cache\Mongo\MongoOptions;
use ZendTest\Cache\Storage\Adapter\CommonAdapterTest;

/**
 * @group  Zend_Cache_Mongo
 * @covers Zend\Cache\Mongo\MongoAdapter
 */
class MongoTest extends CommonAdapterTest
{
    /**
     * @var MongoAdapter
     */
    protected $storage;

    /**
     * @var MongoOptions
     */
    protected $options;

    public function setUp()
    {
        $this->options = new MongoOptions([
            'server'     => getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING'),
            'database'   => getenv('TESTS_ZEND_CACHE_MONGO_DATABASE'),
            'collection' => getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION'),
        ]);

        $this->storage = new MongoAdapter();
        $this->storage->setOptions($this->options);
        $this->storage->flush();

        parent::setUp();
    }

    public function tearDown()
    {
        if ($this->storage) {
            $this->storage->flush();
        }

        parent::tearDown();
    }

    public function testSetOptionsNotMongoOptions()
    {
        $this->storage->setOptions([
            'server'     => getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING'),
            'database'   => getenv('TESTS_ZEND_CACHE_MONGO_DATABASE'),
            'collection' => getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION'),
        ]);

        $this->assertInstanceOf(MongoOptions::class, $this->storage->getOptions());
    }
}
