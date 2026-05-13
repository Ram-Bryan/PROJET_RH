<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon sidebar-logo-icon-admin"><i class="bi bi-shield-check"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Administration</span></div>
</div>
<div class="sidebar-section">Gestion</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('admin/dashboard') ?>" class="active"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a></li>
    <li><a href="#"><i class="bi bi-inbox"></i> Toutes les demandes</a></li>
    <li><a href="<?= site_url('admin/employes') ?>"><i class="bi bi-people"></i> Employes</a></li>
    <li><a href="#"><i class="bi bi-building"></i> Departements</a></li>
    <li><a href="#"><i class="bi bi-tags"></i> Types de conge</a></li>
</ul>
<div class="sidebar-user">
    <?php
    $prenom = (string) (session('prenom') ?? '');
    $nom = (string) (session('nom') ?? '');
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    $initials = $initials !== '' ? $initials : 'AD';
    ?>
    <div class="s-user-row">
        <div class="avatar avatar-admin"><?= esc($initials) ?></div>
        <div>
            <div class="user-name"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
            <div class="user-role">Administrateur</div>
        </div>
        <a href="<?= site_url('logout') ?>" class="sidebar-logout" title="Deconnexion">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Vue d'ensemble</div>
    <div class="topbar-breadcrumb">Administration</div>
</div>
<div class="topbar-actions">
    <a href="<?= site_url('admin/employes') ?>#form-add" class="btn-forest btn-compact"><i class="bi bi-person-plus"></i> Ajouter un employe</a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="metrics">
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-forest"><i class="bi bi-people"></i></div></div>
        <div class="metric-val">24</div>
        <div class="metric-label">Employes actifs</div>
        <div class="metric-sub up"><i class="bi bi-arrow-up-short"></i> +2 ce mois</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-amber"><i class="bi bi-hourglass-split"></i></div></div>
        <div class="metric-val">4</div>
        <div class="metric-label">Demandes en attente</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-green"><i class="bi bi-calendar-check"></i></div></div>
        <div class="metric-val">31</div>
        <div class="metric-label">Approuvees ce mois</div>
        <div class="metric-sub up"><i class="bi bi-arrow-up-short"></i> +6 vs mois dernier</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-blue"><i class="bi bi-building"></i></div></div>
        <div class="metric-val">4</div>
        <div class="metric-label">Departements</div>
    </div>
</div>

<div class="data-card">
<div class="data-card-head">
    <h3>Demandes recentes</h3>
    <a href="#" class="link-forest">Tout voir →</a>
</div>
    <table class="tbl">
        <thead>
            <tr><th>Employe</th><th>Type</th><th>Duree</th><th>Statut</th></tr>
        </thead>
        <tbody>
            <tr>
                <td class="td-name">Soa Rakoto</td>
                <td>Annuel</td>
                <td class="td-mono">5 j</td>
                <td><span class="statut s-attente">en attente</span></td>
            </tr>
            <tr>
                <td class="td-name">Tsiry Fidy</td>
                <td>Maladie</td>
                <td class="td-mono">2 j</td>
                <td><span class="statut s-attente">en attente</span></td>
            </tr>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
