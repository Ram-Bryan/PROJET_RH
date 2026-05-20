<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
    <div class="sidebar-brand-name">TechMada RH<span>Espace employé</span></div>
</div>
<div class="sidebar-section">Menu</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('employe/dashboard') ?>" class="active"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
    <li><a href="<?= site_url('employe/conges/create') ?>"><i class="bi bi-plus-circle"></i> Nouvelle demande</a></li>
    <li>
        <a href="<?= site_url('employe/conges') ?>">
            <i class="bi bi-calendar3"></i> Mes demandes
            <span class="nav-badge alert"><?= esc((string) $stats['en_attente']) ?></span>
        </a>
    </li>
    <li><a href="<?= site_url('employe/calendrier') ?>"><i class="bi bi-calendar2-week"></i> Calendrier</a></li>
    <li><a href="<?= site_url('employe/profil') ?>"><i class="bi bi-person"></i> Mon profil</a></li>
</ul>
<div class="sidebar-user">
    <div class="s-user-row">
        <div class="avatar av-green"><?= esc($employe['initials']) ?></div>
        <div>
            <div class="user-name"><?= esc(trim($employe['prenom'] . ' ' . $employe['nom'])) ?></div>
            <div class="user-role">Employé<?= $employe['dept'] ? ' · ' . esc($employe['dept']) : '' ?></div>
        </div>
        <a href="<?= site_url('logout') ?>" class="sidebar-logout" title="Déconnexion"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Tableau de bord</div>
    <div class="topbar-breadcrumb">Accueil</div>
</div>
<div class="topbar-actions">
    <a href="<?= site_url('employe/conges/create') ?>" class="btn-forest btn-compact">
        <i class="bi bi-plus-lg"></i> Nouvelle demande
    </a>
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
        <div class="metric-val"><?= esc((string) $stats['approuvees']) ?></div>
        <div class="metric-label">Approuvées</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-forest"><i class="bi bi-calendar-check"></i></div></div>
        <div class="metric-val"><?= esc((string) $stats['restant']) ?></div>
        <div class="metric-label">Jours restants</div>
        <div class="metric-sub">sur <?= esc((string) $stats['attribues']) ?> cette année</div>
    </div>
    <div class="metric">
        <div class="metric-top"><div class="metric-icon mi-red"><i class="bi bi-x-circle"></i></div></div>
        <div class="metric-val"><?= esc((string) $stats['refusees']) ?></div>
        <div class="metric-label">Refusée</div>
    </div>
</div>

<div class="data-card">
    <div class="data-card-head"><h3>Mes soldes de congés — <?= esc((string) $annee) ?></h3></div>
    <div class="grid-soldes">
        <?php foreach ($soldes as $solde): ?>
            <div class="solde-card card-no-margin">
                <div class="solde-header">
                    <span class="solde-type"><?= esc($solde['type']) ?></span>
                    <span class="solde-nums"><strong><?= esc((string) $solde['restant']) ?></strong> / <?= esc((string) $solde['attribues']) ?> j</span>
                </div>
                <progress class="solde-progress<?= $solde['class'] ? ' ' . esc($solde['class']) : '' ?>" value="<?= esc((string) $solde['restant']) ?>" max="<?= esc((string) $solde['attribues']) ?>"></progress>
                <div class="solde-label"><?= esc((string) $solde['restant']) ?> jours restants · <?= esc((string) $solde['pris']) ?> pris</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="data-card">
    <div class="data-card-head">
        <h3>Mes dernières demandes</h3>
        <a href="<?= site_url('employe/conges') ?>" class="link-forest">Voir tout →</a>
    </div>
    <table class="tbl">
        <thead>
            <tr><th>Type</th><th>Du</th><th>Au</th><th>Durée</th><th>Statut</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php if (!$dernieresDemandes): ?>
                <tr>
                    <td class="td-muted" colspan="6">Aucune demande enregistrée.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($dernieresDemandes as $demande): ?>
                    <tr>
                        <td><span class="type-badge <?= esc($demande['typeBadge']) ?>"><?= esc($demande['type']) ?></span></td>
                        <td class="td-muted"><?= esc($demande['dateDebut']) ?></td>
                        <td class="td-muted"><?= esc($demande['dateFin']) ?></td>
                        <td class="td-mono"><?= esc((string) $demande['nbJours']) ?> j</td>
                        <td><span class="statut <?= esc($demande['statutBadge']) ?>"><?= esc($demande['statutLabel']) ?></span></td>
                        <td>
                            <?php if ($demande['statut'] === 'en_attente'): ?>
                                <form action="<?= site_url('employe/conges/annuler/' . $demande['id']) ?>" method="post" class="u-inline">
                                    <?= csrf_field() ?>
                                    <button class="btn-sm btn-cancel"><i class="bi bi-x"></i> Annuler</button>
                                </form>
                            <?php else: ?>
                                <span class="td-muted u-fs-75">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
