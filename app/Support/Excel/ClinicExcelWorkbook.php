<?php

namespace App\Support\Excel;

use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Branded Excel builder: merged banner, column widths, zebra rows, filters.
 */
final class ClinicExcelWorkbook
{
    /** @var list<string> */
    private array $usedSheetNames = [];

    private int $sheetRow = 0;

    private int $tableHeaderRow = 0;

    public function __construct(
        private readonly Writer $writer,
        private readonly ?ClinicExportMeta $meta = null,
    ) {}

    public function writer(): Writer
    {
        return $this->writer;
    }

    public function addSheet(string $name): Sheet
    {
        $unique = $this->uniqueSheetName($name);
        if ($this->usedSheetNames === []) {
            $sheet = $this->writer->getCurrentSheet();
            $sheet->setName($unique);
        } else {
            $this->writer->addNewSheetAndMakeItCurrent();
            $sheet = $this->writer->getCurrentSheet();
            $sheet->setName($unique);
        }
        $this->usedSheetNames[] = $unique;
        $this->sheetRow = 0;
        $this->tableHeaderRow = 0;

        return $sheet;
    }

    /**
     * Banner + table header in one call (recommended).
     *
     * @param  list<string>  $headers
     */
    public function beginTableSheet(
        string $sheetName,
        string $bannerTitle,
        array $headers,
    ): Sheet {
        $sheet = $this->addSheet($sheetName);
        $sheet->setSheetView(XlsxExportResponse::sheetView(null, true));
        $count = count($headers);
        $this->writeBanner($bannerTitle, $count);
        $this->writeTableHeader($sheet, $headers);

        return $sheet;
    }

    /**
     * @param  positive-int  $columnCount  Number of table columns (for merged banner cells)
     */
    public function writeBanner(string $title, int $columnCount, ?string $subtitle = null): void
    {
        $sheet = $this->writer->getCurrentSheet();
        $sheetIndex = $sheet->getIndex();
        $cols = max(1, $columnCount);

        $this->writeMergedBannerRow(
            $title,
            XlsxExportResponse::titleStyle(),
            $cols,
            $sheetIndex,
            XlsxExportResponse::ROW_HEIGHT_TITLE,
        );

        $sub = $subtitle ?? $this->meta?->subtitle();
        if ($sub !== null && $sub !== '') {
            $this->writeMergedBannerRow(
                $sub,
                XlsxExportResponse::metaStyle(),
                $cols,
                $sheetIndex,
                XlsxExportResponse::ROW_HEIGHT_META,
            );
        }

        if ($this->meta !== null) {
            foreach ($this->meta->filterLines as $line) {
                $this->writeMergedBannerRow(
                    $line,
                    XlsxExportResponse::metaStyle(),
                    $cols,
                    $sheetIndex,
                    XlsxExportResponse::ROW_HEIGHT_META,
                );
            }
        }
    }

    /**
     * @param  list<string>  $headers
     */
    public function writeTableHeader(Sheet $sheet, array $headers): void
    {
        $sheet->setSheetView(XlsxExportResponse::sheetView(null, true));
        $this->writer->addRow(XlsxExportResponse::styledRow(
            $headers,
            XlsxExportResponse::headerStyle(),
            XlsxExportResponse::ROW_HEIGHT_HEADER,
        ));
        $this->sheetRow++;
        $this->tableHeaderRow = $this->sheetRow;
    }

    /**
     * @param  list<int|string|float|null>  $values
     */
    public function writeDataRow(array $values, int $index = 0): void
    {
        $style = ($index % 2) === 0
            ? XlsxExportResponse::bodyStyle()
            : XlsxExportResponse::zebraStyle();
        $this->writer->addRow(XlsxExportResponse::styledRow($values, $style));
        $this->sheetRow++;
    }

    public function writeSectionTitle(string $title, int $columnCount = 2): void
    {
        $sheetIndex = $this->writer->getCurrentSheet()->getIndex();
        $cols = max(1, $columnCount);
        $this->writeMergedBannerRow($title, XlsxExportResponse::sectionStyle(), $cols, $sheetIndex);
    }

    /**
     * @param  list<array{0: string, 1: int|string|float|null}>  $pairs
     */
    public function writeKeyValueBlock(array $pairs): void
    {
        foreach ($pairs as [$label, $value]) {
            $this->writer->addRow(XlsxExportResponse::styledRow([$label, $value], XlsxExportResponse::keyValueStyle()));
            $this->sheetRow++;
        }
    }

    public function finishCurrentTable(Sheet $sheet, int $columnCount): void
    {
        if ($this->tableHeaderRow > 0) {
            $this->finishTable($sheet, $this->tableHeaderRow, $columnCount);
        }
    }

    /**
     * @param  positive-int  $headerRow   1-based row of column headers
     * @param  positive-int  $columnCount
     */
    public function finishTable(Sheet $sheet, int $headerRow, int $columnCount): void
    {
        if ($columnCount < 1) {
            return;
        }

        $freezeRow = $this->sheetRow > $headerRow ? $headerRow + 1 : $headerRow;
        $sheet->setSheetView(XlsxExportResponse::sheetView($freezeRow));

        if ($this->sheetRow <= $headerRow) {
            return;
        }

        $sheet->setAutoFilter(new AutoFilter(
            fromColumnIndex: 0,
            fromRow: $headerRow,
            toColumnIndex: $columnCount - 1,
            toRow: $this->sheetRow,
        ));
    }

    public function prepareKeyValueSheet(Sheet $sheet): void
    {
        $sheet->setSheetView(XlsxExportResponse::sheetView($this->sheetRow > 0 ? $this->sheetRow + 1 : 2));
    }

    private function writeMergedBannerRow(
        string $text,
        \OpenSpout\Common\Entity\Style\Style $style,
        int $columnCount,
        int $sheetIndex,
        float $rowHeight = XlsxExportResponse::ROW_HEIGHT_META,
    ): void {
        $rowIndex = $this->sheetRow + 1;
        $this->writer->addRow(XlsxExportResponse::styledRow([$text], $style, $rowHeight));
        if ($columnCount > 1) {
            $this->writer->getOptions()->mergeCells(0, $rowIndex, $columnCount - 1, $rowIndex, $sheetIndex);
        }
        $this->sheetRow++;
    }

    private function uniqueSheetName(string $name): string
    {
        $base = self::sanitizeSheetName($name);
        $candidate = $base;
        $n = 2;
        while (in_array($candidate, $this->usedSheetNames, true)) {
            $suffix = ' '.$n;
            $candidate = self::sanitizeSheetName(mb_substr($base, 0, 31 - mb_strlen($suffix)).$suffix);
            $n++;
        }

        return $candidate;
    }

    private static function sanitizeSheetName(string $name): string
    {
        $sanitized = preg_replace('/[\\\\\\/*?:\\[\\]]/', '-', trim($name)) ?? 'Sheet';
        $sanitized = trim($sanitized) !== '' ? trim($sanitized) : 'Sheet';

        return mb_substr($sanitized, 0, 31);
    }
}
