<?php
/**
 * Logout Handler
 * This file clears the session and redirects the user to the role selection portal.
 * It exists solely as a bridge for any links that might still be pointing to 'logout.php'.
 */
session_start();
session_unset();
session_destroy();

// Redirect to the actual role selection page
header("Location: role_select.php");
exit();
?>
