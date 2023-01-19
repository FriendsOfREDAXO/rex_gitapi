<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex;
use rex_addon;
use rex_be_controller;
use rex_view;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

if (rex::isBackend()) {
    if (\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON === rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addCssFile($addon->getAssetsUrl('css/rex_gitapi.css'));
        rex_view::addJsFile($addon->getAssetsUrl('js/rex_gitapi.js'));
    }
    require_once __DIR__ . '/functions/rex_gitapi_functions.php';
}
