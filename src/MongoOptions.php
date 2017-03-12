<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Mongo;

use Zend\Cache\Storage\Adapter\AdapterOptions;

class MongoOptions extends AdapterOptions
{
    // @codingStandardsIgnoreStart
    /**
     * Prioritized properties ordered by prio to be set first
     * in case a bulk of options sets set at once
     *
     * @var string[]
     */
    protected $__prioritizedProperties__ = ['resource_manager', 'resource_id'];
    // @codingStandardsIgnoreEnd

    /**
     * The namespace separator
     *
     * @var string
     */
    private $namespaceSeparator = ':';

    /**
     * The mongo DB resource manager
     *
     * @var null|MongoResourceManager
     */
    private $resourceManager;

    /**
     * The resource id of the resource manager
     *
     * @var string
     */
    private $resourceId = 'default';

    /**
     * Set namespace separator
     *
     * @param  string $namespaceSeparator
     *
     * @return MongoOptions Provides a fluent interface
     */
    public function setNamespaceSeparator($namespaceSeparator)
    {
        $namespaceSeparator = (string) $namespaceSeparator;

        if ($this->namespaceSeparator !== $namespaceSeparator) {
            $this->triggerOptionEvent('namespace_separator', $namespaceSeparator);

            $this->namespaceSeparator = $namespaceSeparator;
        }

        return $this;
    }

    /**
     * Get namespace separator
     *
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }

    /**
     * Set the mongo resource manager to use
     *
     * @param null|MongoResourceManager $resourceManager
     *
     * @return MongoOptions Provides a fluent interface
     */
    public function setResourceManager(MongoResourceManager $resourceManager = null)
    {
        if ($this->resourceManager !== $resourceManager) {
            $this->triggerOptionEvent('resource_manager', $resourceManager);

            $this->resourceManager = $resourceManager;
        }

        return $this;
    }

    /**
     * Get the mongo resource manager
     *
     * @return MongoResourceManager
     */
    public function getResourceManager()
    {
        return $this->resourceManager ?: $this->resourceManager = new MongoResourceManager();
    }

    /**
     * Get the mongo resource id
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Set the mongo resource id
     *
     * @param string $resourceId
     *
     * @return MongoOptions Provides a fluent interface
     */
    public function setResourceId($resourceId)
    {
        $resourceId = (string) $resourceId;

        if ($this->resourceId !== $resourceId) {
            $this->triggerOptionEvent('resource_id', $resourceId);

            $this->resourceId = $resourceId;
        }

        return $this;
    }

    /**
     * Set the mongo DB server
     *
     * @param string $server
     *
     * @return MongoOptions Provides a fluent interface
     */
    public function setServer($server)
    {
        $this->getResourceManager()->setServer($this->getResourceId(), $server);
        return $this;
    }

    /**
     *
     *
     * @param array $connectionOptions
     *
     * @return MongoOptions Provides a fluent interface
     */
    public function setConnectionOptions(array $connectionOptions)
    {
        $this->getResourceManager()->setConnectionOptions($this->getResourceId(), $connectionOptions);
        return $this;
    }

    /**
     *
     *
     * @param array $driverOptions
     * @return MongoOptions Provides a fluent interface
     */
    public function setDriverOptions(array $driverOptions)
    {
        $this->getResourceManager()->setDriverOptions($this->getResourceId(), $driverOptions);
        return $this;
    }

    /**
     *
     *
     * @param string $database
     *
     * @return MongoOptions Provides a fluent interface
     */
    public function setDatabase($database)
    {
        $this->getResourceManager()->setDatabase($this->getResourceId(), $database);
        return $this;
    }

    /**
     *
     *
     * @param string $collection
     *
     * @return MongoOptions Provides a fluent interface
     */
    public function setCollection($collection)
    {
        $this->getResourceManager()->setCollection($this->getResourceId(), $collection);
        return $this;
    }
}
