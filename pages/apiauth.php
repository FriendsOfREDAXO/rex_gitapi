<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_config_form;
use rex_fragment;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

if ('' === rex_post('_csrf_token', 'string', '')) {
    rex_gitapi_check_api(false);
}

$form = rex_config_form::factory((string) $addon->getPackageId());

$field = $form->addTextField('gituser');
$field->setLabel($addon->i18n('rex_gitapi_apiauth_gituser_label'));
$field->setNotice($addon->i18n('rex_gitapi_apiauth_gituser_notice'));

$field = $form->addTextField('gittoken');
$field->setLabel($addon->i18n('rex_gitapi_apiauth_gittoken_label'));
$field->setNotice($addon->i18n('rex_gitapi_apiauth_gittoken_notice'));

$field = $form->addRawField('<dl class="rex-form-group form-group"><dt></dt><dd><p>'.$addon->i18n('rex_gitapi_apiauth_description').'</p></dd></dl>');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('rex_gitapi_apiauth_title'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

rex_gitapi_output_limits();
