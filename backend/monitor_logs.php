#!/usr/bin/env php
<?php

/**
 * Простой монитор логов для отслеживания проблем пользователей
 * Использование: php monitor_logs.php
 */

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "Лог файл не найден: $logFile\n";
    exit(1);
}

echo "🔍 Мониторинг логов Laravel...\n";
echo "📁 Файл: $logFile\n";
echo "⏰ Время: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 80) . "\n\n";

// Читаем последние 50 строк
$lines = file($logFile);
$lastLines = array_slice($lines, -50);

foreach ($lastLines as $line) {
    if (empty(trim($line))) continue;
    
    // Определяем уровень логирования
    $level = 'INFO';
    $color = "\033[32m"; // зеленый
    
    if (strpos($line, 'local.ERROR') !== false) {
        $level = 'ERROR';
        $color = "\033[31m"; // красный
    } elseif (strpos($line, 'local.WARNING') !== false) {
        $level = 'WARNING';
        $color = "\033[33m"; // желтый
    } elseif (strpos($line, 'local.INFO') !== false) {
        $level = 'INFO';
        $color = "\033[32m"; // зеленый
    }
    
    // Парсим время
    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
        $timestamp = $matches[1];
        $message = substr($line, strlen($matches[0]));
        
        echo $color . "[$level] $timestamp\033[0m\n";
        echo "   " . trim($message) . "\n\n";
    }
}

echo str_repeat("=", 80) . "\n";
echo "💡 Для мониторинга в реальном времени используйте: tail -f storage/logs/laravel.log\n";
echo "🔍 Для фильтрации ошибок: grep -i 'error\\|warning' storage/logs/laravel.log\n";
echo "📊 Для статистики: grep -c 'local.ERROR\\|local.WARNING\\|local.INFO' storage/logs/laravel.log\n";
