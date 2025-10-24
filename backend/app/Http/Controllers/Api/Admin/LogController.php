<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Лог файл не найден'
            ]);
        }

        $logs = [];
        $lines = File::lines($logFile);
        $filter = $request->get('filter', 'all'); // all, error, warning, info
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Парсим строку лога Laravel
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(\w+): (.+)$/', $line, $matches)) {
                $timestamp = $matches[1];
                $level = $matches[2];
                $message = $matches[3];
                
                // Фильтруем по уровню
                if ($filter !== 'all' && $level !== $filter) {
                    continue;
                }
                
                $logs[] = [
                    'timestamp' => $timestamp,
                    'level' => $level,
                    'message' => $message,
                    'raw' => $line
                ];
            }
        }
        
        // Сортируем по времени (новые сверху)
        $logs = array_reverse($logs);
        
        // Пагинация
        $page = $request->get('page', 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $paginatedLogs = array_slice($logs, $offset, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => $paginatedLogs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => count($logs),
                'last_page' => ceil(count($logs) / $perPage)
            ]
        ]);
    }
    
    public function clear(): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            File::put($logFile, '');
            
            Log::info('Logs cleared by admin', [
                'timestamp' => now()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Логи очищены'
        ]);
    }
    
    public function download(): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return response()->json([
                'success' => false,
                'message' => 'Лог файл не найден'
            ], 404);
        }
        
        $content = File::get($logFile);
        
        return response()->json([
            'success' => true,
            'data' => [
                'content' => $content,
                'filename' => 'laravel-' . now()->format('Y-m-d-H-i-s') . '.log'
            ]
        ]);
    }
}
