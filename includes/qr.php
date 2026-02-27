<?php
require_once __DIR__ . '/phpqrcode.php';

$text = isset($_GET['text']) ? $_GET['text'] : 'Standardtext';

// Sicherheit: Länge begrenzen
$text = substr($text, 0, 500);

header('Content-Type: image/png');
QRcode::png($text);