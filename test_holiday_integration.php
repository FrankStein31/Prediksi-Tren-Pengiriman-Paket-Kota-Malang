<?php
// Test script untuk memeriksa apakah IndonesianHoliday helper bekerja

require __DIR__ . '/vendor/autoload.php';

use App\Helpers\IndonesianHoliday;
use Carbon\Carbon;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test IndonesianHoliday Helper ===\n\n";

// Test beberapa tanggal dari screenshot
$testDates = [
    '2023-12-03', // Minggu 48, 2023
    '2023-12-10', // Minggu 49, 2023
    '2023-12-17', // Minggu 50, 2023
    '2023-12-24', // Minggu 51, 2023 (Cuti Natal)
    '2023-12-25', // Natal
    '2023-12-31', // Minggu 52, 2023
    '2024-01-01', // Tahun Baru
    '2024-01-07', // Minggu 1, 2024
    '2024-01-14', // Minggu 2, 2024
];

foreach ($testDates as $date) {
    $holiday = IndonesianHoliday::getHoliday($date);
    $isHoliday = $holiday !== null;
    
    echo "Date: $date\n";
    echo "Holiday: " . ($holiday ?? '-') . "\n";
    echo "Is Holiday: " . ($isHoliday ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

// Test dengan data format seperti Flask API
echo "\n=== Test dengan Format Flask API ===\n\n";

$forecastData = [
    ['date' => '2023-12-03', 'predicted' => 829],
    ['date' => '2023-12-10', 'predicted' => 810],
    ['date' => '2023-12-24', 'predicted' => 569],
    ['date' => '2023-12-25', 'predicted' => 589],
];

foreach ($forecastData as &$item) {
    $holiday = IndonesianHoliday::getHoliday($item['date']);
    $item['holiday'] = $holiday;
    $item['is_holiday'] = $holiday !== null;
}

echo json_encode($forecastData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";
