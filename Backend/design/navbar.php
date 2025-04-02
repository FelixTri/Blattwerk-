<?php require_once __DIR__ . '/../config/paths.php'; ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">🌿 Blattwerk</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Menü">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php">Startseite</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITES_URL ?>/products.php">Produkte</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITES_URL ?>/cart.php">Warenkorb</a>
                </li>
            </ul>
        </div>
    </div>
</nav>