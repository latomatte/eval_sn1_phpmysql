<?php
require 'config.php';

$errors = [];
$email = '';
$returnTo = '';
if (isset($_GET['retour']) && is_string($_GET['retour']) && preg_match('/^evenement\.php\?id=[1-9][0-9]*$/', $_GET['retour'])) {
    $returnTo = $_GET['retour'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $postedReturnTo = isset($_POST['retour']) && is_string($_POST['retour']) ? $_POST['retour'] : '';
    if (preg_match('/^evenement\.php\?id=[1-9][0-9]*$/', $postedReturnTo)) {
        $returnTo = $postedReturnTo;
    }

    $statement = $pdo->prepare('SELECT id, email, password, role FROM `user` WHERE email = :email');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if ($user === false || !password_verify($password, $user['password'])) {
        $errors[] = 'Adresse e-mail ou mot de passe incorrect.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        $_SESSION['flash'] = 'Vous êtes connecté.';
        header('Location: ' . ($returnTo !== '' ? $returnTo : 'index.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require 'header.php'; ?>

    <div class="narrow">
        <div class="card">
            <h1>Connexion</h1>

            <?php foreach ($errors as $error): ?>
                <div class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>

            <form class="form" method="post">
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                </div>

                <div>
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <input type="hidden" name="retour" value="<?= htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Se connecter</button>
            </form>

            <p class="form-footer">Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
