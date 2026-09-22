/**
 * SIRA-LSC | Activar/desactivar usuarios sin recargar la página.
 * Consume la API REST del proyecto (api/usuarios.php, método PUT) y demuestra
 * la comunicación funcional entre el frontend y el servicio backend.
 * Se engancha únicamente a los botones .boton-estado de administrador/usuarios.php.
 */
document.addEventListener('DOMContentLoaded', function () {
    var tabla = document.getElementById('tabla-usuarios');
    if (!tabla) {
        return; // Esta página no tiene la tabla de usuarios: no hay nada que hacer.
    }

    var baseUrl = tabla.dataset.baseUrl || '';

    tabla.querySelectorAll('.boton-estado').forEach(function (boton) {
        boton.addEventListener('click', function () {
            cambiarEstado(boton, baseUrl);
        });
    });
});

/** Envía el nuevo estado (activo/inactivo) al recurso REST /api/usuarios.php. */
function cambiarEstado(boton, baseUrl) {
    var id            = boton.dataset.id;
    var activoActual  = parseInt(boton.dataset.activo, 10);
    var nuevoActivo   = activoActual === 1 ? 0 : 1;
    var textoOriginal = boton.textContent;

    boton.disabled    = true;
    boton.textContent = 'Guardando…';

    fetch(baseUrl + 'api/usuarios.php?id=' + encodeURIComponent(id), {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin', // envía la cookie de sesión: la API exige rol administrador
        body: JSON.stringify({ activo: nuevoActivo }),
    })
        .then(function (respuesta) {
            return respuesta.json().then(function (cuerpo) {
                return { ok: respuesta.ok, cuerpo: cuerpo };
            });
        })
        .then(function (resultado) {
            if (!resultado.ok || !resultado.cuerpo.exito) {
                throw new Error(resultado.cuerpo.mensaje || 'No se pudo actualizar el estado.');
            }
            aplicarEstado(boton, nuevoActivo);
        })
        .catch(function (error) {
            boton.textContent = textoOriginal;
            alert(error.message || 'No se pudo conectar con el servidor.');
        })
        .finally(function () {
            boton.disabled = false;
        });
}

/** Refleja el nuevo estado en el botón sin recargar la tabla. */
function aplicarEstado(boton, nuevoActivo) {
    boton.dataset.activo = String(nuevoActivo);
    boton.textContent    = nuevoActivo === 1 ? 'Activo' : 'Inactivo';
    boton.classList.toggle('estado-activo', nuevoActivo === 1);
    boton.classList.toggle('estado-inactivo', nuevoActivo === 0);
}
