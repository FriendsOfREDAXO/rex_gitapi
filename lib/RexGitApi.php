<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_request;

/**
 * Class for accessing the GitHub API (ReadOnly).
 *
 * In general, ALL GitHub API pages can be accessed via this class (public or authorized URL's). The whole thing ReadOnly!
 *
 * The results are returned as a PHP array. However, the original JSON result of the GitHub API can also be queried.
 * The entry `rexgiturl` with the GitHub API URL is appended to the PHP result array.
 * If the data is loaded from the cache, the entry `rexgitcache` is also appended.
 *
 * For simplification, there are a few special methods, some of which return a simplified result.
 *
 * INFO:
 *
 * - There is a limit of 60 unauthenticated accesses per hour.
 *
 * - Authenticated access with token is limited to 5,000 per hour.
 *   See: Personal access token (classic) https://github.com/settings/tokens
 *
 * -----------------------------------------------------------------------------
 *
 * Documentation https://docs.github.com/rest
 *
 * GitHub API https://api.github.com/
 *
 * - Query limits
 *   https://api.github.com/rate_limit (not limited)
 *
 * - Oranisation
 *   https://api.github.com/orgs/{ORG}
 *   https://api.github.com/orgs/{ORG}/repos
 *
 * - User
 *   https://api.github.com/users/{USER}
 *   https://api.github.com/users/{USER}/repos
 *
 * - Repository
 *   https://api.github.com/repos/{ORG|USER}/{REPO}
 *
 * -----------------------------------------------------------------------------
 *
 * HOW TO USE
 *
 * INFO:
 *
 * `https://api.github.com/` can be omitted from the {URL}
 * i.e. {URL} `https://api.github.com/rate_limit` can be passed as `rate_limit`
 *
 * The preferred way to use `RexGitApi` is to create a new instance (due to error handling).
 * {TOKEN} is optional. If no token is specified, the token from the addon settings is used.
 *
 *      $gitapi = new \FriendsOfRedaxo\RexGitApi\RexGitApi({TOKEN});
 *      $gitapi->setUrl('{URL}');
 *      $gitapi->setDebug(true);
 *      $gitapi->setCache(true);
 *      $gitapi->execute();
 *      $gitresult = $gitapi->get();
 *
 *      $gitapi = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory({TOKEN});
 *      $gitapi->execute('{URL}');
 *      if (true === $gitapi->hasError()) {
 *          echo $gitapi->getMessage();
 *          echo '<br>Requested URL: ' . $gitapi->getUrl();
 *          echo '<br>GitHub-Token: ' . $gitapi->getToken();
 *      } else {
 *          $gitresult = $gitapi->get();
 *      }
 *
 * - Get result as array (default)
 *
 *      $gitresult = $gitapi->get();
 *
 * - Get result as JSON
 *
 *      $jsonresult = $gitapi->get(true);
 *
 * RexGitApui static calls
 *
 *      $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory({TOKEN})->setDebug(true)->setUrl('{URL}')->execute()->get();
 *
 *      $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory({TOKEN})->setDebug(true)->execute('{URL}')->get();
 *
 *      $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory({TOKEN})->execute('{URL}')->get();
 *
 * -----------------------------------------------------------------------------
 *
 * Special Methods
 *
 * Notice: Set `$json` to true to get JSON results
 *
 * // https://api.github.com/rate_limit (not limited)
 * $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getLimits($json);
 *
 * // https://api.github.com/orgs/{ORG}
 * $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getOrgInfo('{ORG}', $json);
 *
 * // https://api.github.com/orgs/{ORG}/repos
 * $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getOrgRepoList('{ORG}', $json);
 *
 * // https://api.github.com/users/{USER}
 * $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getUserInfo('{USER}', $json);
 *
 * // https://api.github.com/users/{USER}/repos
 * $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getUserRepoList('{USER}', $json);
 *
 * // https://api.github.com/repos/{ORG|USER}/{REPO}
 * $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getRepoInfo('{ORG|USER}', '{REPO}', $json);
 *
 * // https://api.github.com/{URL}
 * $gitresult = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getPagedContent('{URL}', $json);
 */

class RexGitApi
{
    public const REXGITAPI_ADDON = 'rex_gitapi';

    public const REXGITAPI_API_VERSION = '2022-11-28'; /** @see https://docs.github.com/en/rest/overview/api-versions */

