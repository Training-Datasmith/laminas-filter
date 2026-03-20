<?php

declare (strict_types=1);
namespace Laminas\Filter;

use Laminas\Service_Manager\Service_Manager;
/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class Config_Provider
{
    /**
     * Return configuration for this component.
     *
     * @return array{dependencies: ServiceManagerConfiguration}
     */
    public function __invoke(): array
    {
        return ['dependencies' => $this->get_dependency_config()];
    }
    /**
     * Return dependency mappings for this component.
     *
     * @return ServiceManagerConfiguration
     */
    public function get_dependency_config(): array
    {
        return ['aliases' => ['FilterManager' => Filter_Plugin_Manager::class], 'factories' => [Filter_Plugin_Manager::class => Filter_Plugin_Manager_Factory::class]];
    }
}