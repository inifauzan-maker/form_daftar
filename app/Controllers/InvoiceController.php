<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Registration;

class InvoiceController extends Controller
{
    private Registration $registrations;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        Auth::requirePermission($this->request, $this->response, 'view_invoice');
        $this->registrations = new Registration();
    }

    public function show(): void
    {
        $id = (int) $this->request->input('id', 0);

        if ($id <= 0) {
            $this->response->json(['message' => 'Invalid registration id'], 400);
            return;
        }

        $registration = $this->registrations->findWithProgram($id);

        if (!$registration) {
            $this->response->json(['message' => 'Data pendaftaran tidak ditemukan'], 404);
            return;
        }

        $pdf = $this->buildInvoicePdf($registration);
        $fileName = 'invoice-' . ($registration['registration_number'] ?: ('reg-' . $registration['id'])) . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($pdf));

        echo $pdf;
    }

    private function buildInvoicePdf(array $data): string
    {
        $pageWidth = 595.28;
        $pageHeight = 841.89;
        $top = $pageHeight - 60;

        $lines = [];

        $lines[] = ['text' => 'INVOICE', 'size' => 20, 'x' => 50, 'y' => $top];
        $lines[] = ['text' => 'Nomor Invoice: ' . ($data['invoice_number'] ?: '-'), 'size' => 12, 'x' => 50, 'y' => $top - 40];
        $lines[] = ['text' => 'Tanggal: ' . date('d M Y', strtotime((string) ($data['created_at'] ?? 'now'))), 'size' => 12, 'x' => 50, 'y' => $top - 60];

        $lines[] = ['text' => 'Data Pendaftar', 'size' => 14, 'x' => 50, 'y' => $top - 100];
        $lines[] = ['text' => 'Nama', 'size' => 11, 'x' => 50, 'y' => $top - 125];
        $lines[] = ['text' => $data['full_name'], 'size' => 11, 'x' => 200, 'y' => $top - 125];

        $lines[] = ['text' => 'Sekolah', 'size' => 11, 'x' => 50, 'y' => $top - 145];
        $lines[] = ['text' => $data['school_name'], 'size' => 11, 'x' => 200, 'y' => $top - 145];

        $lines[] = ['text' => 'Kelas', 'size' => 11, 'x' => 50, 'y' => $top - 165];
        $lines[] = ['text' => $data['class_level'], 'size' => 11, 'x' => 200, 'y' => $top - 165];

        $lines[] = ['text' => 'Lokasi Belajar', 'size' => 11, 'x' => 50, 'y' => $top - 185];
        $lines[] = ['text' => $data['study_location'] ?: '-', 'size' => 11, 'x' => 200, 'y' => $top - 185];

        $lines[] = ['text' => 'Program Bimbel', 'size' => 11, 'x' => 50, 'y' => $top - 205];
        $programText = $data['program_name'] . ' (' . $data['program_code'] . ')';
        $lines[] = ['text' => $programText, 'size' => 11, 'x' => 200, 'y' => $top - 205];

        $totalDue = (float) $data['total_due'];
        if ($totalDue <= 0) {
            $totalDue = max(
                0,
                (float) $data['program_fee'] + (float) $data['registration_fee'] - (float) $data['discount_amount']
            );
        }

        $lines[] = ['text' => 'Ringkasan Biaya', 'size' => 14, 'x' => 50, 'y' => $top - 245];
        $lines[] = ['text' => 'Biaya Program', 'size' => 11, 'x' => 50, 'y' => $top - 270];
        $lines[] = ['text' => $this->formatCurrency($data['program_fee']), 'size' => 11, 'x' => 200, 'y' => $top - 270];

        $lines[] = ['text' => 'Biaya Registrasi', 'size' => 11, 'x' => 50, 'y' => $top - 290];
        $lines[] = ['text' => $this->formatCurrency($data['registration_fee']), 'size' => 11, 'x' => 200, 'y' => $top - 290];

        $lines[] = ['text' => 'Diskon', 'size' => 11, 'x' => 50, 'y' => $top - 310];
        $lines[] = ['text' => $this->formatCurrency($data['discount_amount']), 'size' => 11, 'x' => 200, 'y' => $top - 310];

        $lines[] = ['text' => 'Total Tagihan', 'size' => 12, 'x' => 50, 'y' => $top - 340];
        $lines[] = ['text' => $this->formatCurrency($totalDue), 'size' => 12, 'x' => 200, 'y' => $top - 340];

        $lines[] = ['text' => 'Jumlah Dibayar', 'size' => 11, 'x' => 50, 'y' => $top - 360];
        $lines[] = ['text' => $this->formatCurrency($data['amount_paid']), 'size' => 11, 'x' => 200, 'y' => $top - 360];

        $lines[] = ['text' => 'Sisa Tagihan', 'size' => 12, 'x' => 50, 'y' => $top - 380];
        $lines[] = ['text' => $this->formatCurrency($data['balance_due']), 'size' => 12, 'x' => 200, 'y' => $top - 380];

        $lines[] = ['text' => 'Nomor HP', 'size' => 11, 'x' => 50, 'y' => $top - 420];
        $lines[] = ['text' => $data['phone_number'], 'size' => 11, 'x' => 200, 'y' => $top - 420];

        $addressText = sprintf(
            '%s, %s, %s, %s (%s)',
            $data['province'],
            $data['city'],
            $data['district'],
            $data['subdistrict'],
            $data['postal_code']
        );
        $lines[] = ['text' => 'Domisili', 'size' => 11, 'x' => 50, 'y' => $top - 440];
        $lines[] = ['text' => $addressText, 'size' => 11, 'x' => 200, 'y' => $top - 440];

        $lines[] = ['text' => 'Terima kasih telah mendaftar di SI VMI.', 'size' => 11, 'x' => 50, 'y' => $top - 490];
        $lines[] = ['text' => 'Silakan hubungi admin untuk informasi pembayaran lebih lanjut.', 'size' => 11, 'x' => 50, 'y' => $top - 510];

        $content = $this->buildContentStream($lines);

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 /MediaBox [0 0 {$pageWidth} {$pageHeight}] >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
        $stream = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream";
        $objects[] = "5 0 obj " . $stream . "\nendobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $startXref = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$startXref}\n%%EOF";

        return $pdf;
    }

    private function buildContentStream(array $lines): string
    {
        $parts = [];
        foreach ($lines as $line) {
            $size = isset($line['size']) ? (float) $line['size'] : 12.0;
            $x = isset($line['x']) ? (float) $line['x'] : 50.0;
            $y = isset($line['y']) ? (float) $line['y'] : 800.0;
            $text = $this->escapeText($line['text'] ?? '');

            $parts[] = sprintf("BT /F1 %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET", $size, $x, $y, $text);
        }

        return implode("\n", $parts) . "\n";
    }

    private function escapeText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        $text = str_replace("\r", '\\r', $text);
        $text = str_replace("\n", ' ', $text);

        return $text;
    }

    private function formatCurrency(mixed $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}
