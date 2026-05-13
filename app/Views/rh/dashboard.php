<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon"><i class="bi bi-person-check"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Espace responsable</span></div>
</div>
<div class="sidebar-section">Menu</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('rh/dashboard') ?>" class="active"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
    <li><a href="<?= site_url('rh/demandes') ?>"><i class="bi bi-inbox"></i> Demandes à traiter</a></li>
    <li><a href="<?= site_url('rh/historique') ?>"><i class="bi bi-archive"></i> Historique</a></li>
    <li><a href="<?= site_url('rh/soldes') ?>"><i class="bi bi-people"></i> Soldes employés</a></li>
</ul>
<div class="sidebar-user">
    <div class="s-user-row">
        <div class="avatar av-blue"><?= esc($rh['initials']) ?></div>
        <div>
            <div class="user-name"><?= esc(trim($rh['prenom'] . ' ' . $rh['nom'])) ?></div>
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
    <div class="topbar-title">Tableau de bord RH</div>
    <div class="topbar-breadcrumb">Accueil</div>
</div>
<div class="topbar-actions">
    <span style="font-size:.8rem;color:var(--muted);background:var(--warn-bg);border:1px solid var(--warn-br);border-radius:6px;padding:5px 10px;display:flex;align-items:center;gap:5px;color:var(--warn)">
        <i class="bi bi-hourglass-split"></i> <?= esc((string) $stats['en_attente']) ?> en attente
    </span>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="metrics">
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-amber"><i class="bi bi-hourglass-split"></i></div></div>
        <div class="metric-val"><?= esc((string) $stats['en_attente']) ?></div>
        <div class="metric-label">En attente</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-green"><i class="bi bi-check-circle"></i></div></div>
        <div class="metric-val"><?= esc((string) $stats['approuvees_mois']) ?></div>
        <div class="metric-label">Approuvées ce mois</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-red"><i class="bi bi-x-circle"></i></div></div>
        <div class="metric-val"><?= esc((string) $stats['refusees_mois']) ?></div>
        <div class="metric-label">Refusées ce mois</div>
    </div>
</div>

<div class="data-card">
    <div class="data-card-head">
        <h3>Dernieres demandes</h3>
        <a href="<?= site_url('rh/demandes') ?>" style="font-size:.8rem;color:var(--forest);text-decoration:none">Voir tout →</a>
    </div>
    <table class="tbl">
        <thead>
            <tr><th>Employe</th><th>Type</th><th>Periode</th><th>Statut</th></tr>
        </thead>
        <tbody>
            <?php if (!$recent): ?>
                <tr><td class="td-muted" colspan="4">Aucune demande.</td></tr>
            <?php else: ?>
                <?php foreach ($recent as $demande): ?>
                    <tr>
                        <td class="td-name"><?= esc($demande['employe']) ?></td>
                        <td><?= esc($demande['type']) ?></td>
                        <td class="td-muted"><?= esc($demande['periode']) ?></td>
                        <td><span class="statut <?= esc($demande['statutBadge']) ?>"><?= esc($demande['statutLabel']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
