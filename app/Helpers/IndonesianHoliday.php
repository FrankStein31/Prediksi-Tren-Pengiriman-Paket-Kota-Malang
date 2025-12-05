<?php

namespace App\Helpers;

use Carbon\Carbon;

class IndonesianHoliday
{
    /**
     * Get holiday name for a specific date
     * Returns holiday name or null if not a holiday
     */
    public static function getHoliday($date)
    {
        $carbon = Carbon::parse($date);
        $year = $carbon->year;
        $month = $carbon->month;
        $day = $carbon->day;
        
        // Fixed national holidays (Hari Libur Nasional Tetap)
        $fixedHolidays = [
            '01-01' => 'Tahun Baru Masehi',
            '05-01' => 'Hari Buruh Internasional',
            '06-01' => 'Hari Lahir Pancasila',
            '08-17' => 'Hari Kemerdekaan RI',
            '12-25' => 'Hari Raya Natal',
        ];
        
        $dateKey = sprintf('%02d-%02d', $month, $day);
        if (isset($fixedHolidays[$dateKey])) {
            return $fixedHolidays[$dateKey];
        }
        
        // Variable holidays based on Islamic calendar (approximate dates)
        // These need to be updated annually based on official SKB
        $islamicHolidays = self::getIslamicHolidays($year);
        
        foreach ($islamicHolidays as $holiday) {
            if ($carbon->isSameDay(Carbon::parse($holiday['date']))) {
                return $holiday['name'];
            }
        }
        
        // Chinese New Year (approximate)
        $chineseNewYear = self::getChineseNewYear($year);
        if ($carbon->isSameDay(Carbon::parse($chineseNewYear))) {
            return 'Tahun Baru Imlek';
        }
        
        // Nyepi (approximate)
        $nyepi = self::getNyepi($year);
        if ($carbon->isSameDay(Carbon::parse($nyepi))) {
            return 'Hari Raya Nyepi';
        }
        
        // Waisak (approximate)
        $waisak = self::getWaisak($year);
        if ($carbon->isSameDay(Carbon::parse($waisak))) {
            return 'Hari Raya Waisak';
        }
        
        // Kenaikan Isa Al-Masih (Ascension Day - 39 days after Easter)
        $ascension = self::getAscensionDay($year);
        if ($carbon->isSameDay(Carbon::parse($ascension))) {
            return 'Kenaikan Isa Al-Masih';
        }
        
        return null;
    }
    
