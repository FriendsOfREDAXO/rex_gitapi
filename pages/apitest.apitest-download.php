<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_fragment;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

// Check API is available
if ('' === rex_post('formsubmit', 'string', '')) {
    if (false === rex_gitapi_check_api(true)) {
        return;
    }
}

$output = 'TODO...<br><br>Sonderfunktionen für ZIP-Downloads';

// Output forms
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('rex_gitapi_apitest_download_title'), false);
$fragment->setVar('body', $output, false);
echo $fragment->parse('core/page/section.php');

rex_gitapi_output_limits();
