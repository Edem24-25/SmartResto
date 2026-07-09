<?php
// API JSON — état des tables (polling léger pour Ajax si besoin)
require_once __DIR__ . '/../config/config.php';
require_login();
header('Content-Type: application/json');
$tables = $pdo->query('SELECT id,number,status,capacity,pos_x,pos_y FROM tables_resto')->fetchAll();
echo json_encode(['tables'=>$tables, 'ts'=>time()]);
