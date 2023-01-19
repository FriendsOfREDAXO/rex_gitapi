<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

$addon->setProperty('successmsg', '<br>' . $addon->i18n('rex_gitapi_install_info'));
