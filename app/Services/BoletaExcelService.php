<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BoletaExcelService
{
    public function generarSpreadsheet(array $boleta): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BOLETA');
        $sheet->setShowGridLines(true);

        // Anchos de columnas aproximados para 24 columnas (A a X)
        foreach (range('A', 'X') as $col) {
            $sheet->getColumnDimension($col)->setWidth(3.8);
        }

        // Generar 2 papeletas (Original filas 2-27 y Copia filas 30-55)
        $this->construirBloquePapeleta($sheet, 2, $boleta);
        $this->construirBloquePapeleta($sheet, 30, $boleta);

        return $spreadsheet;
    }

    private function construirBloquePapeleta($sheet, int $startRow, array $boleta): void
    {
        $r = $startRow;

        // Logo de Correos en la esquina superior derecha
        $logoPath = public_path('images/menu-logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo Correos');
            $drawing->setDescription('Agencia Boliviana de Correos');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates("S{$r}");
            $drawing->setHeight(48);
            $drawing->setWorksheet($sheet);
        }

        // 1. Encabezado
        $sheet->mergeCells("A{$r}:X{$r}");
        $sheet->setCellValue("A{$r}", 'AGENCIA BOLIVIANA DE CORREOS');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r++;
        $sheet->mergeCells("A{$r}:X{$r}");
        $sheet->setCellValue("A{$r}", 'DIRECCION ADMINISTRATIVA FINANCIERA');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(9.5);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r++;
        $sheet->mergeCells("A{$r}:X{$r}");
        $sheet->setCellValue("A{$r}", 'RECURSOS HUMANOS');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r++;
        $sheet->mergeCells("A{$r}:X{$r}");
        $sheet->setCellValue("A{$r}", 'PAPELETA DE COMISION - PERMISO PARTICULAR');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setUnderline(true)->setSize(10);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Fila vacía
        $r += 2; // Salta a fila 7 o 35

        // 2. Nombre del funcionario y CI
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", 'NOMBRE DEL FUNCIONARIO:');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(8.5);

        $sheet->mergeCells("F{$r}:P{$r}");
        $sheet->setCellValue("F{$r}", mb_strtoupper($boleta['nombre'] ?? ''));
        $sheet->getStyle("F{$r}")->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle("F{$r}:P{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells("Q{$r}:R{$r}");
        $sheet->setCellValue("Q{$r}", 'C.I.:');
        $sheet->getStyle("Q{$r}")->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle("Q{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->mergeCells("S{$r}:W{$r}");
        $sheet->setCellValue("S{$r}", $boleta['ci'] ?? '');
        $sheet->getStyle("S{$r}")->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle("S{$r}:W{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Fila 10 / 38: Cargo
        $r += 3;
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->setCellValue("A{$r}", 'CARGO');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(8.5);

        $sheet->mergeCells("D{$r}:W{$r}");
        $sheet->setCellValue("D{$r}", mb_strtoupper($boleta['cargo'] ?? 'PERSONAL'));
        $sheet->getStyle("D{$r}:W{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Fila 12 / 40: Motivo
        $r += 2;
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->setCellValue("A{$r}", 'MOTIVO:');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(8.5);

        $sheet->mergeCells("D{$r}:W{$r}");
        $sheet->setCellValue("D{$r}", mb_strtoupper($boleta['motivo'] ?? ''));
        $sheet->getStyle("D{$r}:W{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Fila 14 / 42: Fechas y Horas
        $r += 2;
        $r2 = $r + 1;

        // Desde
        $sheet->mergeCells("A{$r}:D{$r2}");
        $sheet->setCellValue("A{$r}", "DESDE FECHA Y\n HORA:");
        $sheet->getStyle("A{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("A{$r}:D{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells("E{$r}:G{$r}");
        $sheet->setCellValue("E{$r}", $boleta['desde_fecha'] ?? '');
        $sheet->getStyle("E{$r}:G{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("E{$r2}:G{$r2}");
        $sheet->setCellValue("E{$r2}", $boleta['desde_hora'] ?? '');
        $sheet->getStyle("E{$r2}:G{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("E{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Hasta
        $sheet->mergeCells("H{$r}:L{$r2}");
        $sheet->setCellValue("H{$r}", "HASTA FECHA \nHORA:");
        $sheet->getStyle("H{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$r}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("H{$r}:L{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells("M{$r}:P{$r}");
        $sheet->setCellValue("M{$r}", $boleta['hasta_fecha'] ?? '');
        $sheet->getStyle("M{$r}:P{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("M{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells("M{$r2}:P{$r2}");
        $sheet->setCellValue("M{$r2}", $boleta['hasta_hora'] ?? '');
        $sheet->getStyle("M{$r2}:P{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("M{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Tiempo solicitado
        $sheet->mergeCells("Q{$r}:S{$r2}");
        $sheet->setCellValue("Q{$r}", "TIEMPO \nSOLICITADO");
        $sheet->getStyle("Q{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("Q{$r}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("Q{$r}:S{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells("T{$r}:W{$r2}");
        $sheet->setCellValue("T{$r}", mb_strtoupper($boleta['tiempo_solicitado'] ?? ''));
        $sheet->getStyle("T{$r}:W{$r2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("T{$r}")->getFont()->setBold(true);
        $sheet->getStyle("T{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Fila 17 / 45: Tipo de permiso
        $r += 3;
        $sheet->setCellValue("B{$r}", 'COMISION');
        $sheet->getStyle("B{$r}")->getFont()->setBold(true);
        $sheet->setCellValue("D{$r}", ($boleta['tipo'] ?? '') === 'comision' ? 'x' : '');
        $sheet->getStyle("D{$r}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->setCellValue("G{$r}", 'PARTICULAR');
        $sheet->getStyle("G{$r}")->getFont()->setBold(true);
        $sheet->setCellValue("I{$r}", ($boleta['tipo'] ?? '') === 'particular' ? 'x' : '');
        $sheet->getStyle("I{$r}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("I{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->setCellValue("S{$r}", 'MEDICO');
        $sheet->getStyle("S{$r}")->getFont()->setBold(true);
        $sheet->setCellValue("U{$r}", ($boleta['tipo'] ?? '') === 'medico' ? 'x' : '');
        $sheet->getStyle("U{$r}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("U{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("U{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Fila 22 / 50: Firmas
        $r += 5;
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", 'FIRMA FUNCIONARIO (A)');
        $sheet->getStyle("A{$r}:E{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(7.5);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("G{$r}:O{$r}");
        $sheet->setCellValue("G{$r}", 'FIRMA SELLO DEL INMEDIATO SUPERIOR');
        $sheet->getStyle("G{$r}:O{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("G{$r}")->getFont()->setBold(true)->setSize(7.5);
        $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("R{$r}:W{$r}");
        $sheet->setCellValue("R{$r}", 'FECHA Y SELLO DE RR.HH.');
        $sheet->getStyle("R{$r}:W{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("R{$r}")->getFont()->setBold(true)->setSize(7.5);
        $sheet->getStyle("R{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Fila 25 / 53: Lugar y fecha
        $r += 3;
        $sheet->mergeCells("G{$r}:H{$r}");
        $sheet->setCellValue("G{$r}", mb_strtoupper($boleta['ciudad'] ?? 'LA PAZ') . ',');
        $sheet->getStyle("G{$r}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->mergeCells("I{$r}:P{$r}");
        $sheet->setCellValue("I{$r}", mb_strtoupper($boleta['fecha_texto'] ?? ''));
        $sheet->getStyle("I{$r}")->getFont()->setBold(true)->setSize(8);

        // Fila 27 / 55: Nota
        $r += 2;
        $sheet->mergeCells("A{$r}:X{$r}");
        $sheet->setCellValue("A{$r}", 'NOTA: EL PRESENTE FORMULARIO NO DEBE CONTENER BORRONES, ENMIENDAS Y/O CORRECCIONES.');
        $sheet->getStyle("A{$r}")->getFont()->setItalic(true)->setSize(7);
    }
}
