<?php
// src/Models/FuelEntry.php
namespace Models;

class FuelEntry {
    private $id;
    private $fecha;
    private $kilometraje_actual;
    private $litros_cargados;
    private $precio_total;
    private $surtidor;
    private $kilometros_recorridos;
    private $rendimiento_kml;
    private $costo_por_km;
    private $created_at;
    
    public function __construct($data = []) {
        $this->hydrate($data);
    }
    
    private function hydrate($data) {
        foreach($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if(method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    
    public function getFecha() { return $this->fecha; }
    public function setFecha($fecha) { $this->fecha = $fecha; }
    
    public function getKilometrajeActual() { return $this->kilometraje_actual; }
    public function setKilometrajeActual($km) { $this->kilometraje_actual = (float)$km; }
    
    public function getLitrosCargados() { return $this->litros_cargados; }
    public function setLitrosCargados($litros) { $this->litros_cargados = (float)$litros; }
    
    public function getPrecioTotal() { return $this->precio_total; }
    public function setPrecioTotal($precio) { $this->precio_total = (float)$precio; }
    
    public function getSurtidor() { return $this->surtidor; }
    public function setSurtidor($surtidor) { $this->surtidor = htmlspecialchars($surtidor); }
    
    public function getKilometrosRecorridos() { return $this->kilometros_recorridos; }
    public function setKilometrosRecorridos($km) { $this->kilometros_recorridos = (float)$km; }
    
    public function getRendimientoKml() { return $this->rendimiento_kml; }
    public function setRendimientoKml($rend) { $this->rendimiento_kml = (float)$rend; }
    
    public function getCostoPorKm() { return $this->costo_por_km; }
    public function setCostoPorKm($costo) { $this->costo_por_km = (float)$costo; }
    
    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    
    // Método para validar datos
    public function validate() {
        $errors = [];
        
        if(empty($this->fecha)) {
            $errors[] = "La fecha es requerida";
        }
        
        if($this->kilometraje_actual <= 0) {
            $errors[] = "El kilometraje debe ser mayor a 0";
        }
        
        if($this->litros_cargados <= 0) {
            $errors[] = "Los litros deben ser mayores a 0";
        }
        
        if($this->precio_total <= 0) {
            $errors[] = "El precio debe ser mayor a 0";
        }
        
        if(empty($this->surtidor)) {
            $errors[] = "El nombre del surtidor es requerido";
        }
        
        return $errors;
    }
    
    // Método para calcular costo por km
    public function calcularCostoPorKm() {
        if($this->kilometros_recorridos > 0) {
            return $this->precio_total / $this->kilometros_recorridos;
        }
        return 0;
    }
}