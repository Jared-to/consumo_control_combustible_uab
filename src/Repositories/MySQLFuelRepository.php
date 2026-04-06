<?php

class MySQLFuelRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function save($entry)
    {
        try {
            $lastEntry = $this->getLastEntry();

            $kilometrosRecorridos = 0;
            if ($lastEntry) {
                $kilometrosRecorridos = $entry->getKilometrajeActual() - $lastEntry['kilometraje_actual'];
                if ($kilometrosRecorridos < 0) {
                    $kilometrosRecorridos = 0;
                }
            }

            $entry->setKilometrosRecorridos($kilometrosRecorridos);

            $rendimiento = ($kilometrosRecorridos > 0 && $entry->getLitrosCargados() > 0)
                ? ($kilometrosRecorridos / $entry->getLitrosCargados())
                : 0;

            $entry->setRendimientoKml($rendimiento);

            $costoPorKm = $entry->calcularCostoPorKm();
            $entry->setCostoPorKm($costoPorKm);

            $query = "INSERT INTO cargas
                        (fecha, kilometraje_actual, litros_cargados, precio_total, surtidor, kilometros_recorridos, rendimiento_kml, costo_por_km)
                      VALUES
                        (:fecha, :km, :litros, :precio, :surtidor, :km_recorridos, :rendimiento, :costo_por_km)";

            $stmt = $this->db->prepare($query);

            return $stmt->execute([
                ':fecha' => $entry->getFecha(),
                ':km' => $entry->getKilometrajeActual(),
                ':litros' => $entry->getLitrosCargados(),
                ':precio' => $entry->getPrecioTotal(),
                ':surtidor' => $entry->getSurtidor(),
                ':km_recorridos' => $kilometrosRecorridos,
                ':rendimiento' => $rendimiento,
                ':costo_por_km' => $costoPorKm
            ]);
        } catch (PDOException $e) {
            error_log('Error en save: ' . $e->getMessage());
            return false;
        }
    }

    private function getLastEntry()
    {
        $query = "SELECT * FROM cargas ORDER BY fecha DESC, id DESC LIMIT 1";
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        $query = "SELECT * FROM cargas ORDER BY fecha DESC, id DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSurtidorAnalysis()
    {
        $query = "SELECT
                    surtidor,
                    COUNT(*) as total_cargas,
                    AVG(rendimiento_kml) as promedio,
                    MAX(rendimiento_kml) as max_rendimiento,
                    AVG(costo_por_km) as costo_promedio_km
                  FROM cargas
                  WHERE rendimiento_kml > 0
                  GROUP BY surtidor
                  ORDER BY promedio DESC";

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteAll()
    {
        $query = "TRUNCATE TABLE cargas";
        $stmt = $this->db->prepare($query);
        return $stmt->execute();
    }

    public function getEstadisticasGenerales()
    {
        $query = "SELECT
                    COUNT(*) as total_cargas,
                    SUM(litros_cargados) as total_litros,
                    SUM(precio_total) as total_gastado,
                    AVG(rendimiento_kml) as rendimiento_promedio,
                    AVG(costo_por_km) as costo_promedio_km,
                    MAX(rendimiento_kml) as mejor_rendimiento,
                    MIN(rendimiento_kml) as peor_rendimiento
                  FROM cargas
                  WHERE rendimiento_kml > 0";

        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFilteredEntries($fechaInicio = '', $fechaFin = '', $surtidor = '')
    {
        $sql = "SELECT * FROM cargas WHERE 1=1";
        $params = [];

        if (!empty($fechaInicio)) {
            $sql .= " AND fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fechaInicio;
        }

        if (!empty($fechaFin)) {
            $sql .= " AND fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fechaFin;
        }

        if (!empty($surtidor)) {
            $sql .= " AND surtidor = :surtidor";
            $params[':surtidor'] = $surtidor;
        }

        $sql .= " ORDER BY fecha DESC, id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilteredSurtidorAnalysis($fechaInicio = '', $fechaFin = '', $surtidor = '')
    {
        $sql = "SELECT
                    surtidor,
                    COUNT(*) as total_cargas,
                    AVG(rendimiento_kml) as promedio,
                    MAX(rendimiento_kml) as max_rendimiento,
                    AVG(costo_por_km) as costo_promedio_km
                FROM cargas
                WHERE rendimiento_kml > 0";
        $params = [];

        if (!empty($fechaInicio)) {
            $sql .= " AND fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fechaInicio;
        }

        if (!empty($fechaFin)) {
            $sql .= " AND fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fechaFin;
        }

        if (!empty($surtidor)) {
            $sql .= " AND surtidor = :surtidor";
            $params[':surtidor'] = $surtidor;
        }

        $sql .= " GROUP BY surtidor ORDER BY promedio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLowPerformanceEntries($fechaInicio = '', $fechaFin = '', $surtidor = '', $umbral = 8)
    {
        $sql = "SELECT * FROM cargas WHERE rendimiento_kml > 0 AND rendimiento_kml < :umbral";
        $params = [':umbral' => $umbral];

        if (!empty($fechaInicio)) {
            $sql .= " AND fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fechaInicio;
        }

        if (!empty($fechaFin)) {
            $sql .= " AND fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fechaFin;
        }

        if (!empty($surtidor)) {
            $sql .= " AND surtidor = :surtidor";
            $params[':surtidor'] = $surtidor;
        }

        $sql .= " ORDER BY fecha DESC, id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}