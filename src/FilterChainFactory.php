<?php

declare (strict_types=1);
namespace Laminas\Filter;

use function assert;
use Laminas\Service_Manager\Factory\Factory_Interface;
use Psr\Container\Container_Interface;
/** @psalm-import-type FilterChainConfiguration from FilterChain */
final class Filter_Chain_Factory implements Factory_Interface
{
    public function __invoke(Container_Interface $container, string $requested_name, ?array $options = null): Filter_Chain
    {
        /**
         * Runtime validation of the chain spec can be done but is not because it would introduce a BC break
         *
         * @see FilterChain::validateSpecification()
         *
         * @psalm-var FilterChainConfiguration $options
         */
        $options ??= [];
        $plugin_manager = $container->get(Filter_Plugin_Manager::class);
        assert($plugin_manager instanceof Filter_Plugin_Manager);
        return new Filter_Chain($plugin_manager, $options);
    }
}