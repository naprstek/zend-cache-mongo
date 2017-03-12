<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Cache\Mongo;

use PHPUnit\Framework\TestCase;
use Zend\Cache\Mongo\MongoAdapter;
use Zend\Cache\Mongo\Module;
use Zend\Cache\StorageFactory;

/**
 * @group  Zend_Cache_Mongo
 * @covers Zend\Cache\Mongo\Module
 */
class ModuleTest extends TestCase
{

    public function setUp()
    {
        (new Module())->init();
    }

    public function tearDown()
    {
        StorageFactory::resetAdapterPluginManager();
    }

    public function getCommonPluginNamesProvider()
    {
        return [
            ['mongo'],
            ['Mongo'],
        ];
    }

    /**
     * @dataProvider getCommonPluginNamesProvider
     */
    public function testCommonPluginNameProviders($name)
    {
        $this->assertInstanceOf(MongoAdapter::class, StorageFactory::adapterFactory($name));
    }

    public function testPluginFactoryByClassName()
    {
        $this->assertInstanceOf(MongoAdapter::class, StorageFactory::adapterFactory(MongoAdapter::class));
    }
}
