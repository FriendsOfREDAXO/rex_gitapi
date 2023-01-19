<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

/**
 * RexGitApiCurlVO - Variable Object.
 */

class RexGitApiCurlVO
{
    /** @var int */
    private $errno = 0;

    /** @var string */
    private $error = '';

    /** @var string */
    private $httpCode = '';

    /** @var string */
    private $result = '';

    private $curlHandle = ''; /** @phpstan-ignore-line */

    /**
     * Construtor.
     */
    public function __construct(int $errno, string $error, string $httpCode, string $result, $curlHandle = null) /** @phpstan-ignore-line */
    {
        $this->errno = $errno;
        $this->error = $error;
        $this->httpCode = $httpCode;
        $this->result = $result;
        $this->curlHandle = $curlHandle;
    }

    /**
     * Get errno.
     * @api
     */
    public function getErrno(): int
    {
        return $this->errno;
    }

    /**
     * Get error message.
     * @api
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Get httpd code.
     * @api
     */
    public function getHttpCode(): string
    {
        return $this->httpCode;
    }

    /**
     * Get result.
     * @api
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * Get curlHandle.
     * @api
     */
    public function getCurlHandle() /** @phpstan-ignore-line */
    {
        return $this->curlHandle;
    }
}
