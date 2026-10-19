<!-- ===== Début de l'en-tête (à mettre dans un fichier header.php) ===== -->
    <header class="site-header">
        <div class="container">
            <a class="logo" href="index.php">Agenda<span>.</span></a>
            <nav class="nav">
                <a href="index.php">Événements</a>

                <?php if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id'])): ?>
                    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                        <a href="validation.php">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            Validation
                        </a>
                        <a href="inscrits.php">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v1"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M19 14a5 5 0 0 1 3 5v2"/></svg>
                            Inscrits
                        </a>
                    <?php endif; ?>
                    <form class="logout-form" method="post" action="deconnexion.php">
                        <button class="btn btn-small" type="submit">Se déconnecter</button>
                    </form>
                <?php else: ?>
                    <a href="connexion.php">Connexion</a>
                    <a class="btn btn-small" href="inscription.php">Créer un compte</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php
        $flashMessage = null;
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['flash']) && is_string($_SESSION['flash'])) {
            $flashMessage = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        ?>
        <?php if ($flashMessage !== null): ?>
            <div class="flash flash-success"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <!-- Exemples de messages :
        <div class="flash flash-success">Votre événement a bien été ajouté.</div>
        <div class="flash flash-error">Une erreur est survenue.</div>
        -->
        <!-- ===== Fin de l'en-tête ===== -->