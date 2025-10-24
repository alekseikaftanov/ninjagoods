<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductCsvController extends Controller
{
    public function export(): JsonResponse
    {
        try {
            $products = Product::with('category')->get();
            
            $csvData = [];
            $csvData[] = [
                'name',
                'photo_url', 
                'description',
                'unit',
                'price',
                'min_order',
                'category_name',
                'category_id'
            ];
            
            foreach ($products as $product) {
                $csvData[] = [
                    $product->name,
                    $product->photo_url,
                    $product->description,
                    $product->unit,
                    $product->price,
                    $product->min_order,
                    $product->category->name ?? '',
                    $product->category_id
                ];
            }
            
            $csvContent = $this->arrayToCsv($csvData);
            
            Log::info('Products CSV export', [
                'products_count' => $products->count(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'csv_content' => $csvContent,
                    'filename' => 'products_' . now()->format('Y-m-d_H-i-s') . '.csv',
                    'products_count' => $products->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('CSV export failed', [
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при экспорте CSV: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function import(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'csv_content' => 'required|string',
            ]);
            
            $csvContent = $request->csv_content;
            $csvData = $this->csvToArray($csvContent);
            
            if (empty($csvData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV файл пуст или имеет неправильный формат'
                ], 400);
            }
            
            // Проверяем заголовки
            $headers = array_shift($csvData);
            $expectedHeaders = ['name', 'photo_url', 'description', 'unit', 'price', 'min_order', 'category_name', 'category_id'];
            
            if (array_diff($expectedHeaders, $headers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неправильный формат CSV файла. Ожидаемые колонки: ' . implode(', ', $expectedHeaders)
                ], 400);
            }
            
            $results = [
                'success' => 0,
                'errors' => 0,
                'skipped' => 0,
                'details' => []
            ];
            
            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2; // +2 потому что мы убрали заголовки и индексы начинаются с 0
                
                try {
                    // Валидация данных строки
                    $validator = Validator::make([
                        'name' => $row[0] ?? '',
                        'photo_url' => $row[1] ?? '',
                        'description' => $row[2] ?? '',
                        'unit' => $row[3] ?? '',
                        'price' => $row[4] ?? '',
                        'min_order' => $row[5] ?? '',
                        'category_name' => $row[6] ?? '',
                        'category_id' => $row[7] ?? ''
                    ], [
                        'name' => 'required|string|max:255',
                        'photo_url' => 'required|string|max:500',
                        'description' => 'required|string',
                        'unit' => 'required|in:штуки,килограммы',
                        'price' => 'required|numeric|min:0',
                        'min_order' => 'required|numeric|min:0.01',
                        'category_name' => 'nullable|string',
                        'category_id' => 'nullable|integer'
                    ]);
                    
                    if ($validator->fails()) {
                        $results['errors']++;
                        $results['details'][] = [
                            'row' => $rowNumber,
                            'status' => 'error',
                            'message' => 'Ошибка валидации: ' . implode(', ', $validator->errors()->all())
                        ];
                        continue;
                    }
                    
                    $data = $validator->validated();
                    
                    // Определяем category_id
                    $categoryId = null;
                    if (!empty($data['category_id'])) {
                        $categoryId = $data['category_id'];
                    } elseif (!empty($data['category_name'])) {
                        $category = Category::where('name', $data['category_name'])->first();
                        if ($category) {
                            $categoryId = $category->id;
                        }
                    }
                    
                    if (!$categoryId) {
                        $results['errors']++;
                        $results['details'][] = [
                            'row' => $rowNumber,
                            'status' => 'error',
                            'message' => 'Категория не найдена. Проверьте category_name или category_id. Доступные категории: ' . Category::pluck('name', 'id')->map(function($name, $id) { return "$name (ID: $id)"; })->join(', ')
                        ];
                        continue;
                    }
                    
                    // Проверяем, существует ли уже товар с таким именем
                    $existingProduct = Product::where('name', $data['name'])->first();
                    if ($existingProduct) {
                        $results['skipped']++;
                        $results['details'][] = [
                            'row' => $rowNumber,
                            'status' => 'skipped',
                            'message' => 'Товар с таким именем уже существует'
                        ];
                        continue;
                    }
                    
                    // Создаем товар
                    Product::create([
                        'name' => $data['name'],
                        'photo_url' => $data['photo_url'],
                        'description' => $data['description'],
                        'unit' => $data['unit'],
                        'price' => $data['price'],
                        'min_order' => $data['min_order'],
                        'category_id' => $categoryId
                    ]);
                    
                    $results['success']++;
                    $results['details'][] = [
                        'row' => $rowNumber,
                        'status' => 'success',
                        'message' => 'Товар успешно создан'
                    ];
                    
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['details'][] = [
                        'row' => $rowNumber,
                        'status' => 'error',
                        'message' => 'Ошибка: ' . $e->getMessage()
                    ];
                }
            }
            
            Log::info('Products CSV import completed', [
                'success' => $results['success'],
                'errors' => $results['errors'],
                'skipped' => $results['skipped'],
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('CSV import failed', [
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при импорте CSV: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getTemplate(): JsonResponse
    {
        $csvData = [];
        $csvData[] = [
            'name',
            'photo_url', 
            'description',
            'unit',
            'price',
            'min_order',
            'category_name',
            'category_id'
        ];
        
        // Добавляем примеры с существующими категориями
        $csvData[] = [
            'Микрозелень салата Айсберг',
            'https://example.com/image1.jpg',
            'Нежная микрозелень салата Айсберг с хрустящими листочками',
            'штуки',
            '150.00',
            '1.00',
            'Листовая зелень',
            '1'
        ];
        
        $csvContent = $this->arrayToCsv($csvData);
        
        return response()->json([
            'success' => true,
            'data' => [
                'csv_content' => $csvContent,
                'filename' => 'products_template.csv'
            ]
        ]);
    }
    
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    private function csvToArray(string $csvContent): array
    {
        // Проверяем, что содержимое не является бинарным файлом
        if (strlen($csvContent) > 0 && ord($csvContent[0]) < 32 && ord($csvContent[0]) !== 9 && ord($csvContent[0]) !== 10 && ord($csvContent[0]) !== 13) {
            throw new \Exception('Загруженный файл не является текстовым CSV файлом. Возможно, это бинарный файл (Excel, Numbers и т.д.). Пожалуйста, сохраните файл в формате CSV.');
        }
        
        // Проверяем, что содержимое содержит хотя бы одну запятую или точку с запятой (признак CSV)
        if (strpos($csvContent, ',') === false && strpos($csvContent, ';') === false) {
            throw new \Exception('Файл не содержит разделителей CSV (запятых или точек с запятой). Убедитесь, что это корректный CSV файл.');
        }
        
        // Определяем разделитель
        $delimiter = (strpos($csvContent, ',') !== false) ? ',' : ';';
        
        $lines = str_getcsv($csvContent, "\n");
        $data = [];
        
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $data[] = str_getcsv($line, $delimiter);
            }
        }
        
        return $data;
    }
}
