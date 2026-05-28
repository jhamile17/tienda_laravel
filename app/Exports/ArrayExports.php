<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ArrayExports implements
    FromArray,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    protected array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    /**
     * Estilos generales
     */
    public function styles(Worksheet $sheet)
    {
        return [

            // Encabezados
            1 => [

                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ],
                    'size' => 12,
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,

                    'startColor' => [
                        'rgb' => '6F4E37'
                    ]
                ],
            ],
        ];
    }

    /**
     * Eventos del Excel
     */
    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (
                AfterSheet $event
            ) {

                $sheet = $event
                    ->sheet
                    ->getDelegate();

                // Bordes
                $sheet->getStyle(
                    'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow()
                )->applyFromArray([

                    'borders' => [

                        'allBorders' => [

                            'borderStyle' =>
                                Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Centrar encabezados
                $sheet->getStyle('A1:F1')
                    ->getAlignment()
                    ->setHorizontal('center');

                // Fila TOTAL
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle(
                    'A' . $lastRow . ':' . $sheet->getHighestColumn() . $lastRow
                )->applyFromArray([

                    'font' => [
                        'bold' => true,
                    ],

                    'fill' => [

                        'fillType' =>
                            Fill::FILL_SOLID,

                        'startColor' => [
                            'rgb' => 'FFF2CC'
                        ]
                    ]
                ]);
            },
        ];
    }
}