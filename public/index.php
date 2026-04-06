<?php
session_start();

require_once '../config/database.php';
require_once '../src/Repositories/MySQLFuelRepository.php';
require_once '../src/Controllers/FuelController.php';
require_once '../src/Controllers/ReportController.php';
require_once '../src/Models/FuelEntry.php';

$database = new Database();
$db = $database->getConnection();

$repository = new MySQLFuelRepository($db);
$fuelController = new FuelController($repository);
$reportController = new ReportController($repository);

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'limpiar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fuelController->limpiarTodo();
        }
        header('Location: index.php');
        exit;

    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fuelController->registrarCarga($_POST);
        }
        header('Location: index.php');
        exit;

    case 'exportar_pdf':
        $reportController->exportarPDF();
        exit;

    default:
        $cargas = $fuelController->obtenerHistorial();
        $reporte = $fuelController->obtenerAnalisis();
        $estadisticas = $fuelController->obtenerEstadisticas();

        $mensaje = $_SESSION['flash_mensaje'] ?? '';
        $flash_tipo = $_SESSION['flash_tipo'] ?? 'info';

        unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']);

        require_once '../src/Views/fuel/index.view.php';
        break;
}