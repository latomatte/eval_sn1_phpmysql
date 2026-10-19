# Évaluation PHP / MySQL – Agenda d'événements

Application de gestion d'événements (concerts, conférences, ateliers, sport) à développer en **PHP** (sans framework) avec une base **MySQL**.

Ce qui est **déjà fait** :

- **Le design** : toutes les pages existent en HTML/CSS statique (les fichiers `.html` à la racine). Vous n'avez **pas à toucher au CSS**.
- **La base de données** : le fichier `database/agenda.sql` crée la base `agenda` avec les tables `user` et `event`, des événements de test et deux comptes.

Votre travail : transformer ces pages statiques en pages PHP dynamiques (requêtes SQL, formulaires, sessions…) et **créer vous-même la table des réservations**.

---

## ⚠️ Avant de commencer : forkez le dépôt

> **Ne commitez et ne poussez JAMAIS sur ce dépôt.** Vous travaillez uniquement sur **votre fork**.

Rendez-vous sur <https://github.com/louvelmathieu/eval_sn1_phpmysql> et cliquez sur le bouton **Fork** en haut à droite.

> Votre fork doit être **public** : c'est lui qui sera corrigé.

Ensuite, choisissez **une** des deux méthodes ci-dessous pour récupérer le projet sur votre ordinateur.

### Méthode 1 : avec Git

