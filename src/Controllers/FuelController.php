<?php
require_once __DIR__ . '/../Models/FuelEntry.php';

use Models\FuelEntry;

class FuelController
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function registrarCarga($data)
    {
        try {
            $entry = new FuelEntry([
                'fecha' => $data['fecha'] ?? '',
                'kilometraje_actual' => $data['kilometraje'] ?? 0,
                'litros_cargados' => $data['litros'] ?? 0,
                'precio_total' => $data['precio'] ?? 0,
                'surtidor' => $data['surtidor'] ?? ''
            ]);

            $errors = $entry->validate();

            if (!empty($errors)) {
                $_SESSION['flash_mensaje'] = implode(', ', $errors);
                $_SESSION['flash_tipo'] = 'error';
                return false;
            }

            if ($this->repository->save($entry)) {
                $_SESSION['flash_mensaje'] = 'Carga registrada con éxito.';
                $_SESSION['flash_tipo'] = 'success';
                return true;
            }

            $_SESSION['flash_mensaje'] = 'Error al registrar la carga.';
            $_SESSION['flash_tipo'] = 'error';
            return false;
        } catch (Exception $e) {
            $_SESSION['flash_mensaje'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_tipo'] = 'error';
            return false;
        }
    }

    public function limpiarTodo()
    {
        try {
            if ($this->repository->deleteAll()) {
                $_SESSION['flash_mensaje'] = 'Base de datos limpiada con éxito.';
                $_SESSION['flash_tipo'] = 'success';
                return true;
            }

            $_SESSION['flash_mensaje'] = 'No se pudo limpiar la base de datos.';
            $_SESSION['flash_tipo'] = 'error';
            return false;
        } catch (Exception $e) {
            $_SESSION['flash_mensaje'] = 'Error al limpiar: ' . $e->getMessage();
            $_SESSION['flash_tipo'] = 'error';
            return false;
        }
    }

    public function obtenerHistorial()
    {
        return $this->repository->findAll();
    }

    public function obtenerAnalisis()
    {
        return $this->repository->getSurtidorAnalysis();
    }

    public function obtenerEstadisticas()
    {
        $estadisticas = $this->repository->getEstadisticasGenerales();

        if (!$estadisticas) {
            return [
                'total_cargas' => 0,
                'total_litros' => 0,
                'total_gastado' => 0,
                'rendimiento_promedio' => 0,
                'costo_promedio_km' => 0,
                'mejor_rendimiento' => 0,
                'peor_rendimiento' => 0
            ];
        }

        return [
            'total_cargas' => (int)($estadisticas['total_cargas'] ?? 0),
            'total_litros' => (float)($estadisticas['total_litros'] ?? 0),
            'total_gastado' => (float)($estadisticas['total_gastado'] ?? 0),
            'rendimiento_promedio' => (float)($estadisticas['rendimiento_promedio'] ?? 0),
            'costo_promedio_km' => (float)($estadisticas['costo_promedio_km'] ?? 0),
            'mejor_rendimiento' => (float)($estadisticas['mejor_rendimiento'] ?? 0),
            'peor_rendimiento' => (float)($estadisticas['peor_rendimiento'] ?? 0)
        ];
    }
}