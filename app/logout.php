<?php
require_once __DIR__ . '/../includes/auth.php';
logout();
redirect('/public/login.php');
