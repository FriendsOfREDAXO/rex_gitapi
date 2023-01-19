<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexGitApi;

use rex_addon;
use rex_csrf_token;
use rex_fragment;
use rex_i18n;
use rex_set_session;
use rex_url;
use rex_view;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

// Check API is available
if ('' === rex_post('formsubmit', 'string', '')) {
    if (false === rex_gitapi_check_api(true)) {
        return;
    }
}

// Output octocat ASCII art on 1st call
$octocat = '';
if ('' === rex_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON, 'string', '')) {
    $octocat = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->setCache(false)->execute('octocat')->get(true);
    if ('' !== $octocat) {
        $octocat = '<pre>' . strval($octocat) . '</pre>';
    }
    rex_set_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON, 'octocat');
}
// rex_set_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON, '');
$output = $octocat . PHP_EOL;

// Formular submitted, get form data
if ('1' === rex_post('formsubmit', 'string')) {
    $form_giturl = rex_request('giturl', 'array');
    if (!isset($form_giturl['cache'])) {
        $form_giturl['cache'] = '';
    }
    if (!isset($form_giturl['debug'])) {
        $form_giturl['debug'] = '';
    }
    if (!isset($form_giturl['json'])) {
        $form_giturl['json'] = '';
    }
    rex_set_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON . '-giturl',
        ['url' => $form_giturl['url'], 'cache' => $form_giturl['cache'], 'debug' => $form_giturl['debug'], 'json' => $form_giturl['json']]);
} else {
    $form_giturl = rex_request('giturl', 'array', ['url' => '', 'cache' => '1', 'debug' => '', 'json' => '']);
    $sessiondata = rex_session(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON . '-giturl', 'array', []);
    if (isset($sessiondata['url'])) {
        $form_giturl['url'] = $sessiondata['url'];
    }
    if (isset($sessiondata['cache'])) {
        $form_giturl['cache'] = $sessiondata['cache'];
    }
    if (isset($sessiondata['debug'])) {
        $form_giturl['debug'] = $sessiondata['debug'];
    }
    if (isset($sessiondata['json'])) {
        $form_giturl['json'] = $sessiondata['json'];
    }
}

