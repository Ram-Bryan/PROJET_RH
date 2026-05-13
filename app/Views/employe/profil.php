<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-briefcase"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace employé</span></div>
</div>
<ul class="sidebar-nav sidebar-nav-spaced">
  <li><a href="<?= site_url('employe/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li><a href="<?= site_url('employe/conges/create') ?>"><i class="bi bi-plus-circle"></i> Nouvelle demande</a></li>
  <li><a href="<?= site_url('employe/conges') ?>"><i class="bi bi-calendar3"></i> Mes demandes</a></li>
  <li><a href="<?= site_url('employe/profil') ?>" class="active"><i class="bi bi-person"></i> Mon profil</a></li>
</ul>
<div class="sidebar-user">
  <div class="s-user-row">
    <div class="avatar av-green"><?= esc($employe['initials']) ?></div>
    <div><div class="user-name"><?= esc(trim($employe['prenom'] . ' ' . $employe['nom'])) ?></div><div class="user-role">Employé<?= $employe['dept'] ? ' · ' . esc($employe['dept']) : '' ?></div></div>
    <a href="<?= site_url('logout') ?>" class="sidebar-logout" title="Déconnexion"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
  <div class="topbar-title">Mon profil</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('employe/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right breadcrumb-sep"></i> Mon profil</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="profil-layout form-layout">

  <div>
    <div class="form-section">
      <h3>Informations personnelles</h3>
      <form action="<?= site_url('employe/profil') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-grid-2 u-mb-1">
          <div class="f-group">
            <label class="f-label">Nom <span class="f-required">*</span></label>
            <input type="text" class="f-input" name="nom" value="<?= esc(old('nom') ?? $employe['nom']) ?>" />
            <?php if (!empty($errors['nom'])): ?>
              <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['nom']) ?></div>
            <?php endif; ?>
          </div>
          <div class="f-group">
            <label class="f-label">Prénom <span class="f-required">*</span></label>
            <input type="text" class="f-input" name="prenom" value="<?= esc(old('prenom') ?? $employe['prenom']) ?>" />
            <?php if (!empty($errors['prenom'])): ?>
              <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['prenom']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="f-group u-mb-1">
          <label class="f-label">Adresse email <span class="f-required">*</span></label>
          <input type="email" class="f-input" name="email" value="<?= esc(old('email') ?? $employe['email']) ?>" />
          <?php if (!empty($errors['email'])): ?>
            <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="f-group u-mb-1">
          <label class="f-label">Nouveau mot de passe</label>
          <input type="password" class="f-input" name="password" placeholder="Laisser vide pour conserver" />
          <?php if (!empty($errors['password'])): ?>
            <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['password']) ?></div>
          <?php endif; ?>
          <div class="f-hint">Minimum 6 caractères.</div>
        </div>

        <div class="form-actions">
          <button class="btn-forest" type="submit"><i class="bi bi-check-lg"></i> Enregistrer</button>
          <a href="<?= site_url('employe/dashboard') ?>" class="btn-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
        </div>
      </form>
    </div>
  </div>

  <div>
    <div class="data-card card-no-margin">
      <div class="data-card-head"><h3>Résumé</h3></div>
      <div class="profil-resume">
        <div class="profile-row">
          <div class="avatar av-green"><?= esc($employe['initials']) ?></div>
          <div class="profile-info">
            <div class="pname"><?= esc(trim($employe['prenom'] . ' ' . $employe['nom'])) ?></div>
            <div class="pdept"><?= esc($employe['dept'] !== '' ? $employe['dept'] : $employe['email']) ?></div>
          </div>
        </div>
        <div class="inline-stats profil-stats">
          <div class="inline-stat"><i class="bi bi-envelope"></i> <strong><?= esc($employe['email']) ?></strong></div>
        </div>
      </div>
    </div>
    <div class="flash flash-info profil-info">
      <i class="bi bi-info-circle-fill"></i>
      <span class="td-note-80">Vos informations sont visibles par les RH et l'administration.</span>
    </div>
  </div>

</div>
<?= $this->endSection() ?>
