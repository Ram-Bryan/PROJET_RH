# Répartition des tâches — Binôme TechMada RH

> Durée cible : 4h · Découpage pensé pour **zéro conflit GitHub**  
> Principe : chaque personne travaille sur des fichiers/dossiers exclusifs.

---

## Règle d'or anti-conflit

| Zone partagée | Comment gérer |
|--------------|---------------|
| `app/Config/Routes.php` | **Personne A** l'écrit en entier au départ (étape 0), Personne B ne le touche plus |
| `app/Views/layouts/app.php` | **Personne A** le crée, Personne B ne le modifie pas |
| `public/css/app.css` | Copié une fois par Personne A depuis le template — personne ne le retouche |
| `writable/techmada.db` | Fichier binaire → **dans `.gitignore`**, chaque binôme régénère via `php spark migrate && php spark db:seed` |

---

## Vue d'ensemble du découpage

```
PERSONNE A — "Socle & Admin"         PERSONNE B — "Métier & RH"
────────────────────────────         ──────────────────────────
Config, Auth, Migrations             Models métier (Solde, Congé)
Seeder                               Espace employé (demandes)
Layout + CSS                         Espace RH (approbation)
Admin (CRUD employés, types)         Calcul jours & logique solde
Dashboard admin                      Dashboard RH + employé
```

---

## PERSONNE A — Socle, Auth & Admin

### Phase 1 — Setup (20 min)
**Fichiers exclusifs :**
```
app/Config/Database.php
app/Config/Routes.php        ← écrire TOUTES les routes du projet
app/Config/Filters.php       ← enregistrer AuthFilter
app/Filters/AuthFilter.php
app/Database/Migrations/001_create_departements.php
app/Database/Migrations/002_create_types_conge.php
app/Database/Migrations/003_create_employes.php
app/Database/Migrations/004_create_soldes.php
app/Database/Migrations/005_create_conges.php
app/Database/Seeds/MainSeeder.php
writable/.gitkeep
.gitignore                   ← ajouter writable/techmada.db
```

**Livrable :** `php spark migrate && php spark db:seed` tourne sans erreur.  
**Signal à Personne B :** pusher la branche `feat/socle` et prévenir.

---

### Phase 2 — Auth + Layout (40 min)
**Fichiers exclusifs :**
```
app/Controllers/Auth/LoginController.php
app/Views/layouts/auth.php
app/Views/layouts/app.php    ← sidebar dynamique selon rôle
app/Views/auth/login.php
public/css/app.css           ← copie depuis template HTML fourni
```

**Comportement attendu :**
- Login avec email/password → session `user_id`, `role`, `nom`
- Redirection selon rôle : `/employe/dashboard`, `/rh/dashboard`, `/admin/dashboard`
- Logout détruit la session
- AuthFilter redirige vers `/` si non connecté ou rôle insuffisant

---

### Phase 3 — Espace Admin (30 min)
**Fichiers exclusifs :**
```
app/Models/EmployeModel.php
app/Models/DepartementModel.php
app/Models/TypeCongeModel.php
app/Controllers/Admin/EmployeController.php
app/Controllers/Admin/TypeCongeController.php
app/Controllers/Admin/DashboardController.php
app/Views/admin/employes.php
app/Views/admin/types_conge.php
app/Views/admin/dashboard.php
```

**Fonctionnalités :**
- Créer / éditer / désactiver un employé (CRUD)
- Initialiser les soldes automatiquement à la création
- CRUD types de congé
- Dashboard : tableau absences du mois en cours

---

## PERSONNE B — Métier, Employé & RH

> ⚠️ Ne démarrer qu'après que Personne A a pushé `feat/socle` (migrations OK).

### Phase 1 — Models métier (20 min)
**Fichiers exclusifs :**
```
app/Models/SoldeModel.php
app/Models/CongeModel.php
```

**SoldeModel — méthodes clés :**
```php
getRestant(int $employeId, int $typeId, int $annee): int
// retourne jours_attribues - jours_pris

debiter(int $employeId, int $typeId, int $annee, int $nbJours): void
crediter(int $employeId, int $typeId, int $annee, int $nbJours): void
```

