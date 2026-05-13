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
  <li><a href="<?= site_url('rh/historique') ?>"><i class="bi bi-archive"></i> Historique</a></li>
  <li><a href="<?= site_url('rh/soldes') ?>" class="active"><i class="bi bi-people"></i> Soldes employés</a></li>
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
  <div class="topbar-title">Soldes employés</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('rh/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right breadcrumb-sep"></i> Soldes</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
  <div class="data-card-head">
    <h3>Soldes — <?= esc((string) $annee) ?></h3>
    <form method="get" action="<?= site_url('rh/soldes') ?>" class="filter-form">
      <input class="f-input input-compact soldes-year" type="number" name="annee" value="<?= esc((string) $annee) ?>" min="2000" max="2100"/>

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
      <a class="btn-secondary btn-xs" href="<?= site_url('rh/soldes') ?>"><i class="bi bi-x"></i> Reset</a>
    </form>
  </div>

  <?php if (!$employesSoldes): ?>
    <div class="empty">
      <i class="bi bi-info-circle"></i>
      <p>Aucun solde à afficher.</p>
    </div>
  <?php else: ?>
    <div class="grid-cards-260">
      <?php foreach ($employesSoldes as $emp): ?>
        <div class="data-card card-no-margin">
          <div class="data-card-head">
            <h3 class="employee-card-head">
              <span class="avatar av-green avatar-28"><?= esc($emp['initials']) ?></span>
              <span><?= esc($emp['employe']) ?></span>
            </h3>
            <span class="td-muted td-note-75"><?= esc($emp['departement']) ?></span>
          </div>
          <div class="soldes-stack">
            <?php foreach ($emp['soldes'] as $solde): ?>
              <div>
                <div class="soldes-row">
                  <span class="td-note-80"><?= esc($solde['type']) ?></span>
                  <span class="td-mono td-note-80 u-fw-500"><?= esc((string) $solde['restant']) ?> / <?= esc((string) $solde['attribues']) ?> j</span>
                </div>
                <progress class="solde-progress<?= $solde['class'] !== '' ? ' ' . esc($solde['class']) : '' ?>" value="<?= esc((string) $solde['restant']) ?>" max="<?= esc((string) $solde['attribues']) ?>"></progress>
                <div class="solde-meta"><?= esc((string) $solde['restant']) ?> restants · <?= esc((string) $solde['pris']) ?> pris</div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
