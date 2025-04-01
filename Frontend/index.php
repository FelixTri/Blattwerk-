<?php
require_once __DIR__ . '/../Backend/config/paths.php';
include DESIGN_PATH . '/navbar.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Blattwerk – Dein Pflanzen-Webshop</title>
    <link rel="stylesheet" href="css/style.css">
    <script defer src="js/test.js"></script>
</head>
<body>
    
    <main>
        <section class="hero">
            <h2>Willkommen bei Blattwerk</h2>
            <p>Finde deine perfekte Zimmerpflanze – nachhaltig, schön und direkt zu dir geliefert.</p>
            <a href="sites/products.php" class="btn">Jetzt entdecken</a>
        </section>
        </main>

        <?php include DESIGN_PATH . '/footer.php'; ?>