1. Clonez **votre fork** (et non le dépôt d'origine) dans le dossier de votre serveur (`htdocs/` ou `www/`) :

   ```bash
   git clone https://github.com/<votre-compte>/eval_sn1_phpmysql.git
   cd eval_sn1_phpmysql
   ```

2. Vérifiez que `origin` pointe bien vers **votre** compte :

   ```bash
   git remote -v
   ```

3. Commitez et poussez régulièrement, avec des messages clairs :

   ```bash
   git add .
   git commit -m "Page d'accueil dynamique"
   git push
   ```

### Méthode 2 : sans Git (téléchargement puis upload sur GitHub)

1. **Télécharger le projet** : sur la page de **votre fork**, cliquez sur le bouton vert **Code** puis **Download ZIP**. Dézippez l'archive dans le dossier de votre serveur (`htdocs/` ou `www/`).

   > Le dossier dézippé s'appelle `eval_sn1_phpmysql-main` : renommez-le en `eval_sn1_phpmysql`.

2. **Envoyer votre travail sur GitHub** : sur la page de votre fork, cliquez sur **Add file** → **Upload files**, glissez-déposez vos fichiers (`.php`, `README.md`…) puis cliquez sur **Commit changes**.

   > Pour un fichier dans un sous-dossier (ex. `database/rendu.sql`), ouvrez d'abord le dossier `database` sur GitHub, puis faites **Add file** → **Upload files** depuis ce dossier.

   Un fichier envoyé avec le même nom qu'un fichier existant le remplace. Envoyez votre travail **régulièrement**, pas seulement à la fin.

---

## 📦 Rendu

Le rendu se fait sur **votre fork**. Avant la date limite :

1. Complétez ce bloc dans le `README.md` (remplacez les `...`) :

   ```text
   Nom       : ...
   Prénom    : ...
   ```

   > La correction se fera avec les comptes de test fournis (`admin@agenda.fr` et `user@agenda.fr`) : ne les supprimez pas et ne changez pas leur mot de passe.

2. Ajoutez le fichier **`database/reservation.sql`** contenant la requête `CREATE TABLE` de votre table des réservations.

3. Exportez **toute votre base** (avec vos utilisateurs, événements et réservations de test) dans **`database/rendu.sql`** :
   dans phpMyAdmin, sélectionnez la base `agenda` → onglet **Exporter** → **Exécuter**, puis enregistrez le fichier sous ce nom.

4. Envoyez le tout sur votre fork :
   - **Méthode 1 (Git)** : `git add .`, `git commit -m "Rendu"`, `git push`
   - **Méthode 2 (sans Git)** : **Add file** → **Upload files** sur GitHub, pour chaque fichier modifié ou ajouté

> Vérifiez sur GitHub que vos fichiers `.php`, `database/reservation.sql` et `database/rendu.sql` apparaissent bien dans votre fork.


---

## Installation

### Prérequis

Avoir installé **WAMP**, **MAMP** ou **XAMPP**.

### 1. Importer la base de données

**Avec phpMyAdmin** : onglet **Importer** → choisir le fichier `database/agenda.sql` → **Exécuter**.

> ⚠️ Le script **supprime puis recrée** la base `agenda`. Si vous le relancez, vous perdez vos données.

### 2. Lancer le site

Placez le projet dans le dossier de votre serveur (`htdocs/` pour XAMPP, `www/` pour WAMP, `htdocs/` pour MAMP), puis ouvrez-le dans votre navigateur (ex. : <http://localhost/eval_sn1_phpmysql/>).

### 3. Se connecter à la base en PHP

Créez un fichier `config.php` (adaptez l'utilisateur et le mot de passe à votre installation : sous XAMPP/WAMP c'est souvent `root` sans mot de passe, sous MAMP `root` / `root`) :

```php
<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=agenda;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
```

Puis incluez-le en haut de chaque page : `require 'config.php';`

---

## Ce qui est fourni

### Les maquettes HTML

| Maquette | Page PHP à créer | Contenu |
|---|---|---|
| `index.html` | `index.php` | Accueil : liste des événements à venir |
| `evenement.html` | `evenement.php?id=...` | Détail d'un événement + inscription |
| `ajouter.html` | `ajouter.php` | Formulaire d'ajout d'un événement |
| `validation.html` | `validation.php` | Liste des événements à valider (admin) |
| `inscrits.html` | `inscrits.php` | Liste des inscrits par événement validé (admin) |
| `connexion.html` | `connexion.php` | Formulaire de connexion |
| `inscription.html` | `inscription.php` | Formulaire de création de compte |
| `404.html` | (à inclure quand un événement n'existe pas) | Page d'erreur 404 |
| – | `deconnexion.php` | Déconnexion (pas de HTML, juste une redirection) |

- Pour démarrer une page, **copiez la maquette** (`index.html` → `index.php`) puis remplacez les données écrites en dur (titres, dates, images…) par celles de la base.
- Pensez à remplacer les liens `xxx.html` par `xxx.php`.
- L'en-tête et le pied de page sont identiques sur toutes les pages : ils sont délimités par des commentaires `Début de l'en-tête` / `Fin de l'en-tête`… Mettez-les dans `header.php` et `footer.php` et incluez-les avec `require`.
- Certaines variantes (badge « Complet », utilisateur connecté, messages d'erreur, liste vide…) sont **en commentaire** dans les maquettes : décommentez-les quand vous en avez besoin.
- Les images d'exemple sont dans `images/`, vous pouvez les utiliser pour tester l'upload.

### Classes CSS utiles

- Couleur par catégorie : ajoutez `cat-concert`, `cat-conference`, `cat-atelier` ou `cat-sport` sur la carte (`event-card`) ou sur un `badge`. La colonne `category` de la base contient directement `concert`, `conference`, `atelier` ou `sport`.
- Messages : `flash flash-success` / `flash flash-error`.
- Boutons : `btn`, `btn-small`, `btn-success`, `btn-secondary`, `btn-block`.

### La base de données

**Table `user`**

| Colonne | Type | Description |
|---|---|---|
| `id` | INT | Clé primaire |
| `email` | VARCHAR(180) | Unique |
| `password` | VARCHAR(255) | Mot de passe **hashé** avec `password_hash()` |
| `role` | VARCHAR(20) | `user` ou `admin` |
| `created_at` | DATETIME | Date de création du compte |

**Table `event`**

| Colonne | Type | Description |
|---|---|---|
| `id` | INT | Clé primaire |
| `title` | VARCHAR(255) | Titre |
| `date` | DATETIME | Date et heure de l'événement |
| `information` | TEXT | Description (lieu, programme…) |
| `category` | VARCHAR(20) | `concert`, `conference`, `atelier` ou `sport` |
| `places` | INT | Nombre **total** de places |
| `image` | VARCHAR(255) | Chemin de l'image (ex. `images/concert.jpg`), peut être `NULL` |
| `is_validated` | TINYINT(1) | `0` = « Non validé », `1` = validé par un admin |
| `created_at` | DATETIME | Date d'ajout |

**Données de test**

- 7 événements à venir et validés (dont « Atelier tricot pour débutants » avec seulement **2 places**, pratique pour tester « Complet »)
- 3 événements **non validés** (pour la page de validation)
- 2 événements **passés** (qui ne doivent pas s'afficher sur l'accueil)

**Comptes de test**

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | `admin@agenda.fr` | `admin123` |
| Utilisateur | `user@agenda.fr` | `user123` |

**Table `reservation`** : ❌ **n'existe pas**, c'est à vous de la créer (voir la question 8).

---

## Sujet

1. La page d'accueil affiche la date et le titre des événements **à venir** et **validés** (pas les passés, pas les non validés), triés par date, et un lien pour en ajouter un.

2. Sur la page pour ajouter un événement, un formulaire s'affiche avec les champs :
   - **Titre**
   - **Date**
   - **Informations** : description de l'événement (lieu, programme…)
   - **Catégorie** : liste déroulante avec « Concert » / « Conférence » / « Atelier » / « Sport »
   - **Nombre de places**
   - **Image à la une** : upload d'une image, affichée sur la page d'accueil et sur la page de détail (doc : <https://www.php.net/manual/fr/features.file-upload.post-method.php>)

3. Le titre, la date et les informations sont **obligatoires**, le nombre de places doit être **supérieur à 0** : ces règles sont vérifiées **en PHP** (pas seulement avec `required` en HTML), avec un message d'erreur si elles ne sont pas respectées. À l'ajout, l'événement a le statut « **Non validé** ».

4. Sur la page d'accueil, rajouter une **couleur différente** sur chaque événement suivant sa catégorie.

5. Sur la page d'accueil, les catégories sont **cliquables** : un clic sur une catégorie n'affiche que les événements de cette catégorie (ex. : `index.php?categorie=concert`), « Toutes » réaffiche la liste complète. La catégorie sélectionnée est mise en évidence (classe `active`).

6. Chaque titre est un lien vers la page de détail de l'événement (`evenement.php?id=1`), avec une **page 404** si l'événement n'existe pas (pensez à `http_response_code(404)`).

7. Ajouter la **création de compte** et la **connexion** (avec `$_SESSION`) : tout le monde peut créer un événement, seuls les connectés s'inscrivent à un événement.

   Une fois connecté, les boutons « Connexion » et « Créer un compte » du menu sont remplacés par un bouton **Se déconnecter**.

8. **Créer la table `reservation`** qui enregistre quel utilisateur est inscrit à quel événement. À vous de choisir les colonnes, les types et les clés (clé primaire, clés étrangères…). Un utilisateur ne doit pas pouvoir s'inscrire deux fois au même événement. Mettez la requête `CREATE TABLE` dans `database/reservation.sql`.

   Sur la page de détail, un bouton « **S'inscrire** » permet de participer à l'événement. Le nombre de places disponibles diminue.

9. Un utilisateur inscrit peut **se désinscrire** d'un événement.

10. Afficher le **nombre de places restantes** (nombre de places de l'événement − nombre de réservations) et un badge « **Complet** » quand il n'y en a plus : l'inscription est alors bloquée.

11. Seuls les admins (`role = 'admin'`) accèdent à la page `validation.php` : liste des événements « Non validé » avec un bouton **Valider**. Un utilisateur non admin qui tape l'URL est redirigé vers l'accueil.

    Le lien « Validation » du menu n'est **affiché qu'aux admins**.

12. La page `inscrits.php` est **réservée aux admins** (comme `validation.php`, un utilisateur non admin ou non connecté qui tape l'URL est redirigé vers l'accueil) : pour chaque événement **validé**, elle affiche son titre, sa date, sa catégorie, le nombre d'inscrits sur le nombre de places (avec le badge « Complet » si besoin) et la **liste des inscrits** (email et date d'inscription). Si un événement n'a aucun inscrit, afficher « Aucun inscrit pour le moment. ».

    Le lien « Inscrits » du menu n'est lui aussi **affiché qu'aux admins**.

### Ce qui sera aussi évalué

- **Requêtes préparées** (`prepare` / `execute`) dès qu'une valeur vient de l'utilisateur (`$_GET`, `$_POST`, `$_SESSION`) : pas de variable directement dans le SQL.
- **`htmlspecialchars()`** sur tout ce qui est affiché et qui vient de la base ou de l'utilisateur.
- Mots de passe **hashés** avec `password_hash()` / vérifiés avec `password_verify()`.
- Code **factorisé** : connexion à la base, en-tête et pied de page dans des fichiers inclus.

---

## Astuces

- Événements à venir : `WHERE date > NOW()`.
- Places restantes : `SELECT COUNT(*) FROM reservation WHERE event_id = ?` ou une jointure avec `LEFT JOIN` et `GROUP BY`.
- Liste des inscrits d'un événement : une jointure entre `reservation` et `user` (`INNER JOIN user ON user.id = reservation.user_id`).
- Afficher une date : `date('d/m/Y H\hi', strtotime($event['date']))`.
- Sauter les lignes du texte : `nl2br(htmlspecialchars($event['information']))`.
- Après un formulaire traité avec succès, redirigez avec `header('Location: index.php'); exit;` pour éviter le double envoi en cas de rafraîchissement.
- Les messages de succès peuvent être stockés en session (`$_SESSION['flash']`), affichés puis supprimés avec `unset()`.
- Ajouter un événement (requête SQL) :

  ```sql
  INSERT INTO event (title, date, information, category, places, image, is_validated)
  VALUES ('Soirée jazz au Kiosque', '2026-11-07 20:30:00', 'Concert au parc municipal.', 'concert', 80, 'images/concert.jpg', 0);
  ```
- Gérer l'upload de l'image (le formulaire doit avoir `enctype="multipart/form-data"`, c'est déjà le cas dans la maquette). Les images sont enregistrées dans le dossier `uploads/` et le chemin est stocké dans la colonne `image` :

  ```php
  $image = null; // pas d'image par défaut

  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      // Vérifier l'extension du fichier
      $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
      $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];

      if (in_array($extension, $extensionsAutorisees)) {
          // Nom unique pour ne pas écraser une image existante
          $image = 'uploads/' . uniqid() . '.' . $extension;
          move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $image);
      } else {
          $erreurs[] = "L'image doit être au format JPG, PNG ou WebP.";
      }
  }

  // $image vaut maintenant null ou 'uploads/xxxx.jpg' : à enregistrer dans la colonne `image`
  ```
