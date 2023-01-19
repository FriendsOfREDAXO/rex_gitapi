<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_fragment;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

if (false === rex_gitapi_check_api(false)) {
    return;
}

$output = $addon->i18n('rex_gitapi_for_intro');

$fragment = new rex_fragment();
$fragment->setVar('class', 'default', false);
$fragment->setVar('title', $addon->i18n('rex_gitapi_for_title'), false);
$fragment->setVar('body', $output, false);
echo $fragment->parse('core/page/section.php');

$output = 'TODO ...';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', $addon->i18n('rex_gitapi_for_output'), false);
$fragment->setVar('body', $output, false);
echo $fragment->parse('core/page/section.php');

// Tests
require_once __DIR__ . '/tests.php';
