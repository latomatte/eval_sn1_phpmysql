<?php
require 'config.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 180) {
        $errors[] = 'Veuillez saisir une adresse e-mail valide (180 caractères maximum).';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }

    if ($errors === []) {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO `user` (`email`, `password`, `role`) VALUES (:email, :password, :role)'
            );
            $statement->execute([
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
            ]);

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $pdo->lastInsertId(),
                'email' => $email,
                'role' => 'user',
            ];
            $_SESSION['flash'] = 'Votre compte a été créé. Vous êtes maintenant connecté.';
            header('Location: index.php');
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'Cette adresse e-mail est déjà utilisée.';
            } else {
                error_log('Impossible de créer le compte : ' . $exception->getMessage());
                $errors[] = 'Une erreur est survenue lors de la création du compte.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte · Agenda</title>
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
            <h1>Créer un compte</h1>

            <?php foreach ($errors as $error): ?>
                <div class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>

            <form class="form" method="post">
                <div>
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email" maxlength="180" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div>
                    <label for="password" class="required">Mot de passe</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" required>
                    <div class="help-text">6 caractères minimum.</div>
                </div>

                <button type="submit">Créer mon compte</button>
            </form>

            <p class="form-footer">Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
