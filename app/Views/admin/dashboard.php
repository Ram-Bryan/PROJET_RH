<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon sidebar-logo-icon-admin"><i class="bi bi-shield-check"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Administration</span></div>
</div>
<div class="sidebar-section">Gestion</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('admin/dashboard') ?>" class="active"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a></li>
    <li><a href="<?= site_url('admin/demandes') ?>"><i class="bi bi-inbox"></i> Toutes les demandes</a></li>
    <li><a href="<?= site_url('admin/employes') ?>"><i class="bi bi-people"></i> Employes</a></li>
    <li><a href="<?= site_url('admin/departements') ?>"><i class="bi bi-building"></i> Departements</a></li>
    <li><a href="<?= site_url('admin/types-conge') ?>"><i class="bi bi-tags"></i> Types de conge</a></li>
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
        <div class="metric-val"><?= esc($employesActifs ?? 0) ?></div>
        <div class="metric-label">Employes actifs</div>
        <div class="metric-sub up"><i class="bi bi-arrow-up-short"></i> +2 ce mois</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-amber"><i class="bi bi-hourglass-split"></i></div></div>
        <div class="metric-val"><?= esc($demandesEnAttente ?? 0) ?></div>
        <div class="metric-label">Demandes en attente</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-green"><i class="bi bi-calendar-check"></i></div></div>
        <div class="metric-val"><?= esc($approuveesCeMois ?? 0) ?></div>
        <div class="metric-label">Approuvees ce mois</div>
        <div class="metric-sub up"><i class="bi bi-arrow-up-short"></i> +6 vs mois dernier</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-blue"><i class="bi bi-building"></i></div></div>
        <div class="metric-val"><?= esc($departementsCount ?? 0) ?></div>
        <div class="metric-label">Departements</div>
    </div>
</div>

<div class="charts-grid">
    <div class="data-card">
        <div class="data-card-head">
            <h3>Conges par mois (<?= esc($anneeGraph ?? date('Y')) ?>)</h3>
        </div>
        <div class="chart-wrap">
            <canvas id="admin-chart-month" height="160"></canvas>
        </div>
    </div>
    <div class="data-card">
        <div class="data-card-head">
            <h3>Conges par jour de semaine</h3>
        </div>
        <div class="chart-wrap">
            <canvas id="admin-chart-weekday" height="160"></canvas>
        </div>
    </div>
</div>

<div class="data-card">
<div class="data-card-head">
    <h3>Demandes recentes</h3>
    <a href="<?= site_url('admin/demandes') ?>" class="link-forest">Tout voir →</a>
</div>
    <table class="tbl">
        <thead>
            <tr><th>Employe</th><th>Type</th><th>Duree</th><th>Statut</th></tr>
        </thead>
        <tbody>
            <?php if (!empty($recentes)): ?>
                <?php foreach ($recentes as $demande): ?>
                    <tr>
                        <td class="td-name"><?= esc(($demande['employe_prenom'] ?? '') . ' ' . ($demande['employe_nom'] ?? '')) ?></td>
                        <td><?= esc($demande['type_conge_libelle'] ?? '-') ?></td>
                        <td class="td-mono"><?= esc(($demande['nb_jours'] ?? 0) . ' j') ?></td>
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
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="td-muted">Aucune demande recente.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="<?= base_url('assets/lib/chartjs/dist/chart.umd.min.js') ?>"></script>
<script>
    const congesParMois = <?= json_encode($congesParMois ?? array_fill(0, 12, 0)) ?>;
    const congesParJour = <?= json_encode($congesParJour ?? array_fill(0, 7, 0)) ?>;

    const monthLabels = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'];
    const weekdayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    const sharedScales = {
        x: {
            grid: { color: 'rgba(45,90,61,0.08)' },
            ticks: { color: '#7a8f80', font: { size: 11 } },
        },
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(45,90,61,0.08)' },
            ticks: { color: '#7a8f80', font: { size: 11 }, precision: 0 },
        },
    };

    const monthCtx = document.getElementById('admin-chart-month');
    if (monthCtx) {
        new Chart(monthCtx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Demandes',
                    data: congesParMois,
                    borderColor: '#2d5a3d',
                    backgroundColor: 'rgba(45,90,61,0.15)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#2d5a3d',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: sharedScales,
            },
        });
    }

    const weekdayCtx = document.getElementById('admin-chart-weekday');
    if (weekdayCtx) {
        new Chart(weekdayCtx, {
            type: 'bar',
            data: {
                labels: weekdayLabels,
                datasets: [{
                    label: 'Demandes',
                    data: congesParJour,
                    backgroundColor: 'rgba(95,168,118,0.7)',
                    borderColor: '#3d7a52',
                    borderWidth: 1,
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: sharedScales,
            },
        });
    }
</script>
<?= $this->endSection() ?>
