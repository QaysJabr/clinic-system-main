<?php

namespace App\Support\Excel;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class XlsxExportResponse
{
    private const BRAND_RGB = '0F4C81';

    /** Cream banner (matches manual Excel template). */
    private const BANNER_RGB = 'F5F0E6';

    private const WHITE_RGB = 'FFFFFF';

    private const META_RGB = '5C6B7A';

    private const BORDER_RGB = 'B8C4CE';

    public const float ROW_HEIGHT_TITLE = 28;

    public const float ROW_HEIGHT_META = 20;

    public const float ROW_HEIGHT_HEADER = 22;

    public const float ROW_HEIGHT_DATA = 20;

    /**
     * @param  callable(Writer): void  $callback
     */
    public static function stream(string $downloadFileName, callable $callback): StreamedResponse
    {
        return response()->streamDownload(function () use ($callback): void {
            $writer = self::createWriter();
            $writer->openToFile('php://output');
            try {
                $callback($writer);
            } finally {
                $writer->close();
            }
        }, $downloadFileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public static function createWriter(): Writer
    {
        return new Writer(new Options(
            DEFAULT_ROW_HEIGHT: self::ROW_HEIGHT_DATA,
            DEFAULT_COLUMN_WIDTH: 18,
        ));
    }

    public static function titleStyle(): Style
    {
        return new Style(
            fontBold: true,
            fontSize: 18,
            fontName: 'Calibri',
            fontColor: Color::toARGB(self::BRAND_RGB),
            cellAlignment: CellAlignment::CENTER,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            backgroundColor: Color::toARGB(self::BANNER_RGB),
            shouldWrapText: false,
        );
    }

    public static function metaStyle(): Style
    {
        return new Style(
            fontSize: 10,
            fontName: 'Calibri',
            fontColor: Color::toARGB(self::META_RGB),
            cellAlignment: CellAlignment::CENTER,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            backgroundColor: Color::toARGB(self::BANNER_RGB),
            shouldWrapText: false,
        );
    }

    public static function headerStyle(): Style
    {
        return new Style(
            fontBold: true,
            fontSize: 11,
            fontName: 'Calibri',
            fontColor: Color::WHITE,
            cellAlignment: CellAlignment::CENTER,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            backgroundColor: Color::toARGB(self::BRAND_RGB),
            border: self::tableBorder(),
            shouldWrapText: false,
        );
    }

    public static function bodyStyle(): Style
    {
        return new Style(
            fontSize: 11,
            fontName: 'Calibri',
            cellAlignment: CellAlignment::CENTER,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            backgroundColor: Color::toARGB(self::WHITE_RGB),
            border: self::tableBorder(),
            shouldWrapText: false,
        );
    }

    public static function zebraStyle(): Style
    {
        return new Style(
            fontSize: 11,
            fontName: 'Calibri',
            cellAlignment: CellAlignment::CENTER,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            backgroundColor: Color::toARGB(self::BANNER_RGB),
            border: self::tableBorder(),
            shouldWrapText: false,
        );
    }

    public static function sectionStyle(): Style
    {
        return new Style(
            fontBold: true,
            fontSize: 11,
            fontName: 'Calibri',
            fontColor: Color::toARGB(self::BRAND_RGB),
            cellAlignment: CellAlignment::CENTER,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            backgroundColor: Color::toARGB(self::BANNER_RGB),
            shouldWrapText: false,
        );
    }

    public static function keyValueStyle(): Style
    {
        return new Style(
            fontSize: 11,
            fontName: 'Calibri',
            cellAlignment: CellAlignment::RIGHT,
            cellVerticalAlignment: CellVerticalAlignment::CENTER,
            border: self::tableBorder(),
            shouldWrapText: false,
        );
    }

    /**
     * @param  list<int|string|float|null>  $values
     */
    public static function styledRow(array $values, Style $style, float $height = self::ROW_HEIGHT_DATA): Row
    {
        $cells = [];
        foreach (array_values($values) as $i => $v) {
            $cells[$i] = Cell::fromValue($v, $style);
        }

        return new Row($cells, $height);
    }

    /**
     * @param  list<int|string|float|null>  $values
     */
    public static function dataRow(array $values, int $stripeIndex = 0): Row
    {
        $style = ($stripeIndex % 2) === 0 ? self::bodyStyle() : self::zebraStyle();

        return self::styledRow($values, $style);
    }

    /**
     * @param  list<int|string|float|null>  $values
     */
    public static function row(array $values): Row
    {
        return self::dataRow($values);
    }

    public static function sheetView(?int $freezeBelowRow = null, bool $rightToLeft = true): SheetView
    {
        $view = (new SheetView)->withRightToLeft($rightToLeft);

        if ($freezeBelowRow !== null && $freezeBelowRow > 0) {
            $view = $view->withFreezeRow($freezeBelowRow);
        }

        return $view;
    }

    public static function money(?float $amount, ?string $currency = null): string
    {
        $formatted = number_format((float) $amount, 2);

        return $currency !== null && $currency !== ''
            ? $formatted.' '.$currency
            : $formatted;
    }

    private static function tableBorder(): Border
    {
        static $border = null;
        if ($border !== null) {
            return $border;
        }

        $color = self::BORDER_RGB;
        $border = new Border(
            new BorderPart(BorderName::LEFT, $color, BorderWidth::THIN),
            new BorderPart(BorderName::RIGHT, $color, BorderWidth::THIN),
            new BorderPart(BorderName::TOP, $color, BorderWidth::THIN),
            new BorderPart(BorderName::BOTTOM, $color, BorderWidth::THIN),
        );

        return $border;
    }
}
