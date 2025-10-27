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
            // Run tests with JUnit XML output using exec
            $outputFile = storage_path('logs/test-results.xml');
            $basePath = base_path();
            
            // Run PHPUnit directly
            $command = "cd {$basePath} && php artisan test --log-junit {$outputFile} 2>&1";
            exec($command, $outputLines, $returnCode);
            $output = implode("\n", $outputLines);
            
            // Parse JUnit XML if it exists
            if (file_exists($outputFile)) {
                $testData = $this->parseJUnitXml($outputFile);
                unlink($outputFile); // Clean up
                
                return response()->json([
                    'success' => true,
                    'data' => $testData,
                ]);
            }
            
            // Fallback: parse console output
            return $this->parseConsoleOutput($output);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function parseJUnitXml(string $file): array
    {
        $xml = simplexml_load_file($file);
        
        if (!$xml) {
            return [
                'tests' => 0,
                'assertions' => 0,
                'duration' => 0,
                'suites' => [],
            ];
        }
        
        $suites = [];
        $mainSuite = $xml->testsuite;
        
        // Get total stats from main testsuite
        $totalTests = (int) $mainSuite['tests'];
        $totalAssertions = (int) $mainSuite['assertions'];
        $totalDuration = (float) $mainSuite['time'];
        
        // Parse nested testsuites recursively
        $this->parseTestSuite($mainSuite, $suites);
        
        return [
            'tests' => $totalTests,
            'assertions' => $totalAssertions,
            'duration' => round($totalDuration, 2),
            'suites' => $suites,
        ];
    }
    
    private function parseTestSuite($suite, &$result): void
    {
        $suiteName = basename((string) $suite['file'] ?? (string) $suite['name']);
        $tests = [];
        
        // Get direct test cases
        foreach ($suite->testcase as $test) {
            $tests[] = [
                'name' => (string) $test['name'],
                'status' => isset($test->failure) || isset($test->error) ? 'FAIL' : 'PASS',
                'duration' => round((float) $test['time'], 3),
            ];
        }
        
        // Add suite if it has tests or nested suites
        if (count($tests) > 0 || count($suite->testsuite) > 0) {
            if (count($tests) > 0) {
                $result[] = [
                    'name' => $suiteName,
                    'tests' => $tests,
                ];
            }
        }
        
        // Recursively parse nested testsuites
        foreach ($suite->testsuite as $nestedSuite) {
            $this->parseTestSuite($nestedSuite, $result);
        }
    }
    
    private function parseConsoleOutput(string $output): JsonResponse
    {
        // Parse PHPUnit console output
        preg_match('/(\d+) passed/', $output, $passedMatches);
        preg_match('/(\d+) assertions/', $output, $assertionsMatches);
        preg_match('/Duration:\s+([\d.]+)\s+s/', $output, $durationMatches);
        
        $tests = isset($passedMatches[1]) ? (int) $passedMatches[1] : 0;
        $assertions = isset($assertionsMatches[1]) ? (int) $assertionsMatches[1] : 0;
        $duration = isset($durationMatches[1]) ? round((float) $durationMatches[1], 2) : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'tests' => $tests,
                'assertions' => $assertions,
                'duration' => $duration,
                'suites' => [], // Will need actual parsing for suites
                'raw_output' => $output,
            ],
        ]);
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

