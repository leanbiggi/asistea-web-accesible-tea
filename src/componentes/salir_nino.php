<?php
session_start();
session_unset(); // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión
header("Location: ../panel_nino.php"); // Redirige de vuelta a la página de login del niño
exit();
?>