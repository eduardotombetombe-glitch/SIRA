<?php $titulo = 'Página no encontrada'; $sinMenu = true; require RUTA_BASE . '/views/layouts/header.php'; ?>
<section class="tarjeta-acceso">
    <p class="logo-acceso">SIRA-LSC</p>
    <h1>Esta página no existe</h1>
    <p class="texto-apoyo">Es posible que el enlace esté mal escrito o que la sección ya no esté disponible.</p>
    <a class="boton" href="<?php echo url(haySesion() ? panelDe(rolActual()) : 'login'); ?>">Volver al inicio</a>
</section>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
