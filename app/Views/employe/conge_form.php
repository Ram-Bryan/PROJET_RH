<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace employé</span></div>
</div>
<ul class="sidebar-nav">
  <li><a href="<?= site_url('employe/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li><a href="<?= site_url('employe/conges/create') ?>" class="active"><i class="bi bi-plus-circle"></i> Nouvelle demande</a></li>
  <li><a href="<?= site_url('employe/conges') ?>"><i class="bi bi-calendar3"></i> Mes demandes</a></li>
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
  <div class="topbar-title">Nouvelle demande de congé</div>
  <div class="topbar-breadcrumb">
    <a href="<?= site_url('employe/dashboard') ?>">Accueil</a>
    <i class="bi bi-chevron-right"></i> Nouvelle demande
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="conge-form-layout">
  <!-- Formulaire principal -->
  <div class="conge-form-main">
    <div class="form-section">
      <h3>Détails de la demande</h3>

      <form action="<?= site_url('employe/conges') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Type de congé -->
        <div class="f-group">
          <label class="f-label">Type de congé <span class="f-required">*</span></label>
          <select class="f-select" name="type_conge_id">
            <option value="">-- Choisir un type --</option>
            <?php foreach ($types as $type): ?>
              <option value="<?= esc((string) $type['id']) ?>" <?= old('type_conge_id') == $type['id'] ? 'selected' : '' ?>>
                <?= esc($type['libelle']) ?> (<?= esc((string) $type['restant']) ?> j restants)
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['type_conge_id'])): ?>
            <div class="f-error">
              <i class="bi bi-exclamation-circle"></i> <?= esc($errors['type_conge_id']) ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Dates de la demande -->
        <div class="form-grid-2">
          <div class="f-group">
            <label class="f-label">Date de début <span class="f-required">*</span></label>
            <input type="date" class="f-input" name="date_debut" value="<?= esc(old('date_debut') ?? '') ?>" />
          </div>
          <div class="f-group">
            <label class="f-label">Date de fin <span class="f-required">*</span></label>
            <input type="date" class="f-input" name="date_fin" value="<?= esc(old('date_fin') ?? '') ?>" />
          </div>
        </div>

        <!-- Calcul automatique -->
        <div class="f-computed">
          <div class="f-computed-num"><?= esc((string) ($computed['jours'] ?? 0)) ?></div>
          <div class="f-computed-label">
            jours calendaires calculés<br>
            <span class="f-computed-sub"><?= esc($computed['label'] ?? 'Sélectionnez vos dates pour obtenir le calcul.') ?></span>
          </div>
        </div>

        <!-- Motif optionnel -->
        <div class="f-group">
          <label class="f-label">Motif (optionnel)</label>
          <textarea class="f-textarea" name="motif" placeholder="Précisez le motif de votre demande si nécessaire..."><?= esc(old('motif') ?? '') ?></textarea>
          <div class="f-hint">Le motif est visible par le responsable RH.</div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <button class="btn-forest" type="submit">
            <i class="bi bi-send"></i> Soumettre la demande
          </button>
          <a href="<?= site_url('employe/dashboard') ?>" class="btn-secondary">
            <i class="bi bi-x"></i> Annuler
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Panneau latéral : soldes et règles -->
  <div class="conge-form-sidebar">
    <!-- Soldes actuels -->
    <div class="data-card">
      <div class="data-card-head">
        <h3>
          <i class="bi bi-piggy-bank"></i> Vos soldes actuels
        </h3>
      </div>
      <div class="soldes-container">
        <?php foreach ($soldes as $solde): ?>
          <div class="solde-item">
            <div class="solde-header">
              <span class="solde-label"><?= esc($solde['type']) ?></span>
              <span class="solde-value"><?= esc((string) $solde['restant']) ?> j</span>
            </div>
            <progress class="solde-progress<?= $solde['class'] ? ' ' . esc($solde['class']) : '' ?>" value="<?= esc((string) $solde['restant']) ?>" max="<?= esc((string) $solde['attribues']) ?>"></progress>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Message informatif -->
    <div class="flash flash-info">
      <i class="bi bi-info-circle-fill"></i>
      <span>Le solde est déduit uniquement à l'approbation de votre responsable.</span>
    </div>

    <!-- Rappel des règles -->
    <div class="rules-box">
      <div class="rules-header">
        <i class="bi bi-clipboard-check"></i> Rappel des règles
      </div>
      <ul class="rules-list">
        <li>Préavis minimum : 48h avant la date de début</li>
        <li>Pas de chevauchement avec une demande en cours</li>
        <li>Solde insuffisant = demande refusée automatiquement</li>
      </ul>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
