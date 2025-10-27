<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{
    public function run(): JsonResponse
    {
        try {
            // Run tests and capture output
            Artisan::call('test', [
                '--json' => true
            ]);
            
            $output = Artisan::output();
            $testData = json_decode($output, true);
            
            // Format the response
            $formattedResponse = [
                'tests' => $testData['tests'] ?? 0,
                'assertions' => $testData['assertions'] ?? 0,
                'duration' => isset($testData['duration']) ? round($testData['duration'], 2) : 0,
                'suites' => $this->formatTestSuites($testData['testsuite'] ?? []),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $formattedResponse,
            ]);
        } catch (\Exception $e) {
            // Fallback: run tests without JSON and parse
            return $this->runAndParse();
        }
    }
    
    private function runAndParse(): JsonResponse
    {
        try {
            Artisan::call('test');
            $output = Artisan::output();
            
            // Parse PHPUnit output
            return response()->json([
                'success' => true,
                'data' => [
                    'raw_output' => $output,
                    'message' => 'Тесты выполнены. Парсинг результатов.',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function formatTestSuites(array $suites): array
    {
        $formatted = [];
        
        foreach ($suites as $suite) {
            $tests = [];
            
            if (isset($suite['tests']['test'])) {
                foreach ($suite['tests']['test'] as $test) {
                    $tests[] = [
                        'name' => $test['@attributes']['name'] ?? '',
                        'status' => isset($test['@attributes']['status']) && $test['@attributes']['status'] === '0' ? 'PASS' : 'FAIL',
                        'duration' => isset($test['@attributes']['time']) ? round($test['@attributes']['time'], 3) : 0,
                    ];
                }
            }
            
            $formatted[] = [
                'name' => $suite['@attributes']['name'] ?? 'Unknown',
                'tests' => $tests,
            ];
        }
        
        return $formatted;
    }
}