    /**
     * GitHub urls.
     */
    public const REXGITAPI_APIURL = 'https://api.github.com';
    public const REXGITAPI_ORGS_URL = self::REXGITAPI_APIURL . '/orgs';
    public const REXGITAPI_USERS_URL = self::REXGITAPI_APIURL . '/users';
    public const REXGITAPI_REPOS_URL = self::REXGITAPI_APIURL . '/repos';
    public const REXGITAPI_LIMITS_URL = self::REXGITAPI_APIURL . '/rate_limit'; /** @see https://docs.github.com/en/rest/rate-limit */

    /** @var string */
    protected $token = '';

    /** @var string */
    protected $apiVersion = '';

    /** @var string */
    protected $useragent = '';

    /** @var string */
    protected $url = '';

    /** @var string */
    protected $org = '';

    /** @var string */
    protected $user = '';

    /** @var string */
    protected $repo = '';

    /** @var string */
    protected $jsonResponse = '';

    /** @var array<int|string, mixed>|string|false */
    protected $response;

    /** @var bool */
    protected $debug = false;

    /** @var bool */
    protected $cache = true;

    /** @var bool */
    protected $hasError = false;

    /** @var string */
    protected $message = '';

    /**
     * Construtor.
     */
    public function __construct(string $token = '', string $apiVersion = '')
    {
        if ('' !== $token) {
            $this->token = $token;
        }

        if ('' !== $apiVersion) {
            $this->apiVersion = $apiVersion;
        }
    }

    /**
     * Reset values.
     */
    public function reset(): self
    {
        $this->token = '';
        $this->apiVersion = '';
        $this->url = '';
        $this->org = '';
        $this->user = '';
        $this->repo = '';
        $this->jsonResponse = '';
        $this->response = false;
        $this->debug = false;
        $this->cache = true;
        $this->hasError = false;
        $this->message = '';

        return $this;
    }

    /**
     * Set API token.
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get API token.
     *  api.
     */
    public function getToken(): string
    {
        $addon = rex_addon::get(self::REXGITAPI_ADDON);
        $token = strval($addon->getConfig('gittoken', ''));
        if ('' !== $this->token) {
            $token = $this->token;
        }

        return $token;
    }

    /**
     * Set API Version.
     */
    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * Get API Version.
     */
    public function getApiVersion(): string
    {
        $apiVersion = $this->apiVersion;
        if ('' === $this->apiVersion) {
            $apiVersion = self::REXGITAPI_API_VERSION;
        }

        return $apiVersion;
    }

    /**
     * Set useragent.
     * @api
     */
    public function setUserAgent(string $useragent): self
    {
        $this->useragent = $useragent;

        return $this;
    }