// csrf protection
$csrfToken = rex_csrf_token::factory(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

// form submitted
$giturl_result = '';

if ('1' === rex_post('formsubmit', 'string') && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
} elseif ('1' === rex_post('formsubmit', 'string')) {
    ob_start();
    $gitapi = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory()->setDebug(false);
    $gitapi->setUrl($form_giturl['url']);
    // $form_giturl['url'] = $gitapi->getUrl();
    if ('1' !== $form_giturl['cache']) {
        $gitapi->setCache(false);
    }
    if ('1' === $form_giturl['debug']) {
        $gitapi->setDebug(true);
    }
    $gitapi->execute();
    if (true === $gitapi->hasError()) {
        echo rex_view::error('<em class="fa fa-warning"></em> <strong>RexGitApi Error:</strong> '
            . $gitapi->getMessage()
            . '<br>Requested URL: ' . $gitapi->getUrl() . '<br>GitHub-Token: ' . $gitapi->getToken()
        );
    } else {
        $jsonresult = $gitapi->get(true);
        $gitresult = $gitapi->get();
        //echo '<hr>';

        echo '<h4>' . $addon->i18n('rex_gitapi_results_url') . '</h4>';

        echo '<div class="row">';
        echo '<div class="col-sm-6">';
        if (isset($gitresult['avatar_url'])) {
            echo '<a href="'.$gitresult['html_url'].'"><img class="rexgitapiavatar" src="'.$gitresult['avatar_url'].'" alt="'.$gitresult['name'].'" title="'.$gitresult['name'].'" /></a>';
        } else {
            $logoPath = $addon->getAssetsUrl('img/GitHub-Mark-64px.png');
            echo '<a href="'.$gitapi->getUrl().'"><img class="rexgitapioctocat" src="' . $logoPath . '" title="'.$gitapi->getUrl().'" /></a>';
        }
        echo '<code>' . $gitapi->getUrl() . '</code>';
        echo '</div>';

        echo '<div class="col-sm-6">';
        /** @var array<string, string> $gitresult */
        if (isset($gitresult['rexgitcache']) && true === $gitresult['rexgitcache']) {
            echo rex_view::info($addon->i18n('rex_gitapi_apitest_data_from_cache'));
        }
        echo '</div>';
        echo '</div>';

        if ('1' === $form_giturl['json']) {
            echo '<h4>' . $addon->i18n('rex_gitapi_results_json') . '</h4>';
            dump($jsonresult);
        }

        echo '<h4>' . $addon->i18n('rex_gitapi_results_array') . '</h4>';
        dump($gitresult);

        $phpcode = '<?php' . PHP_EOL;
        $phpcode .= '    $gitapi = \FriendsOfRedaxo\RexGitApi\RexGitApi::factory();' . PHP_EOL;
        $phpcode .= '    $gitapi->setUrl(\''.$form_giturl['url'].'\');' . PHP_EOL;
        if ('1' === $form_giturl['cache']) {
            $phpcode .= '    $gitapi->setCache(true);' . PHP_EOL;
        } else {
            $phpcode .= '    $gitapi->setCache(false);' . PHP_EOL;
        }
        if ('1' === $form_giturl['debug']) {
            $phpcode .= '    $gitapi->setDebug(true);' . PHP_EOL;
        }
        $phpcode .= '    $gitapi->execute();' . PHP_EOL;
        $phpcode .= '    if (true === $gitapi->hasError()) {' . PHP_EOL;
        $phpcode .= '        echo rex_view::error($gitapi->getMessage());' . PHP_EOL;
        $phpcode .= '    } else {'. PHP_EOL;
        if ('1' === $form_giturl['json']) {
            $phpcode .= '        $jsonresult = $gitapi->get(true);' . PHP_EOL;
        }
        $phpcode .= '        $gitresult = $gitapi->get();' . PHP_EOL;
        $phpcode .= '    }' . PHP_EOL . PHP_EOL;

        $phpcode .= '    // Static call' . PHP_EOL;
        $staticphpcode = '\FriendsOfRedaxo\RexGitApi\RexGitApi::factory()';
        $staticphpcode .= '->setUrl(\''.$form_giturl['url'].'\')';
        if ('1' === $form_giturl['cache']) {
            $staticphpcode .= '->setCache(true)';
        } else {
            $staticphpcode .= '->setCache(false)';
        }
        if ('1' === $form_giturl['debug']) {
            $staticphpcode .= '->setDebug(true)';
        }
        if ('1' === $form_giturl['json']) {
            $phpcode .= '    $jsonresult = ' .$staticphpcode . '->execute()->get(true);' . PHP_EOL;
        }
        $phpcode .= '    $gitresult = ' . $staticphpcode . '->execute()->get();';

        echo '<h4>' . $addon->i18n('rex_gitapi_results_code') . '</h4>';
        echo '<textarea id="rex-github-php" class="form-control rex-code rex-github-php" rows="10" data-codemirror-mode="php" data-codemirror-options=\'{"readOnly": true}\'>' . $phpcode . '</textarea>';

        echo '<br><div style="text-align:center"><a href="#top">' . $addon->i18n('rex_gitapi_apitest_scroll_top') . ' <i class="rex-icon fa fa-arrow-up"></i></a></div';
    }
    $giturl_result = ob_get_contents();
    ob_end_clean();
}

// URL form
$urldata = [
    'url' => $form_giturl['url'],
    'cache' => $form_giturl['cache'],
    'debug' => $form_giturl['debug'],
    'json' => $form_giturl['json'],
    'label' => $addon->i18n('rex_gitapi_apitest_url_label_url'),
    'help' => $addon->i18n('rex_gitapi_apitest_url_help_url'),
];

$fragment = new rex_fragment();
// $fragment->setVar('title', $addon->i18n('rex_gitapi_apitest_url_title'));
$fragment->setVar('urldata', $urldata);
$fragment->setVar('urlresult', $giturl_result);
$content = $fragment->parse('apitest-url.php');

$output .= '
<form action="' . rex_url::currentBackendPage() . '" method="post">
<input type="hidden" name="formsubmit" value="1" />
    ' . $csrfToken->getHiddenField() . '
    ' . $content . '
</form>
';

// Output forms
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('rex_gitapi_apitest_url_title'), false);
$fragment->setVar('body', $output, false);
echo $fragment->parse('core/page/section.php');

rex_gitapi_output_limits();
