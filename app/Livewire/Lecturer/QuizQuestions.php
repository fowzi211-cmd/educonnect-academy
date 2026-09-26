<?php

namespace App\Livewire\Lecturer;

use App\Models\AuditLog;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

#[Layout('layouts.app')]
class QuizQuestions extends Component
{
    use WithFileUploads;

    public Quiz $quiz;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $type = QuizQuestion::TYPE_MCQ_SINGLE;

    public string $prompt = '';

    public string $points = '1';

    public string $explanation = '';

    /** @var array<int, array{text: string, is_correct: bool}> */
    public array $options = [];

    /** @var array<int, array{left: string, right: string}> */
    public array $matchingPairs = [];

    public string $correctShortAnswer = '';

    public string $correctNumerical = '';

    public string $numericalTolerance = '0';

    public bool $showBulkAdd = false;

    public string $bulkAddText = '';

    public int $bulkAddCreated = 0;

    /** @var array<int, array{line: string, error: string}> */
    public array $bulkAddFailures = [];

    public $bulkAddFile = null;

    /** Question types expressible as a single bulk-paste line. */
    protected const BULK_TYPES = [
        QuizQuestion::TYPE_MCQ_SINGLE,
        QuizQuestion::TYPE_MCQ_MULTI,
        QuizQuestion::TYPE_TRUE_FALSE,
        QuizQuestion::TYPE_SHORT_ANSWER,
    ];

    protected const BULK_ADD_MAX_ROWS = 300;

    public function mount(Quiz $quiz): void
    {
        abort_unless($quiz->course->isTaughtBy(Auth::user()), 403);
        $this->quiz = $quiz;
    }

    public function newForm(): void
    {
        $this->resetFormFields();
        $this->showForm = true;
    }

    protected function resetFormFields(): void
    {
        $this->reset(['editingId', 'prompt', 'points', 'explanation', 'options', 'matchingPairs', 'correctShortAnswer', 'correctNumerical', 'numericalTolerance']);
        $this->type = QuizQuestion::TYPE_MCQ_SINGLE;
        $this->points = '1';
        $this->numericalTolerance = '0';
        $this->seedDefaultsForType();
    }

    public function updatedType(): void
    {
        $this->seedDefaultsForType();
    }

    protected function seedDefaultsForType(): void
    {
        if ($this->type === QuizQuestion::TYPE_TRUE_FALSE) {
            $this->options = [
                ['text' => __('True'), 'is_correct' => true],
                ['text' => __('False'), 'is_correct' => false],
            ];
        } elseif (in_array($this->type, [QuizQuestion::TYPE_MCQ_SINGLE, QuizQuestion::TYPE_MCQ_MULTI], true) && count($this->options) < 2) {
            $this->options = [
                ['text' => '', 'is_correct' => false],
                ['text' => '', 'is_correct' => false],
            ];
        } elseif ($this->type === QuizQuestion::TYPE_MATCHING && count($this->matchingPairs) < 2) {
            $this->matchingPairs = [
                ['left' => '', 'right' => ''],
                ['left' => '', 'right' => ''],
            ];
        }
    }

    public function addOptionRow(): void
    {
        $this->options[] = ['text' => '', 'is_correct' => false];
    }

