<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Registration;

class DashboardController extends Controller
{
    private Registration $registrations;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->registrations = new Registration();
    }

    public function index(): void
    {
        $this->response->view('dashboard/index', [
            'appName' => config('app.name'),
        ]);
    }

    public function list(): void
    {
        $records = $this->registrations->all();

        $this->response->json(['data' => $records]);
    }

    public function updateStatus(): void
    {
        $payload = $this->request->json();
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        $studentStatus = $payload['student_status'] ?? '';
        $paymentStatus = $payload['payment_status'] ?? '';
        $paymentNotes = $payload['payment_notes'] ?? null;

        $errors = [];

        if ($id <= 0) {
            $errors['id'][] = 'ID registrasi tidak valid.';
        }

        if (!in_array($studentStatus, $this->studentStatusOptions(), true)) {
            $errors['student_status'][] = 'Status siswa tidak valid.';
        }

        if (!in_array($paymentStatus, $this->paymentStatusOptions(), true)) {
            $errors['payment_status'][] = 'Status pembayaran tidak valid.';
        }

        if (!empty($errors)) {
            $this->response->json(['errors' => $errors], 422);
            return;
        }

        $this->registrations->updateStatus($id, [
            'student_status' => $studentStatus,
            'payment_status' => $paymentStatus,
            'payment_notes' => $paymentNotes,
        ]);

        $this->response->json([
            'message' => 'Status pendaftaran berhasil diperbarui.',
        ]);
    }

    private function studentStatusOptions(): array
    {
        return ['pending', 'active', 'graduated', 'dropped'];
    }

    private function paymentStatusOptions(): array
    {
        return ['unpaid', 'partial', 'paid'];
    }
}

