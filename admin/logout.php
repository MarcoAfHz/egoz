<?php
// =============================================
// EGOZ — Boutique Esotérica
// admin/logout.php
// =============================================
require_once __DIR__ . '/../config.php';

session_destroy();
redirect(URL_ADMIN . '/login.php');