<?php

namespace Zend\Cache\Mongo;

use Zend\Cache\StorageFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

class Module
{
    public function init()
    {
        $adapterPluginManager = StorageFactory::getAdapterPluginManager();
        $adapterPluginManager->setFactory(MongoAdapter::class, InvokableFactory::class);
        $adapterPluginManager->setAlias('mongo', MongoAdapter::class);
        $adapterPluginManager->setAlias('Mongo', MongoAdapter::class);
    }
}
