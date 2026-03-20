<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function assert;
use Laminas\Service_Manager\Factory\Factory_Interface;
use Psr\Container\Container_Interface;
/** @psalm-import-type ChainSpec from ImmutableFilterChain */
final class Immutable_Filter_Chain_Factory implements Factory_Interface
{
    public function __invoke(Container_Interface $container, string $requested_name, ?array $options = null): Immutable_Filter_Chain
    {
        /**
         * It's not worth attempting runtime validation of the specification shape
         * @psalm-var ChainSpec $options
         */
        $options ??= [];
        $plugin_manager = $container->get(Filter_Plugin_Manager::class);
        assert($plugin_manager instanceof Filter_Plugin_Manager);
        return Immutable_Filter_Chain::from_array($options, $plugin_manager);
    }
}