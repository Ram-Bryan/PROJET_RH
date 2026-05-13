<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Espace employe</span></div>
</div>
<div class="sidebar-section">Menu</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('employe/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
    <li><a href="<?= site_url('employe/conges') ?>" class="active"><i class="bi bi-calendar3"></i> Mes conges</a></li>
</ul>
<div class="sidebar-user">
    <?php
    $prenom = (string) (session('prenom') ?? '');
    $nom = (string) (session('nom') ?? '');
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    $initials = $initials !== '' ? $initials : '??';
    ?>
    <div class="s-user-row">
        <div class="avatar av-green"><?= esc($initials) ?></div>
        <div>
            <div class="user-name"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
            <div class="user-role">Employe</div>
        </div>
        <a href="<?= site_url('logout') ?>" style="margin-left:auto;color:rgba(255,255,255,.25);font-size:1.1rem" title="Deconnexion">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Mes demandes de conge</div>
    <div class="topbar-breadcrumb"><a href="<?= site_url('employe/dashboard') ?>">Employe</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Conges</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>

<div class="form-section">
    <h3><i class="bi bi-plus-circle" style="color:var(--forest);margin-right:6px"></i>Nouvelle demande</h3>
    <form action="<?= site_url('employe/conges/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-grid-2" style="margin-bottom:1rem">
            <div class="f-group">
                <label class="f-label">Type de conge</label>
                <select class="f-select" name="type_conge_id">
                    <option value="">-- Selectionner --</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= esc($type['id']) ?>" <?= (string) old('type_conge_id') === (string) $type['id'] ? 'selected' : '' ?>>
                            <?= esc($type['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['type_conge_id'])): ?><div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['type_conge_id']) ?></div><?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Date debut</label>
                <input class="f-input" type="date" name="date_debut" value="<?= esc(old('date_debut') ?? '') ?>" />
                <?php if (isset($errors['date_debut'])): ?><div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['date_debut']) ?></div><?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Date fin</label>
                <input class="f-input" type="date" name="date_fin" value="<?= esc(old('date_fin') ?? '') ?>" />
                <?php if (isset($errors['date_fin'])): ?><div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['date_fin']) ?></div><?php endif; ?>
            </div>
            <div class="f-group" style="grid-column:1/-1">
                <label class="f-label">Motif (optionnel)</label>
                <textarea class="f-input" name="motif" rows="3" placeholder="Motif de la demande"><?= esc(old('motif') ?? '') ?></textarea>
                <?php if (isset($errors['motif'])): ?><div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['motif']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn-forest" type="submit"><i class="bi bi-send"></i> Soumettre</button>
        </div>
    </form>
</div>

<div class="data-card" style="margin-bottom:1rem">
    <div class="data-card-head">
        <h3>Mes soldes (<?= esc($annee) ?>)</h3>
    </div>
    <table class="tbl">
        <thead>
            <tr><th>Type</th><th>Attribues</th><th>Pris</th><th>Restant</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($soldes)): ?>
                <?php foreach ($soldes as $solde): ?>
                    <tr>
                        <td class="td-name"><?= esc($solde['type_conge_libelle']) ?></td>
                        <td class="td-mono"><?= esc($solde['jours_attribues']) ?></td>
                        <td class="td-mono"><?= esc($solde['jours_pris']) ?></td>
                        <td class="td-mono"><?= esc($solde['jours_restant']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="td-muted">Aucun solde initialise pour cette annee.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="data-card">
    <div class="data-card-head">
        <h3>Mes demandes</h3>
    </div>
    <table class="tbl">
        <thead>
            <tr><th>Type</th><th>Periode</th><th>Jours</th><th>Statut</th><th>Commentaire RH</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($demandes)): ?>
                <?php foreach ($demandes as $demande): ?>
                    <?php $statut = (string) ($demande['statut'] ?? ''); ?>
                    <tr>
                        <td class="td-name"><?= esc($demande['type_conge_libelle'] ?? '-') ?></td>
                        <td class="td-muted"><?= esc(($demande['date_debut'] ?? '-') . ' au ' . ($demande['date_fin'] ?? '-')) ?></td>
                        <td class="td-mono"><?= esc($demande['nb_jours'] ?? 0) ?></td>
                        <td>
                            <?php if ($statut === 'en_attente'): ?>
                                <span class="statut s-attente">en attente</span>
                            <?php elseif ($statut === 'approuvee'): ?>
                                <span class="statut s-approuvee">approuvee</span>
                            <?php elseif ($statut === 'refusee'): ?>
                                <span class="statut s-refusee">refusee</span>
                            <?php else: ?>
                                <span class="statut s-annulee">annulee</span>
                            <?php endif; ?>
                        </td>
                        <td class="td-muted"><?= esc($demande['commentaire_rh'] ?? '-') ?></td>
                        <td>
                            <?php if ($statut === 'en_attente'): ?>
                                <form action="<?= site_url('employe/conges/' . $demande['id'] . '/annuler') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button class="btn-sm btn-del" type="submit"><i class="bi bi-x-circle"></i> Annuler</button>
                                </form>
                            <?php else: ?>
                                <span class="td-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="td-muted">Aucune demande pour le moment.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