**CongeModel — méthodes clés :**
```php
getByEmploye(int $id): array
getPendingForRh(int $rhEmployeId = null): array
hasOverlap(int $employeId, string $debut, string $fin): bool
calculerJoursOuvrables(string $debut, string $fin): int
```

---

### Phase 2 — Espace Employé (60 min)
**Fichiers exclusifs :**
```
app/Controllers/Employe/DashboardController.php
app/Controllers/Employe/CongeController.php
app/Views/employe/dashboard.php
app/Views/employe/conge_form.php
app/Views/employe/conge_list.php
```

**Fonctionnalités :**
- Dashboard : solde restant par type + liste de ses demandes
- Formulaire nouvelle demande : type, date début, date fin, motif
- Validations : date_fin > date_debut, pas de chevauchement, solde suffisant
- Annuler une demande `en_attente`

**Pattern PRG obligatoire sur tous les POST.**

---

### Phase 3 — Espace RH (50 min)
**Fichiers exclusifs :**
```
app/Controllers/Rh/DashboardController.php
app/Controllers/Rh/DemandeController.php
app/Views/rh/dashboard.php
app/Views/rh/demandes.php
```

**Fonctionnalités :**
- Liste toutes les demandes `en_attente` de tous les employés
- Approuver (+ commentaire optionnel) → débite le solde
- Refuser (+ commentaire optionnel) → solde intact
- Filtrer par département ou statut
- Voir solde de chaque employé

---

## Chronologie recommandée (4h)

```
T+0h00  A : crée le repo GitHub, pousse structure vide + .gitignore
        B : fork / clone

T+0h05  A : démarre Phase 1 (migrations + seeder)
        B : lit les specs, prépare ses models sur branche feat/metier

T+0h25  A : pousse feat/socle → merge main
        B : git pull main, démarre ses models

T+1h05  A : démarre feat/auth-layout
        B : pousse feat/models, démarre feat/employe

T+1h45  A : pousse feat/auth-layout → merge main
        B : git pull (pour avoir le layout), continue feat/employe

T+2h05  A : démarre feat/admin
        B : pousse feat/employe → merge main

T+2h35  B : démarre feat/rh
        A : pousse feat/admin → merge main

T+3h25  B : pousse feat/rh → merge main

T+3h30  A+B : tests croisés, corrections, flashdata, README
T+3h55  A+B : démo finale
```

---

## Branches Git

| Branche | Propriétaire |
|---------|-------------|
| `main` | partagée — merge uniquement |
| `feat/socle` | Personne A |
| `feat/auth-layout` | Personne A |
| `feat/admin` | Personne A |
| `feat/models` | Personne B |
| `feat/employe` | Personne B |
| `feat/rh` | Personne B |

**Règle de merge :** toujours faire un `git pull main` avant de pousser une nouvelle branche.

---

## Checklist livrables finaux

- [ ] `php spark migrate` → 5 tables créées sans erreur
- [ ] `php spark db:seed` → 1 admin, 2 employés, 3 types de congé, soldes initialisés
- [ ] Login admin / employé / RH fonctionnel
- [ ] Employé peut soumettre, consulter, annuler une demande
- [ ] RH peut approuver / refuser avec MAJ automatique du solde
- [ ] Admin peut créer / éditer / désactiver un employé
- [ ] Admin dashboard : absences du mois
- [ ] Flashdata succès/erreur sur toutes les actions
- [ ] `README.md` : instructions d'install + comptes de test

---

## README minimal (à compléter)

```markdown
## Installation

git clone ...
cd techmada-rh
composer install
cp env .env        # éditer baseURL et database.default.database

php spark migrate
php spark db:seed

php spark serve

## Comptes de test

| Rôle    | Email                  | Mot de passe |
|---------|------------------------|--------------|
| Admin   | admin@techmada.mg      | admin123     |
| RH      | rh@techmada.mg         | pass123      |
| Employé | soa@techmada.mg        | pass123      |
```