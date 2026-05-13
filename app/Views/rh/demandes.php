<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon"><i class="bi bi-person-check"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Espace responsable</span></div>
</div>
<div class="sidebar-section">Menu</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('rh/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
    <li><a href="<?= site_url('rh/demandes') ?>" class="active"><i class="bi bi-inbox"></i> Demandes a traiter</a></li>
</ul>
<div class="sidebar-user">
    <?php
    $prenom = (string) (session('prenom') ?? '');
    $nom = (string) (session('nom') ?? '');
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    $initials = $initials !== '' ? $initials : '??';
    ?>
    <div class="s-user-row">
        <div class="avatar av-blue"><?= esc($initials) ?></div>
        <div>
            <div class="user-name"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
            <div class="user-role">Responsable RH</div>
        </div>
        <a href="<?= site_url('logout') ?>" style="margin-left:auto;color:rgba(255,255,255,.25);font-size:1.1rem" title="Deconnexion">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Demandes de conge</div>
    <div class="topbar-breadcrumb"><a href="<?= site_url('rh/dashboard') ?>">RH</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Demandes</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
    <div class="data-card-head">
        <h3>Toutes les demandes</h3>
    </div>

    <table class="tbl">
        <thead>
            <tr><th>Employe</th><th>Type</th><th>Periode</th><th>Jours</th><th>Statut</th><th>Commentaire RH</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($demandes)): ?>
                <?php foreach ($demandes as $demande): ?>
                    <tr>
                        <td class="td-name"><?= esc(($demande['employe_prenom'] ?? '') . ' ' . ($demande['employe_nom'] ?? '')) ?></td>
                        <td><?= esc($demande['type_conge_libelle'] ?? '-') ?></td>
                        <td class="td-muted"><?= esc(($demande['date_debut'] ?? '-') . ' au ' . ($demande['date_fin'] ?? '-')) ?></td>
                        <td class="td-mono"><?= esc($demande['nb_jours'] ?? 0) ?></td>
                        <td>
                            <?php $statut = (string) ($demande['statut'] ?? ''); ?>
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
                            <?php if ($statut === 'annulee'): ?>
                                <span class="td-muted">Aucune action</span>
                            <?php else: ?>
                                <form action="<?= site_url('rh/demandes/' . $demande['id'] . '/approuver') ?>" method="post" style="display:grid;gap:.35rem;min-width:210px">
                                    <?= csrf_field() ?>
                                    <input class="f-input" type="text" name="commentaire_rh" placeholder="Commentaire RH (optionnel)" />
                                    <button class="btn-sm btn-edit" type="submit"><i class="bi bi-check2-circle"></i> Approuver</button>
                                </form>
                                <form action="<?= site_url('rh/demandes/' . $demande['id'] . '/refuser') ?>" method="post" style="margin-top:.4rem">
                                    <?= csrf_field() ?>
                                    <input class="f-input" type="text" name="commentaire_rh" placeholder="Motif du refus (optionnel)" style="margin-bottom:.35rem" />
                                    <button class="btn-sm btn-del" type="submit"><i class="bi bi-x-circle"></i> Refuser</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="td-muted">Aucune demande pour le moment.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
