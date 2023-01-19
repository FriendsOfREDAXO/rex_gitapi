<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex;
use rex_addon;
use rex_be_controller;
use rex_i18n;
use rex_timer;
use rex_view;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

$logoPath = $addon->getAssetsUrl('img/GitHub-Mark-64px.png');
echo rex_view::title('<span><img class="title-octocat" src="' . $logoPath . '" width="32" height="32" /></span>' . $addon->i18n('title'));

$subpage = rex_be_controller::getCurrentPagePart(2);

rex_be_controller::includeCurrentPageSubPath();

$img = '<img src="' . $addon->getAssetsUrl('img/octocat.png') . '" width="16" height="16" style="opacity:.6;filter: grayscale(1);" /> ';
$url = '<small><a href="https://github.com/FriendsOfREDAXO/rex_gitapi">rex_gitapi @FriendsOfREDAXO</a></small>';
$scripttime = rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC); /** @phpstan-ignore-line */

echo '<div style="text-align:center;"><p>';
echo $img . $url . ' - <small>' . rex_i18n::msg('footer_scripttime', $scripttime) . '</small>';
echo ' <small><a href="#top"><i class="rex-icon fa fa-arrow-up"></i></a></small>';
echo '</p></div>';
