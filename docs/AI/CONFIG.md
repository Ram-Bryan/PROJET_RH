# CLAUDE.md — Règles du projet TechMada RH

> Ce fichier configure le comportement de l'assistant IA pour ce projet.
> Toutes les règles sont obligatoires. Aucune exception sans discussion explicite.

---

## Contexte du projet

- **Framework :** CodeIgniter 4
- **Base de données :** SQLite (driver SQLite3)
- **Projet :** Système de gestion des congés RH interne (TechMada)
- **Rôles :** `employe`, `rh`, `admin`

---

## 1. Architecture MVC — Séparation stricte

### Controllers
- Un controller ne contient **aucune requête SQL directe** (pas de `$this->db->...` dans un controller)
- Un controller appelle uniquement des méthodes de Model, puis passe les données à la View
- La logique métier (calculs, validations métier) appartient au Model, pas au controller
- Chaque controller correspond à **un seul rôle ou une seule entité**
- Appliquer systématiquement le **pattern PRG** (Post → Redirect → Get) sur tout formulaire POST

### Models
- Chaque Model correspond à **une seule table** principale
- Les requêtes sont écrites dans le Model via le **Query Builder CI4** — jamais de SQL brut sauf cas documenté
- Les méthodes d'un Model sont nommées de façon explicite (`getByEmploye`, `debiterSolde`, `hasOverlap`)
- Les jointures simples de lecture peuvent utiliser les **vues SQL** définies dans `database.sql`
- Un Model ne manipule **jamais la session** ni ne génère de réponse HTTP

### Views
- Une View ne contient **aucune logique métier** : pas de calcul, pas de requête, pas de condition complexe
- Les conditions autorisées dans une View : affichage conditionnel simple (`if $statut === 'approuvee'`), boucles `foreach`
- Les données sont **toujours préparées et transmises par le controller** avant d'atteindre la View
- Pas d'appel à un Model depuis une View
- Pas de fonction PHP définie à l'intérieur d'un fichier View

---

## 2. Structure des fichiers — Emplacement strict

```
app/Controllers/Auth/
app/Controllers/Employe/
app/Controllers/Rh/
app/Controllers/Admin/

app/Models/          ← un fichier par table

app/Views/layouts/   ← app.php et auth.php uniquement
app/Views/auth/
app/Views/employe/
app/Views/rh/
app/Views/admin/

app/Filters/         ← AuthFilter uniquement

app/Database/Migrations/
app/Database/Seeds/

public/assets/css/   ← tous les fichiers CSS
public/assets/js/    ← tous les fichiers JS
public/assets/icons/ ← icônes locales (ne pas utiliser de CDN pour les icônes)
```

- **Jamais** de CSS inline dans une View sauf attribut `style` de positionnement ponctuel inévitable
- **Jamais** de `<script>` avec du code JS dans une View — tout JS va dans `public/assets/js/`
- Les imports CSS et JS se font **uniquement dans les layouts** (`layouts/app.php`, `layouts/auth.php`)

---

## 3. Fonctionnalités CI4 — À utiliser en priorité

- **Validation :** utiliser `$this->validate()` ou le service `\Config\Services::validation()` avec des règles déclarées — pas de validation manuelle avec des `if`
- **Sessions :** utiliser le service session CI4 (`session()` ou `$this->session`) — pas de `$_SESSION` direct
- **Flashdata :** utiliser `session()->setFlashdata()` pour tous les messages succès/erreur — pas d'affichage direct dans le controller
- **Filtres :** utiliser le système de filtres CI4 (`app/Filters/`) pour l'authentification et la vérification des rôles — pas de vérification de session au début de chaque méthode controller
- **Helpers :** déclarer les helpers nécessaires (`url_helper`, `form_helper`) dans `BaseController` ou dans le controller concerné — pas de `require` manuel
- **Routing groupé :** les routes sont organisées par groupe avec `filter` associé dans `app/Config/Routes.php`
- **Query Builder :** utiliser exclusivement le Query Builder CI4 pour toutes les requêtes — pas de SQL brut sauf pour les vues SQL définies dans `database.sql`

---

## 4. Sécurité

- **Mots de passe :** `password_hash()` à l'écriture, `password_verify()` à la vérification — aucune autre méthode
- **CSRF :** activé sur tous les formulaires POST via la configuration CI4 — le token est injecté via `csrf_field()` dans chaque formulaire
- **Rôles :** la vérification du rôle se fait **uniquement dans `AuthFilter`** — jamais dans un controller ou une View
- **Données utilisateur :** toujours utiliser les méthodes CI4 (`$request->getPost()`, `$request->getVar()`) — jamais `$_POST` ou `$_GET` directs

---

## 5. Simplicité du code

- Préférer la solution la plus simple qui répond au besoin — pas de sur-ingénierie
- Pas de classes abstraites, interfaces ou traits sauf si la duplication de code l'impose vraiment
- Pas de design patterns avancés (Repository, Service Layer, etc.) — MVC CI4 natif suffit
- Une méthode fait **une seule chose** — si une méthode dépasse 30 lignes, la découper
- Les noms de variables, méthodes et fichiers sont en **français ou anglais mais jamais mixés** dans un même fichier
- Pas de commentaires évidents — commenter uniquement ce qui n'est pas immédiatement lisible

---

## 6. Frontend

- Le CSS du projet est dans `public/assets/css/app.css` — **un seul fichier CSS principal**
- Le JS est découpé par contexte fonctionnel dans `public/assets/js/` (ex: `conge.js`, `admin.js`)
- Les icônes viennent de `public/assets/icons/` — pas d'appel réseau pour les icônes
- Bootstrap est chargé via CDN **dans le layout uniquement**
- Pas de jQuery — JS vanilla uniquement si du JS est nécessaire
- Le JS ne contient **aucune logique métier** — uniquement de l'amélioration d'interface (affichage, UX)

---

## 7. Base de données

- Le schéma de référence est `writable/database.sql` — toute modification de schéma passe par une nouvelle Migration CI4
- Les vues SQL (`v_conges_detail`, `v_soldes_detail`, `v_employes_detail`) sont en lecture seule — jamais d'INSERT/UPDATE sur une vue
- `PRAGMA foreign_keys = ON` est activé — les relations doivent être respectées
- Le fichier `writable/techmada.db` est dans `.gitignore` — la base se régénère via `php spark migrate && php spark db:seed`

---

## 8. Ce que l'assistant ne doit jamais faire

- Écrire du SQL brut dans un controller ou une View
- Créer une méthode `getAll()` générique sans critère dans un Model
- Mettre de la logique de redirection ou de session dans un Model
- Proposer une solution qui court-circuite le système de filtres CI4 pour l'auth
- Générer du code mort ou des fonctions non utilisées
- Modifier `public/assets/css/app.css` pour une seule vue — utiliser des classes existantes en priorité