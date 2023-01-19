<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_file;

/**
 * RexGitApi CURL handling.
 */

class RexGitApiCurl
{
    /** @api */
    public const REXGITAPI_HEADER_API_VERSION = 'X-GitHub-Api-Version:' . \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_API_VERSION;

    /** @api */
    public const REXGITAPI_HEADER_ACCEPT = 'Accept: application/vnd.github+json';

    /** @api */
    public const REXGITAPI_CURLOPT_TIMEOUT = 5;

    /** @api */
    public const REXGITAPI_CURL_LOG = 'RexGitApi-curl.log';

    protected static $curlHandle; /** @phpstan-ignore-line */

    /** @var string */
    protected $url = '';

    /** @var bool */
    protected $hasError = false;

    /** @var string */
    protected $message = '';

    /**
     * Construtor.
     */
    public function __construct()
    {
    }

    /**
     * Creates a RexGitApiCurl instance.
     * @api
     * @return self Returns a RexGitApi instance
     */
    public static function factory(): self
    {
        return new self();
    }

    /**
     * Set url.
     * @api
     */
    public function setUrl(string $url): self
    {
        $this->url = \FriendsOfRedaxo\RexGitApi\RexGitApiUrl::getUrl($url);

        return $this;
    }

    /**
     * Get last curl log.
     * @return string|null
     */
    public static function getLastCurlLog()
    {
        return rex_file::get(RexGitApiCache::getPath() . self::REXGITAPI_CURL_LOG);
    }

    /**
     * Get header.
     */
    public static function getGithubHeader(string $url, string $token = ''): string
    {
        $addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);
        $headerurl = (new RexGitApi($token))->setUrl($url)->getUrl();

        $ch = curl_init();
        if (0 === curl_errno($ch)) {
            RexGitApiCache::createDirectory();

            curl_setopt($ch, CURLOPT_URL, $headerurl); /** @phpstan-ignore-line */
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::REXGITAPI_CURLOPT_TIMEOUT);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            $log = fopen(RexGitApiCache::getPath() . self::REXGITAPI_CURL_LOG, 'w');
            if (false !== $log) {
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_STDERR, $log);
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, (new self())->getCurlHeader($token));

            $response = curl_exec($ch);
            $lastUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            dump([static::class, (new self())->getCurlHeader($token), $response, $httpCode, $lastUrl]);

            if (200 === $httpCode) {
                return (string) $response;
            }
        }

        return '';
    }

    /**
     * Get header.
     * @return array<int, string>
     */
    protected function getCurlHeader(string $token = ''): array
    {
        $addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

        $header = [];
        $header[] = self::REXGITAPI_HEADER_ACCEPT;
        $header[] = self::REXGITAPI_HEADER_API_VERSION;
        // $header[] = 'Connection: keep-alive';
        // $header[] = 'Keep-Alive: 300';

        $usetoken = '';
        if ('' !== $addon->getConfig('gittoken', '')) {
            $token = $addon->getConfig('gittoken');
        }
        if ('' !== $token) {
            $usetoken = $token;
        }
        if ('' !== $usetoken) {
            $header[] = 'Authorization: token ' . $usetoken;
        }

        return $header;
    }

    /**
     * Check GitHub API is available.
     */
    public static function executeGithubApi(string $url, string $token = ''): RexGitApiCurlVO
    {
        self::$curlHandle = curl_init();
        if (0 === curl_errno(self::$curlHandle)) {
            \FriendsOfRedaxo\RexGitApi\RexGitApiCache::createDirectory();

            $header = (new self())->getCurlHeader('');

            $options = [
                CURLOPT_URL => $url,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FAILONERROR => true,
                CURLOPT_USERAGENT => 'PHP',
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_TIMEOUT => self::REXGITAPI_CURLOPT_TIMEOUT,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_ENCODING => '',
            ];
            $logoptions = self::getLogOptions();

            $curloptions = $options + $logoptions;
            curl_setopt_array(self::$curlHandle, $curloptions);

            $response = curl_exec(self::$curlHandle);
            // dump($logoptions[CURLOPT_STDERR]);
            if (isset($logoptions[CURLOPT_STDERR]) && is_resource($logoptions[CURLOPT_STDERR])) {
                fclose($logoptions[CURLOPT_STDERR]);
            }

            $httpCode = (string) curl_getinfo(self::$curlHandle, CURLINFO_HTTP_CODE);
            // dump([static::class . '::apiIsAvailable()', $httpCode, $response]);
            // dump(self::$curlHandle);
            return new RexGitApiCurlVO(curl_errno(self::$curlHandle), curl_error(self::$curlHandle), $httpCode, (string) $response, self::$curlHandle);
        }

        return new RexGitApiCurlVO(curl_errno(self::$curlHandle), curl_error(self::$curlHandle), '', '', self::$curlHandle);
    }

    /**
     * Check GitHub API is available.
     */
    public static function apiIsAvailable(): RexGitApiCurlVO
    {
        self::$curlHandle = curl_init();
        if (0 === curl_errno(self::$curlHandle)) {
            \FriendsOfRedaxo\RexGitApi\RexGitApiCache::createDirectory();

            $header = (new self())->getCurlHeader('');

            $options = [
                CURLOPT_URL => \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_LIMITS_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'PHP',
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_TIMEOUT => self::REXGITAPI_CURLOPT_TIMEOUT,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_ENCODING => '',
            ];
            $logoptions = self::getLogOptions();

            $curloptions = $options + $logoptions;
            curl_setopt_array(self::$curlHandle, $curloptions);

            $response = curl_exec(self::$curlHandle);
            // dump($logoptions[CURLOPT_STDERR]);
            if (isset($logoptions[CURLOPT_STDERR]) && is_resource($logoptions[CURLOPT_STDERR])) {
                fclose($logoptions[CURLOPT_STDERR]);
            }

            $httpCode = (string) curl_getinfo(self::$curlHandle, CURLINFO_HTTP_CODE);
            // dump([static::class . '::apiIsAvailable()', $httpCode, $response]);
            // dump(self::$curlHandle);
            return new RexGitApiCurlVO(curl_errno(self::$curlHandle), curl_error(self::$curlHandle), $httpCode, (string) $response, self::$curlHandle);
        }

        return new RexGitApiCurlVO(curl_errno(self::$curlHandle), curl_error(self::$curlHandle), '', '', self::$curlHandle);
    }

    /**
     * Get curl options for logging.
     * @return array<int, bool|resource>
     */
    protected static function getLogOptions(): array
    {
        $logoptions = [];

        $log = @fopen(RexGitApiCache::getPath() . self::REXGITAPI_CURL_LOG, 'w');
        if (false !== $log) {
            $logoptions = [
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR => $log,
            ];
        }

        return $logoptions;
    }
}
