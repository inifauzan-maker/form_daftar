<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\School;

class SchoolController extends Controller
{
    private School $schools;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->schools = new School();
    }

    public function search(): void
    {
        $term = (string) $this->request->input('q', '');
        $results = $this->schools->search($term);

        $payload = array_map(static function (array $school) {
            return [
                'id' => (int) $school['id'],
                'name' => $school['name'],
                'type' => $school['type'],
                'city' => $school['city'],
                'province' => $school['province'],
                'level_group' => $school['level_group'],
                'label' => sprintf('%s - %s, %s', $school['name'], $school['city'], $school['province']),
            ];
        }, $results);

        $this->response->json(['data' => $payload]);
    }
}
