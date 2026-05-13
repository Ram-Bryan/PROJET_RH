# Conception — TechMada RH · CI4 + SQLite

---
# Rôles utilisateurs

## Employé

### Fonctionnalités

- Se connecter
- Voir son solde
- Soumettre une demande de congé
- Voir ses demandes
- Annuler une demande en attente

---

## RH

### Fonctionnalités

- Voir toutes les demandes
- Approuver une demande
- Refuser une demande
- Ajouter un commentaire RH
- Mettre à jour automatiquement les soldes

---

## Administrateur

### Fonctionnalités

- Gérer les employés
- CRUD utilisateurs
- Tableau de bord
- Gérer les types de congés

---
## 1. Architecture générale

```
techmada-rh/
├── app/
│   ├── Config/
│   │   ├── Routes.php
│   │   └── Auth.php            ← filtre d'auth custom
│   ├── Filters/
│   │   └── AuthFilter.php      ← vérifie session + rôle
│   ├── Models/
│   │   ├── EmployeModel.php
│   │   ├── DepartementModel.php
│   │   ├── TypeCongeModel.php
│   │   ├── SoldeModel.php
│   │   └── CongeModel.php
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── LoginController.php
│   │   ├── Employe/
│   │   │   ├── DashboardController.php
│   │   │   └── CongeController.php
│   │   ├── Rh/
│   │   │   ├── DashboardController.php
│   │   │   └── DemandeController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── EmployeController.php
│   │       └── TypeCongeController.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── app.php          ← layout principal (sidebar + topbar)
│   │   │   └── auth.php         ← layout login
│   │   ├── auth/
│   │   │   └── login.php
│   │   ├── employe/
│   │   │   ├── dashboard.php
│   │   │   ├── conge_form.php
│   │   │   └── conge_list.php
│   │   ├── rh/
│   │   │   ├── dashboard.php
│   │   │   └── demandes.php
│   │   └── admin/
│   │       ├── dashboard.php
│   │       ├── employes.php
│   │       └── types_conge.php
│   └── Database/
│       ├── Migrations/
│       │   ├── 001_create_departements.php
│       │   ├── 002_create_types_conge.php
│       │   ├── 003_create_employes.php
│       │   ├── 004_create_soldes.php
│       │   └── 005_create_conges.php
│       └── Seeds/
│           └── MainSeeder.php
├── writable/
│   └── database/techmada.db             ← base SQLite
└── public/
```

---

## 2. Schéma de base de données

### Table `departements`
| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | INTEGER | PK AUTOINCREMENT |
| nom | VARCHAR(100) | NOT NULL |
| description | TEXT | nullable |

### Table `types_conge`
| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | INTEGER | PK AUTOINCREMENT |
| libelle | VARCHAR(100) | NOT NULL |
| jours_annuels | INTEGER | NOT NULL DEFAULT 30 |
| deductible | TINYINT | DEFAULT 1 (0=non déduit) |

### Table `employes`
| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | INTEGER | PK AUTOINCREMENT |
| nom | VARCHAR(100) | NOT NULL |
| prenom | VARCHAR(100) | NOT NULL |
| email | VARCHAR(150) | UNIQUE NOT NULL |
| password | VARCHAR(255) | NOT NULL (password_hash) |
| role | VARCHAR(20) | DEFAULT 'employe' — enum: employe/rh/admin |
| departement_id | INTEGER | FK → departements.id |
| date_embauche | DATE | NOT NULL |
| actif | TINYINT | DEFAULT 1 |

### Table `soldes`
| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | INTEGER | PK AUTOINCREMENT |
| employe_id | INTEGER | FK → employes.id |
| type_conge_id | INTEGER | FK → types_conge.id |
| annee | INTEGER | NOT NULL (ex: 2025) |
| jours_attribues | INTEGER | NOT NULL |
| jours_pris | INTEGER | DEFAULT 0 |

> **Règle métier :** `nb_jours_restant = jours_attribues − jours_pris` — jamais stocké.

### Table `conges`
| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | INTEGER | PK AUTOINCREMENT |
| employe_id | INTEGER | FK → employes.id |
| type_conge_id | INTEGER | FK → types_conge.id |
| date_debut | DATE | NOT NULL |
| date_fin | DATE | NOT NULL |
| nb_jours | INTEGER | NOT NULL (calculé à la soumission) |
| motif | TEXT | nullable |
| statut | VARCHAR(20) | DEFAULT 'en_attente' — enum: en_attente/approuvee/refusee/annulee |
| commentaire_rh | TEXT | nullable |
| created_at | DATETIME | NOT NULL |
| traite_par | INTEGER | FK → employes.id — nullable |

---

## 3. Routing (Routes.php)

