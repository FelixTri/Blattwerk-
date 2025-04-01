<?php
require_once __DIR__ . '/../../Backend/config/paths.php';
include DESIGN_PATH . '/navbar.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkte – Blattwerk</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Dein Style -->
    <link rel="stylesheet" href="../css/style.css">

    <!-- JS -->
    <script defer src="../js/test.js"></script>
</head>
<body>
    
    <!-- Hauptinhalt -->
    <main class="container py-5">
        <h1 class="text-center mb-4">Unsere Pflanzen</h1>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">

            <!-- Produkt 1 -->
            <div class="col">
                <div class="card h-100">
                    <img src="../Backend/productpictures/aloe.jpeg" class="card-img-top" alt="Aloe Vera">
                    <div class="card-body">
                        <h5 class="card-title">Aloe Vera</h5>
                        <p class="card-text">Pflegeleicht, schön und perfekt für Anfänger.</p>
                        <p class="text-success fw-bold">9,99 €</p>
                        <button class="btn btn-success w-100">In den Warenkorb</button>
                    </div>
                </div>
            </div>

            <!-- Produkt 2 -->
            <div class="col">
                <div class="card h-100">
                    <img src="../Backend/productpictures/monstera.jpg" class="card-img-top" alt="Monstera">
                    <div class="card-body">
                        <h5 class="card-title">Monstera</h5>
                        <p class="card-text">Stylische Zimmerpflanze mit einzigartigen Blättern.</p>
                        <p class="text-success fw-bold">14,99 €</p>
                        <button class="btn btn-success w-100">In den Warenkorb</button>
                    </div>
                </div>
            </div>

            <!-- Produkt 3 -->
            <div class="col">
                <div class="card h-100">
                    <img src="../Backend/productpictures/kaktus.jpg" class="card-img-top" alt="Kaktus">
                    <div class="card-body">
                        <h5 class="card-title">Kaktus</h5>
                        <p class="card-text">Kommt mit wenig Wasser aus und ist super robust.</p>
                        <p class="text-success fw-bold">6,49 €</p>
                        <button class="btn btn-success w-100">In den Warenkorb</button>
                    </div>
                </div>
            </div>

            <!-- Produkt 4 -->
            <div class="col">
                <div class="card h-100">
                    <img src="../Backend/productpictures/basilikum.jpg" class="card-img-top" alt="Basilikum">
                    <div class="card-body">
                        <h5 class="card-title">Basilikum</h5>
                        <p class="card-text">Frisch, lecker und perfekt für deine Küche.</p>
                        <p class="text-success fw-bold">3,99 €</p>
                        <button class="btn btn-success w-100">In den Warenkorb</button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>