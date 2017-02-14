<?php
/**
 * @link      http://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

class ConfigProvider
{
    /**
     * Return default configuration for zend-cache-mongo.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return default service mappings for zend-cache-mongo.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            // TODO
        ];
    }
}
