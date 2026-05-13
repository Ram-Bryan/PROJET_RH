<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
  <div class="sidebar-logo-icon"><i class="bi bi-person-check"></i></div>
  <div class="sidebar-brand-name">TechMada RH<span>Espace responsable</span></div>
</div>
<div class="sidebar-section">Menu</div>
<ul class="sidebar-nav">
  <li><a href="<?= site_url('rh/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
  <li>
    <a href="<?= site_url('rh/demandes') ?>" class="active">
      <i class="bi bi-inbox"></i> Demandes à traiter
      <?php if (($counts['en_attente'] ?? 0) > 0): ?>
        <span class="nav-badge alert"><?= esc((string) $counts['en_attente']) ?></span>
      <?php endif; ?>
    </a>
  </li>
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
    <a href="<?= site_url('logout') ?>" class="sidebar-logout" title="Déconnexion">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
  <div class="topbar-title">Demandes à traiter</div>
  <div class="topbar-breadcrumb"><a href="<?= site_url('rh/dashboard') ?>">Accueil</a> <i class="bi bi-chevron-right breadcrumb-sep"></i> Demandes</div>
</div>
<div class="topbar-actions">
  <span class="topbar-pill-warn">
    <i class="bi bi-hourglass-split"></i> <?= esc((string) ($counts['en_attente'] ?? 0)) ?> en attente
  </span>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="data-card">
  <div class="data-card-head">
    <h3>Toutes les demandes</h3>
    <form method="get" action="<?= site_url('rh/demandes') ?>" class="filter-form">
      <select class="f-select f-compact" name="statut">
        <option value="" <?= $statutActif === '' ? 'selected' : '' ?>>Tous (<?= esc((string) ($counts['total'] ?? 0)) ?>)</option>
        <option value="en_attente" <?= $statutActif === 'en_attente' ? 'selected' : '' ?>>En attente (<?= esc((string) ($counts['en_attente'] ?? 0)) ?>)</option>
        <option value="approuvee" <?= $statutActif === 'approuvee' ? 'selected' : '' ?>>Approuvées (<?= esc((string) ($counts['approuvee'] ?? 0)) ?>)</option>
        <option value="refusee" <?= $statutActif === 'refusee' ? 'selected' : '' ?>>Refusées (<?= esc((string) ($counts['refusee'] ?? 0)) ?>)</option>
        <option value="annulee" <?= $statutActif === 'annulee' ? 'selected' : '' ?>>Annulées (<?= esc((string) ($counts['annulee'] ?? 0)) ?>)</option>
      </select>

      <select class="f-select f-compact" name="departement_id">
        <option value="">Tous les départements</option>
        <?php foreach ($departements as $dept): ?>
          <option value="<?= esc((string) $dept['id']) ?>" <?= (int) $departementIdActif === (int) $dept['id'] ? 'selected' : '' ?>>
            <?= esc($dept['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn-secondary btn-xs" type="submit"><i class="bi bi-funnel"></i> Filtrer</button>
      <a class="btn-secondary btn-xs" href="<?= site_url('rh/demandes') ?>"><i class="bi bi-x"></i> Reset</a>
    </form>
  </div>

  <table class="tbl">
    <thead>
      <tr><th>Employé</th><th>Type</th><th>Période</th><th>Durée</th><th>Solde dispo</th><th>Statut</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (!$demandes): ?>
        <tr><td class="td-muted" colspan="7">Aucune demande trouvée.</td></tr>
      <?php else: ?>
        <?php foreach ($demandes as $demande): ?>
          <tr>
            <td>
              <div class="profile-row">
                <div class="avatar av-green avatar-32"><?= esc($demande['initials']) ?></div>
                <div class="profile-info">
                  <div class="pname"><?= esc($demande['employe']) ?></div>
                  <div class="pdept"><?= esc($demande['departement']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="type-badge <?= esc($demande['typeBadge']) ?>"><?= esc($demande['type']) ?></span></td>
            <td class="td-muted td-note-80"><?= esc($demande['periode']) ?></td>
            <td class="td-mono"><?= esc((string) $demande['nbJours']) ?> j</td>
            <td>
              <?php if ($demande['soldeRestant'] === null): ?>
                <span class="td-muted">—</span>
              <?php else: ?>
                <span class="solde-restant <?= esc($demande['soldeClass']) ?>"><?= esc((string) $demande['soldeRestant']) ?> j</span>
              <?php endif; ?>
            </td>
            <td><span class="statut <?= esc($demande['statutBadge']) ?>"><?= esc($demande['statutLabel']) ?></span></td>
            <td>
              <?php if (in_array($demande['statut'], ['en_attente', 'en attente'], true)): ?>
                <div class="action-btns">
                  <a class="btn-sm btn-approve<?= !$demande['approvable'] ? ' btn-disabled' : '' ?>"
                     href="<?= site_url('rh/demandes') . '?' . http_build_query(['focus' => $demande['id'], 'action' => 'approve', 'statut' => $statutActif, 'departement_id' => $departementIdActif]) ?>"
                     aria-disabled="<?= !$demande['approvable'] ? 'true' : 'false' ?>">
                    <i class="bi bi-check-lg"></i> Approuver
                  </a>
                  <a class="btn-sm btn-refuse"
                     href="<?= site_url('rh/demandes') . '?' . http_build_query(['focus' => $demande['id'], 'action' => 'refuse', 'statut' => $statutActif, 'departement_id' => $departementIdActif]) ?>">
                    <i class="bi bi-x-lg"></i> Refuser
                  </a>
                </div>
              <?php else: ?>
                <span class="td-muted td-note-75"><?= $demande['traitePar'] !== '' ? 'Traité par ' . esc($demande['traitePar']) : '—' ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($focus): ?>
  <div class="form-section <?= $focus['action'] === 'refuse' ? 'form-section-refuse' : 'form-section-approve' ?>">
    <h3 class="<?= $focus['action'] === 'refuse' ? 'form-title-refuse' : 'form-title-approve' ?>">
      <?php if ($focus['action'] === 'refuse'): ?>
        <i class="bi bi-x-circle"></i> Confirmer le refus — <?= esc($focus['employe']) ?>
      <?php else: ?>
        <i class="bi bi-check-circle"></i> Confirmer l'approbation — <?= esc($focus['employe']) ?>
      <?php endif; ?>
    </h3>

    <div class="td-muted u-mb-1">
      Demande de <strong><?= esc((string) $focus['nbJours']) ?> jours</strong> · <?= esc($focus['periode']) ?> · Type : <?= esc($focus['type']) ?>
      <?php if ($focus['action'] === 'approve' && !$focus['soldeSuffisant']): ?>
        <div class="flash flash-warn u-mt-75">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span>Solde insuffisant pour approuver cette demande.</span>
        </div>
      <?php endif; ?>
    </div>

    <form method="post" action="<?= site_url('rh/demandes/' . ($focus['action'] === 'approve' ? 'approuver/' : 'refuser/') . $focus['id']) ?>">
      <?= csrf_field() ?>
      <div class="f-group">
        <label class="f-label">Commentaire pour l'employé (optionnel)</label>
        <textarea class="f-textarea" name="commentaire" placeholder="Ex : Merci de fournir un justificatif, ou précisions sur le solde..."></textarea>
      </div>
      <div class="form-actions">
        <?php if ($focus['action'] === 'refuse'): ?>
          <button class="btn-sm btn-refuse action-confirm" type="submit"><i class="bi bi-x-lg"></i> Confirmer le refus</button>
        <?php else: ?>
          <button class="btn-sm btn-approve action-confirm<?= !$focus['soldeSuffisant'] ? ' action-confirm-disabled' : '' ?>" type="submit" <?= !$focus['soldeSuffisant'] ? 'disabled' : '' ?>><i class="bi bi-check-lg"></i> Confirmer l'approbation</button>
        <?php endif; ?>
        <a class="btn-secondary action-confirm" href="<?= site_url('rh/demandes') ?>"><i class="bi bi-arrow-left"></i> Annuler</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
