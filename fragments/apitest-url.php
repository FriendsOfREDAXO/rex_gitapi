<?php

declare(strict_types=1);

/**
 * @var rex_fragment $this
 * @var array<string, string> $urldata
 * @psalm-scope-this rex_fragment
 */

if (isset($this->title)) {
    /** @var string $title */
    $title = $this->title;
} else {
    $title = '';
}
$urldata = (array) $this->urldata;
$urlresult = $this->urlresult;

$addon = rex_addon::get(\FriendsOfRedaxo\RexGitApi\RexGitApi::REXGITAPI_ADDON);

$cache_checked = ('1' === $urldata['cache']) ? 'checked="checked"' : '';
$debug_checked = ('1' === $urldata['debug']) ? 'checked="checked"' : '';
$json_checked = ('1' === $urldata['json']) ? 'checked="checked"' : '';
?>

    <?php if (isset($this->title)): ?>
        <fieldset><legend><?= $title ?></legend>
    <?php endif ?>

    <dl class="rex-form-group form-group">
        <dt><label class="control-label" for="giturl"><?= strval($urldata['label']); ?></label></dt>
        <dd><input list="giturls" id="giturl" class="form-control" type="text" name="giturl[url]" value="<?= strval($urldata['url']); ?>">
            <datalist id="giturls">
            <option value="https://api.github.com">
            <option value="orgs/FriendsOfREDAXO">
            <option value="orgs/FriendsOfREDAXO/repos">
            <option value="orgs/FriendsOfREDAXO/members">
            <option value="meta">
            <option value="octocat">
            <option value="rate_limit">
            <option value="user">
            <option value="versions">
            <option value="zen">
            </datalist>
        <?php if (isset($urldata['help'])): ?>
            <p class="help-block rex-note"><?= strval($urldata['help']) ?></p>
        <?php endif ?>
        </dd>
    </dl>
    <dl class="rex-form-group form-group">
        <dt><label class="control-label" for="giturl"><?= $addon->i18n('rex_gitapi_apitest_options'); ?></label></dt>
        <dd><div class="checkbox"><label><input type="checkbox" name="giturl[cache]" value="1" <?= $cache_checked ?>><?= $addon->i18n('rex_gitapi_apitest_options_use_cache'); ?></label></div></dd>
        <dd><div class="checkbox"><label><input type="checkbox" name="giturl[debug]" value="1" <?= $debug_checked ?>><?= $addon->i18n('rex_gitapi_apitest_options_show_debug'); ?></label></div></dd>
        <dd><div class="checkbox"><label><input type="checkbox" name="giturl[json]" value="1" <?= $json_checked ?>><?= $addon->i18n('rex_gitapi_apitest_options_show_json'); ?></label></div></dd>
    </dl>

    <button class="btn btn-setup rex-form-aligned" type="submit" name="sendit"><?= $addon->i18n('rex_gitapi_apitest_btn_submit'); ?></button>
    <br><br>

    <?php if (isset($this->urlresult) && '' !== $this->urlresult): ?>
        <?= htmlspecialchars_decode(strval($urlresult)); ?>
    <?php endif ?>

    <?php if (isset($this->title)): ?>
        </fieldset>
    <?php endif ?>
