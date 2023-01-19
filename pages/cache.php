<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_config_form;
use rex_fragment;
use rex_view;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

if ('true' === rex_request('delete_cache', 'string', '')) {
    \FriendsOfRedaxo\RexGitApi\RexGitApiCache::deleteCache();
    echo rex_view::success($addon->i18n('rex_gitapi_cache_deleted'));
}

$form = rex_config_form::factory((string) $addon->getPackageId());

$field = $form->addSelectField('cachelifetime', $value = null, ['class' => 'form-control selectpicker']);
$field->setLabel($addon->i18n('rex_gitapi_cache_lifetime_label'));
$select = $field->getSelect();
$minutes = $addon->i18n('rex_gitapi_cache_lifetime_minutes');
$select->addOption($addon->i18n('rex_gitapi_cache_nocache'), 0);
$select->addOption('5 ' . $minutes, 5);
$select->addOption('10 ' . $minutes, 10);
$select->addOption('15 ' . $minutes, 15);
$select->addOption('30 ' . $minutes, 30);
$select->addOption('60 ' . $minutes, 60);
$select->addOption('120 ' . $minutes, 120);

$field = $form->addRawField('<dl class="rex-form-group form-group"><dt></dt><dd><p>'.$addon->i18n('rex_gitapi_cache_description').'</p></dd></dl>');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('rex_gitapi_cache_title'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

$csrftoken = rex_request('_csrf_token', 'string', '');
if ('' !== $csrftoken && '0' === $addon->getConfig('cachelifetime', '')) {
    \FriendsOfRedaxo\RexGitApi\RexGitApiCache::deleteCache();
    echo rex_view::success($addon->i18n('rex_gitapi_cache_deleted'));
}

rex_gitapi_output_cache();