    /**
     * Get useragent.
     * @api
     */
    public function getUserAgent(): self
    {
        $useragent = '' === rex_request::server('HTTP_USER_AGENT', 'string', '') ? 'PHP' : strval(rex_request::server('HTTP_USER_AGENT'));
        if ('' !== $this->useragent) {
            $useragent = $this->useragent;
        }

        return $this;
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
     * Get url.
     * @api
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set org.
     * @api
     */
    public function setOrg(string $org): self
    {
        $this->org = $org;

        return $this;
    }

    /**
     * Get org.
     * @api
     */
    public function getOrg(): string
    {
        return $this->org;
    }

    /**
     * Set user.
     * @api
     */
    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     * @api
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Set repo.
     * @api
     */
    public function setRepo(string $repo): self
    {
        $this->repo = $repo;

        return $this;
    }

    /**
     * Get repo.
     * @api
     */
    public function getRepo(): string
    {
        return $this->repo;
    }

    /**
     * Set debug.
     * @api
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Set cache.
     * @api
     */
    public function setCache(bool $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Set jsonResponse.
     */
    protected function setJsonResponse(string $response): self
    {
        $this->jsonResponse = $response;

        return $this;
    }

    /**
     * Get jsonResponse.
     * @api
     */
    protected function getJsonResponse(): string
    {
        return $this->jsonResponse;
    }

    /**
     * Set response.
     * @param array<int|string, mixed> $response
     */
    protected function setResponse(array $response): self
    {
        $response['rexgiturl'] = $this->getUrl();
        $this->response = $response;

        if (true === $this->debug) {
            dump(['DEBUG RESPONSE ' . static::class, $this->jsonResponse, $this->response]);
        }

        return $this;
    }

    /**
     * Get response.
     * @api
     * @return array<int|string, mixed>|string|false
     */
    protected function getResponse(bool $json = false)
    {
        if (true === $json) {
            return $this->getJsonResponse();
        }
        return $this->response;
    }

    /**
     * Set Error-Message.
     */
    protected function setError(int $errNo, string $error): bool
    {
        $this->setMessage('Curl-Error: ' . $errNo . ' - ' . $error);

        return false;
    }

    /**
     * Set message.
     * @api
     */
    protected function setMessage(string $message): bool
    {
        $this->message = $message;
        $this->hasError = true;

        return false;
    }

    /**
     * Get message.
     * @api
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get hasError.
     * @api
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * Get response.
     * @api
     * @return array<int|string, mixed>|string|false
     */
    public function get(bool $json = false)
    {
        if (true === $this->cache && false === $this->hasError) {
            \FriendsOfRedaxo\RexGitApi\RexGitApiCache::writeCache($this->getUrl(), $this->getJsonResponse());
        }
        return $this->getResponse($json);
    }

    /**
     * Get Cached response.
     */
    protected function getCache(): bool
    {
        $jsonresponse = RexGitApiCache::getCache($this->getUrl());
        $this->setJsonResponse($jsonresponse);
        $gitresult = (array) json_decode($jsonresponse, true);
        if (0 === count($gitresult) && strlen($this->getJsonResponse()) > 0) {
            $gitresult['rexgitjson'] = $this->getJsonResponse();
        }
        $gitresult['rexgiturl'] = $this->getUrl();
        $gitresult['rexgitcache'] = true;
        $this->setResponse($gitresult);
        return true;
    }

    /**
     * Creates a RexGitApi instance.
     * @return self Returns a RexGitApi instance
     */
    public static function factory(string $token = '', string $apiVersion = ''): self
    {
        return new self($token, $apiVersion);
    }

    /**
     * Execute GitHub API.
     * @api
     */
    public function execute(string $url = ''): self
    {
        if ('' !== $url) {
            $this->setUrl($url);
        }
        $this->executeGithubApi();

        return $this;
    }

    /**
     * Execute GitHub API using curl.
     */
    protected function executeGithubApi(): bool
    {
        $this->hasError = false;

        if (true === $this->cache && true === RexGitApiCache::existCache($this->getUrl())) {
            return $this->getCache();
        }

        $curlVo = \FriendsOfRedaxo\RexGitApi\RexGitApiCurl::executeGithubApi($this->getUrl(), $this->getToken());

        $curlErrno = $curlVo->getErrno();
        $curlError = $curlVo->getError();
        $httpCode = $curlVo->getHttpCode();
        $gitresult = $curlVo->getResult();

        if (0 !== $curlErrno || '200' !== $httpCode) {
            return $this->setError($curlErrno, $curlError);
        }

        $this->setJsonResponse($gitresult);
        $gitresult = (array) json_decode($gitresult, true);
        if (0 === count($gitresult) && strlen($this->getJsonResponse()) > 0) {
            $gitresult['rexgitjson'] = $this->getJsonResponse();
        }
        $this->setResponse($gitresult);

        return true;
    }

    /**
     * Check GitHub API is available.
     */
    public static function apiIsAvailable(): RexGitApiCurlVO
    {
        return \FriendsOfRedaxo\RexGitApi\RexGitApiCurl::apiIsAvailable();
    }

    /**
     * Get GitHub header.
     * @api
     */
    public static function getGithubHeader(string $url, string $token = ''): string
    {
        $headerurl = \FriendsOfRedaxo\RexGitApi\RexGitApiUrl::getUrl($url);

        return \FriendsOfRedaxo\RexGitApi\RexGitApiCurl::getGithubHeader($headerurl, $token);
    }

    /**
     * Get last curl log.
     * @return string|null
     * @api
     */
    public static function getLastCurlLog()
    {
        return \FriendsOfRedaxo\RexGitApi\RexGitApiCurl::getLastCurlLog();
    }

    /**
     * Get rate limits - @see https://api.github.com/rate_limit (not limited).
     * @return array<int|string, mixed>|string|false
     * @api
     */
    public function getLimits(bool $json = false)
    {
        $this->setUrl(self::REXGITAPI_LIMITS_URL);
        $this->executeGithubApi();

        return $this->get($json);
    }

    /**
     * Get organisation info.
     *
     * @api
     * @return array<string, string>|mixed
     */
    public function getOrgInfo(string $org = '', bool $json = false)
    {
        if ('' === $org) {
            $org = $this->getOrg();
        }
        $this->setUrl(self::REXGITAPI_ORGS_URL . $org);
        $this->executeGithubApi();

        return $this->get($json);
    }

    /** TODO
     * Get organisation repos.
     *
     * @api
     * @return array<string, string>|mixed
     */
    public function getOrgRepoList(string $org = '', bool $json = false)
    {
        if ('' === $org) {
            $org = $this->getOrg();
        }
        $this->getPagedContent(self::REXGITAPI_ORGS_URL . $org . '/repos');

        return $this->get($json);
    }

    /**
     * Get user info.
     *
     * @api
     * @return array<string, string>|mixed
     */
    public function getUserInfo(string $user = '', bool $json = false)
    {
        if ('' === $user) {
            $user = $this->getUser();
        }
        $this->setUrl(self::REXGITAPI_USERS_URL . $user);
        $this->executeGithubApi();

        return $this->get($json);
    }

    /** TODO
     * Get user repos.
     *
     * @api
     * @return array<string, string>|mixed
     */
    public function getUserRepoList(string $user, bool $json = false)
    {
        if ('' === $user) {
            $user = $this->getUser();
        }
        $this->getPagedContent(self::REXGITAPI_USERS_URL . $user . '/repos');

        return $this->get($json);
    }

    /**
     * Get repo info.
     * @api
     * @return array<string, string>|mixed|false
     */
    public function getRepoInfo(string $user = '', string $repo = '', bool $json = false)
    {
        if ('' === $user) {
            $user = $this->getUser();
        }
        if ('' === $user) {
            $user = $this->getOrg();
        }
        if ('' === $repo) {
            $repo = $this->getRepo();
        }
        $this->setUrl(self::REXGITAPI_REPOS_URL . $user . '/' . $repo);
        $this->executeGithubApi();

        return $this->get($json);
    }

    /**
     * Get last page from header.
     * @api
     */
    public function getLastPageNumber(string $url): int
    {
        if (strpos($url, '?') > 0) {
            $headerurl = $url . '&per_page=100';
        } else {
            $headerurl = $url . '?per_page=100';
        }

        $header = self::getGithubHeader($headerurl);
        if (strpos($header, 'link:') > 0 || strpos($header, 'Link:') > 0) {
            preg_match('/page=(\d+)>; rel="last"/', $header, $matches);
            if (isset($matches[0]) && isset($matches[1])) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    /** TODO
     * Get paged content.
     * @api
     * @return array<string, string>|mixed|false
     */
    public function getPagedContent(string $url, bool $json = false)
    {
        $saveurl = $url;
        $jsonresult = [];

        $lastpage = $this->getLastPageNumber($url);
        dump($lastpage);
        if (0 === $lastpage || $lastpage < 2) {
            $this->setUrl($url);
            $this->executeGithubApi();
            return $this->get($json);
        }

        return $this->get($json);

        /*$page = 1;
        $this->setUrl($saveurl . '?page=' . $page);
        $this->executeGithubApi();
        if (true === $this->hasError()) {
            return false;
        }

        if ('[' !== mb_substr($this->jsonResponse, 0, 1)) {
            $this->setMessage(self::REXGITAPI_MSG_NO_PAGED_CONTENT . $saveurl);
            return false;
        }
        $jsonresult[] = rtrim(ltrim($this->get(true), '['), ']');

        $stopwhile = false;
        do {
            $page = $page + 1;
            // dump($saveurl . '?page=' . $page);
            $this->setUrl($saveurl . '?page=' . $page);
            $this->executeGithubApi();
            $stopwhile = $this->hasError();
            if ($page > 13) {
                $this->setMessage('possible loop');
                $stopwhile = true;
            }
            if (false === $stopwhile) {
                $jsonresult[] = rtrim(ltrim($this->get(true), '['), ']');
            }
        } while (false === $stopwhile);

        dump($jsonresult);

        $this->setJsonResponse(implode('', $jsonresult));

        $gitresult = (array) json_decode((string) $this->get(true), true);
        dump($gitresult);
        $gitresult['rexgiturl'] = $saveurl;
        $this->setResponse($gitresult);

        $this->hasError = false;
        $this->message = '';
        return $this->get($json);*/
    }
}
