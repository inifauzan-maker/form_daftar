<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    private ActivityLog $logs;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->logs = new ActivityLog();
    }

    public function index(): void
    {
        Auth::requirePermission($this->request, $this->response, 'view_activity_logs');

        $filters = $this->filters();
        $result = $this->logs->paginate($filters);

        $this->response->view('activity_logs/index', [
            'appName' => config('app.name'),
            'user' => Auth::user(),
            'logs' => $result['data'],
            'meta' => $result['meta'],
            'filters' => $filters,
            'actions' => $this->logs->actions(),
        ]);
    }

    public function list(): void
    {
        Auth::requirePermission($this->request, $this->response, 'view_activity_logs');

        $filters = $this->filters();
        $this->response->json($this->logs->paginate($filters));
    }

    private function filters(): array
    {
        $page = (int) $this->request->input('page', 1);
        $perPage = (int) $this->request->input('per_page', 25);
        $search = trim((string) $this->request->input('search', ''));
        $action = trim((string) $this->request->input('action', ''));
        $userIdRaw = $this->request->input('user_id', null);
        $userId = is_numeric($userIdRaw) ? (int) $userIdRaw : null;

        return [
            'page' => max(1, $page),
            'per_page' => max(5, min(50, $perPage)),
            'search' => $search,
            'action' => $action !== '' ? $action : null,
            'user_id' => $userId && $userId > 0 ? $userId : null,
        ];
    }
}
