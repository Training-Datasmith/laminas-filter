<?php

declare (strict_types=1);
namespace Laminas\Filter;

use Laminas\Service_Manager\Service_Manager;
/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class Module
{
    /**
     * Return default laminas-filter configuration for laminas-mvc applications.
     *
     * @return array{service_manager: ServiceManagerConfiguration}
     */
    public function get_config(): array
    {
        $provider = new Config_Provider();
        return ['service_manager' => $provider->get_dependency_config()];
    }
}