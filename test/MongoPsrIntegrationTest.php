<?php
/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache\Mongo;

use Cache\IntegrationTests\CachePoolTest;
use Zend\Cache\Psr\CacheItemPoolAdapter;
use Zend\Cache\Mongo\MongoAdapter;
use Zend\Cache\Exception;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * @group  Zend_Cache_Mongo
 * @covers Zend\Cache\Mongo\MongoAdapter
 */
class MongoPsrIntegrationTest extends CachePoolTest
{
    /**
     * Backup default timezone
     * @var string
     */
    private $tz;

    /**
     * @var MongoAdapter
     */
    private $storage;

    protected function setUp()
    {
        // set non-UTC timezone
        $this->tz = date_default_timezone_get();
        date_default_timezone_set('America/Vancouver');

        parent::setUp();
    }

    protected function tearDown()
    {
        date_default_timezone_set($this->tz);

        if ($this->storage) {
            $this->storage->flush();
        }

        parent::tearDown();
    }

    public function createCachePool()
    {
        try {
            $storage = new MongoAdapter([
                'server'     => getenv('TESTS_ZEND_CACHE_MONGO_CONNECTSTRING'),
                'database'   => getenv('TESTS_ZEND_CACHE_MONGO_DATABASE'),
                'collection' => getenv('TESTS_ZEND_CACHE_MONGO_COLLECTION'),
            ]);
            return new CacheItemPoolAdapter($storage);
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (ServiceNotCreatedException $e) {
            if ($e->getPrevious() instanceof Exception\ExtensionNotLoadedException) {
                $this->markTestSkipped($e->getMessage());
            }
            throw $e;
        }
    }
}
