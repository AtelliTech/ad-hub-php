<?php

namespace AtelliTech\Ads;

/**
 * This is an abstract service, every service class must extend this class.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
abstract class AbstractService
{
    /**
     * construct.
     *
     * @param mixed $client
     * @return void
     */
    public function __construct(protected mixed $client) {}

    /**
     * Get client.
     *
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get service name.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * create service.
     *
     * @param array<string, mixed> $config
     * @return self
     */
    abstract public static function create(array $config): self;
}
