<?php

namespace Webkul\BagistoApi\Admin\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminGridExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function __construct(
        protected array $headings,
        protected array $rows
    ) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }
}
