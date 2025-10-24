#!/usr/bin/env php
<?php

/**
 * Анализатор логов для выявления проблем пользователей
 * Использование: php analyze_logs.php [--errors] [--warnings] [--stats]
 */

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "❌ Лог файл не найден: $logFile\n";
    exit(1);
}

$options = getopt('', ['errors', 'warnings', 'stats', 'help']);

if (isset($options['help'])) {
    echo "📊 Анализатор логов Laravel\n\n";
    echo "Использование:\n";
    echo "  php analyze_logs.php --errors     Показать только ошибки\n";
    echo "  php analyze_logs.php --warnings   Показать только предупреждения\n";
    echo "  php analyze_logs.php --stats      Показать статистику\n";
    echo "  php analyze_logs.php              Показать все логи\n\n";
    exit(0);
}

$lines = file($logFile);
$errors = [];
$warnings = [];
$info = [];
$loginAttempts = [];
$apiRequests = [];

foreach ($lines as $line) {
    if (empty(trim($line))) continue;
    
    // Парсим строку лога
    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(\w+): (.+)$/', $line, $matches)) {
        $timestamp = $matches[1];
        $level = $matches[2];
        $message = $matches[3];
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'raw' => $line
        ];
        
        switch ($level) {
            case 'ERROR':
                $errors[] = $logEntry;
                break;
            case 'WARNING':
                $warnings[] = $logEntry;
                break;
            case 'INFO':
                $info[] = $logEntry;
                
                // Анализируем специфичные события
                if (strpos($message, 'Admin login attempt') !== false) {
                    $loginAttempts[] = $logEntry;
                } elseif (strpos($message, 'API Request') !== false) {
                    $apiRequests[] = $logEntry;
                }
                break;
        }
    }
}

// Показываем результаты
if (isset($options['stats'])) {
    echo "📊 Статистика логов\n";
    echo str_repeat("=", 50) . "\n";
    echo "🔴 Ошибки: " . count($errors) . "\n";
    echo "🟡 Предупреждения: " . count($warnings) . "\n";
    echo "🔵 Информационные: " . count($info) . "\n";
    echo "🔐 Попытки входа: " . count($loginAttempts) . "\n";
    echo "🌐 API запросы: " . count($apiRequests) . "\n";
    echo "📈 Всего записей: " . (count($errors) + count($warnings) + count($info)) . "\n\n";
    
    // Статистика по времени
    $today = date('Y-m-d');
    $todayErrors = array_filter($errors, function($e) use ($today) {
        return strpos($e['timestamp'], $today) === 0;
    });
    $todayWarnings = array_filter($warnings, function($w) use ($today) {
        return strpos($w['timestamp'], $today) === 0;
    });
    
    echo "📅 За сегодня ($today):\n";
    echo "   🔴 Ошибки: " . count($todayErrors) . "\n";
    echo "   🟡 Предупреждения: " . count($todayWarnings) . "\n\n";
}

if (isset($options['errors']) || (!isset($options['warnings']) && !isset($options['stats']))) {
    if (!empty($errors)) {
        echo "🔴 ОШИБКИ (" . count($errors) . ")\n";
        echo str_repeat("=", 50) . "\n";
        foreach (array_slice($errors, -10) as $error) {
            echo "⏰ {$error['timestamp']}\n";
            echo "📝 " . substr($error['message'], 0, 100) . "...\n\n";
        }
    }
}

if (isset($options['warnings']) || (!isset($options['errors']) && !isset($options['stats']))) {
    if (!empty($warnings)) {
        echo "🟡 ПРЕДУПРЕЖДЕНИЯ (" . count($warnings) . ")\n";
        echo str_repeat("=", 50) . "\n";
        foreach (array_slice($warnings, -10) as $warning) {
            echo "⏰ {$warning['timestamp']}\n";
            echo "📝 " . substr($warning['message'], 0, 100) . "...\n\n";
        }
    }
}

if (!isset($options['errors']) && !isset($options['warnings']) && !isset($options['stats'])) {
    echo "🔍 ПОСЛЕДНИЕ СОБЫТИЯ\n";
    echo str_repeat("=", 50) . "\n";
    
    $allLogs = array_merge($errors, $warnings, $info);
    usort($allLogs, function($a, $b) {
        return strcmp($a['timestamp'], $b['timestamp']);
    });
    
    foreach (array_slice($allLogs, -20) as $log) {
        $icon = $log['level'] === 'ERROR' ? '🔴' : ($log['level'] === 'WARNING' ? '🟡' : '🔵');
        echo "$icon [{$log['level']}] {$log['timestamp']}\n";
        echo "   " . substr($log['message'], 0, 80) . "...\n\n";
    }
}

echo "💡 Для мониторинга в реальном времени: tail -f storage/logs/laravel.log\n";
echo "🔍 Для фильтрации: grep -i 'error\\|warning' storage/logs/laravel.log\n";
