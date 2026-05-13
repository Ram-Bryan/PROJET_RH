-- ============================================================
--  TechMada RH — Base SQLite
--  Fichier : writable/database.sql
--  Usage   : sqlite3 writable/techmada.db < writable/database.sql
-- ============================================================

PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;

-- ============================================================
--  TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS departements (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    nom         VARCHAR(100) NOT NULL,
    description TEXT
);

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS types_conge (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle       VARCHAR(100) NOT NULL,
    jours_annuels INTEGER      NOT NULL DEFAULT 30,
    deductible    TINYINT      NOT NULL DEFAULT 1  -- 1 = déduit du solde, 0 = non déduit
);

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS employes (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    role            VARCHAR(20)  NOT NULL DEFAULT 'employe', -- employe | rh | admin
    departement_id  INTEGER      REFERENCES departements(id),
    date_embauche   DATE         NOT NULL,
    actif           TINYINT      NOT NULL DEFAULT 1
);

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS soldes (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    employe_id      INTEGER NOT NULL REFERENCES employes(id),
    type_conge_id   INTEGER NOT NULL REFERENCES types_conge(id),
    annee           INTEGER NOT NULL,
    jours_attribues INTEGER NOT NULL DEFAULT 0,
    jours_pris      INTEGER NOT NULL DEFAULT 0,
    UNIQUE(employe_id, type_conge_id, annee)
);

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS conges (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    employe_id      INTEGER      NOT NULL REFERENCES employes(id),
    type_conge_id   INTEGER      NOT NULL REFERENCES types_conge(id),
    date_debut      DATE         NOT NULL,
    date_fin        DATE         NOT NULL,
    nb_jours        INTEGER      NOT NULL DEFAULT 0,
    motif           TEXT,
    statut          VARCHAR(20)  NOT NULL DEFAULT 'en_attente', -- en_attente | approuvee | refusee | annulee
    commentaire_rh  TEXT,
    traite_par      INTEGER      REFERENCES employes(id),
    created_at      DATETIME     NOT NULL DEFAULT (datetime('now'))
);

-- ============================================================
--  VUES  (joins utiles, aucune logique métier)
-- ============================================================

-- Vue : demandes de congé avec infos employé, département et type
CREATE VIEW IF NOT EXISTS v_conges_detail AS
SELECT
    c.id,
    c.date_debut,
    c.date_fin,
    c.nb_jours,
    c.motif,
    c.statut,
    c.commentaire_rh,
    c.created_at,
    e.id          AS employe_id,
    e.nom         AS employe_nom,
    e.prenom      AS employe_prenom,
    e.email       AS employe_email,
    d.id          AS departement_id,
    d.nom         AS departement_nom,
    t.id          AS type_conge_id,
    t.libelle     AS type_conge_libelle,
    t.deductible  AS type_deductible,
    rh.nom        AS rh_nom,
    rh.prenom     AS rh_prenom
FROM conges c
JOIN employes    e  ON e.id  = c.employe_id
JOIN types_conge t  ON t.id  = c.type_conge_id
LEFT JOIN departements d  ON d.id  = e.departement_id
LEFT JOIN employes     rh ON rh.id = c.traite_par;

-- ------------------------------------------------------------

-- Vue : soldes avec infos employé et type de congé
CREATE VIEW IF NOT EXISTS v_soldes_detail AS
SELECT
    s.id,
    s.annee,
    s.jours_attribues,
    s.jours_pris,
    (s.jours_attribues - s.jours_pris) AS jours_restant,
    e.id      AS employe_id,
    e.nom     AS employe_nom,
    e.prenom  AS employe_prenom,
    e.email   AS employe_email,
    d.id      AS departement_id,
    d.nom     AS departement_nom,
    t.id      AS type_conge_id,
    t.libelle AS type_conge_libelle
FROM soldes s
JOIN employes    e ON e.id = s.employe_id
JOIN types_conge t ON t.id = s.type_conge_id
LEFT JOIN departements d ON d.id = e.departement_id;

-- ------------------------------------------------------------

-- Vue : liste des employés avec leur département
CREATE VIEW IF NOT EXISTS v_employes_detail AS
SELECT
    e.id,
    e.nom,
    e.prenom,
    e.email,
    e.role,
    e.date_embauche,
    e.actif,
    d.id  AS departement_id,
    d.nom AS departement_nom
FROM employes e
LEFT JOIN departements d ON d.id = e.departement_id;

-- ============================================================
--  DONNÉES INITIALES (Seeder de base)
-- ============================================================

-- Départements
INSERT OR IGNORE INTO departements (id, nom) VALUES
    (1, 'IT'),
    (2, 'Finance'),
    (3, 'Marketing'),
    (4, 'RH');

-- Types de congé
INSERT OR IGNORE INTO types_conge (id, libelle, jours_annuels, deductible) VALUES
    (1, 'Congé annuel', 30, 1),
    (2, 'Congé Maladie', 10, 1),
    (3, 'Congé Special', 5, 0);

-- Comptes utilisateurs  (passwords = password_hash via PHP — à remplacer)

/* 

php -r "echo password_hash('emp123', PASSWORD_DEFAULT) . PHP_EOL;"
admin123
rh123
emp123

*/

INSERT OR IGNORE INTO employes (id, nom, prenom, email, password, role, departement_id, date_embauche) VALUES
    (1, 'Admin',  'System', 'admin@techmada.mg', '$2y$10$RWtUcqCuE739pdCGxrVuV.zYDThDYx7IGzLzqK0S9J8ti7Z5LnDkm', 'admin',  4, '2020-01-01'),
    (2, 'Rakoto', 'Soa',    'employe@techmada.mg',   '$2y$10$UIM6rAlKL9Gbc0Rm1yyOUel5Oy0TZxxxS.NqgAI7p5of3bKH5xEHy',  'employe',1, '2022-03-01'),
    (3, 'Rabe',   'Marie',  'rh@techmada.mg',    '$2y$10$.bU7UOk/z7ItDiPwVzWIDO.F1Is4UZZpVdbqhFUw6FVK.04nc4ogO',  'rh',     4, '2020-01-15');

-- Soldes initiaux année 2025
-- Soa : congé annuel + maladie
-- Marie : congé annuel + maladie

INSERT OR IGNORE INTO soldes (employe_id, type_conge_id, annee, jours_attribues, jours_pris) VALUES
    (2, 1, 2026, 30, 0),
    (2, 2, 2026, 15, 0),
    (2, 3, 2026, 5, 0),
    (3, 1, 2026, 30, 0),
    (3, 2, 2026, 15, 0),
    (3, 3, 2026, 5, 0);