```php
// Public
$routes->get('/', 'Auth\LoginController::index');
$routes->post('/login', 'Auth\LoginController::login');
$routes->get('/logout', 'Auth\LoginController::logout');

// Groupe employé — filtre 'auth:employe'
$routes->group('employe', ['filter' => 'auth:employe'], function($routes) {
    $routes->get('dashboard', 'Employe\DashboardController::index');
    $routes->get('conges', 'Employe\CongeController::index');
    $routes->get('conges/new', 'Employe\CongeController::create');
    $routes->post('conges/store', 'Employe\CongeController::store');
    $routes->post('conges/(:num)/annuler', 'Employe\CongeController::annuler/$1');
});

// Groupe RH — filtre 'auth:rh'
$routes->group('rh', ['filter' => 'auth:rh'], function($routes) {
    $routes->get('dashboard', 'Rh\DashboardController::index');
    $routes->get('demandes', 'Rh\DemandeController::index');
    $routes->post('demandes/(:num)/approuver', 'Rh\DemandeController::approuver/$1');
    $routes->post('demandes/(:num)/refuser', 'Rh\DemandeController::refuser/$1');
});

// Groupe Admin — filtre 'auth:admin'
$routes->group('admin', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('employes', 'Admin\EmployeController::index');
    $routes->post('employes/store', 'Admin\EmployeController::store');
    $routes->post('employes/(:num)/edit', 'Admin\EmployeController::edit/$1');
    $routes->post('employes/(:num)/desactiver', 'Admin\EmployeController::desactiver/$1');
    $routes->get('types-conge', 'Admin\TypeCongeController::index');
    $routes->post('types-conge/store', 'Admin\TypeCongeController::store');
});
```

---

## 4. AuthFilter — logique de rôle

```php
// app/Filters/AuthFilter.php
public function before(RequestInterface $request, $arguments = null)
{
    $session = session();
    if (!$session->has('user_id')) {
        return redirect()->to('/');
    }
    if ($arguments) {
        $roleRequis = $arguments[0];
        $roleUser   = $session->get('role');
        // admin accède à tout, rh accède à rh+employe
        $acces = match($roleRequis) {
            'employe' => in_array($roleUser, ['employe','rh','admin']),
            'rh'      => in_array($roleUser, ['rh','admin']),
            'admin'   => $roleUser === 'admin',
            default   => false,
        };
        if (!$acces) return redirect()->to('/');
    }
}
```

---

## 5. Logique métier — Calcul des jours

```php
// Dans CongeController::store()
$debut    = new DateTime($data['date_debut']);
$fin      = new DateTime($data['date_fin']);
$nbJours  = 0;
$current  = clone $debut;
while ($current <= $fin) {
    $dow = (int)$current->format('N'); // 1=Lun … 7=Dim
    if ($dow < 6) $nbJours++;          // exclure sam+dim
    $current->modify('+1 day');
}
// Validations bloquantes
if ($debut >= $fin)       → flash erreur
if ($nbJours === 0)       → flash erreur
// Vérif chevauchement (2 demandes actives aux mêmes dates)
// Vérif solde : jours_pris + $nbJours <= jours_attribues
```

```php
// Dans RH DemandeController::approuver()
// Vérif solde une dernière fois (sécurité)
UPDATE soldes SET jours_pris = jours_pris + $nb_jours
WHERE employe_id = ? AND type_conge_id = ? AND annee = YEAR(NOW())

// Dans DemandeController::refuser() — si était approuvée avant
UPDATE soldes SET jours_pris = jours_pris - $nb_jours ...
```

---

## 6. Seeder initial

| Entité | Données |
|--------|---------|
| Départements | IT, Finance, Marketing, RH |
| Types de congé | Congé annuel (30j), Maladie (15j), Sans solde (∞ non déduit) |
| Admin | admin@techmada.mg / admin123 — rôle: admin |
| Employés | soa@techmada.mg / pass123 — rôle: employe (IT) |
|  | rh@techmada.mg / pass123 — rôle: rh (RH) |
| Soldes | Initialisés pour l'année en cours pour les 2 employés |

---

## 7. Vues & layout

- **Layout** `app.php` : sidebar (liens dynamiques selon `session('role')`) + topbar + `$this->renderSection('content')`
- **Flashdata** CI4 : `session()->setFlashdata('success', '...')` → affiché dans le layout
- **CSS** : tout dans `public/css/app.css` copié depuis le template fourni — aucun JavaScript métier
- **Pattern PRG** : chaque POST redirige après écriture (évite double-submit sur F5)

---

## 8. Configuration SQLite (app/Config/Database.php)

```php
'default' => [
    'DSN'      => '',
    'hostname' => '',
    'username' => '',
    'password' => '',
    'database' => WRITEPATH . 'techmada.db',
    'DBDriver' => 'SQLite3',
    'DBPrefix' => '',
    'pConnect' => false,
    'DBDebug'  => true,
    'charset'  => 'utf8',
    'DBCollat' => '',
]
```