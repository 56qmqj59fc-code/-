<?php
    session_start();
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('Admin only');
    }

require_once __DIR__ . '/../project-core/src/Seeders/MovieSeeder.php';

$seeder = new MovieSeeder();
$seeder->run(5);