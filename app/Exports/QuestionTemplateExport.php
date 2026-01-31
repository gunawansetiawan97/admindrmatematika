<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'question_text',
            'type',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'option_e',
            'correct_answer',
            'points',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Apa ibukota Indonesia?',
                'multiple_choice',
                'Jakarta',
                'Bandung',
                'Surabaya',
                'Medan',
                'Makassar',
                'A',
                '10',
            ],
            [
                'Siapa presiden pertama Indonesia?',
                'multiple_choice',
                'Soekarno',
                'Soeharto',
                'Habibie',
                'Megawati',
                '',
                'A',
                '10',
            ],
            [
                'Jelaskan proses fotosintesis!',
                'essay',
                '',
                '',
                '',
                '',
                '',
                '',
                '20',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
