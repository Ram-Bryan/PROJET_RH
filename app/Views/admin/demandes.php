<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon sidebar-logo-icon-admin"><i class="bi bi-shield-check"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Administration</span></div>
</div>
<div class="sidebar-section">Gestion</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('admin/dashboard') ?>"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a></li>
    <li><a href="<?= site_url('admin/demandes') ?>" class="active"><i class="bi bi-inbox"></i> Toutes les demandes</a></li>
    <li><a href="<?= site_url('admin/employes') ?>"><i class="bi bi-people"></i> Employes</a></li>
    <li><a href="<?= site_url('admin/departements') ?>"><i class="bi bi-building"></i> Departements</a></li>
    <li><a href="<?= site_url('admin/types-conge') ?>"><i class="bi bi-tags"></i> Types de conge</a></li>
</ul>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Toutes les demandes</div>
    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Demandes</div>
</div>
<div class="topbar-actions">
    <form method="get" action="<?= site_url('admin/demandes') ?>">
        <select class="f-select" name="statut" onchange="this.form.submit()">
            <option value="" <?= ($statutActif ?? '') === '' ? 'selected' : '' ?>>Tous</option>
            <option value="en_attente" <?= ($statutActif ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
            <option value="approuvee" <?= ($statutActif ?? '') === 'approuvee' ? 'selected' : '' ?>>Approuvee</option>
            <option value="refusee" <?= ($statutActif ?? '') === 'refusee' ? 'selected' : '' ?>>Refusee</option>
            <option value="annulee" <?= ($statutActif ?? '') === 'annulee' ? 'selected' : '' ?>>Annulee</option>
        </select>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
    <div class="data-card-head"><h3>Liste des demandes</h3></div>
    <table class="tbl">
        <thead>
            <tr><th>Employe</th><th>Departement</th><th>Type</th><th>Periode</th><th>Jours</th><th>Statut</th><th>RH</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($demandes)): ?>
                <?php foreach ($demandes as $demande): ?>
                    <tr>
                        <td class="td-name"><?= esc(($demande['employe_prenom'] ?? '') . ' ' . ($demande['employe_nom'] ?? '')) ?></td>
                        <td class="td-muted"><?= esc($demande['departement_nom'] ?? '-') ?></td>
                        <td><?= esc($demande['type_conge_libelle'] ?? '-') ?></td>
                        <td class="td-muted"><?= esc(($demande['date_debut'] ?? '-') . ' au ' . ($demande['date_fin'] ?? '-')) ?></td>
                        <td class="td-mono"><?= esc($demande['nb_jours'] ?? 0) ?></td>
                        <td>
                            <?php if (($demande['statut'] ?? '') === 'en_attente'): ?>
                                <span class="statut s-attente">en attente</span>
                            <?php elseif (($demande['statut'] ?? '') === 'approuvee'): ?>
                                <span class="statut s-approuvee">approuvee</span>
                            <?php elseif (($demande['statut'] ?? '') === 'refusee'): ?>
                                <span class="statut s-refusee">refusee</span>
                            <?php else: ?>
                                <span class="statut s-annulee">annulee</span>
                            <?php endif; ?>
                        </td>
                        <td class="td-muted"><?= esc(trim(($demande['rh_prenom'] ?? '') . ' ' . ($demande['rh_nom'] ?? '')) ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="td-muted">Aucune demande trouvee.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
