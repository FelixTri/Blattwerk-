<?php
// Pfade für include/require
define('BASE_PATH', dirname(__DIR__, 1));
define('DESIGN_PATH', BASE_PATH . '/design');
define('FRONTEND_PATH', dirname(BASE_PATH) . '/Frontend');
define('SITES_PATH', FRONTEND_PATH . '/sites');

// URLs für HTML <a href>, <link>, etc.
define('BASE_URL', '/Blattwerk_Aktualisiert_FooterFix/Frontend');
define('SITES_URL', BASE_URL . '/sites');
?>
