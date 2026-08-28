<?php

namespace App\Actions;

class ExportDashboardSummary
{
    /**
     * Turns the summary counts array into CSV-ready rows, header first.
     */
    public function handle(array $summary): array
    {
        $rows = [['Metric', 'Count']];

        foreach ($summary as $metric => $count) {
            $rows[] = [$this->sanitiseCell(ucfirst($metric)), $count];
        }

        return $rows;
    }

    /**
     * Prevents CSV formula injection: a cell starting with =, +, - or @ is
     * interpreted as a formula by Excel/Sheets when the file is opened, so
     * prefix it with a single quote to force plain-text treatment.
     */
    private function sanitiseCell(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }
}