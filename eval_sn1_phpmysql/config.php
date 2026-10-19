<?php
session_start(); // Important pour gérer les sessions utilisateurs

$pdo = new PDO('mysql:host=127.0.0.1;dbname=agenda;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);