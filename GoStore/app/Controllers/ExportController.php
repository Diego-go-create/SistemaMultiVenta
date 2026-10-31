<?php
declare(strict_types=1);

final class ExportController
{
    public function __construct(private StoreModel $store)
    {
    }

    public function download(): never
    {
        requireAdmin();
        $page = (string)($_GET['page'] ?? '');
        $format = (string)($_GET['export'] ?? '');
        if (!in_array($page, ['admin', 'admin-products', 'admin-carousel', 'admin-orders'], true)
            || !in_array($format, ['excel', 'pdf'], true)) {
            http_response_code(400);
            exit('La exportación solicitada no es válida.');
        }
        $pagePermissions = [
            'admin-products' => 'admin_products',
            'admin-carousel' => 'admin_carousel',
            'admin-orders' => 'admin_orders',
        ];
        if (isset($pagePermissions[$page]) && !isSuperAdmin() && !isOperationEnabled(db(), $pagePermissions[$page])) {
            http_response_code(403);
            exit('Esta exportación está temporalmente restringida por el SuperAdmin.');
        }

        $export = $this->store->getAdminExportData($page);
        $filename = $page . '-' . date('Y-m-d');
        if ($format === 'excel') {
            $this->sendCsv($export, $filename);
        }
        $this->sendPdf($export, $filename);
    }

    private function sendCsv(array $export, string $filename): never
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('X-Content-Type-Options: nosniff');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'wb');
        if ($output === false) {
            throw new RuntimeException('No se pudo iniciar la exportación.');
        }
        fputcsv($output, $export['headers'], ';', '"', '');
        foreach ($export['rows'] as $row) {
            fputcsv($output, array_map([$this, 'safeSpreadsheetValue'], $row), ';', '"', '');
        }
        fclose($output);
        exit;
    }

    private function safeSpreadsheetValue(mixed $value): string|int|float
    {
        if (!is_string($value)) {
            return $value;
        }
        if (preg_match('/^[\s]*[=+\-@]/u', $value)) {
            return "'" . $value;
        }
        return $value;
    }

    private function sendPdf(array $export, string $filename): never
    {
        $lines = [$export['title'], 'Generado: ' . date('d/m/Y H:i:s'), ''];
        foreach ($export['headers'] as $header) {
            $lines[] = (string)$header;
        }
        $lines[] = str_repeat('-', 72);
        foreach ($export['rows'] as $row) {
            foreach ($export['headers'] as $index => $header) {
                $value = (string)($row[$index] ?? '');
                foreach (explode("\n", wordwrap($header . ': ' . $value, 88, "\n", true)) as $line) {
                    $lines[] = $line;
                }
            }
            $lines[] = '';
        }

        $pages = array_chunk($lines, 48);
        if (!$pages) {
            $pages = [[]];
        }
        $objects = [];
        $pageIds = [];
        foreach ($pages as $index => $pageLines) {
            $pageId = 4 + ($index * 2);
            $contentId = $pageId + 1;
            $pageIds[] = $pageId . ' 0 R';
            $content = "BT\n/F1 10 Tf\n50 750 Td\n14 TL\n";
            foreach ($pageLines as $line) {
                $line = $this->pdfText($line);
                $content .= '(' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line) . ") Tj\nT*\n";
            }
            $content .= "ET\n";
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
            $objects[$contentId] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "endstream";
        }
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageIds) . '] /Count ' . count($pageIds) . ' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }
        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        header('X-Content-Type-Options: nosniff');
        echo $pdf;
        exit;
    }

    private function pdfText(string $text): string
    {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        return $converted === false ? preg_replace('/[^\x20-\x7E]/', '?', $text) : $converted;
    }
}
