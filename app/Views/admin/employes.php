<?= $this->extend('layouts/app') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-brand">
    <div class="sidebar-logo-icon" style="background:var(--ink);border:1px solid rgba(255,255,255,.15)">
        <i class="bi bi-shield-check" style="color:var(--leaf)"></i>
    </div>
    <div class="sidebar-brand-name">TechMada RH<span>Administration</span></div>
</div>
<div class="sidebar-section">Gestion</div>
<ul class="sidebar-nav">
    <li><a href="<?= site_url('admin/dashboard') ?>"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a></li>
    <li><a href="#"><i class="bi bi-inbox"></i> Toutes les demandes</a></li>
    <li><a href="<?= site_url('admin/employes') ?>" class="active"><i class="bi bi-people"></i> Employes</a></li>
    <li><a href="#"><i class="bi bi-building"></i> Departements</a></li>
    <li><a href="#"><i class="bi bi-tags"></i> Types de conge</a></li>
</ul>
<div class="sidebar-user">
    <?php
    $prenom = (string) (session('prenom') ?? '');
    $nom = (string) (session('nom') ?? '');
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    $initials = $initials !== '' ? $initials : 'AD';
    ?>
    <div class="s-user-row">
        <div class="avatar" style="background:#5a2d82;width:32px;height:32px;font-size:.7rem"><?= esc($initials) ?></div>
        <div>
            <div class="user-name"><?= esc(trim($prenom . ' ' . $nom)) ?></div>
            <div class="user-role">Administrateur</div>
        </div>
        <a href="<?= site_url('logout') ?>" style="margin-left:auto;color:rgba(255,255,255,.25);font-size:1.1rem" title="Deconnexion">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<div>
    <div class="topbar-title">Gestion des employes</div>
    <div class="topbar-breadcrumb"><a href="<?= site_url('admin/dashboard') ?>">Admin</a> <i class="bi bi-chevron-right" style="font-size:.6rem"></i> Employes</div>
</div>
<div class="topbar-actions">
    <a href="#form-add" class="btn-forest" style="padding:7px 14px;font-size:.82rem"><i class="bi bi-person-plus"></i> Ajouter</a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<div class="form-section" id="form-add">
    <h3><i class="bi bi-person-plus" style="color:var(--forest);margin-right:6px"></i>Ajouter un employe</h3>
    <form action="<?= site_url('admin/employes/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-grid-2" style="margin-bottom:1rem">
            <div class="f-group">
                <label class="f-label">Prenom</label>
                <input type="text" class="f-input" name="prenom" placeholder="Jean" value="<?= esc(old('prenom') ?? '') ?>" />
                <?php if (isset($errors['prenom'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['prenom']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Nom</label>
                <input type="text" class="f-input" name="nom" placeholder="Rakoto" value="<?= esc(old('nom') ?? '') ?>" />
                <?php if (isset($errors['nom'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['nom']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Email</label>
                <input type="email" class="f-input" name="email" placeholder="jean.rakoto@techmada.mg" value="<?= esc(old('email') ?? '') ?>" />
                <?php if (isset($errors['email'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['email']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Mot de passe initial</label>
                <input type="password" class="f-input" name="password" placeholder="A communiquer a l'employe" />
                <?php if (isset($errors['password'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['password']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Departement</label>
                <select class="f-select" name="departement_id">
                    <option value="">-- Aucun --</option>
                    <?php foreach ($departements as $departement): ?>
                        <option value="<?= esc($departement['id']) ?>" <?= (string) old('departement_id') === (string) $departement['id'] ? 'selected' : '' ?>>
                            <?= esc($departement['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['departement_id'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['departement_id']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Role</label>
                <select class="f-select" name="role">
                    <?php $selectedRole = old('role') ?? 'employe'; ?>
                    <option value="employe" <?= $selectedRole === 'employe' ? 'selected' : '' ?>>Employe</option>
                    <option value="rh" <?= $selectedRole === 'rh' ? 'selected' : '' ?>>Responsable RH</option>
                    <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['role']) ?></div>
                <?php endif; ?>
            </div>
            <div class="f-group">
                <label class="f-label">Date d'embauche</label>
                <input type="date" class="f-input" name="date_embauche" value="<?= esc(old('date_embauche') ?? date('Y-m-d')) ?>" />
                <?php if (isset($errors['date_embauche'])): ?>
                    <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['date_embauche']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="flash flash-info" style="margin-bottom:1rem">
            <i class="bi bi-info-circle-fill"></i>
            <span style="font-size:.82rem">Les soldes de conges seront initialises automatiquement selon les types de conge configures.</span>
        </div>
        <div class="form-actions">
            <button class="btn-forest" type="submit"><i class="bi bi-plus"></i> Creer l'employe</button>
            <button class="btn-secondary" type="reset">Reinitialiser</button>
        </div>
    </form>
</div>

<div class="data-card">
    <div class="data-card-head">
        <h3>Tous les employes</h3>
        <div style="display:flex;gap:6px">
            <input type="text" class="f-input" placeholder="Rechercher..." style="width:200px;padding:6px 10px;font-size:.8rem" />
            <select class="f-select" style="font-size:.8rem;padding:6px 10px;width:auto">
                <option>Tous les depts</option>
                <?php foreach ($departements as $departement): ?>
                    <option value="<?= esc($departement['id']) ?>"><?= esc($departement['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <table class="tbl">
        <thead>
            <tr><th>Employe</th><th>Departement</th><th>Role</th><th>Embauche</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($employes as $employe): ?>
                <tr>
                    <td>
                        <div class="profile-row">
                            <div class="avatar av-green" style="width:32px;height:32px;font-size:.68rem">
                                <?= esc(strtoupper(substr($employe['prenom'], 0, 1) . substr($employe['nom'], 0, 1))) ?>
                            </div>
                            <div class="profile-info">
                                <div class="pname"><?= esc($employe['prenom'] . ' ' . $employe['nom']) ?></div>
                                <div class="pdept"><?= esc($employe['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="td-muted"><?= esc($employe['departement_nom'] ?? '—') ?></td>
                    <td><span class="type-badge"><?= esc($employe['role']) ?></span></td>
                    <td class="td-muted td-mono" style="font-size:.78rem"><?= esc($employe['date_embauche']) ?></td>
                    <td>
                        <?php if ((int) $employe['actif'] === 1): ?>
                            <span class="statut s-approuvee" style="font-size:.68rem">actif</span>
                        <?php else: ?>
                            <span class="statut s-annulee" style="font-size:.68rem">inactif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-sm btn-edit" type="button"><i class="bi bi-pencil"></i> Editer</button>
                            <?php if ((int) $employe['actif'] === 1): ?>
                                <button class="btn-sm btn-del" type="button"><i class="bi bi-slash-circle"></i></button>
                            <?php else: ?>
                                <button class="btn-sm btn-view" type="button"><i class="bi bi-arrow-counterclockwise"></i> Reactiver</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
