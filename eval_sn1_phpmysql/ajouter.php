<?php
require 'config.php';

$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];
$formValues = [
    'title' => '',
    'date' => '',
    'information' => '',
    'category' => 'concert',
    'places' => '20',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formValues as $field => $default) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $formValues[$field] = trim($_POST[$field]);
        }
    }

    if ($formValues['title'] === '' || mb_strlen($formValues['title']) > 255) {
        $errors[] = 'Le titre est obligatoire et doit contenir au maximum 255 caractères.';
    }

    $eventDate = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $formValues['date']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (
        $eventDate === false
        || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        || $eventDate->format('Y-m-d\TH:i') !== $formValues['date']
    ) {
        $errors[] = 'Veuillez saisir une date et une heure valides.';
    } else {
        $futureDateStatement = $pdo->prepare('SELECT CAST(:event_date AS DATETIME) > NOW()');
        $futureDateStatement->execute(['event_date' => $eventDate->format('Y-m-d H:i:s')]);
        if (!(bool) $futureDateStatement->fetchColumn()) {
            $errors[] = 'La date et l’heure de l’événement doivent être dans le futur.';
        }
    }

    if ($formValues['information'] === '') {
        $errors[] = 'Les informations de l’événement sont obligatoires.';
    }

    if (!array_key_exists($formValues['category'], $categories)) {
        $errors[] = 'Veuillez sélectionner une catégorie valide.';
    }

    $places = filter_var($formValues['places'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if ($places === false) {
        $errors[] = 'Le nombre de places doit être un entier supérieur ou égal à 1.';
    }

    $imagePath = null;
    $uploadedImagePath = null;
    if (isset($_FILES['image']) && is_array($_FILES['image'])) {
        $upload = $_FILES['image'];
        $uploadError = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if (!is_int($uploadError)) {
            $errors[] = 'Le fichier image reçu est invalide.';
        } elseif ($uploadError !== UPLOAD_ERR_NO_FILE && $uploadError !== UPLOAD_ERR_OK) {
            $errors[] = 'Le téléversement de l’image a échoué.';
        } elseif (
            $uploadError === UPLOAD_ERR_OK
            && (!isset($upload['size'], $upload['tmp_name']) || !is_int($upload['size']) || !is_string($upload['tmp_name']))
        ) {
            $errors[] = 'Le fichier image reçu est invalide.';
        } elseif ($uploadError === UPLOAD_ERR_OK && $upload['size'] > 5 * 1024 * 1024) {
            $errors[] = 'L’image ne doit pas dépasser 5 Mo.';
        } elseif ($uploadError === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($upload['tmp_name']);
            $allowedImages = [
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
                'image/webp' => '.webp',
            ];

            if (!isset($allowedImages[$mimeType])) {
                $errors[] = 'L’image doit être au format JPG, PNG ou WebP.';
            } else {
                $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
                if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                    $errors[] = 'Le dossier de téléversement des images est indisponible.';
                } else {
                    $fileName = bin2hex(random_bytes(16)) . $allowedImages[$mimeType];
                    $uploadedImagePath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
                    $imagePath = 'uploads/' . $fileName;

                    if (!move_uploaded_file($upload['tmp_name'], $uploadedImagePath)) {
                        $errors[] = 'Impossible d’enregistrer l’image téléversée.';
                        $uploadedImagePath = null;
                        $imagePath = null;
                    }
                }
            }
        }
    }

    if ($errors === []) {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO `event` (`title`, `date`, `information`, `category`, `places`, `image`, `is_validated`)
                 VALUES (:title, :date, :information, :category, :places, :image, 0)'
            );
            $statement->execute([
                'title' => $formValues['title'],
                'date' => $eventDate->format('Y-m-d H:i:s'),
                'information' => $formValues['information'],
                'category' => $formValues['category'],
                'places' => $places,
                'image' => $imagePath,
            ]);

            $_SESSION['flash'] = 'Votre événement a bien été ajouté et sera visible après validation.';
            header('Location: index.php');
            exit;
        } catch (PDOException $exception) {
            error_log('Impossible d’ajouter un événement : ' . $exception->getMessage());
            if ($uploadedImagePath !== null && !unlink($uploadedImagePath)) {
                error_log('Impossible de supprimer l’image téléversée après l’échec de l’ajout.');
            }
            $errors[] = 'Une erreur est survenue lors de l’enregistrement de l’événement.';
        }
    } elseif ($uploadedImagePath !== null && file_exists($uploadedImagePath) && !unlink($uploadedImagePath)) {
        error_log('Impossible de supprimer l’image téléversée après une erreur de validation.');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter un événement · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require 'header.php'; ?>

    <div class="narrow">
        <a class="back-link" href="index.php">← Retour aux événements</a>

        <div class="card">
            <h1>Ajouter un événement</h1>
            <p class="muted">Votre événement sera visible après validation par un administrateur.</p>

            <?php foreach ($errors as $error): ?>
                <div class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>

            <form class="form" method="post" enctype="multipart/form-data">
                <div>
                    <label for="title" class="required">Titre</label>
                    <input type="text" id="title" name="title" placeholder="Ex. : Soirée jazz au Kiosque" maxlength="255" value="<?= htmlspecialchars($formValues['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div>
                    <label for="date" class="required">Date</label>
                    <input type="datetime-local" id="date" name="date" min="<?= date('Y-m-d\TH:i') ?>" value="<?= htmlspecialchars($formValues['date'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div>
                    <label for="information" class="required">Informations</label>
                    <textarea id="information" name="information" placeholder="Lieu, programme, tarif..." required><?= htmlspecialchars($formValues['information'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div>
                    <label for="category">Catégorie</label>
                    <select id="category" name="category">
                        <?php foreach ($categories as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $formValues['category'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="places">Nombre de places</label>
                    <input type="number" id="places" name="places" min="1" value="<?= htmlspecialchars($formValues['places'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label for="image">Image à la une</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div class="help-text">JPG, PNG ou WebP.</div>
                </div>

                <button type="submit">Enregistrer l'événement</button>
            </form>
        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
