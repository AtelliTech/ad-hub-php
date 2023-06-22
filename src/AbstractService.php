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
     * Custom error
     *
     * @var CustomError
     */
    protected CustomError $customError;

    /**
     * construct
     *
     * @param mixed $client
     * @param string $apiVersion
     * @return void
     */
    public function __construct(protected $client, protected string $apiVersion)
    {}

    /**
     * Get service name
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get Custom Error
     *
     * @return CustomError
     */
    public function getCustomError(): CustomError
    {
        return $this->customError;
    }

    /**
     * Set Custom Error
     *
     * @param CustomError $customError
     * @return self
     */
    public function setCustomError(CustomError $customError): self
    {
        $this->customError = $customError;
        return $this;
    }

    /**
     * Get client
     *
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get API version
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }
}