<?php
// src/Views/fuel/index.view.php
require_once 'partials/header.php';
require_once 'partials/messages.php';
?>

<div class="container">
    <div class="main-header">
        <h1>⛽ Control de Consumo y Calidad de Combustible</h1>
        <p>Sistema de monitoreo y análisis de consumo vehicular</p>
    </div>
    
    <!-- Tarjetas de estadísticas -->
    <?php if($estadisticas && $estadisticas['total_cargas'] > 0): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-title">Total Cargas</div>
            <div class="stat-value"><?php echo $estadisticas['total_cargas']; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⛽</div>
            <div class="stat-title">Total Litros</div>
            <div class="stat-value"><?php echo number_format($estadisticas['total_litros'], 2); ?> <span class="stat-unit">L</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-title">Total Gastado</div>
            <div class="stat-value">Bs. <?php echo number_format($estadisticas['total_gastado'], 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-title">Rendimiento Promedio</div>
            <div class="stat-value"><?php echo number_format($estadisticas['rendimiento_promedio'], 2); ?> <span class="stat-unit">km/l</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💵</div>
            <div class="stat-title">Costo por km</div>
            <div class="stat-value">Bs. <?php echo number_format($estadisticas['costo_promedio_km'], 2); ?></div>
        </div>
    </div>
    
    
    <?php endif; ?>
    
    <!-- Gráficos -->
    <?php if(!empty($reporte)): ?>
    <div class="section">
        <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
            <h2>📊 Análisis Gráfico</h2>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button class="btn btn-secondary" onclick="abrirModalFechas('general')">
                    PDF General
                </button>
                <button class="btn btn-secondary" onclick="abrirModalFechas('historial')">
                    PDF Historial
                </button>
                <button class="btn btn-secondary" onclick="abrirModalFechas('comparativo')">
                    PDF Comparativo
                </button>
                <button class="btn btn-secondary" onclick="abrirModalFechas('bajo_rendimiento')">
                    PDF Bajo Rendimiento
                </button>
            </div>
        </div>
        <div class="charts-container">
            <div class="chart-card">
                <h3>Rendimiento por Surtidor (km/l)</h3>
                <canvas id="rendimientoChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Costo por Kilómetro (Bs)</h3>
                <canvas id="costoChart"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Tabla de historial -->
    <div class="section">
        <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
            <h2>📋 Historial de Cargas</h2>

            <button class="btn btn-primary" onclick="abrirModal()">
                Nueva Carga
            </button>
        </div>
        
        <?php if(empty($cargas)): ?>
            <div class="no-data">
                <i>📭</i>
                <p>No hay registros disponibles</p>
                <button class="btn btn-primary" onclick="abrirModal()" style="margin-top: 15px;">
                    Registrar Primera Carga
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Surtidor</th>
                            <th>KM Actual</th>
                            <th>Litros</th>
                            <th>Precio Total</th>
                            <th>KM Recorridos</th>
                            <th>Rendimiento</th>
                            <th>Costo/km</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cargas as $c): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($c['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($c['surtidor']); ?></td>
                            <td><?php echo number_format($c['kilometraje_actual'], 2); ?> km</td>
                            <td><?php echo number_format($c['litros_cargados'], 2); ?> L</td>
                            <td>Bs. <?php echo number_format($c['precio_total'], 2); ?></td>
                            <td><?php echo number_format($c['kilometros_recorridos'], 2); ?> km</td>
                            <td class="rendimiento"><?php echo number_format($c['rendimiento_kml'], 2); ?> km/l</td>
                            <td>Bs. <?php echo number_format($c['costo_por_km'], 4); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Análisis por surtidor -->
    <div class="section">
        <div class="section-header">
            <h2>🏪 Análisis Comparativo por Surtidor</h2>
        </div>
        
        <div class="legend">
            <span class="legend-high">● Excelente: ≥ 12 km/l</span>
            <span class="legend-medium">● Regular: 8 - 12 km/l</span>
            <span class="legend-low">● Deficiente: < 8 km/l</span>
        </div>
        
        <?php if(empty($reporte)): ?>
            <div class="no-data">
                <i>📊</i>
                <p>No hay datos suficientes para el análisis</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="analysis-table">
                    <thead>
                        <tr>
                            <th>Surtidor</th>
                            <th>Cargas</th>
                            <th>Rendimiento Promedio</th>
                            <th>Mejor Rendimiento</th>
                            <th>Costo Promedio/km</th>
                            <th>Calidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reporte as $r): ?>
                            <?php 
                                $colorClass = 'quality-high';
                                $estado = "Excelente";
                                if($r['promedio'] < 8) { 
                                    $colorClass = 'quality-low'; 
                                    $estado = "Deficiente"; 
                                } elseif($r['promedio'] < 12) { 
                                    $colorClass = 'quality-medium'; 
                                    $estado = "Regular"; 
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($r['surtidor']); ?></strong></td>
                                <td><?php echo $r['total_cargas']; ?></td>
                                <td class="promedio"><?php echo number_format($r['promedio'], 2); ?> km/l</td>
                                <td><?php echo number_format($r['max_rendimiento'], 2); ?> km/l</td>
                                <td>Bs. <?php echo number_format($r['costo_promedio_km'], 4); ?></td>
                                <td><span class="<?php echo $colorClass; ?>"><?php echo $estado; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para agregar carga -->
<div id="modalAgregar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>➕ Nueva Carga de Combustible</h2>
            <span class="close" onclick="cerrarModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" action="index.php?action=registrar" id="formCarga">
                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha">📅 Fecha:</label>
                        <input type="date" name="fecha" id="fecha" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="surtidor">⛽ Surtidor:</label>
                        <input type="text" name="surtidor" id="surtidor" required placeholder="Ej: YPFB, Shell, etc.">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kilometraje">📊 Kilometraje Actual:</label>
                        <input type="number" step="0.01" name="kilometraje" id="kilometraje" required placeholder="Ej: 15000">
                    </div>
                    
                    <div class="form-group">
                        <label for="litros">⛽ Litros Cargados:</label>
                        <input type="number" step="0.01" name="litros" id="litros" required placeholder="Ej: 45.5">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">💰 Precio Total (Bs):</label>
                        <input type="number" step="0.01" name="precio" id="precio" required placeholder="Ej: 350.75">
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_litro">💵 Precio por Litro:</label>
                        <input type="text" id="precio_litro" readonly style="background: #f5f5f5;">
                    </div>
                </div>
                
                <div class="form-actions" style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">💾 Guardar Carga</button>
                    <button type="button" class="btn btn-danger" onclick="cerrarModal()">❌ Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para seleccionar rango de fechas -->
<div id="modalFechas" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Seleccionar Rango de Fechas</h2>
            <span class="close" onclick="cerrarModalFechas()">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="tipoReporte" value="">
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" required>
                </div>
            </div>
            <div class="form-actions" style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-success" onclick="generarReporte()">Generar PDF</button>
                <button type="button" class="btn btn-danger" onclick="cerrarModalFechas()">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Botón flotante para agregar -->
<button class="fab" onclick="abrirModal()">+</button>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Modal functions
function abrirModal() {
    document.getElementById('modalAgregar').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    document.getElementById('modalAgregar').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function abrirModalFechas(tipo) {
    document.getElementById('tipoReporte').value = tipo;
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value = '';
    document.getElementById('modalFechas').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModalFechas() {
    document.getElementById('modalFechas').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function generarReporte() {
    var tipo = document.getElementById('tipoReporte').value;
    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFin = document.getElementById('fecha_fin').value;
    
    if (!fechaInicio || !fechaFin) {
        alert('Seleccione ambas fechas');
        return;
    }
    
    if (fechaInicio > fechaFin) {
        alert('Fecha inicio no puede ser mayor a fecha fin');
        return;
    }
    
    var url = 'index.php?action=exportar_pdf&report_type=' + tipo + '&fecha_inicio=' + fechaInicio + '&fecha_fin=' + fechaFin;
    window.open(url, '_blank');
    cerrarModalFechas();
}

// Cerrar modal clickeando fuera
window.onclick = function(event) {
    let modal = document.getElementById('modalAgregar');
    if (event.target == modal) {
        cerrarModal();
    }
    let modalFechas = document.getElementById('modalFechas');
    if (event.target == modalFechas) {
        cerrarModalFechas();
    }
}

// Calcular precio por litro automáticamente
document.getElementById('precio').addEventListener('input', function() {
    let litros = document.getElementById('litros').value;
    let precio = this.value;
    if(litros > 0 && precio > 0) {
        let precioLitro = (precio / litros).toFixed(2);
        document.getElementById('precio_litro').value = 'Bs. ' + precioLitro + ' / L';
    } else {
        document.getElementById('precio_litro').value = '';
    }
});

document.getElementById('litros').addEventListener('input', function() {
    let precio = document.getElementById('precio').value;
    let litros = this.value;
    if(litros > 0 && precio > 0) {
        let precioLitro = (precio / litros).toFixed(2);
        document.getElementById('precio_litro').value = 'Bs. ' + precioLitro + ' / L';
    } else {
        document.getElementById('precio_litro').value = '';
    }
});

// Gráficos
<?php if(!empty($reporte)): ?>
// Gráfico de rendimiento
const ctx1 = document.getElementById('rendimientoChart').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($reporte, 'surtidor')) . "'"; ?>],
        datasets: [{
            label: 'Rendimiento (km/l)',
            data: [<?php echo implode(',', array_column($reporte, 'promedio')); ?>],
            backgroundColor: [
                'rgba(46, 204, 113, 0.7)',
                'rgba(52, 152, 219, 0.7)',
                'rgba(155, 89, 182, 0.7)',
                'rgba(241, 196, 15, 0.7)',
                'rgba(231, 76, 60, 0.7)'
            ],
            borderColor: [
                '#2ecc71',
                '#3498db',
                '#9b59b6',
                '#f1c40f',
                '#e74c3c'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.parsed.y.toFixed(2) + ' km/l';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Kilómetros por Litro (km/l)'
                }
            }
        }
    }
});

// Gráfico de costo por km
const ctx2 = document.getElementById('costoChart').getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($reporte, 'surtidor')) . "'"; ?>],
        datasets: [{
            label: 'Costo por Kilómetro (Bs)',
            data: [<?php echo implode(',', array_column($reporte, 'costo_promedio_km')); ?>],
            backgroundColor: 'rgba(231, 76, 60, 0.2)',
            borderColor: '#e74c3c',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#e74c3c',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Bs. ' + context.parsed.y.toFixed(4) + ' por km';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Costo en Bolivianos (Bs)'
                },
                ticks: {
                    callback: function(value) {
                        return 'Bs. ' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Animación de números
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animar estadísticas al cargar
document.addEventListener('DOMContentLoaded', function() {
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(stat => {
        const finalValue = parseFloat(stat.innerText);
        if(!isNaN(finalValue)) {
            animateValue(stat, 0, finalValue, 1000);
        }
    });
});
</script>

<?php require_once 'partials/footer.php'; ?>