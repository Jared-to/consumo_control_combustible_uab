<?php
// src/Views/fuel/partials/messages.php
if(isset($mensaje) && $mensaje): 
    $tipo = $_SESSION['flash_tipo'] ?? 'info';
    $iconos = [
        'success' => '✅',
        'error' => '❌',
        'info' => 'ℹ️'
    ];
    $icono = $iconos[$tipo] ?? 'ℹ️';
?>
<div class="alert alert-<?php echo $tipo; ?>">
    <?php echo $icono; ?> <?php echo htmlspecialchars($mensaje); ?>
</div>
<script>
    // Auto cerrar alerta después de 5 segundos
    setTimeout(function() {
        let alert = document.querySelector('.alert');
        if(alert) {
            alert.style.animation = 'fadeOut 0.5s ease';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }
    }, 5000);
</script>
<?php endif; ?>