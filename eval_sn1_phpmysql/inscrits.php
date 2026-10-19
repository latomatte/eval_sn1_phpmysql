<?php
require 'config.php';

$user = $_SESSION['user'] ?? null;
if (!is_array($user) || ($user['role'] ?? null) !== 'admin') {
    header('Location: index.php');
    exit;
}

$statement = $pdo->query(
    'SELECT e.id, e.title, e.`date`, e.category, e.places,
            r.user_id, r.created_at AS registration_date, u.email
     FROM `event` e
     LEFT JOIN `reservation` r ON r.event_id = e.id
     LEFT JOIN `user` u ON u.id = r.user_id
     WHERE e.is_validated = 1
     ORDER BY e.`date` ASC, e.id ASC, r.created_at ASC'
);
$rows = $statement->fetchAll();
$events = [];
foreach ($rows as $row) {
    $eventId = (int) $row['id'];
    if (!isset($events[$eventId])) {
        $events[$eventId] = [
            'id' => $eventId,
            'title' => $row['title'],
            'date' => $row['date'],
            'category' => $row['category'],
            'places' => (int) $row['places'],
            'attendees' => [],
        ];
    }

    if ($row['user_id'] !== null) {
        $events[$eventId]['attendees'][] = [
            'email' => $row['email'],
            'registered_at' => $row['registration_date'],
        ];
    }
}

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
    <title>Inscrits aux événements · Agenda</title>
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
            <h1>Inscrits aux événements</h1>
            <p class="muted">Liste des participants pour chaque événement validé.</p>
        </div>
    </div>

    <?php foreach ($events as $event): ?>
        <?php
        $attendeeCount = count($event['attendees']);
        $category = $event['category'];
        ?>
        <section class="card attendees">
            <div class="attendees-head">
                <div>
                    <h2><a href="evenement.php?id=<?= $event['id'] ?>"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></a></h2>
                    <span class="badge cat-<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($categories[$category] ?? $category, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="muted"><?= date('d/m/Y H\hi', strtotime($event['date'])) ?></span>
                </div>
                <p class="places">
                    <?php if ($attendeeCount >= $event['places']): ?>
                        <span class="badge badge-full">Complet</span>
                    <?php endif; ?>
                    <strong><?= $attendeeCount ?></strong> inscrit<?= $attendeeCount === 1 ? '' : 's' ?> sur <?= $event['places'] ?> places
                </p>
            </div>

            <?php if ($attendeeCount === 0): ?>
                <p class="empty">Aucun inscrit pour le moment.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Inscrit le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($event['attendees'] as $attendee): ?>
                                <tr>
                                    <td><?= htmlspecialchars($attendee['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= date('d/m/Y H\hi', strtotime($attendee['registered_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

    <?php if ($events === []): ?>
        <p class="empty">Aucun événement validé pour le moment.</p>
    <?php endif; ?>

    <?php require 'footer.php'; ?>
</body>
</html>
