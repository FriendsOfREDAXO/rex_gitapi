<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_dir;
use rex_file;
use rex_path;

/**
 * Handle GitHub API Cache files.
 */

class RexGitApiCache
{
    public const REXGITAPI_CACHE_EXT = '.cache';

    /** @var array<int, string> */
    protected static $noCacheUrls = [
        \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_APIURL . '/rate_limit',
        \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_APIURL . '/zen',
        \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_APIURL . '/octocat',
        \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_APIURL . '/user',
    ];

    /**
     * Construtor.
     */
    public function __construct()
    {
    }

    /**
     * Create cache directory.
     */
    public static function createDirectory(): bool
    {
        if (false === rex_dir::isWritable(self::getPath())) {
            return rex_dir::create(self::getPath(), true);
        }

        return true;
    }

    /**
     * Get cache path.
     */
    public static function getPath(): string
    {
        return rex_path::addonCache(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);
    }

    /**
     * Get cache files.
     * @return array<int, string>|false
     */
    public static function getFiles()
    {
        $files = glob(self::getPath() . '*' . self::REXGITAPI_CACHE_EXT);
        if (false !== $files) {
            natsort($files);
        }

        return $files;
    }

    /**
     * Delete cache.
     */
    public static function deleteCache(): bool
    {
        $cachepath = self::getPath();
        if ('' !== $cachepath && is_dir($cachepath)) {
            return rex_dir::delete($cachepath, false);
        }

        return false;
    }

    /**
     * Check cache exists.
     */
    public static function existCache(string $url): bool
    {
        $addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

        $lifetime = $addon->getConfig('cachelifetime', 0);
        settype($lifetime, 'int');

        if (0 === $lifetime || in_array($url, self::$noCacheUrls, true)) {
            return false;
        }

        $filename = self::getPath() . self::urlToFileName($url) . self::REXGITAPI_CACHE_EXT;
        if (true === file_exists($filename)) {
            $filetime = (int) filemtime($filename);
            $endtime = $filetime + ($lifetime * 60);
            if (time() < $endtime) {
                return true;
            }
        }

        return false;
    }

    /**
     * Write cache.
     */
    public static function writeCache(string $url, string $content): bool
    {
        $addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

        $lifetime = $addon->getConfig('cachelifetime', 0);
        settype($lifetime, 'int');

        if (0 === $lifetime || in_array($url, self::$noCacheUrls, true)) {
            return false;
        }
        if (true === self::existCache($url)) {
            return false;
        }

        return rex_file::put(self::getPath() . self::urlToFileName($url) . self::REXGITAPI_CACHE_EXT, $content);
    }

    /**
     * Get cache.
     */
    public static function getCache(string $url): string
    {
        $filename = self::getPath() . self::urlToFileName($url) . self::REXGITAPI_CACHE_EXT;

        $content = (string) rex_file::get($filename);
        $content = str_replace('\n', "\n", $content);
        $content = stripslashes($content);
        $content = ltrim(rtrim($content, '"'), '"');

        return $content;
    }

    /**
     * Url to filename.
     */
    public static function urlToFileName(string $url): string
    {
        $search = [
            'https://',
            '.',
            '/',
            '?',
            '&',
        ];
        $replace = [
            '',
            '_',
            '-',
            '#',
            '+',
        ];

        $string = mb_strtolower($url);
        $string = str_replace($search, $replace, $string);

        return $string;
    }

    /**
     * Filename to url.
     */
    public static function fileNameToUrl(string $filename): string
    {
        $search = [
            '_',
            '-',
            '#',
            '+',
        ];
        $replace = [
            '.',
            '/',
            '?',
            '&',
        ];

        $string = mb_strtolower($filename);
        $string = str_replace($search, $replace, $string);

        return 'https://' . $string;
    }
}
