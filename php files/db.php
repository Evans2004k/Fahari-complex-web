<?php
$pdo = new PDO(
    "mysql:host=localhost;dbname=marketplace;charset=utf8",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
session_start();