    public function removeOptionRow(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function markSingleCorrect(int $index): void
    {
        foreach ($this->options as $i => $option) {
            $this->options[$i]['is_correct'] = $i === $index;
        }
    }

    public function addMatchingPairRow(): void
    {
        $this->matchingPairs[] = ['left' => '', 'right' => ''];
    }

    public function removeMatchingPairRow(int $index): void
    {
        unset($this->matchingPairs[$index]);
        $this->matchingPairs = array_values($this->matchingPairs);
    }

    public function edit(int $id): void
    {
        $question = $this->quiz->questions()->with('options')->findOrFail($id);

        $this->editingId = $question->id;
        $this->type = $question->type;
        $this->prompt = $question->prompt;
        $this->points = (string) $question->points;
        $this->explanation = (string) $question->explanation;
        $this->options = $question->options->map(fn ($o) => ['text' => $o->text, 'is_correct' => $o->is_correct])->all();
        $this->matchingPairs = $question->matching_pairs ?? [];
        $this->correctShortAnswer = (string) $question->correct_short_answer;
        $this->correctNumerical = (string) ($question->correct_numerical ?? '');
        $this->numericalTolerance = (string) ($question->numerical_tolerance ?? '0');
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
    }

    public function toggleBulkAdd(): void
    {
        $this->showBulkAdd = ! $this->showBulkAdd;
        $this->reset(['bulkAddText', 'bulkAddFailures', 'bulkAddCreated', 'bulkAddFile']);
    }

    /**
     * One question per line, pipe-delimited: type | points | prompt | ...answers.
     * Only the four types with a simple single-line answer shape are supported here —
     * essay, numerical, matching, and file-upload questions still need the full form.
     *
     *   mcq_single  | 1 | Capital of Sudan? | Cairo | *Khartoum | Nairobi
     *   mcq_multi   | 2 | Which are primes? | *2 | *3 | 4 | *5
     *   true_false  | 1 | The Nile flows through Sudan. | true
     *   short_answer| 1 | H2O is commonly known as? | water
     */
    public function runBulkAdd(): void
    {
        $this->bulkAddFailures = [];
        $this->bulkAddCreated = 0;

        $lines = array_values(array_filter(array_map('trim', explode("\n", $this->bulkAddText)), fn ($line) => $line !== ''));

        if (empty($lines)) {
            $this->addError('bulkAddText', __('Paste at least one row first.'));

            return;
        }

        if (count($lines) > self::BULK_ADD_MAX_ROWS) {
            $this->addError('bulkAddText', __('Import up to :max rows at a time — split larger lists into batches.', ['max' => self::BULK_ADD_MAX_ROWS]));

            return;
        }

        $position = (int) ($this->quiz->questions()->max('position') ?? 0);

        foreach ($lines as $line) {
            $cells = array_map('trim', explode('|', $line));

            if ($this->processBulkRow($cells, $line, $position + 1)) {
                $position++;
                $this->bulkAddCreated++;
            }
        }

        $this->bulkAddText = '';
    }

    /**
     * Same column layout as the paste format, spread across spreadsheet columns
     * instead of pipes: Type | Points | Prompt | ...answers. An optional header
     * row (first cell literally "type") is detected and skipped automatically.
     */
    public function importBulkFile(): void
    {
        $this->validate(['bulkAddFile' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv']]);

        $this->bulkAddFailures = [];
        $this->bulkAddCreated = 0;

        try {
            $spreadsheet = IOFactory::load($this->bulkAddFile->getRealPath());
        } catch (Throwable) {
            $this->addError('bulkAddFile', __('Could not read this file. Make sure it is a valid Excel (.xlsx), .xls, or .csv file.'));

            return;
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $position = (int) ($this->quiz->questions()->max('position') ?? 0);
        $dataRowCount = 0;

        foreach ($rows as $index => $row) {
            $cells = array_map(fn ($cell) => trim((string) $cell), $row);

            if (collect($cells)->every(fn ($cell) => $cell === '')) {
                continue;
            }

            if ($index === 0 && strtolower($cells[0] ?? '') === 'type') {
                continue;
            }

            $dataRowCount++;

            if ($dataRowCount > self::BULK_ADD_MAX_ROWS) {
                $this->addError('bulkAddFile', __('Import up to :max rows at a time — split larger files into batches.', ['max' => self::BULK_ADD_MAX_ROWS]));

                break;
            }

            $displayLine = implode(' | ', array_filter($cells, fn ($cell) => $cell !== ''));

            if ($this->processBulkRow($cells, $displayLine, $position + 1)) {
                $position++;
                $this->bulkAddCreated++;
            }
        }

        if ($dataRowCount === 0) {
            $this->addError('bulkAddFile', __('The file has no data rows.'));

            return;
        }

        $this->bulkAddFile = null;
    }

    /**
     * Shared validation + creation for one bulk row, from either the paste
     * textarea (pipe-split) or a spreadsheet row (column-split) — both hand in
     * the same shape: [type, points, prompt, ...answers]. Failures are pushed
     * to bulkAddFailures using the given human-readable line for display;
     * returns whether a question was created so the caller can advance its
     * running position counter and created-count.
     */
    protected function processBulkRow(array $cells, string $displayLine, int $position): bool
    {
        $type = strtolower($cells[0] ?? '');
        $points = $cells[1] ?? '';
        $prompt = $cells[2] ?? '';
        $rest = array_values(array_slice($cells, 3));

        if (! in_array($type, self::BULK_TYPES, true)) {
            $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('Unknown or unsupported type. Use mcq_single, mcq_multi, true_false, or short_answer.')];

            return false;
        }

        if (! is_numeric($points) || (float) $points < 0.25) {
            $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('Points must be a number of at least 0.25.')];

            return false;
        }

        if ($prompt === '') {
            $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('Prompt is required.')];

            return false;
        }

        $data = [
            'quiz_id' => $this->quiz->id,
            'type' => $type,
            'prompt' => $prompt,
            'points' => $points,
            'explanation' => null,
            'correct_short_answer' => null,
            'correct_numerical' => null,
            'numerical_tolerance' => 0,
            'matching_pairs' => null,
        ];

        $options = null;

        if (in_array($type, [QuizQuestion::TYPE_MCQ_SINGLE, QuizQuestion::TYPE_MCQ_MULTI], true)) {
            $rest = array_values(array_filter($rest, fn ($cell) => $cell !== ''));

            if (count($rest) < 2) {
                $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('Provide at least two answer options.')];

                return false;
            }

            $options = array_map(fn ($option) => [
                'text' => ltrim($option, '*'),
                'is_correct' => str_starts_with($option, '*'),
            ], $rest);

            $correctCount = collect($options)->where('is_correct', true)->count();

            if ($type === QuizQuestion::TYPE_MCQ_SINGLE && $correctCount !== 1) {
                $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('Mark exactly one option as correct with a leading *.')];

                return false;
            }

            if ($type === QuizQuestion::TYPE_MCQ_MULTI && $correctCount < 1) {
                $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('Mark at least one option as correct with a leading *.')];

                return false;
            }
        } elseif ($type === QuizQuestion::TYPE_TRUE_FALSE) {
            $answer = strtolower($rest[0] ?? '');

            if (! in_array($answer, ['true', 'false'], true)) {
                $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('The answer for true_false must be "true" or "false".')];

                return false;
            }

            $options = [
                ['text' => __('True'), 'is_correct' => $answer === 'true'],
                ['text' => __('False'), 'is_correct' => $answer === 'false'],
            ];
        } elseif ($type === QuizQuestion::TYPE_SHORT_ANSWER) {
            $answer = $rest[0] ?? '';

            if ($answer === '') {
                $this->bulkAddFailures[] = ['line' => $displayLine, 'error' => __('The correct answer is required for short_answer questions.')];

                return false;
            }

            $data['correct_short_answer'] = $answer;
        }

        DB::transaction(function () use ($data, $options, $position) {
            $question = $this->quiz->questions()->create($data + ['position' => $position]);

            foreach ($options ?? [] as $i => $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'is_correct' => $option['is_correct'],
                    'position' => $i,
                ]);
            }

            AuditLog::record('quiz_question.created', subject: $question);
        });

        return true;
    }

    protected function rules(): array
    {
        $rules = [
            'type' => ['required', 'in:'.implode(',', QuizQuestion::TYPES)],
            'prompt' => ['required', 'string', 'max:5000'],
            'points' => ['required', 'numeric', 'min:0.25'],
            'explanation' => ['nullable', 'string', 'max:2000'],
            'correctShortAnswer' => [$this->type === QuizQuestion::TYPE_SHORT_ANSWER ? 'required' : 'nullable', 'string', 'max:500'],
            'correctNumerical' => [$this->type === QuizQuestion::TYPE_NUMERICAL ? 'required' : 'nullable', 'numeric'],
            'numericalTolerance' => ['nullable', 'numeric', 'min:0'],
        ];

        // Only validate options/matchingPairs for the types that actually use them —
        // an empty array isn't null, so "nullable" alone wouldn't exempt an unused
        // field from a "min:2" check on the other types.
        if (in_array($this->type, [QuizQuestion::TYPE_MCQ_SINGLE, QuizQuestion::TYPE_MCQ_MULTI, QuizQuestion::TYPE_TRUE_FALSE], true)) {
            $rules['options'] = ['required', 'array', 'min:2'];
            $rules['options.*.text'] = ['required', 'string', 'max:500'];
        }

        if ($this->type === QuizQuestion::TYPE_MATCHING) {
            $rules['matchingPairs'] = ['required', 'array', 'min:2'];
            $rules['matchingPairs.*.left'] = ['required', 'string', 'max:255'];
            $rules['matchingPairs.*.right'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate($this->rules());

        if (in_array($this->type, [QuizQuestion::TYPE_MCQ_SINGLE, QuizQuestion::TYPE_TRUE_FALSE], true)) {
            if (collect($this->options)->where('is_correct', true)->count() !== 1) {
                $this->addError('options', __('Mark exactly one option as correct.'));

                return;
            }
        }

        if ($this->type === QuizQuestion::TYPE_MCQ_MULTI && collect($this->options)->where('is_correct', true)->count() < 1) {
            $this->addError('options', __('Mark at least one option as correct.'));

            return;
        }

        DB::transaction(function () {
            $data = [
                'quiz_id' => $this->quiz->id,
                'type' => $this->type,
                'prompt' => $this->prompt,
                'points' => $this->points,
                'explanation' => $this->explanation ?: null,
                'correct_short_answer' => $this->type === QuizQuestion::TYPE_SHORT_ANSWER ? $this->correctShortAnswer : null,
                'correct_numerical' => $this->type === QuizQuestion::TYPE_NUMERICAL ? $this->correctNumerical : null,
                'numerical_tolerance' => $this->type === QuizQuestion::TYPE_NUMERICAL ? ($this->numericalTolerance ?: 0) : 0,
                'matching_pairs' => $this->type === QuizQuestion::TYPE_MATCHING ? $this->matchingPairs : null,
            ];

            if ($this->editingId) {
                $question = $this->quiz->questions()->findOrFail($this->editingId);
                $question->update($data);
                $question->options()->delete();
                AuditLog::record('quiz_question.updated', subject: $question);
            } else {
                $position = ($this->quiz->questions()->max('position') ?? 0) + 1;
                $question = $this->quiz->questions()->create($data + ['position' => $position]);
                AuditLog::record('quiz_question.created', subject: $question);
            }

            if (in_array($this->type, [QuizQuestion::TYPE_MCQ_SINGLE, QuizQuestion::TYPE_MCQ_MULTI, QuizQuestion::TYPE_TRUE_FALSE], true)) {
                foreach ($this->options as $i => $option) {
                    $question->options()->create([
                        'text' => $option['text'],
                        'is_correct' => (bool) ($option['is_correct'] ?? false),
                        'position' => $i,
                    ]);
                }
            }
        });

        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $question = $this->quiz->questions()->findOrFail($id);
        AuditLog::record('quiz_question.deleted', subject: $question, old: ['prompt' => $question->prompt]);
        $question->delete();
    }

    public function moveQuestion(int $id, string $direction): void
    {
        $questions = $this->quiz->questions()->orderBy('position')->get();
        $index = $questions->search(fn ($q) => $q->id === $id);

        if ($index === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! isset($questions[$targetIndex])) {
            return;
        }

        $current = $questions[$index];
        $target = $questions[$targetIndex];

        [$current->position, $target->position] = [$target->position, $current->position];
        $current->save();
        $target->save();
    }

    public function render()
    {
        return view('livewire.lecturer.quiz-questions', [
            'questions' => $this->quiz->questions()->with('options')->get(),
        ]);
    }
}
