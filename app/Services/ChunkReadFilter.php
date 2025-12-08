<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Chunk Read Filter for PhpSpreadsheet
 * Allows reading Excel files in chunks to reduce memory usage
 */
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 0;
    private int $endRow = 0;

    /**
     * Set the row range to read
     * 
     * @param int $startRow Starting row number (1-indexed)
     * @param int $chunkSize Number of rows to read
     */
    public function __construct(int $startRow, int $chunkSize)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize - 1;
    }

    /**
     * Determine if a cell should be read
     * 
     * @param string $columnAddress Column address (e.g., 'A', 'B', 'C')
     * @param int $row Row number
     * @param string $worksheetName Worksheet name
     * @return bool True if cell should be read, false otherwise
     */
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // Only read rows within our chunk range
        return ($row >= $this->startRow && $row <= $this->endRow);
    }
}
