<?php
require_once __DIR__ . '/../../Backend/config/paths.php';
include DESIGN_PATH . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Warenkorb – Blattwerk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<main class="container py-5">
    <h1 class="text-center">Dein Warenkorb</h1>
    <p class="text-center">Hier erscheinen deine ausgewählten Pflanzen.</p>
</main>

<?php include DESIGN_PATH . '/footer.php'; ?>
</body>
</html>
