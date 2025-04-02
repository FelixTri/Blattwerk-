<?php
require_once __DIR__ . '/../Backend/config/paths.php';
include DESIGN_PATH . '/navbar.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Blattwerk – Dein Pflanzen-Webshop</title>
    <link rel="stylesheet" href="/../css/style.css">
    <script defer src="js/test.js"></script>
</head>
<body>

<main>
    <section class="hero text-center p-5 bg-success text-white">
        <h2>Willkommen bei Blattwerk</h2>
        <p>Finde deine perfekte Zimmerpflanze – nachhaltig, schön und direkt zu dir geliefert.</p>
        <a href="sites/products.php" class="btn btn-light mt-3">Jetzt entdecken</a>
    </section>
</main>

<?php include DESIGN_PATH . '/footer.php'; ?>
