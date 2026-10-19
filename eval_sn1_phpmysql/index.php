<?php
require 'config.php';

$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];

$categoryImages = [
    'concert' => 'images/concert.jpg',
    'conference' => 'images/conference.jpg',
    'atelier' => 'images/atelier.jpg',
    'sport' => 'images/sport.jpg',
];

$selectedCategory = $_GET['categorie'] ?? '';
if (!is_string($selectedCategory) || !array_key_exists($selectedCategory, $categories)) {
    $selectedCategory = '';
}

$sql = 'SELECT id, title, date, category, image
        FROM `event`
        WHERE is_validated = 1 AND `date` > NOW()';
$parameters = [];

if ($selectedCategory !== '') {
    $sql .= ' AND category = :category';
    $parameters['category'] = $selectedCategory;
}

$sql .= ' ORDER BY `date` ASC';
$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$events = $statement->fetchAll();

$months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Événements à venir · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require 'header.php'; ?>

    <section class="hero" style="background-image: url('images/rock.jpg')">
        <div class="hero-content">
            <span class="hero-kicker">Agenda local</span>
            <h1>Les événements près de chez vous</h1>
            <p>Concerts, conférences, ateliers et sport : trouvez votre prochaine sortie ou proposez la vôtre.</p>
            <a class="btn" href="ajouter.php">+ Ajouter un événement</a>
        </div>
    </section>

    <div class="section-head">
        <h2>À venir</h2>
        <!-- La catégorie sélectionnée porte la classe « active » -->
        <ul class="legend">
            <li><a class="<?= $selectedCategory === '' ? 'active' : '' ?>" href="index.php">Toutes</a></li>
            <?php foreach ($categories as $slug => $label): ?>
                <li class="cat-<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
                    <a class="<?= $selectedCategory === $slug ? 'active' : '' ?>" href="index.php?categorie=<?= urlencode($slug) ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <ul class="event-list">
        <?php foreach ($events as $event): ?>
            <?php
            $eventTimestamp = strtotime($event['date']);
            $category = $event['category'];
            $image = $event['image'] ?: ($categoryImages[$category] ?? '');
            ?>
            <li class="event-card cat-<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
                <div class="event-media">
                    <?php if ($image !== ''): ?>
                        <img class="event-image" src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    <?php endif; ?>
                    <div class="event-date">
                        <span class="day"><?= date('d', $eventTimestamp) ?></span>
                        <span class="month"><?= $months[(int) date('n', $eventTimestamp) - 1] ?></span>
                    </div>
                    <span class="badge"><?= htmlspecialchars($categories[$category] ?? $category, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <h3 class="event-title"><a href="evenement.php?id=<?= (int) $event['id'] ?>"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                <span class="muted"><?= date('d/m/Y H\hi', $eventTimestamp) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (count($events) === 0): ?>
        <p class="empty">Aucun événement à venir pour le moment.</p>
    <?php endif; ?>

    <?php require 'footer.php'; ?>
</body>
</html>
