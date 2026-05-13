<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TechMada RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet" />
</head>
<body>
<div class="app-wrap">
    <aside class="sidebar">
        <?= $this->renderSection('sidebar') ?>
    </aside>

    <div class="main">
        <div class="topbar">
            <?= $this->renderSection('topbar') ?>
        </div>

        <div class="content">
            <?php if ($message = session()->getFlashdata('success')): ?>
                <div class="flash flash-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= esc($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($message = session()->getFlashdata('error')): ?>
                <div class="flash flash-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= esc($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($message = session()->getFlashdata('warn')): ?>
                <div class="flash flash-warn">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= esc($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($message = session()->getFlashdata('info')): ?>
                <div class="flash flash-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <?= esc($message) ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>

        <div class="footer-app">
            <i class="bi bi-c-circle"></i> 2025 <span>TechMada RH</span>
        </div>
    </div>
</div>
</body>
</html>
