<?php
require_once __DIR__ . '/../includes/helpers.php';

// L'entrée de l'admin mène directement au dashboard (ou à la connexion si besoin)
require_admin();
header('Location: /admin/dashboard.php');
exit;
