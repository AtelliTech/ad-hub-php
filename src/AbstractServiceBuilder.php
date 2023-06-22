<?php

namespace AtelliTech\AdHub;

/**
 * service ServiceBuilderInterface
 */
abstract class AbstractServiceBuilder
{
    /**
     * create service
     *
     * @param array<string, string> $config
     * @return AbstractService
     */
    abstract public function create(array $config): AbstractService;
}