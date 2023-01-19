<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

/**
 * Return a valid GitHub API URL from string.
 */

class RexGitApiUrl
{
    /**
     * Construtor.
     */
    public function __construct()
    {
    }

    /**
     * Return a valid GitHub API URL from string.
     */
    public static function getUrl(string $url): string
    {
        $search = [
            'http://',
            '#',
            ' ',
        ];
        $replace = [
            'https://',
            '',
            '',
        ];

        $seturl = mb_strtolower($url);

        $seturl = str_replace($search, $replace, trim($seturl));

        if (false === strpos($seturl, \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_APIURL)) {
            $seturl = \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_APIURL . '/' . ltrim($seturl, '/');
        }

        $seturl = trim(rtrim($seturl, '/'));
        // dump([static::class . '::getUrl()', $url, $seturl]);

        return $seturl;
    }
}
