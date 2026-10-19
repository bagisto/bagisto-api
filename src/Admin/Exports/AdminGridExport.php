<?php

namespace Webkul\BagistoApi\Admin\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminGridExport implements FromArray, ShouldAutoSize, WithHeadings
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected array $headings,
        protected array $rows
    ) {}

    /**
     * Headings.
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * Rows.
     */
    public function array(): array
    {
        return $this->rows;
    }
}
