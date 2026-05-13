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
    <a href="<?= site_url('logout') ?>" style="margin-left:auto;color:rgba(255,255,255,.25);font-size:1.1rem" title="Déconnexion">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
  <div class="topbar-title">Soldes employés</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('rh/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Soldes</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
  <div class="data-card-head">
    <h3>Soldes — <?= esc((string) $annee) ?></h3>
    <form method="get" action="<?= site_url('rh/soldes') ?>" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <input class="f-input" type="number" name="annee" value="<?= esc((string) $annee) ?>" style="width:120px;font-size:.8rem;padding:6px 10px" min="2000" max="2100"/>

      <select class="f-select" name="departement_id" style="font-size:.8rem;padding:6px 10px;width:auto">
        <option value="">Tous les départements</option>
        <?php foreach ($departements as $dept): ?>
          <option value="<?= esc((string) $dept['id']) ?>" <?= (int) $departementIdActif === (int) $dept['id'] ? 'selected' : '' ?>>
            <?= esc($dept['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select class="f-select" name="employe_id" style="font-size:.8rem;padding:6px 10px;width:auto">
        <option value="">Tous les employés</option>
        <?php foreach ($employes as $emp): ?>
          <option value="<?= esc((string) $emp['id']) ?>" <?= (int) $employeIdActif === (int) $emp['id'] ? 'selected' : '' ?>>
            <?= esc(trim($emp['prenom'] . ' ' . $emp['nom'])) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn-secondary" type="submit" style="padding:7px 12px;font-size:.8rem"><i class="bi bi-funnel"></i> Filtrer</button>
      <a class="btn-secondary" href="<?= site_url('rh/soldes') ?>" style="padding:7px 12px;font-size:.8rem"><i class="bi bi-x"></i> Reset</a>
    </form>
  </div>

  <?php if (!$employesSoldes): ?>
    <div class="empty">
      <i class="bi bi-info-circle"></i>
      <p>Aucun solde à afficher.</p>
    </div>
  <?php else: ?>
    <div style="padding:1rem 1.25rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem">
      <?php foreach ($employesSoldes as $emp): ?>
        <div class="data-card" style="margin:0">
          <div class="data-card-head">
            <h3 style="display:flex;align-items:center;gap:8px">
              <span class="avatar av-green" style="width:28px;height:28px;font-size:.62rem"><?= esc($emp['initials']) ?></span>
              <span><?= esc($emp['employe']) ?></span>
            </h3>
            <span class="td-muted" style="font-size:.75rem"><?= esc($emp['departement']) ?></span>
          </div>
          <div style="padding:.75rem 1.1rem;display:flex;flex-direction:column;gap:.75rem">
            <?php foreach ($emp['soldes'] as $solde): ?>
              <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                  <span style="font-size:.8rem;color:var(--ink)"><?= esc($solde['type']) ?></span>
                  <span class="td-mono" style="font-size:.8rem;color:var(--forest);font-weight:500"><?= esc((string) $solde['restant']) ?> / <?= esc((string) $solde['attribues']) ?> j</span>
                </div>
                <div class="solde-bar"><div class="solde-fill<?= $solde['class'] !== '' ? ' ' . esc($solde['class']) : '' ?>" style="width:<?= esc((string) $solde['percent']) ?>%"></div></div>
                <div class="td-muted" style="font-size:.72rem;margin-top:4px"><?= esc((string) $solde['restant']) ?> restants · <?= esc((string) $solde['pris']) ?> pris</div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

