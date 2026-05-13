<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace employé</span></div>
</div>
<ul class="sidebar-nav" style="margin-top:1rem">
  <li><a href="<?= site_url('employe/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li><a href="<?= site_url('employe/conges/create') ?>" class="active"><i class="bi bi-plus-circle"></i> Nouvelle demande</a></li>
  <li><a href="<?= site_url('employe/conges') ?>"><i class="bi bi-calendar3"></i> Mes demandes</a></li>
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
  <div class="topbar-title">Nouvelle demande de congé</div>
  <div class="topbar-breadcrumb">
    <a href="<?= site_url('employe/dashboard') ?>">Accueil</a>
    <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Nouvelle demande
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start" class="form-layout">

  <!-- Formulaire principal -->
  <div>
    <div class="form-section">
      <h3>Détails de la demande</h3>

      <form action="<?= site_url('employe/conges') ?>" method="post">
        <?= csrf_field() ?>
        <div class="f-group" style="margin-bottom:1rem">
          <label class="f-label">Type de congé <span style="color:var(--danger)">*</span></label>
          <select class="f-select" name="type_conge_id">
            <option value="">-- Choisir un type --</option>
            <?php foreach ($types as $type): ?>
              <option value="<?= esc((string) $type['id']) ?>" <?= old('type_conge_id') == $type['id'] ? 'selected' : '' ?>>
                <?= esc($type['libelle']) ?> (<?= esc((string) $type['restant']) ?> j restants)
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['type_conge_id'])): ?>
            <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['type_conge_id']) ?></div>
          <?php endif; ?>
        </div>

        <div class="form-grid-2" style="margin-bottom:1rem">
          <div class="f-group">
            <label class="f-label">Date de début <span style="color:var(--danger)">*</span></label>
            <input type="date" class="f-input" name="date_debut" value="<?= esc(old('date_debut') ?? '') ?>"/>
          </div>
          <div class="f-group">
            <label class="f-label">Date de fin <span style="color:var(--danger)">*</span></label>
            <input type="date" class="f-input" name="date_fin" value="<?= esc(old('date_fin') ?? '') ?>"/>
          </div>
        </div>

        <div class="f-computed">
          <div class="f-computed-num"><?= esc((string) ($computed['jours'] ?? 0)) ?></div>
          <div class="f-computed-label">jours calendaires calculés<br><span style="font-size:.7rem;opacity:.7"><?= esc($computed['label'] ?? 'Sélectionnez vos dates pour obtenir le calcul.') ?></span></div>
        </div>

        <div class="f-group" style="margin-bottom:1rem">
          <label class="f-label">Motif (optionnel)</label>
          <textarea class="f-textarea" name="motif" placeholder="Précisez le motif de votre demande si nécessaire..."><?= esc(old('motif') ?? '') ?></textarea>
          <div class="f-hint">Le motif est visible par le responsable RH.</div>
        </div>

        <div class="form-actions">
          <button class="btn-forest" type="submit"><i class="bi bi-send"></i> Soumettre la demande</button>
          <a href="<?= site_url('employe/dashboard') ?>" class="btn-secondary"><i class="bi bi-x"></i> Annuler</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Panneau latéral : solde & règles -->
  <div style="display:flex;flex-direction:column;gap:1rem">
    <div class="data-card" style="margin:0">
      <div class="data-card-head"><h3><i class="bi bi-piggy-bank" style="color:var(--forest);margin-right:5px"></i>Vos soldes actuels</h3></div>
      <div style="padding:.75rem 1.1rem;display:flex;flex-direction:column;gap:.75rem">
        <?php foreach ($soldes as $solde): ?>
          <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
              <span style="font-size:.8rem;color:var(--ink)"><?= esc($solde['type']) ?></span>
              <span style="font-family:'DM Mono',monospace;font-size:.8rem;color:var(--forest);font-weight:500"><?= esc((string) $solde['restant']) ?> j</span>
            </div>
            <div class="solde-bar"><div class="solde-fill<?= $solde['class'] ? ' ' . esc($solde['class']) : '' ?>" style="width:<?= esc((string) $solde['percent']) ?>%"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="flash flash-info" style="margin:0">
      <i class="bi bi-info-circle-fill"></i>
      <span style="font-size:.8rem">Le solde est déduit uniquement à l'approbation de votre responsable.</span>
    </div>
    <div style="background:var(--cream);border:1px solid var(--border);border-radius:8px;padding:.85rem 1rem">
      <div style="font-size:.78rem;font-weight:500;color:var(--ink);margin-bottom:.5rem"><i class="bi bi-clipboard-check" style="color:var(--forest);margin-right:5px"></i>Rappel des règles</div>
      <ul style="margin:0;padding-left:1rem;font-size:.75rem;color:var(--muted);line-height:1.7">
        <li>Préavis minimum : 48h avant la date de début</li>
        <li>Pas de chevauchement avec une demande en cours</li>
        <li>Solde insuffisant = demande refusée automatiquement</li>
      </ul>
    </div>
  </div>

</div>
<?= $this->endSection() ?>
