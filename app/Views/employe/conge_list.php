<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace employé</span></div>
</div>
<ul class="sidebar-nav sidebar-nav-spaced">
  <li><a href="<?= site_url('employe/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li><a href="<?= site_url('employe/conges/create') ?>"><i class="bi bi-plus-circle"></i> Nouvelle demande</a></li>
  <li><a href="<?= site_url('employe/conges') ?>" class="active"><i class="bi bi-calendar3"></i> Mes demandes</a></li>
  <li><a href="<?= site_url('employe/profil') ?>"><i class="bi bi-person"></i> Mon profil</a></li>
</ul>
<div class="sidebar-user">
  <div class="s-user-row">
    <div class="avatar av-green"><?= esc($employe['initials']) ?></div>
    <div><div class="user-name"><?= esc(trim($employe['prenom'] . ' ' . $employe['nom'])) ?></div><div class="user-role">Employé<?= $employe['dept'] ? ' · ' . esc($employe['dept']) : '' ?></div></div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
  <div class="topbar-title">Mes demandes de congé</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('employe/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right breadcrumb-sep"></i> Mes demandes</div>
</div>
<div class="topbar-actions">
  <a href="<?= site_url('employe/conges/create') ?>" class="btn-forest btn-compact"><i class="bi bi-plus-lg"></i> Nouvelle demande</a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
<div class="data-card-head">
    <h3>Toutes mes demandes</h3>
    <div class="u-flex u-gap-6">
      <form method="get" action="<?= site_url('employe/conges') ?>">
        <select class="f-select f-compact" name="statut" onchange="this.form.submit()">
          <option value="">Tous les statuts</option>
          <option value="en_attente" <?= $statutActif === 'en_attente' ? 'selected' : '' ?>>En attente</option>
          <option value="approuvee" <?= $statutActif === 'approuvee' ? 'selected' : '' ?>>Approuvée</option>
          <option value="refusee" <?= $statutActif === 'refusee' ? 'selected' : '' ?>>Refusée</option>
          <option value="annulee" <?= $statutActif === 'annulee' ? 'selected' : '' ?>>Annulée</option>
        </select>
      </form>
    </div>
  </div>
  <table class="tbl">
    <thead>
      <tr><th>Type</th><th>Début</th><th>Fin</th><th>Durée</th><th>Statut</th><th>Commentaire RH</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php if (!$conges): ?>
        <tr>
          <td class="td-muted" colspan="7">Aucune demande trouvée.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($conges as $conge): ?>
          <tr>
            <td><span class="type-badge <?= esc($conge['typeBadge']) ?>"><?= esc($conge['type']) ?></span></td>
            <td class="td-muted"><?= esc($conge['dateDebut']) ?></td>
            <td class="td-muted"><?= esc($conge['dateFin']) ?></td>
            <td class="td-mono"><?= esc((string) $conge['nbJours']) ?> j</td>
            <td><span class="statut <?= esc($conge['statutBadge']) ?>"><?= esc($conge['statutLabel']) ?></span></td>
            <td class="td-muted u-fs-78"><?= esc($conge['commentaire']) ?></td>
            <td>
              <?php if ($conge['statut'] === 'en_attente'): ?>
                <form action="<?= site_url('employe/conges/annuler/' . $conge['id']) ?>" method="post" class="u-inline">
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
