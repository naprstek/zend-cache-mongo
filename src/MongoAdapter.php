<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Mongo;

use MongoDB\Collection as MongoCollection;
use MongoDB\BSON\UTCDateTime as MongoDate;
use MongoDB\Model\BSONDocument;
use MongoDB\Exception\Exception as MongoException;
use stdClass;
use Zend\Cache\Exception;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\FlushableInterface;

class MongoAdapter extends AbstractAdapter implements FlushableInterface
{
    /**
     * Has this instance be initialized
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * the mongo resource manager
     *
     * @var null|MongoResourceManager
     */
    private $resourceManager;

    /**
     * The mongo resource id
     *
     * @var null|string
     */
    private $resourceId;

    /**
     * The namespace prefix
     *
     * @var string
     */
    private $namespacePrefix = '';

    /**
     * {@inheritDoc}
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $initialized = & $this->initialized;

        $this->getEventManager()->attach(
            'option',
            function () use (& $initialized) {
                $initialized = false;
            }
        );
    }

    /**
     * get mongo resource
     *
     * @return MongoCollection
     */
    private function getMongoCollection()
    {
        if (! $this->initialized) {
            $options = $this->getOptions();

            $this->resourceManager = $options->getResourceManager();
            $this->resourceId      = $options->getResourceId();
            $namespace             = $options->getNamespace();
            $this->namespacePrefix = ($namespace === '' ? '' : $namespace . $options->getNamespaceSeparator());
            $this->initialized     = true;
        }

        return $this->resourceManager->getResource($this->resourceId);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions($options)
    {
        return parent::setOptions($options instanceof MongoOptions ? $options : new MongoOptions($options));
    }

    /**
     * Get options.
     *
     * @return MongoOptions
     * @see    setOptions()
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $result  = $this->fetchFromCollection($normalizedKey);
        $success = false;

        if (null === $result) {
            return null;
        }

        if (isset($result['expires'])) {
            if (! $result['expires'] instanceof MongoDate) {
                throw new Exception\RuntimeException(sprintf(
                    "The found item _id '%s' for key '%s' is not a valid cache item"
                    . ": the field 'expired' isn't an instance of MongoDate, '%s' found instead",
                    (string) $result['_id'],
                    $this->namespacePrefix . $normalizedKey,
                    is_object($result['expires']) ? get_class($result['expires']) : gettype($result['expires'])
                ));
            }

            if ($result['expires'] < (new MongoDate())) {
                $this->internalRemoveItem($normalizedKey);
                return null;
            }
        }

        if (! array_key_exists('value', $result)) {
            throw new Exception\RuntimeException(sprintf(
                "The found item _id '%s' for key '%s' is not a valid cache item: missing the field 'value'",
                (string) $result['_id'],
                $this->namespacePrefix . $normalizedKey
            ));
        }

        $success = true;
        return $casToken = $result['value'];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $mongo     = $this->getMongoCollection();
        $key       = $this->namespacePrefix . $normalizedKey;
        $ttl       = $this->getOptions()->getTtl();
        $cacheItem = [
            'key' => $key,
            'value' => $value,
        ];

        if ($ttl > 0) {
            $d = round((microtime(true) + $ttl) * 1000);
            $cacheItem['expires'] = new MongoDate($d);
        }

        try {
            $mongo->deleteOne(['key' => $key]);
            $result = $mongo->insertOne($cacheItem);
        } catch (MongoException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return null !== $result && $result->isAcknowledged();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        try {
            $result = $this->getMongoCollection()->deleteOne(['key' => $this->namespacePrefix . $normalizedKey]);
        } catch (MongoException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return null !== $result && ($result->getDeletedCount() > 0);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $result = $this->getMongoCollection()->drop();
        return ((float) 1) === ($result instanceof BSONDocument ? $result->getArrayCopy()['ok'] : (float) $result->ok);
    }

    /**
     * {@inheritDoc}
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities) {
            return $this->capabilities;
        }

        return $this->capabilities = new Capabilities(
            $this,
            $this->capabilityMarker = new stdClass(),
            [
                'supportedDatatypes' => [
                    'NULL'     => true,
                    'boolean'  => true,
                    'integer'  => true,
                    'double'   => true,
                    'string'   => true,
                    'array'    => false,
                    'object'   => false,
                    'resource' => false,
                ],
                'supportedMetadata'  => [
                    '_id',
                ],
                'minTtl'             => 1,
                'staticTtl'          => true,
                'maxKeyLength'       => 255,
                'namespaceIsPrefix'  => true,
            ]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        $result = $this->fetchFromCollection($normalizedKey);

        return null !== $result ? ['_id' => $result['_id']] : false;
    }

    /**
     * Return raw records from MongoCollection
     *
     * @param string $normalizedKey
     *
     * @return array|null
     *
     * @throws Exception\RuntimeException
     */
    private function fetchFromCollection(& $normalizedKey)
    {
        try {
            return $this->getMongoCollection()->findOne(['key' => $this->namespacePrefix . $normalizedKey]);
        } catch (MongoException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
