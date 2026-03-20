<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function is_array;
use Laminas\Service_Manager\Service_Manager;
use Psr\Container\Container_Interface;
/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class Filter_Plugin_Manager_Factory
{
    public function __invoke(Container_Interface $container): Filter_Plugin_Manager
    {
        // If this is in a laminas-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return new Filter_Plugin_Manager($container);
        }
        // If we do not have a config service, nothing more to do
        if (!$container->has('config')) {
            return new Filter_Plugin_Manager($container);
        }
        $config = $container->get('config');
        // If we do not have filters configuration, nothing more to do
        if (!isset($config['filters']) || !is_array($config['filters'])) {
            return new Filter_Plugin_Manager($container);
        }
        /** @psalm-var ServiceManagerConfiguration $config['filters'] */
        return new Filter_Plugin_Manager($container, $config['filters']);
    }
}