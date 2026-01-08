<?php
if (empty($_SESSION['usuario']['id'])) {
  header("Location: /financas/public/?url=login");
  exit;
}

if (empty($_SESSION['usuario']['is_admin'])) {
  echo "Acesso negado";
  exit;
}
