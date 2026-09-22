<?php
$titulo        = 'Inicia sesión';
$sinMenu       = true;
$errores       = isset($errores) ? $errores : array();
$identificador = isset($identificador) ? $identificador : '';
require RUTA_BASE . '/views/layouts/header.php';
?>

<div class="pantalla-login" aria-label="Acceso a SIRA-LSC">
    <section class="panel-identidad" aria-labelledby="titulo-identidad">
        <div class="identidad-contenido">
            <div class="marca-hero" aria-label="SIRA-LSC">
                <span class="marca-hero-sigla">LSC</span>
                <span>
                    <strong>SIRA-LSC</strong>
                    <small>Aprende Lengua de Señas Colombiana paso a paso</small>
                </span>
            </div>

            <p class="etiqueta-hero">EDUCACIÓN · INCLUSIÓN · COMUNIDAD</p>
            <h1 id="titulo-identidad">Conectando comunidades a través de la Lengua de Señas Colombiana</h1>
            <p class="descripcion-hero">
                Transformamos el aprendizaje de la Lengua de Señas Colombiana a través de una plataforma accesible, gratuita e interactiva.
                Diseñada para conectar a estudiantes y familias eliminando barreras de comunicación paso a paso.
            </p>

            <div class="bloque-proposito">
                <p class="titulo-proposito">Nuestra visión</p>
                <p>
                    Nuestra visión es eliminar las barreras de comunicación en Colombia mediante un aprendizaje inclusivo, práctico y guiado paso a paso para estudiantes, tutores y administradores.
                </p>
            </div>

            <div class="pilares" aria-label="Pilares de SIRA-LSC">
                <article class="pilar">
                    <span class="pilar-icono" aria-hidden="true">♡</span>
                    <div><h2>Inclusión Social</h2><p>Más oportunidades para aprender y comunicarse.</p></div>
                </article>
                <article class="pilar">
                    <span class="pilar-icono" aria-hidden="true">✓</span>
                    <div><h2>Aprendizaje Guiado</h2><p>Lecciones claras, prácticas y paso a paso.</p></div>
                </article>
                <article class="pilar">
                    <span class="pilar-icono" aria-hidden="true">↔</span>
                    <div><h2>Comunidad Unida</h2><p>Acercamos a la comunidad sorda y oyente.</p></div>
                </article>
            </div>

            <div class="recurso-lsc" role="img" aria-label="Espacio para avatar o video de bienvenida en Lengua de Señas Colombiana">
                <div class="avatar-placeholder">
                    <span class="avatar-circulo" aria-hidden="true">LSC</span>
                    <div>
                        <strong>Bienvenida visual en LSC</strong>
                        <p>Aquí puedes integrar un avatar interactivo o un video corto de bienvenida.</p>
                    </div>
                    <span class="play-placeholder" aria-hidden="true">▶</span>
                </div>
            </div>
        </div>
    </section>

    <section class="panel-login" aria-labelledby="titulo-login">
        <div class="login-columna">
            <?php if ($aviso): ?>
                <div class="toast-acceso toast-<?php echo e($aviso['tipo']); ?>" role="status" aria-live="polite">
                    <span class="toast-icono" aria-hidden="true">✓</span>
                    <span><?php echo e($aviso['texto']); ?></span>
                </div>
            <?php endif; ?>

            <div class="tarjeta-acceso tarjeta-login">
                <div class="login-encabezado">
                    <p class="logo-acceso">SIRA-LSC</p>
                    <h2 id="titulo-login">Entra a tu cuenta</h2>
                    <p class="texto-apoyo">Entra como estudiante, tutor o administrador; el sistema te lleva a tu panel.</p>
                </div>

                <?php if (isset($errores['general'])): ?>
                    <div class="aviso aviso-error" role="alert">
                        <?php echo e($errores['general']); ?>
                    </div>
                <?php endif; ?>

                <form class="formulario formulario-login" method="post" action="<?php echo url('login/entrar'); ?>" autocomplete="on">
                    <p class="campo">
                        <label for="identificador">Usuario o correo</label>
                        <input type="text" id="identificador" name="identificador" value="<?php echo e($identificador); ?>" placeholder="Escribe tu usuario o correo" autocomplete="username" autocapitalize="none" spellcheck="false" required aria-describedby="ayuda-identificador">
                        <span class="ayuda" id="ayuda-identificador">Usa el usuario o correo registrado en SIRA-LSC.</span>
                        <?php if (isset($errores['identificador'])): ?><span class="error" role="alert"><?php echo e($errores['identificador']); ?></span><?php endif; ?>
                    </p>

                    <p class="campo">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Escribe tu contraseña" autocomplete="current-password" required>
                        <?php if (isset($errores['contrasena'])): ?><span class="error" role="alert"><?php echo e($errores['contrasena']); ?></span><?php endif; ?>
                    </p>

                    <button class="boton boton-login" type="submit">
                        <span>Iniciar sesión</span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>

                <p class="enlace-alterno">¿Todavía no tienes cuenta? <a href="<?php echo url('registro'); ?>">Regístrate</a></p>

                <div class="accesibilidad-nota">
                    <span aria-hidden="true">▣</span>
                    <p>Diseñado para una experiencia clara, visual y accesible para la comunidad sorda y oyente.</p>
                </div>
            </div>

        </div>
    </section>
</div>
<?php require RUTA_BASE . '/views/layouts/footer.php'; ?>
