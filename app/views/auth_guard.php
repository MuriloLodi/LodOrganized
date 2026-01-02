<?php
if (!isset($_SESSION['usuario'])) {
    header("Location: /financas/public/?url=login");
    exit;
}
