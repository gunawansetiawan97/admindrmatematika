<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionPackage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionPackageImport implements ToCollection, WithHeadingRow
{
    protected QuestionPackage $package;
    protected array $errors = [];
    protected int $importedCount = 0;

    public function __construct(QuestionPackage $package)
    {
        $this->package = $package;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $order = 1;

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header row and 0-index

                // Skip empty rows
                if (empty($row['question_text'])) {
                    continue;
                }

                // Validate row
                $validator = $this->validateRow($row, $rowNumber);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) {
                        $this->errors[] = "Baris {$rowNumber}: {$error}";
                    }
                    continue;
                }

                // Normalize type
                $type = strtolower(trim($row['type']));
                if ($type === 'pilgan' || $type === 'pg' || $type === 'mc') {
                    $type = 'multiple_choice';
                } elseif ($type === 'isian' || $type === 'uraian') {
                    $type = 'essay';
                }

                // Create question
                $question = Question::create([
                    'package_id' => $this->package->id,
                    'question_text' => trim($row['question_text']),
                    'question_type' => $type,
                    'correct_answer' => $type === 'multiple_choice' ? strtoupper(trim($row['correct_answer'] ?? '')) : null,
                    'points' => (int) ($row['points'] ?? 1),
                    'order' => $order++,
                ]);

                // Create options for multiple choice
                if ($type === 'multiple_choice') {
                    $options = [];
                    foreach (['a', 'b', 'c', 'd', 'e'] as $label) {
                        $optionKey = "option_{$label}";
                        if (!empty($row[$optionKey])) {
                            $options[] = [
                                'question_id' => $question->id,
                                'option_label' => strtoupper($label),
                                'option_text' => trim($row[$optionKey]),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    if (!empty($options)) {
                        QuestionOption::insert($options);
                    }
                }

                $this->importedCount++;
            }

            if (!empty($this->errors)) {
                DB::rollBack();
                return;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }

    protected function validateRow($row, int $rowNumber): \Illuminate\Validation\Validator
    {
        $type = strtolower(trim($row['type'] ?? ''));

        // Normalize type for validation
        if (in_array($type, ['pilgan', 'pg', 'mc', 'multiple_choice'])) {
            $type = 'multiple_choice';
        } elseif (in_array($type, ['essay', 'isian', 'uraian'])) {
            $type = 'essay';
        }

        $rules = [
            'question_text' => ['required', 'string'],
            'type' => ['required'],
            'points' => ['required', 'numeric', 'min:1'],
        ];

        $messages = [
            'question_text.required' => 'Teks soal wajib diisi.',
            'type.required' => 'Tipe soal wajib diisi.',
            'points.required' => 'Poin wajib diisi.',
            'points.min' => 'Poin minimal 1.',
        ];

        // Additional validation for multiple choice
        if ($type === 'multiple_choice') {
            $rules['correct_answer'] = ['required', 'in:A,B,C,D,E,a,b,c,d,e'];
            $rules['option_a'] = ['required', 'string'];
            $rules['option_b'] = ['required', 'string'];

            $messages['correct_answer.required'] = 'Jawaban benar wajib diisi untuk soal pilihan ganda.';
            $messages['correct_answer.in'] = 'Jawaban benar harus salah satu dari A, B, C, D, atau E.';
            $messages['option_a.required'] = 'Pilihan A wajib diisi untuk soal pilihan ganda.';
            $messages['option_b.required'] = 'Pilihan B wajib diisi untuk soal pilihan ganda.';
        }

        // Check type validity
        $data = $row->toArray();
        if (!in_array($type, ['multiple_choice', 'essay'])) {
            $data['type'] = null; // Force validation error
        }

        return Validator::make($data, $rules, $messages);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
