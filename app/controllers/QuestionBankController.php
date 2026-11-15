<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\QuestionBankService;

/**
 * Question Bank Management (admin): full CRUD for questions
 * (use case: Admin Manages Question Bank).
 */
final class QuestionBankController extends Controller
{
    /* ---------- list ---------- */

    public function index(?int $page): void
    {
        $this->requireRole('admin');
        $page = max(1, $page ?? 1);

        $data = Question::paginate(
            $this->inputInt('subject'),
            $this->inputInt('topic'),
            $this->input('search'),
            $page,
            PAGINATION_PER_PAGE
        );

        $this->view('admin/questions', [
            'rows'      => $data['rows'],
            'total'     => $data['total'],
            'pages'     => $data['pages'],
            'page'      => $page,
            'subjects'  => Subject::all(),
            'topics'    => Topic::allWithSubject(),
            'subjectId' => $this->inputInt('subject'),
            'topicId'   => $this->inputInt('topic'),
            'search'    => $this->input('search'),
        ]);
    }

    /* ---------- create ---------- */

    public function create(): void
    {
        $this->requireRole('admin');
        $this->form(newQuestion(), []);
    }

    public function store(): void
    {
        $this->requireRole('admin');
        $input = $this->questionInput();
        $validated = QuestionBankService::validateQuestion($input);

        if ($validated['errors'] !== []) {
            $this->form($input, $validated['errors']);
            return;
        }

        Question::create($this->questionRow($validated['data'], Auth::id()));
        $this->flash('ok', 'Question added to the bank.');
        $this->redirect('questionbank/index');
    }

    /* ---------- edit ---------- */

    public function edit(?int $id): void
    {
        $this->requireRole('admin');
        $q = $id !== null ? Question::find($id) : null;
        if ($q === null) {
            ErrorPage404();
            return;
        }
        $this->form($q, []);
    }

    public function update(?int $id): void
    {
        $this->requireRole('admin');
        $q = $id !== null ? Question::find($id) : null;
        if ($q === null) {
            ErrorPage404();
            return;
        }

        $input = $this->questionInput();
        $validated = QuestionBankService::validateQuestion($input);

        if ($validated['errors'] !== []) {
            $input['id'] = $id;
            $this->form($input, $validated['errors']);
            return;
        }

        Question::update($id, $this->questionRow($validated['data'], null));
        $this->flash('ok', 'Question updated.');
        $this->redirect('questionbank/index');
    }

    /* ---------- delete & validate toggle ---------- */

    public function delete(?int $id): void
    {
        $this->requireRole('admin');
        if ($id !== null) {
            if (Question::hasAnswers($id)) {
                $this->flash('error', 'This question already has recorded answers and cannot be deleted (history protection). You can set it to "not validated" instead.');
            } else {
                Question::delete($id);
                $this->flash('ok', 'Question deleted.');
            }
        }
        $this->redirect('questionbank/index');
    }

    public function togglevalidate(?int $id): void
    {
        $this->requireRole('admin');
        $q = $id !== null ? Question::find($id) : null;
        if ($q !== null) {
            Question::setValidated($id, $q['is_validated'] != 1);
            $this->flash('ok', $q['is_validated'] == 1 ? 'Question marked as pending validation.' : 'Question validated.');
        }
        $this->redirect('questionbank/index');
    }

    /* ---------- helpers ---------- */

    private function questionInput(): array
    {
        return [
            'topic_id'       => $this->inputInt('topic_id'),
            'question_text'  => (string) ($this->input('question_text') ?? ''),
            'option_a'       => (string) ($this->input('option_a') ?? ''),
            'option_b'       => (string) ($this->input('option_b') ?? ''),
            'option_c'       => (string) ($this->input('option_c') ?? ''),
            'option_d'       => (string) ($this->input('option_d') ?? ''),
            'correct_option' => strtoupper((string) ($this->input('correct_option') ?? '')),
            'explanation'    => (string) ($this->input('explanation') ?? ''),
            'difficulty'     => (string) ($this->input('difficulty') ?? 'medium'),
            'is_validated'   => $this->input('is_validated') === '1' ? 1 : 0,
        ];
    }

    private function questionRow(array $input, ?int $userId): array
    {
        return [
            'topic_id'       => (int) $input['topic_id'],
            'question_text'  => $input['question_text'],
            'option_a'       => $input['option_a'],
            'option_b'       => $input['option_b'],
            'option_c'       => $input['option_c'],
            'option_d'       => $input['option_d'],
            'correct_option' => $input['correct_option'],
            'explanation'    => $input['explanation'],
            'difficulty'     => $input['difficulty'],
            'is_validated'   => (int) ($input['is_validated'] ?? 0),
            'created_by'     => $userId,
        ];
    }

    /** Shared form view for create/edit. */
    private function form(array $q, array $errors): void
    {
        $this->view('admin/question_form', [
            'q'        => $q,
            'errors'   => $errors,
            'topics'   => Topic::allWithSubject(),
        ]);
    }
}

/** blank question template */
function newQuestion(): array
{
    return [
        'id' => null, 'topic_id' => null, 'question_text' => '', 'option_a' => '', 'option_b' => '',
        'option_c' => '', 'option_d' => '', 'correct_option' => 'A', 'explanation' => '',
        'difficulty' => 'medium', 'is_validated' => 1,
    ];
}

function ErrorPage404(): void
{
    \App\Core\ErrorPage::show(404);
}
