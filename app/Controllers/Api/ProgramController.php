<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Program;

class ProgramController extends Controller
{
    private Program $programs;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->programs = new Program();
    }

    public function index(): void
    {
        $classLevel = strtoupper((string) $this->request->input('classLevel', ''));
        $category = $this->resolveCategory($classLevel);

        if (!$category) {
            $this->response->json(['data' => []]);
            return;
        }

        $programs = $this->programs->getByCategory($category);

        $payload = array_map(static function (array $program) {
            return [
                'id' => (int) $program['id'],
                'name' => $program['name'],
                'code' => $program['code'],
                'class_category' => $program['class_category'],
                'label' => sprintf('%s (%s)', $program['name'], $program['code']),
            ];
        }, $programs);

        $this->response->json(['data' => $payload]);
    }

    private function resolveCategory(string $classLevel): ?string
    {
        return match ($classLevel) {
            'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX' => 'SD_SMP',
            'X', 'XI' => 'X_XI',
            'XII' => 'XII',
            default => null,
        };
    }
}
