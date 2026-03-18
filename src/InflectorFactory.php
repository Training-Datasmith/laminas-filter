<?php

declare(strict_types=1);

namespace Laminas\Filter;

use function assert;

use Laminas\ServiceManager\Factory\FactoryInterface;

use Psr\Container\ContainerInterface;

/** @psalm-import-type Options from Inflector */
final class InflectorFactory implements FactoryInterface
{
    /** @param array<array-key, mixed> $options */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): Inflector {
        /** @psalm-var Options $options - Forcing this type to avoid unnecessary runtime validation */
        $options ??= [];
        $pluginManager = $container->get(FilterPluginManager::class);
        assert($pluginManager instanceof FilterPluginManager);

        return new Inflector($pluginManager, $options);
    }
}
