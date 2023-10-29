<?php

namespace AtelliTech\AdHub;

/**
 * This is an abstract service, every service class must extend this class.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
abstract class AbstractService
{
    /**
     * construct
     *
     * @param mixed $client
     * @return void
     */
    public function __construct(protected $client)
    {}

    /**
     * Get service name
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get client
     *
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }
}