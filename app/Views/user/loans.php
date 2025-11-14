<?php ob_start(); ?>

<div class="container">
    <h1 class="page-title">Moje výpůjčky</h1>

    <?php if (empty($rentals)): ?>
        <p>Zatím jste si nepůjčili žádnou knihu.</p>
        <a href="<?= BASE_URL ?>/" class="btn btn-primary">Prohlédnout katalog</a>
    <?php else: ?>
        <div class="loans-list">
            <?php foreach ($rentals as $rental): ?>
                <div class="loan-card">
                    <?php if ($rental['thumbnail']): ?>
                        <img src="<?= e($rental['thumbnail']) ?>" alt="<?= e($rental['title']) ?>" class="loan-cover" onload="this.classList.add('loaded')">
                    <?php else: ?>
                        <div class="loan-cover-placeholder">📖</div>
                    <?php endif; ?>

                    <div class="loan-info">
                        <h3>
                            <a href="<?= BASE_URL ?>/kniha/<?= e($rental['slug']) ?>">
                                <?= e($rental['title']) ?>
                            </a>
                        </h3>
                        <p><?= e($rental['author']) ?></p>

                        <div class="loan-meta">
                            <p><strong>Vypůjčeno:</strong> <?= date('d.m.Y', strtotime($rental['rented_at'])) ?></p>
                            <p><strong>Vrátit do:</strong> <?= date('d.m.Y', strtotime($rental['due_at'])) ?></p>

                            <?php if ($rental['extension_count'] > 0): ?>
                                <p class="text-info"><strong>🔄 Prodlouženo:</strong> <?= $rental['extension_count'] ?>x (poslední <?= date('d.m.Y', strtotime($rental['extended_at'])) ?>)</p>
                            <?php endif; ?>

                            <?php if ($rental['is_overdue'] && !$rental['returned_at']): ?>
                                <?php
                                // Calculate fine
                                $dueAt = new DateTime($rental['due_at']);
                                $now = new DateTime();
                                $diff = $now->diff($dueAt);
                                $daysOverdue = $diff->days;
                                $weeksOverdue = ceil($daysOverdue / 7);
                                $fine = $weeksOverdue * 100000;
                                ?>
                                <p class="text-danger"><strong>⚠️ Penále:</strong> <?= number_format($fine, 0, ',', ' ') ?> Kč (<?= $weeksOverdue ?> <?= $weeksOverdue == 1 ? 'týden' : 'týdnů' ?> zpoždění)</p>
                                <p class="text-warning" style="font-size: 0.9em;">💰 100 000 Kč za každý týden zpoždění</p>
                            <?php endif; ?>

                            <?php if ($rental['returned_at']): ?>
                                <p><strong>Vráceno:</strong> <?= date('d.m.Y', strtotime($rental['returned_at'])) ?></p>
                                <span class="badge badge-returned">Vráceno</span>
                            <?php elseif ($rental['is_overdue']): ?>
                                <span class="badge badge-overdue">Po termínu</span>
                            <?php else: ?>
                                <span class="badge badge-active">Aktivní</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($rental['is_active']): ?>
                        <div class="loan-actions">
                            <button class="btn btn-secondary" onclick="extendRental(<?= $rental['id'] ?>)" title="Prodloužit výpůjčku o 15 dní (zpoplatněno)">
                                🔄 Prodloužit (+15 dní)
                            </button>
                            <button class="btn btn-primary" onclick="returnBook(<?= $rental['id'] ?>)">
                                Vrátit knihu
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
