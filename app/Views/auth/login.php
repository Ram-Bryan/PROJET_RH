<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<section id="page-login">
    <div class="auth-page geo-bg">
        <div class="auth-split">

            <div class="auth-left">
                <div>
                    <p class="auth-left-brand">TechMada RH<span>Gestion des congés</span></p>
                    <p class="auth-left-text auth-left-text-spaced">
                        <strong>Bienvenue sur votre espace RH.</strong>
                        Gérez vos demandes de congés, consultez votre solde et suivez l'état de vos demandes en temps réel.
                    </p>
                </div>
                <div class="auth-roles">
                    <div class="auth-roles-title">
                        Comptes de démonstration
                    </div>
                    <div class="role-pill">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <div class="role-pill-name">Administrateur</div>
                            <div class="role-pill-cred">admin@techmada.mg · admin123</div>
                        </div>
                    </div>
                    <div class="role-pill">
                        <i class="bi bi-person-check"></i>
                        <div>
                            <div class="role-pill-name">Responsable RH</div>
                            <div class="role-pill-cred">rh@techmada.mg · rh123</div>
                        </div>
                    </div>
                    <div class="role-pill">
                        <i class="bi bi-person"></i>
                        <div>
                            <div class="role-pill-name">Employé</div>
                            <div class="role-pill-cred">employe@techmada.mg · emp123</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-right">
                <p class="auth-title">Connexion</p>
                <p class="auth-sub">Entrez vos identifiants pour accéder à votre espace.</p>

                <?php if ($message = session()->getFlashdata('error')): ?>
                    <div class="flash flash-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <?= esc($message) ?>
                    </div>
                <?php endif; ?>
                <?php if ($message = session()->getFlashdata('success')): ?>
                    <div class="flash flash-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <?= esc($message) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('login') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="f-group">
                        <label class="f-label">Adresse email</label>
                        <input type="email" class="f-input" name="email" placeholder="vous@techmada.mg" value="<?= esc(old('email') ?? '') ?>" />
                        <?php if (isset($errors['email'])): ?>
                            <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Mot de passe</label>
                        <input type="password" class="f-input" name="password" placeholder="••••••••" />
                        <?php if (isset($errors['password'])): ?>
                            <div class="f-error"><i class="bi bi-exclamation-circle"></i> <?= esc($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary auth-submit">
                        Se connecter <i class="bi bi-arrow-right-short"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>
<?= $this->endSection() ?>
