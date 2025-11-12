<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Program;
use App\Models\Registration;

class ProductController extends Controller
{
    private Program $programs;
    private Registration $registrations;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        Auth::requirePermission($this->request, $this->response, 'view_dashboard');
        $this->programs = new Program();
        $this->registrations = new Registration();
    }

    public function index(): void
    {
        $user = Auth::user();
        $programRecords = $this->programs->all();
        $achievementRows = $this->registrations->dashboardByProgram([], true);
        $achievements = [];

        foreach ($achievementRows as $row) {
            $achievements[$row['program_id']] = [
                'students' => $row['students'],
                'revenue' => $row['revenue'],
            ];
        }

        $products = [];
        $totals = [
            'target_students' => 0,
            'target_revenue' => 0.0,
            'actual_students' => 0,
            'actual_revenue' => 0.0,
        ];

        foreach ($programRecords as $program) {
            $actual = $achievements[$program['id']] ?? ['students' => 0, 'revenue' => 0];
            $targetStudents = (int) ($program['target_students'] ?? 0);
            $targetRevenue = (float) ($program['target_revenue'] ?? 0);
            $studentPercent = $targetStudents > 0
                ? round(($actual['students'] / max($targetStudents, 1)) * 100, 1)
                : null;
            $revenuePercent = $targetRevenue > 0
                ? round(($actual['revenue'] / max($targetRevenue, 1)) * 100, 1)
                : null;

            $products[] = [
                'id' => (int) $program['id'],
                'name' => $program['name'] ?? 'Program Tanpa Nama',
                'code' => $program['code'] ?? '',
                'class_category' => $program['class_category'] ?? '',
                'description' => $program['description'] ?? '',
                'image_path' => $program['image_path'] ?? null,
                'targets' => [
                    'students' => $targetStudents,
                    'revenue' => $targetRevenue,
                ],
                'actual' => [
                    'students' => (int) ($actual['students'] ?? 0),
                    'revenue' => (float) ($actual['revenue'] ?? 0),
                ],
                'percent' => [
                    'students' => $studentPercent,
                    'revenue' => $revenuePercent,
                ],
            ];

            $totals['target_students'] += $targetStudents;
            $totals['target_revenue'] += $targetRevenue;
            $totals['actual_students'] += (int) ($actual['students'] ?? 0);
            $totals['actual_revenue'] += (float) ($actual['revenue'] ?? 0);
        }

        $this->response->view('products/index', [
            'appName' => config('app.name'),
            'user' => $user,
            'products' => $products,
            'totals' => $totals,
        ]);
    }
}
