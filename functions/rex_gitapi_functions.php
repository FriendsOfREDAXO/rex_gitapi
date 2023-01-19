<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

// use function count;

use rex_addon;
use rex_file;
use rex_fragment;
use rex_url;
use rex_view;

// Prevent @api in classes
$dummy = \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ORGS_URL;
$dummy = \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_USERS_URL;
$dummy = \FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_REPOS_URL;
\FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->reset();
\FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->setToken('');
\FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->setApiVersion('');
\FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->getApiVersion();

\FriendsOfRedaxo\RexGitApi\RexGitApiCache::urlToFileName('x');


/**
 * Check GitHub API is available.
 */
function rex_gitapi_check_api(bool $showOkStatus): bool
{
    if (true === rex_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON . '-ApiIsAvailable', 'bool', false)) {
        return true;
    }

    $addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

    if (0 === \FriendsOfRedaxo\RexGitApi\RexGitApi::apiIsAvailable()->getErrno()) {
        if (true === $showOkStatus) {
            echo '<h4 class="alert-success" style="padding:10px 20px"><i class="fa fa-thumbs-up"></i> ' . $addon->i18n('rex_gitapi_api_available') . '</h4>';
        }
        rex_set_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON . '-ApiIsAvailable', true);
        return true;
    }

    echo '<h4 class="alert-danger" style="padding:10px 20px"><i class="fa fa-thumbs-down"></i> ' . $addon->i18n('rex_gitapi_api_not_available') . '</h4>';
    dump(\FriendsOfRedaxo\RexGitApi\RexGitApi::getLastCurlLog());
    rex_set_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON . '-ApiIsAvailable', false);
    return false;
}

/**
 * Output limits.
 */
function rex_gitapi_output_limits(): void
{
    $addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

    if (false === rex_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON . '-ApiIsAvailable', 'bool', false)) {
        return;
    }

    $gitapi = new \FriendsOfRedaxo\RexGitApi\RexGitApi();
    $limits = $gitapi->getLimits();

    if (true === $gitapi->hasError()) {
        echo rex_view::error('<em class="fa fa-warning"></em> <strong>RexGitApi Error:</strong> '
            . $gitapi->getMessage()
            . '<br>Requested URL: ' . $gitapi->getUrl() . '<br>GitHub-Token: ' . $gitapi->getToken()
        );
        return;
    }

    if (isset($limits['rate'])) {
        /** @var array<string, string> $rate */
        $rate = $limits['rate'];
        $items = [];

        if (0 === $rate['remaining']) {
            $items[] = ['label' => rex_view::warning($addon->i18n('rex_gitapi_apiauth_limit_exceeded', $rate['limit'], date('d.m.Y - H:i:s', (int) $rate['reset']))), 'value' => ''];
        }
        $items[] = ['label' => $addon->i18n('rex_gitapi_limits_limit_limit'), 'value' => number_format((float) $rate['limit'], 0, ',', '.')];
        $items[] = ['label' => $addon->i18n('rex_gitapi_limits_limit_used'), 'value' => number_format((float) $rate['used'], 0, ',', '.')];
        $items[] = ['label' => $addon->i18n('rex_gitapi_limits_limit_remainig'), 'value' => number_format((float) $rate['remaining'], 0, ',', '.')];
        $items[] = ['label' => $addon->i18n('rex_gitapi_limits_limit_reset'), 'value' => date('d.m.Y - H:i:s', (int) $rate['reset'])];
        $items[] = ['label' => $addon->i18n('rex_gitapi_limits_limit_request'), 'value' => $limits['rexgiturl']];

        $fragment = new rex_fragment();
        $fragment->setVar('items', $items);
        $output = $fragment->parse('limits-table.php');

        $class = 'default';
        $notoken = '';
        if ('' === $gitapi->getToken()) {
            $class = 'warning';
            $notoken = ' <small class="text-muted">' . $addon->i18n('rex_gitapi_limits_notoken') . '</small>';
        }
        $fragment = new rex_fragment();
        $fragment->setVar('class', $class, false);
        $fragment->setVar('title', $addon->i18n('rex_gitapi_limits_title') . $notoken, false);
        $fragment->setVar('body', $output, false);
        echo $fragment->parse('core/page/section.php');
    }
}

/**
 * Output cache files.
 */
function rex_gitapi_output_cache(): void
{
    $addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

    $items = [];
    $buttons = '';

    $cachefiles = \FriendsOfRedaxo\RexGitApi\RexGitApiCache::getFiles();

    if (false !== $cachefiles && count($cachefiles) > 0) {
        $lifetime = intval($addon->getConfig('cachelifetime', ''));
        foreach ($cachefiles as $file) {
            $filetime = (int) filemtime($file);
            $endtime = $filetime + ($lifetime * 60);
            $class = time() < $endtime ? 'rex-online' : 'rex-offline';

            $url = \FriendsOfRedaxo\RexGitApi\RexGitApiCache::fileNameToUrl(basename($file, \FriendsOfRedaxo\RexGitApi\RexGitApiCache::REXGITAPI_CACHE_EXT));
            $filename = basename($file, \FriendsOfRedaxo\RexGitApi\RexGitApiCache::REXGITAPI_CACHE_EXT);
            $size = rex_file::formattedSize($file);

            $items[] = ['file' => $filename, 'size' => $size, 'url' => $url, 'created' => date('d.m.Y H:i:s', $filetime), 'lifetime' => date('d.m.Y H:i:s', $endtime), 'class' => $class];
        }

        $formElements = [];
        $n = [];
        $n['field'] = '<button class="btn btn-delete" type="submit" name="del_btn" data-confirm="'
            . $addon->i18n('rex_gitapi_cache_confirm_delete') . '?">' .  $addon->i18n('rex_gitapi_cache_btn_delete_cache') . '</button>';
        $formElements[] = $n;
        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');
    } else {
        $items[] = ['file' => $addon->i18n('rex_gitapi_cache_nofiles'), 'size' => '', 'url' => '', 'created' => '', 'lifetime' => '', 'class' => ''];
    }

    $fragment = new rex_fragment();
    $fragment->setVar('items', $items);
    $output = $fragment->parse('cache-table.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'default', false);
    $fragment->setVar('title', $addon->i18n('rex_gitapi_cache_list_title') . ' - ' . \FriendsOfRedaxo\RexGitApi\RexGitApiCache::getPath(), false);
    $fragment->setVar('body', $output . $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    echo '<form action="' . rex_url::currentBackendPage() . '" method="post"><input type="hidden" name="delete_cache" value="true">' . $content . '</form>';
}
