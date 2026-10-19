<?php
require 'config.php';

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($eventId === false || $eventId === null) {
    http_response_code(404);
    require '404.html';
    exit;
}

$statement = $pdo->prepare(
    'SELECT id, title, date, information, category, places, image, is_validated
     FROM `event`
     WHERE id = :id'
);
$statement->execute(['id' => $eventId]);
$event = $statement->fetch();

if ($event === false) {
    http_response_code(404);
    require '404.html';
    exit;
}

$user = $_SESSION['user'] ?? null;
$userId = is_array($user) && isset($user['id']) && is_int($user['id']) ? $user['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

    if ($userId === null) {
        header('Location: connexion.php?retour=' . urlencode('evenement.php?id=' . $eventId));
        exit;
    }

    if (!in_array($action, ['register', 'cancel'], true)) {
        http_response_code(400);
        exit('Action invalide.');
    }

    try {
        if ($action === 'register') {
            $pdo->beginTransaction();
            $lockEvent = $pdo->prepare('SELECT places, is_validated, `date` FROM `event` WHERE id = :id FOR UPDATE');
            $lockEvent->execute(['id' => $eventId]);
            $lockedEvent = $lockEvent->fetch();

            if ($lockedEvent === false || !(int) $lockedEvent['is_validated'] || strtotime($lockedEvent['date']) <= time()) {
                $pdo->rollBack();
                $_SESSION['flash'] = 'Cet événement ne peut pas recevoir de nouvelles inscriptions.';
                header('Location: evenement.php?id=' . $eventId);
                exit;
            }

            $existingReservation = $pdo->prepare(
                'SELECT 1 FROM `reservation` WHERE event_id = :event_id AND user_id = :user_id'
            );
            $existingReservation->execute(['event_id' => $eventId, 'user_id' => $userId]);
            if ($existingReservation->fetchColumn()) {
                $pdo->rollBack();
                $_SESSION['flash'] = 'Vous êtes déjà inscrit à cet événement.';
                header('Location: evenement.php?id=' . $eventId);
                exit;
            }

            $countReservations = $pdo->prepare('SELECT COUNT(*) FROM `reservation` WHERE event_id = :event_id');
            $countReservations->execute(['event_id' => $eventId]);
            if ((int) $countReservations->fetchColumn() >= (int) $lockedEvent['places']) {
                $pdo->rollBack();
                $_SESSION['flash'] = 'Cet événement est complet.';
                header('Location: evenement.php?id=' . $eventId);
                exit;
            }

            $insertReservation = $pdo->prepare(
                'INSERT INTO `reservation` (`event_id`, `user_id`, `created_at`)
                 VALUES (:event_id, :user_id, CURRENT_TIMESTAMP)'
            );
            $insertReservation->execute(['event_id' => $eventId, 'user_id' => $userId]);
            $pdo->commit();
            $_SESSION['flash'] = 'Votre inscription à cet événement est confirmée.';
        } else {
            $deleteReservation = $pdo->prepare(
                'DELETE FROM `reservation` WHERE event_id = :event_id AND user_id = :user_id'
            );
            $deleteReservation->execute(['event_id' => $eventId, 'user_id' => $userId]);
            $_SESSION['flash'] = $deleteReservation->rowCount() > 0
                ? 'Vous êtes désinscrit de cet événement.'
                : 'Vous n’étiez pas inscrit à cet événement.';
        }
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Impossible de modifier la réservation : ' . $exception->getMessage());
        $_SESSION['flash'] = 'Une erreur est survenue lors de la modification de votre inscription.';
    }

    header('Location: evenement.php?id=' . $eventId);
    exit;
}

$reservationCountStatement = $pdo->prepare('SELECT COUNT(*) FROM `reservation` WHERE event_id = :event_id');
$reservationCountStatement->execute(['event_id' => $eventId]);
$reservationCount = (int) $reservationCountStatement->fetchColumn();
$remainingPlaces = max(0, (int) $event['places'] - $reservationCount);
$isOpenForRegistration = (int) $event['is_validated'] === 1 && strtotime($event['date']) > time();
$isRegistered = false;
if ($userId !== null) {
    $registeredStatement = $pdo->prepare(
        'SELECT 1 FROM `reservation` WHERE event_id = :event_id AND user_id = :user_id'
    );
    $registeredStatement->execute(['event_id' => $eventId, 'user_id' => $userId]);
    $isRegistered = (bool) $registeredStatement->fetchColumn();
}

$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];
$category = $event['category'];
$categoryLabel = $categories[$category] ?? $category;
$categoryClass = preg_replace('/[^a-z0-9_-]/', '', $category);
$categoryImages = [
    'concert' => 'images/concert.jpg',
    'conference' => 'images/conference.jpg',
    'atelier' => 'images/atelier.jpg',
    'sport' => 'images/sport.jpg',
];
$image = $event['image'] ?: ($categoryImages[$category] ?? 'images/rock.jpg');
$eventTimestamp = strtotime($event['date']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?> · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require 'header.php'; ?>

    <a class="back-link" href="index.php">← Retour aux événements</a>

    <article class="cat-<?= htmlspecialchars($categoryClass, ENT_QUOTES, 'UTF-8') ?>">
        <header class="detail-hero" style="background-image: url('<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>')">
            <div class="detail-hero-content">
                <span class="badge"><?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= date('d/m/Y H\hi', $eventTimestamp) ?></p>
            </div>
        </header>

        <div class="detail-grid">
            <section class="card">
                <h2>Informations</h2>
                <p><?= nl2br(htmlspecialchars($event['information'], ENT_QUOTES, 'UTF-8')) ?></p>
            </section>

            <aside class="card booking">
                <?php if ($remainingPlaces === 0): ?>
                    <p class="places"><span class="badge badge-full">Complet</span></p>
                <?php else: ?>
                    <p class="places"><strong><?= $remainingPlaces ?></strong> places restantes sur <?= (int) $event['places'] ?></p>
                    <div class="progress"><span style="width: <?= (int) round($reservationCount / max(1, (int) $event['places']) * 100) ?>%"></span></div>
                <?php endif; ?>

                <?php if ($userId === null): ?>
                    <?php if ($isOpenForRegistration && $remainingPlaces > 0): ?>
                        <a class="btn btn-block" href="connexion.php?retour=<?= urlencode('evenement.php?id=' . $eventId) ?>">Connectez-vous pour vous inscrire</a>
                    <?php endif; ?>
                <?php elseif ($isRegistered): ?>
                    <p class="muted">Vous êtes inscrit à cet événement.</p>
                    <form method="post">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn btn-secondary btn-block">Se désinscrire</button>
                    </form>
                <?php elseif ($isOpenForRegistration && $remainingPlaces > 0): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="register">
                        <button type="submit" class="btn btn-block">S'inscrire</button>
                    </form>
                <?php elseif (!$isOpenForRegistration): ?>
                    <p class="muted">Les inscriptions ne sont pas ouvertes pour cet événement.</p>
                <?php endif; ?>
            </aside>
        </div>
    </article>

    <?php require 'footer.php'; ?>
</body>
</html>
