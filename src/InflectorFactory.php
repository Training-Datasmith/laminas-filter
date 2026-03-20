<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function assert;
use Laminas\Service_Manager\Factory\Factory_Interface;
use Psr\Container\Container_Interface;
/** @psalm-import-type Options from Inflector */
final class Inflector_Factory implements Factory_Interface
{
    /** @param array<array-key, mixed> $options */
    public function __invoke(Container_Interface $container, string $requested_name, ?array $options = null): Inflector
    {
        /** @psalm-var Options $options - Forcing this type to avoid unnecessary runtime validation */
        $options ??= [];
        $plugin_manager = $container->get(Filter_Plugin_Manager::class);
        assert($plugin_manager instanceof Filter_Plugin_Manager);
        return new Inflector($plugin_manager, $options);
    }
}