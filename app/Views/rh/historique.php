<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-person-check"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace responsable</span></div>
</div>
<div class="sidebar-section">Menu</div>
<ul class="sidebar-nav">
  <li><a href="<?= site_url('rh/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li><a href="<?= site_url('rh/demandes') ?>"><i class="bi bi-inbox"></i> Demandes à traiter</a></li>
  <li><a href="<?= site_url('rh/historique') ?>" class="active"><i class="bi bi-archive"></i> Historique</a></li>
  <li><a href="<?= site_url('rh/soldes') ?>"><i class="bi bi-people"></i> Soldes employés</a></li>
</ul>
<div class="sidebar-user">
  <div class="s-user-row">
    <div class="avatar av-blue"><?= esc($rh['initials']) ?></div>
    <div>
      <div class="user-name"><?= esc(trim($rh['prenom'] . ' ' . $rh['nom'])) ?></div>
      <div class="user-role">Responsable RH</div>
    </div>
    <a href="<?= site_url('logout') ?>" class="sidebar-logout" title="Déconnexion">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
  <div class="topbar-title">Historique des demandes</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('rh/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right breadcrumb-sep"></i> Historique</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
  <div class="data-card-head">
    <h3>Demandes traitées</h3>
    <form method="get" action="<?= site_url('rh/historique') ?>" class="filter-form">
      <select class="f-select f-compact" name="statut">
        <option value="" <?= $statutActif === '' ? 'selected' : '' ?>>Toutes</option>
        <option value="approuvee" <?= $statutActif === 'approuvee' ? 'selected' : '' ?>>Approuvées</option>
        <option value="refusee" <?= $statutActif === 'refusee' ? 'selected' : '' ?>>Refusées</option>
        <option value="annulee" <?= $statutActif === 'annulee' ? 'selected' : '' ?>>Annulées</option>
        <option value="en_attente" <?= $statutActif === 'en_attente' ? 'selected' : '' ?>>En attente</option>
      </select>

      <select class="f-select f-compact" name="departement_id">
        <option value="">Tous les départements</option>
        <?php foreach ($departements as $dept): ?>
          <option value="<?= esc((string) $dept['id']) ?>" <?= (int) $departementIdActif === (int) $dept['id'] ? 'selected' : '' ?>>
            <?= esc($dept['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select class="f-select f-compact" name="employe_id">
        <option value="">Tous les employés</option>
        <?php foreach ($employes as $emp): ?>
          <option value="<?= esc((string) $emp['id']) ?>" <?= (int) $employeIdActif === (int) $emp['id'] ? 'selected' : '' ?>>
            <?= esc(trim($emp['prenom'] . ' ' . $emp['nom'])) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn-secondary btn-xs" type="submit"><i class="bi bi-funnel"></i> Filtrer</button>
      <a class="btn-secondary btn-xs" href="<?= site_url('rh/historique') ?>"><i class="bi bi-x"></i> Reset</a>
    </form>
  </div>

  <table class="tbl">
    <thead>
      <tr><th>Employé</th><th>Département</th><th>Type</th><th>Période</th><th>Durée</th><th>Statut</th><th>Traité par</th><th>Commentaire</th></tr>
    </thead>
    <tbody>
      <?php if (!$demandes): ?>
        <tr><td class="td-muted" colspan="8">Aucune demande trouvée.</td></tr>
      <?php else: ?>
        <?php foreach ($demandes as $demande): ?>
          <tr>
            <td class="td-name"><?= esc($demande['employe']) ?></td>
            <td class="td-muted"><?= esc($demande['departement']) ?></td>
            <td><span class="type-badge <?= esc($demande['typeBadge']) ?>"><?= esc($demande['type']) ?></span></td>
            <td class="td-muted td-note-80"><?= esc($demande['periode']) ?></td>
            <td class="td-mono"><?= esc((string) $demande['nbJours']) ?> j</td>
            <td><span class="statut <?= esc($demande['statutBadge']) ?>"><?= esc($demande['statutLabel']) ?></span></td>
            <td class="td-muted td-note-80"><?= $demande['traitePar'] !== '' ? esc($demande['traitePar']) : '—' ?></td>
            <td class="td-muted td-note-78"><?= $demande['commentaire'] !== '' ? esc($demande['commentaire']) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
