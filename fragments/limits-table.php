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
            <?php if ('' === $item['value']): ?>
                <td colspan="2"><?= htmlspecialchars_decode($item['label']); ?></td>
            <?php endif; ?>
            <?php if ('' !== $item['value']): ?>
                <th nowrap="nowrap"><?= $item['label']; ?></td>
                <td width="100%"><?= $item['value']; ?></td>
            <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
