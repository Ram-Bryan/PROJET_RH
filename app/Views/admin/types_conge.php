<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon" style="background:var(--ink);border:1px solid rgba(255,255,255,.15)">
        <i class="bi bi-shield-check" style="color:var(--leaf)"></i>
    </div>
    <div class="sidebar-brand-name">TechMada RH<span>Administration</span></div>
</div>
<div class="sidebar-section">Gestion</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('admin/dashboard') ?>"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a></li>
    <li><a href="#"><i class="bi bi-inbox"></i> Toutes les demandes</a></li>
    <li><a href="<?= site_url('admin/employes') ?>"><i class="bi bi-people"></i> Employes</a></li>
    <li><a href="#"><i class="bi bi-building"></i> Departements</a></li>
    <li><a href="<?= site_url('admin/types-conge') ?>" class="active"><i class="bi bi-tags"></i> Types de conge</a></li>
</ul>
<div class="sidebar-user">
    <?php
    $prenom = (string) (session('prenom') ?? '');
    $nom = (string) (session('nom') ?? '');
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    $initials = $initials !== '' ? $initials : 'AD';
    ?>
    <div class="s-user-row">
        <div class="avatar" style="background:#5a2d82;width:32px;height:32px;font-size:.7rem"><?= esc($initials) ?></div>
        <div>
            <div class="user-name"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
            <div class="user-role">Administrateur</div>
        </div>
        <a href="<?= site_url('logout') ?>" style="margin-left:auto;color:rgba(255,255,255,.25);font-size:1.1rem" title="Deconnexion">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Types de conge</div>
    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Types de conge</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<div class="form-section">
    <h3><i class="bi bi-tag" style="color:var(--forest);margin-right:6px"></i>Ajouter un type de conge</h3>
    <form action="<?= site_url('admin/types-conge/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-grid-2" style="margin-bottom:1rem">
            <div class="f-group">
                <label class="f-label">Libelle</label>
                <input type="text" class="f-input" name="libelle" placeholder="Conge exceptionnel" value="<?= esc(old('libelle') ?? '') ?>" />
                <?php if (isset($errors['libelle'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['libelle']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Jours annuels</label>
                <input type="number" min="0" class="f-input" name="jours_annuels" value="<?= esc(old('jours_annuels') ?? '0') ?>" />
                <?php if (isset($errors['jours_annuels'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['jours_annuels']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Deductible</label>
                <select class="f-select" name="deductible">
                    <option value="1" <?= (string) old('deductible', '1') === '1' ? 'selected' : '' ?>>Oui</option>
                    <option value="0" <?= (string) old('deductible', '1') === '0' ? 'selected' : '' ?>>Non</option>
                </select>
                <?php if (isset($errors['deductible'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['deductible']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-forest" type="submit"><i class="bi bi-plus"></i> Ajouter le type</button>
        </div>
    </form>
</div>

<div class="data-card">
    <div class="data-card-head">
        <h3>Liste des types de conge</h3>
    </div>
    <table class="tbl">
        <thead>
            <tr><th>ID</th><th>Libelle</th><th>Jours annuels</th><th>Deductible</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($types)): ?>
                <?php foreach ($types as $type): ?>
                    <tr>
                        <td class="td-mono"><?= esc($type['id']) ?></td>
                        <td class="td-name"><?= esc($type['libelle']) ?></td>
                        <td class="td-mono"><?= esc($type['jours_annuels']) ?></td>
                        <td>
                            <?php if ((int) $type['deductible'] === 1): ?>
                                <span class="statut s-approuvee" style="font-size:.68rem">oui</span>
                            <?php else: ?>
                                <span class="statut s-annulee" style="font-size:.68rem">non</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="td-muted">Aucun type de conge enregistre.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
