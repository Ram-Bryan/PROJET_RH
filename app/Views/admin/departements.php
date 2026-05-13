<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon sidebar-logo-icon-admin"><i class="bi bi-shield-check"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Administration</span></div>
</div>
<div class="sidebar-section">Gestion</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('admin/dashboard') ?>"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a></li>
    <li><a href="<?= site_url('admin/demandes') ?>"><i class="bi bi-inbox"></i> Toutes les demandes</a></li>
    <li><a href="<?= site_url('admin/employes') ?>"><i class="bi bi-people"></i> Employes</a></li>
    <li><a href="<?= site_url('admin/departements') ?>" class="active"><i class="bi bi-building"></i> Departements</a></li>
    <li><a href="<?= site_url('admin/types-conge') ?>"><i class="bi bi-tags"></i> Types de conge</a></li>
</ul>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Departements</div>
    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Departements</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<div class="form-section">
    <h3><i class="bi bi-building" style="color:var(--forest);margin-right:6px"></i>Ajouter un departement</h3>
    <form action="<?= site_url('admin/departements/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-grid-2" style="margin-bottom:1rem">
            <div class="f-group">
                <label class="f-label">Nom</label>
                <input type="text" class="f-input" name="nom" value="<?= esc(old('nom') ?? '') ?>" />
                <?php if (isset($errors['nom'])): ?><div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['nom']) ?></div><?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Description (optionnel)</label>
                <input type="text" class="f-input" name="description" value="<?= esc(old('description') ?? '') ?>" />
                <?php if (isset($errors['description'])): ?><div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['description']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="form-actions"><button class="btn-forest" type="submit"><i class="bi bi-plus"></i> Ajouter</button></div>
    </form>
</div>

<div class="data-card">
    <div class="data-card-head"><h3>Liste des departements</h3></div>
    <table class="tbl">
        <thead>
            <tr><th>Nom</th><th>Description</th><th>Employes actifs</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($departements)): ?>
                <?php foreach ($departements as $departement): ?>
                    <tr>
                        <td class="td-name"><?= esc($departement['nom']) ?></td>
                        <td class="td-muted"><?= esc($departement['description'] ?? '-') ?></td>
                        <td class="td-mono"><?= esc($departement['nb_employes'] ?? 0) ?></td>
                        <td>
                            <details>
                                <summary class="btn-sm btn-edit" style="display:inline-block;cursor:pointer;list-style:none"><i class="bi bi-pencil"></i> Editer</summary>
                                <form action="<?= site_url('admin/departements/update/' . $departement['id']) ?>" method="post" style="margin-top:.5rem;display:grid;gap:.45rem;min-width:240px">
                                    <?= csrf_field() ?>
                                    <input type="text" class="f-input" name="nom" value="<?= esc($departement['nom']) ?>" />
                                    <input type="text" class="f-input" name="description" value="<?= esc($departement['description'] ?? '') ?>" />
                                    <button class="btn-sm btn-edit" type="submit"><i class="bi bi-check2-circle"></i> Enregistrer</button>
                                </form>
                            </details>
                            <form action="<?= site_url('admin/departements/delete/' . $departement['id']) ?>" method="post" style="display:inline-block;margin-top:.45rem" onsubmit="return confirm('Supprimer ce departement ?');">
                                <?= csrf_field() ?>
                                <button class="btn-sm btn-del" type="submit"><i class="bi bi-trash"></i> Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="td-muted">Aucun departement trouve.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
