<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache\Storage\Adapter;

use Zend\Cache\Storage\Adapter\Mongo;
use Zend\Cache\Storage\Adapter\MongoOptions;

/**
 * @group      Zend_Cache
 * @covers Zend\Cache\Storage\Adapter\Mongo<extended>
 */
class MongoTest extends CommonAdapterTest
{
    public function setUp()
    {
        $this->_options = new MongoOptions([
            'server'     => getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING'),
            'database'   => getenv('TESTS_ZEND_CACHE_MONGO_DATABASE'),
            'collection' => getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION'),
        ]);

        $this->_storage = new Mongo();
        $this->_storage->setOptions($this->_options);
        $this->_storage->flush();

        parent::setUp();
    }

    public function tearDown()
    {
        if ($this->_storage) {
            $this->_storage->flush();
        }

        parent::tearDown();
    }

    public function getCommonAdapterNamesProvider()
    {
        return [
            ['mongo'],
            ['Mongo'],
        ];
    }

    public function testSetOptionsNotMongoOptions()
    {
        $this->_storage->setOptions([
            'server'     => getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING'),
            'database'   => getenv('TESTS_ZEND_CACHE_MONGO_DATABASE'),
            'collection' => getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION'),
        ]);

        $this->assertInstanceOf('\Zend\Cache\Storage\Adapter\MongoOptions', $this->_storage->getOptions());
    }
}
