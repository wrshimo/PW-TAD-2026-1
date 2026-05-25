<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Bloqueio: redireciona para o login se não houver sessão ativa
    header('Location: /admin/login.php');
    exit;
}