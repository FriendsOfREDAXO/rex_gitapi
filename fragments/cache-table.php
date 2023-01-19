<?php

declare(strict_types=1);

/**
 * @var rex_fragment $this
 * @var array<int, array<string,string>> $listitems
 * @psalm-scope-this rex_fragment
 */

$listitems = $this->items;
?>
    <table class="table table-striped table-hover">
        <tbody>
        <?php foreach ($listitems as $item): ?>
            <tr>
            <?php if ('' === $item['size']): ?>
                <td colspan="3"><?= htmlspecialchars_decode($item['file']); ?></td>
            <?php endif; ?>
            <?php if ('' !== $item['size']): ?>
                <td><span" title="<?= $item['url']; ?>"><?= $item['file']; ?> <small class="text-muted">(<?= $item['size']; ?>)</small></span> - <a href="<?= $item['url']; ?>"><small class="text-primary">@GitHub</small></a></td>
                <td>Created: <?= $item['created']; ?></td>
                <td class="<?= $item['class']; ?>">Lifetime: <?= $item['lifetime']; ?></td>
            <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
