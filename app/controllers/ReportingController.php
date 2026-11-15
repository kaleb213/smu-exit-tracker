<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Subject;
use App\Services\ReportService;

/**
 * Reporting & Analytics — instructor-facing aggregated dashboards.
 * Instructors see class-level statistics only (never raw answers of
 * other students) per the access-control matrix.
 */
final class ReportingController extends Controller
{
    /* ---------- performance dashboard ---------- */

    public function dashboard(): void
    {
        $this->requireRole('instructor');

        $this->view('instructor/dashboard', [
            'kpis'       => ReportService::classKpis(),
            'bySubject'  => ReportService::classAvgBySubject(),
            'weakTopics' => ReportService::weakestTopics(3, 10),
            'trend'      => ReportService::weeklyTrend(),
        ]);
    }

    /* ---------- per-student performance ---------- */

    public function students(): void
    {
        $this->requireRole('instructor');

        $rows = ReportService::studentPerformance(200);
        $weak = ReportService::weakestSubjectPerStudent();

        $this->view('instructor/students', [
            'rows' => $rows,
            'weak' => $weak,
        ]);
    }

    /* ---------- weak topics detail ---------- */

    public function topics(): void
    {
        $this->requireRole('instructor');

        $this->view('instructor/topics', [
            'weakTopics' => ReportService::weakestTopics(3, 20),
        ]);
    }

    /* ---------- question review (read-only) ---------- */

    public function questions(): void
    {
        $this->requireRole('instructor');

        $subjectId = $this->inputInt('subject');

        $this->view('instructor/questions', [
            'subjects' => Subject::all(),
            'subjectId' => $subjectId,
            'rows'     => ReportService::questionReview($subjectId, 60),
        ]);
    }

    /* ---------- CSV export (low-bandwidth reporting) ---------- */

    public function export(): void
    {
        $this->requireRole('instructor');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=class-performance.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Student', 'ID Number', 'Attempts', 'Average %', 'Weakest Subject', 'Last Attempt']);

        foreach (ReportService::studentPerformance(500) as $r) {
            fputcsv($out, [
                $r['name'], $r['id_number'], $r['attempts'], $r['avg_score'],
                '', $r['last_attempt'],
            ]);
        }
        fclose($out);
        exit;
    }
}
