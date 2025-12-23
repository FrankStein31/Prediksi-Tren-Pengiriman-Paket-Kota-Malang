<?php

require __DIR__ . '/vendor/autoload.php';

$excelPath = __DIR__ . '/python/data/model_comparison_results.xlsx';

if (!file_exists($excelPath)) {
    echo "Excel file not found!\n";
    exit(1);
}

echo "Loading Excel file...\n";
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);

echo "Total sheets: " . $spreadsheet->getSheetCount() . "\n\n";

for ($i = 0; $i < $spreadsheet->getSheetCount(); $i++) {
    $sheet = $spreadsheet->getSheet($i);
    echo "Sheet $i: " . $sheet->getTitle() . "\n";
    
    // Get data preview
    $data = $sheet->toArray();
    echo "  Rows: " . count($data) . "\n";
    echo "  Columns: " . (isset($data[0]) ? count($data[0]) : 0) . "\n";
    
    // Show first few rows
    echo "  Preview:\n";
    for ($row = 0; $row < min(3, count($data)); $row++) {
        echo "    Row $row: " . implode(' | ', array_map(function($v) { 
            return is_null($v) ? 'NULL' : (string)$v; 
        }, $data[$row])) . "\n";
    }
    echo "\n";
}
