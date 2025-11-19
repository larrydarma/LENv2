<?php
session_start();
session_unset();
session_destroy();

// Evitar que se pueda volver con "atrás"
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

header("Location: login.php");
exit;
?>
