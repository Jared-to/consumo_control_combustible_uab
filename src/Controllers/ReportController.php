<?php

require_once __DIR__ . '/../../tcpdf/tcpdf.php';

class ReportController
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function exportarPDF()
    {
        $tipo = $_GET['report_type'] ?? 'general';
        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        $surtidor = $_GET['surtidor'] ?? '';

        $cargas = $this->repository->getFilteredEntries($fechaInicio, $fechaFin, $surtidor);
        $analisis = $this->repository->getFilteredSurtidorAnalysis($fechaInicio, $fechaFin, $surtidor);
        $bajoRendimiento = $this->repository->getLowPerformanceEntries($fechaInicio, $fechaFin, $surtidor, 8);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema Combustible UAB');
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('Reporte de Consumo de Combustible');
        $pdf->SetSubject('Reporte PDF');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        $html = $this->buildHeader($tipo, $fechaInicio, $fechaFin, $surtidor);

        switch ($tipo) {
            case 'comparativo':
                $html .= $this->buildComparativoTable($analisis);
                break;

            case 'bajo_rendimiento':
                $html .= $this->buildBajoRendimientoTable($bajoRendimiento);
                break;

            case 'historial':
                $html .= $this->buildHistorialTable($cargas);
                break;

            default:
                $html .= $this->buildHistorialTable($cargas);
                $html .= '<br><br>';
                $html .= $this->buildComparativoTable($analisis);
                break;
        }

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('reporte_combustible.pdf', 'I');
    }

    private function buildHeader($tipo, $fechaInicio, $fechaFin, $surtidor)
    {
        $titulo = 'Reporte General';

        if ($tipo === 'comparativo') {
            $titulo = 'Reporte Comparativo por Surtidor';
        } elseif ($tipo === 'bajo_rendimiento') {
            $titulo = 'Reporte de Bajo Rendimiento';
        } elseif ($tipo === 'historial') {
            $titulo = 'Historial de Cargas';
        }

        $filtros = '
            <table cellpadding="4">
                <tr>
                    <td><strong>Fecha inicio:</strong> ' . ($fechaInicio ?: 'Todas') . '</td>
                    <td><strong>Fecha fin:</strong> ' . ($fechaFin ?: 'Todas') . '</td>
                    <td><strong>Surtidor:</strong> ' . ($surtidor ?: 'Todos') . '</td>
                </tr>
            </table>
        ';

        return '
            <h2 style="text-align:center;">Sistema de Control de Consumo y Calidad de Combustible</h2>
            <h3 style="text-align:center;">' . $titulo . '</h3>
            <p style="text-align:right;"><strong>Generado:</strong> ' . date('d/m/Y H:i') . '</p>
            ' . $filtros . '
            <hr>
        ';
    }

    private function buildHistorialTable($cargas)
    {
        if (empty($cargas)) {
            return '<p>No existen registros para mostrar.</p>';
        }

        $html = '
            <h4>Historial de Cargas</h4>
            <table border="1" cellpadding="4">
                <thead>
                    <tr style="font-weight:bold; background-color:#f2f2f2;">
                        <th>Fecha</th>
                        <th>Surtidor</th>
                        <th>KM Actual</th>
                        <th>Litros</th>
                        <th>Precio Total</th>
                        <th>KM Recorridos</th>
                        <th>Rendimiento</th>
                        <th>Costo/KM</th>
                    </tr>
                </thead>
                <tbody>
        ';

        foreach ($cargas as $c) {
            $html .= '
                <tr>
                    <td>' . htmlspecialchars(date('d/m/Y', strtotime($c['fecha']))) . '</td>
                    <td>' . htmlspecialchars($c['surtidor']) . '</td>
                    <td>' . number_format((float)$c['kilometraje_actual'], 2) . '</td>
                    <td>' . number_format((float)$c['litros_cargados'], 2) . '</td>
                    <td>Bs. ' . number_format((float)$c['precio_total'], 2) . '</td>
                    <td>' . number_format((float)$c['kilometros_recorridos'], 2) . '</td>
                    <td>' . number_format((float)$c['rendimiento_kml'], 2) . ' km/l</td>
                    <td>Bs. ' . number_format((float)$c['costo_por_km'], 4) . '</td>
                </tr>
            ';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function buildComparativoTable($analisis)
    {
        if (empty($analisis)) {
            return '<p>No existen datos comparativos para mostrar.</p>';
        }

        $html = '
            <h4>Rendimiento Promedio por Surtidor</h4>
            <table border="1" cellpadding="4">
                <thead>
                    <tr style="font-weight:bold; background-color:#f2f2f2;">
                        <th>Surtidor</th>
                        <th>Total Cargas</th>
                        <th>Rendimiento Promedio</th>
                        <th>Mejor Rendimiento</th>
                        <th>Costo Promedio/KM</th>
                        <th>Calidad</th>
                    </tr>
                </thead>
                <tbody>
        ';

        foreach ($analisis as $r) {
            $estado = 'Excelente';
            if ((float)$r['promedio'] < 8) {
                $estado = 'Deficiente';
            } elseif ((float)$r['promedio'] < 12) {
                $estado = 'Regular';
            }

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($r['surtidor']) . '</td>
                    <td>' . (int)$r['total_cargas'] . '</td>
                    <td>' . number_format((float)$r['promedio'], 2) . ' km/l</td>
                    <td>' . number_format((float)$r['max_rendimiento'], 2) . ' km/l</td>
                    <td>Bs. ' . number_format((float)$r['costo_promedio_km'], 4) . '</td>
                    <td>' . $estado . '</td>
                </tr>
            ';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function buildBajoRendimientoTable($registros)
    {
        if (empty($registros)) {
            return '<p>No se detectaron cargas de bajo rendimiento.</p>';
        }

        $html = '
            <h4>Detección de Bajo Rendimiento</h4>
            <table border="1" cellpadding="4">
                <thead>
                    <tr style="font-weight:bold; background-color:#f2f2f2;">
                        <th>Fecha</th>
                        <th>Surtidor</th>
                        <th>KM Actual</th>
                        <th>Litros</th>
                        <th>KM Recorridos</th>
                        <th>Rendimiento</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
        ';

        foreach ($registros as $r) {
            $html .= '
                <tr>
                    <td>' . htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) . '</td>
                    <td>' . htmlspecialchars($r['surtidor']) . '</td>
                    <td>' . number_format((float)$r['kilometraje_actual'], 2) . '</td>
                    <td>' . number_format((float)$r['litros_cargados'], 2) . '</td>
                    <td>' . number_format((float)$r['kilometros_recorridos'], 2) . '</td>
                    <td>' . number_format((float)$r['rendimiento_kml'], 2) . ' km/l</td>
                    <td>Posible combustible de menor eficiencia o anomalía en consumo</td>
                </tr>
            ';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}