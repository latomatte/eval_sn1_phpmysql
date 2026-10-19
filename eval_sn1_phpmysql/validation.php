<?php
require 'config.php';

$user = $_SESSION['user'] ?? null;
if (!is_array($user) || ($user['role'] ?? null) !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($eventId === false || $eventId === null) {
        http_response_code(400);
        exit('Identifiant d’événement invalide.');
    }

    $statement = $pdo->prepare(
        'UPDATE `event` SET is_validated = 1 WHERE id = :id AND is_validated = 0'
    );
    $statement->execute(['id' => $eventId]);

    $_SESSION['flash'] = $statement->rowCount() > 0
        ? 'L’événement a été validé.'
        : 'Cet événement n’existe pas ou a déjà été validé.';
    header('Location: validation.php');
    exit;
}

$statement = $pdo->query(
    'SELECT id, title, `date`, category, places
     FROM `event`
     WHERE is_validated = 0
     ORDER BY `date` ASC'
);
$events = $statement->fetchAll();
$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation des événements · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require 'header.php'; ?>

    <div class="section-head">
        <div>
            <h1>Événements à valider</h1>
            <p class="muted"><?= count($events) ?> événement<?= count($events) > 1 ? 's' : '' ?> en attente de validation.</p>
        </div>
    </div>

    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Événement</th>
                    <th>Date</th>
                    <th>Catégorie</th>
                    <th>Places</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <?php $category = $event['category']; ?>
                    <tr>
                        <td><a href="evenement.php?id=<?= (int) $event['id'] ?>"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></a></td>
                        <td><?= date('d/m/Y H\hi', strtotime($event['date'])) ?></td>
                        <td><span class="badge cat-<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($categories[$category] ?? $category, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= (int) $event['places'] ?></td>
                        <td><span class="badge badge-pending">Non validé</span></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                                <button type="submit" class="btn btn-small btn-success">Valider</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (count($events) === 0): ?>
        <p class="empty">Aucun événement en attente 🎉</p>
    <?php endif; ?>

    <?php require 'footer.php'; ?>
</body>
</html>
