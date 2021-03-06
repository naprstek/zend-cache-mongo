<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Mongo;

use MongoDB\Client as MongoClient;
use MongoDB\Collection as MongoCollection;
use MongoDB\Exception\Exception as MongoException;
use Zend\Cache\Exception;

class MongoResourceManager
{
    /**
     * Registered resources
     *
     * @var array[]
     */
    private $resources = [];

    /**
     * Check if a resource exists
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasResource($id)
    {
        return isset($this->resources[$id]);
    }

    /**
     * Set a resource
     *
     * @param string $id
     * @param array|MongoCollection $resource
     *
     * @return MongoResourceManager Provides a fluent interface
     *
     * @throws Exception\RuntimeException
     */
    public function setResource($id, $resource)
    {
        if ($resource instanceof MongoCollection) {
            $this->resources[$id] = [
                'db'                  => $resource->getDatabaseName(),
                'collection'          => $resource->getCollectionName(),
                'collection_instance' => $resource,
            ];
            return $this;
        }

        if (! is_array($resource)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or MongoCollection; received %s',
                __METHOD__,
                (is_object($resource) ? get_class($resource) : gettype($resource))
            ));
        }

        $this->resources[$id] = $resource;
        return $this;
    }

    /**
     * Instantiate and return the MongoCollection resource
     *
     * @param string $id
     * @return MongoCollection
     * @throws Exception\RuntimeException
     */
    public function getResource($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = $this->resources[$id];
        if (! isset($resource['collection_instance'])) {
            try {
                $resource['client_instance'] = new MongoClient(
                    isset($resource['server']) ? $resource['server'] : null,
                    isset($resource['connection_options']) ? $resource['connection_options'] : [],
                    isset($resource['driver_options']) ? $resource['driver_options'] : []
                );
                $collection = $resource['client_instance']->selectCollection(
                    isset($resource['db']) ? $resource['db'] : 'zend',
                    isset($resource['collection']) ? $resource['collection'] : 'cache'
                );
                $collection->createIndex(['key' => 1]);

                $this->resources[$id]['collection_instance'] = $collection;
            } catch (MongoException $e) {
                throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $this->resources[$id]['collection_instance'];
    }

    public function setServer($id, $server)
    {
        $this->resources[$id]['server'] = (string)$server;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    public function getServer($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['server']) ? $this->resources[$id]['server'] : null;
    }

    public function setConnectionOptions($id, array $connectionOptions)
    {
        $this->resources[$id]['connection_options'] = $connectionOptions;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    public function getConnectionOptions($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['connection_options'])
            ? $this->resources[$id]['connection_options']
            : [];
    }

    public function setDriverOptions($id, array $driverOptions)
    {
        $this->resources[$id]['driver_options'] = $driverOptions;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    public function getDriverOptions($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['driver_options']) ? $this->resources[$id]['driver_options'] : [];
    }

    public function setDatabase($id, $database)
    {
        $this->resources[$id]['db'] = (string)$database;

        unset($this->resources[$id]['collection_instance']);
    }

    public function getDatabase($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['db']) ? $this->resources[$id]['db'] : '';
    }

    public function setCollection($id, $collection)
    {
        $this->resources[$id]['collection'] = (string)$collection;

        unset($this->resources[$id]['collection_instance']);
    }

    public function getCollection($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['collection']) ? $this->resources[$id]['collection'] : '';
    }
}
