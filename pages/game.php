<?php
require_once 'config.php';
require_once 'functions.php';

$templatePath = __DIR__ . '/../templates/game.html';
if (file_exists($templatePath)) {
    include $templatePath;
} else {
    echo "Template not found";
}
?>