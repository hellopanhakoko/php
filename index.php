<?php
session_start();

// Load configuration
require_once 'config.php';
require_once 'functions.php';

// Route handling
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string and get path
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/index.php', '', $path);

// Simple routing
switch ($path) {
    case '/':
    case '/game':
        require 'pages/game.php';
        break;
    
    case '/mobile-legends-products':
    case '/mlbb_topup':
        require 'pages/mlbb_topup.php';
        break;
    
    case '/free-fire-products':
    case '/ff_topup':
        require 'pages/freefire_topup.php';
        break;
    
    case '/roblox-products':
    case '/roblox_topup':
        require 'pages/roblox_topup.php';
        break;
    
    case '/check_payment_status':
        require 'api/check_payment_status.php';
        break;
    
    case '/api/check_mlbb_nickname':
        require 'api/check_mlbb_nickname.php';
        break;
    
    case '/api/verify_roblox_username':
        require 'api/verify_roblox_username.php';
        break;
    
    default:
        if (preg_match('/^\/receipt\/(.+)$/', $path, $matches)) {
            $_GET['md5'] = $matches[1];
            require 'pages/receipt.php';
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
        break;
}
?>