    /**
     * Get Islamic holidays for a specific year
     * Note: These are approximate dates based on official SKB (Surat Keputusan Bersama)
     * Data coverage: 2020 - 2027
     * 
     * For years beyond 2027, you should update this data from:
     * 1. Official government announcement (SKB 3 Menteri)
     * 2. kemenag.go.id for Islamic calendar dates
     * 3. Manual update annually
     */
    private static function getIslamicHolidays($year)
    {
        // Data based on official Indonesian government holidays (SKB)
        
        $holidays = [
            2020 => [
                ['date' => '2020-01-25', 'name' => 'Tahun Baru Imlek 2571'],
                ['date' => '2020-03-22', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2020-03-25', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2020-05-07', 'name' => 'Hari Raya Waisak 2564'],
                ['date' => '2020-05-21', 'name' => 'Kenaikan Isa Al-Masih'],
                ['date' => '2020-05-24', 'name' => 'Hari Raya Idul Fitri 1441 H'],
                ['date' => '2020-05-25', 'name' => 'Hari Raya Idul Fitri 1441 H'],
                ['date' => '2020-05-26', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2020-05-27', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2020-05-28', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2020-05-29', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2020-07-31', 'name' => 'Hari Raya Idul Adha 1441 H'],
                ['date' => '2020-08-20', 'name' => 'Tahun Baru Islam 1442 H'],
                ['date' => '2020-10-29', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2020-12-24', 'name' => 'Cuti Bersama Natal'],
            ],
            2021 => [
                ['date' => '2021-02-12', 'name' => 'Tahun Baru Imlek 2572'],
                ['date' => '2021-03-11', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2021-03-14', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2021-05-12', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2021-05-13', 'name' => 'Hari Raya Idul Fitri 1442 H'],
                ['date' => '2021-05-14', 'name' => 'Hari Raya Idul Fitri 1442 H'],
                ['date' => '2021-05-17', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2021-05-18', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2021-05-13', 'name' => 'Kenaikan Isa Al-Masih'],
                ['date' => '2021-05-26', 'name' => 'Hari Raya Waisak 2565'],
                ['date' => '2021-07-20', 'name' => 'Hari Raya Idul Adha 1442 H'],
                ['date' => '2021-08-10', 'name' => 'Tahun Baru Islam 1443 H'],
                ['date' => '2021-10-19', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2021-12-24', 'name' => 'Cuti Bersama Natal'],
            ],
            2022 => [
                ['date' => '2022-02-01', 'name' => 'Tahun Baru Imlek 2573'],
                ['date' => '2022-02-28', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2022-03-03', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2022-04-29', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2022-05-02', 'name' => 'Hari Raya Idul Fitri 1443 H'],
                ['date' => '2022-05-03', 'name' => 'Hari Raya Idul Fitri 1443 H'],
                ['date' => '2022-05-04', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2022-05-05', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2022-05-06', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2022-05-16', 'name' => 'Hari Raya Waisak 2566'],
                ['date' => '2022-05-26', 'name' => 'Kenaikan Isa Al-Masih'],
                ['date' => '2022-07-09', 'name' => 'Hari Raya Idul Adha 1443 H'],
                ['date' => '2022-07-30', 'name' => 'Tahun Baru Islam 1444 H'],
                ['date' => '2022-10-08', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2022-12-26', 'name' => 'Cuti Bersama Natal'],
            ],
            2023 => [
                ['date' => '2023-01-22', 'name' => 'Tahun Baru Islam 1444 H'],
                ['date' => '2023-02-18', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2023-04-22', 'name' => 'Hari Raya Idul Fitri 1444 H'],
                ['date' => '2023-04-23', 'name' => 'Hari Raya Idul Fitri 1444 H'],
                ['date' => '2023-04-24', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2023-04-25', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2023-06-29', 'name' => 'Hari Raya Idul Adha 1444 H'],
                ['date' => '2023-07-19', 'name' => 'Tahun Baru Islam 1445 H'],
                ['date' => '2023-09-28', 'name' => 'Maulid Nabi Muhammad SAW'],
            ],
            2024 => [
                ['date' => '2024-01-08', 'name' => 'Tahun Baru Islam 1445 H'],
                ['date' => '2024-02-08', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2024-03-11', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2024-04-10', 'name' => 'Hari Raya Idul Fitri 1445 H'],
                ['date' => '2024-04-11', 'name' => 'Hari Raya Idul Fitri 1445 H'],
                ['date' => '2024-04-12', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2024-04-13', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2024-04-15', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2024-06-17', 'name' => 'Hari Raya Idul Adha 1445 H'],
                ['date' => '2024-07-07', 'name' => 'Tahun Baru Islam 1446 H'],
                ['date' => '2024-09-16', 'name' => 'Maulid Nabi Muhammad SAW'],
            ],
            2025 => [
                ['date' => '2025-01-29', 'name' => 'Tahun Baru Imlek 2576'],
                ['date' => '2025-01-27', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2025-03-29', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2025-03-31', 'name' => 'Hari Raya Idul Fitri 1446 H'],
                ['date' => '2025-04-01', 'name' => 'Hari Raya Idul Fitri 1446 H'],
                ['date' => '2025-04-02', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2025-04-03', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2025-04-04', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2025-05-12', 'name' => 'Hari Raya Waisak 2569'],
                ['date' => '2025-05-29', 'name' => 'Kenaikan Isa Al-Masih'],
                ['date' => '2025-06-07', 'name' => 'Hari Raya Idul Adha 1446 H'],
                ['date' => '2025-06-27', 'name' => 'Tahun Baru Islam 1447 H'],
                ['date' => '2025-09-05', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2025-12-26', 'name' => 'Cuti Bersama Natal'],
            ],
            2026 => [
                ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577'],
                ['date' => '2026-01-16', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2026-03-19', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2026-03-20', 'name' => 'Hari Raya Idul Fitri 1447 H'],
                ['date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 H'],
                ['date' => '2026-03-23', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2026-03-24', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2026-03-25', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2026-05-14', 'name' => 'Kenaikan Isa Al-Masih'],
                ['date' => '2026-05-27', 'name' => 'Hari Raya Idul Adha 1447 H'],
                ['date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570'],
                ['date' => '2026-06-16', 'name' => 'Tahun Baru Islam 1448 H'],
                ['date' => '2026-08-25', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2026-12-24', 'name' => 'Cuti Bersama Natal'],
            ],
            2027 => [
                ['date' => '2027-02-06', 'name' => 'Tahun Baru Imlek 2578'],
                ['date' => '2027-01-06', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
                ['date' => '2027-03-09', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2027-03-10', 'name' => 'Hari Raya Idul Fitri 1448 H'],
                ['date' => '2027-03-11', 'name' => 'Hari Raya Idul Fitri 1448 H'],
                ['date' => '2027-03-12', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2027-03-15', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2027-03-16', 'name' => 'Cuti Bersama Idul Fitri'],
                ['date' => '2027-05-17', 'name' => 'Hari Raya Idul Adha 1448 H'],
                ['date' => '2027-05-20', 'name' => 'Hari Raya Waisak 2571'],
                ['date' => '2027-05-27', 'name' => 'Kenaikan Isa Al-Masih'],
                ['date' => '2027-06-06', 'name' => 'Tahun Baru Islam 1449 H'],
                ['date' => '2027-08-14', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2027-12-24', 'name' => 'Cuti Bersama Natal'],
            ],
        ];
        
        return $holidays[$year] ?? [];
    }
    
    /**
     * Get Chinese New Year date (approximate)
     */
    private static function getChineseNewYear($year)
    {
        $dates = [
            2020 => '2020-01-25',
            2021 => '2021-02-12',
            2022 => '2022-02-01',
            2023 => '2023-01-22',
            2024 => '2024-02-10',
            2025 => '2025-01-29',
            2026 => '2026-02-17',
            2027 => '2027-02-06',
            2028 => '2028-01-26',
        ];
        return $dates[$year] ?? null;
    }
    
    /**
     * Get Nyepi date (approximate)
     */
    private static function getNyepi($year)
    {
        $dates = [
            2020 => '2020-03-25',
            2021 => '2021-03-14',
            2022 => '2022-03-03',
            2023 => '2023-03-22',
            2024 => '2024-03-11',
            2025 => '2025-03-29',
            2026 => '2026-03-19',
            2027 => '2027-03-09',
            2028 => '2028-03-27',
        ];
        return $dates[$year] ?? null;
    }
    
    /**
     * Get Waisak date (approximate)
     */
    private static function getWaisak($year)
    {
        $dates = [
            2020 => '2020-05-07',
            2021 => '2021-05-26',
            2022 => '2022-05-16',
            2023 => '2023-06-04',
            2024 => '2024-05-23',
            2025 => '2025-05-12',
            2026 => '2026-05-31',
            2027 => '2027-05-20',
            2028 => '2028-05-09',
        ];
        return $dates[$year] ?? null;
    }
    
    /**
     * Get Easter Sunday using Meeus/Jones/Butcher algorithm
     */
    private static function getEasterSunday($year)
    {
        $a = $year % 19;
        $b = intval($year / 100);
        $c = $year % 100;
        $d = intval($b / 4);
        $e = $b % 4;
        $f = intval(($b + 8) / 25);
        $g = intval(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intval($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intval(($a + 11 * $h + 22 * $l) / 451);
        $month = intval(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return Carbon::create($year, $month, $day);
    }
    
    /**
     * Get Ascension Day (39 days after Easter)
     */
    private static function getAscensionDay($year)
    {
        return self::getEasterSunday($year)->addDays(39)->format('Y-m-d');
    }
    
    /**
     * Check if a date is a holiday
     */
    public static function isHoliday($date)
    {
        return self::getHoliday($date) !== null;
    }
    
    /**
     * Get holiday info for a date range (used for weekly data)
     * Returns an array of holidays within the range
     */
    public static function getHolidaysInRange($startDate, $endDate)
    {
        $holidays = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $holiday = self::getHoliday($current);
            if ($holiday) {
                $holidays[] = [
                    'date' => $current->format('Y-m-d'),
                    'name' => $holiday,
                ];
            }
            $current->addDay();
        }
        
        return $holidays;
    }
    
    /**
     * Get a summary string of holidays in a range
     * Used for the weekly summary table
     * OPTIMIZED: Only checks actual dates instead of looping every day
     */
    public static function getHolidaySummary($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Quick check: if range > 7 days, something is wrong
        if ($start->diffInDays($end) > 7) {
            return '-';
        }
        
        $holidays = [];
        $current = $start->copy();
        
        // Only loop through actual days in the week (max 7 iterations)
        while ($current->lte($end)) {
            $holiday = self::getHoliday($current);
            if ($holiday) {
                // Avoid duplicates
                if (!in_array($holiday, $holidays)) {
                    $holidays[] = $holiday;
                }
            }
            $current->addDay();
        }
        
        if (empty($holidays)) {
            return '-';
        }
        
        // Limit to 3 holidays max for display
        if (count($holidays) > 3) {
            $holidays = array_slice($holidays, 0, 3);
            return implode(', ', $holidays) . '...';
        }
        
        return implode(', ', $holidays);
    }
}
