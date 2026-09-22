# SIRA-LSC

Plataforma educativa para aprender **Lengua de Señas Colombiana (LSC)** por niveles,
del **Grado 0 al Grado 5**, con tres actores: **estudiante**, **tutor** y **administrador**.

Hecha únicamente con **PHP + HTML + CSS + MySQL**. Sin JavaScript, sin Bootstrap
y sin ningún framework.

## 1. Instalación en XAMPP

1. Copia la carpeta `sira-lsc` dentro de `C:\xampp\htdocs\`, de modo que quede
   `C:\xampp\htdocs\sira-lsc\`.
2. Abre el panel de XAMPP e inicia **Apache** y **MySQL**.
3. Entra a `http://localhost/phpmyadmin`, pestaña **Importar**, selecciona
   `database.sql` y pulsa *Continuar*.
   Por consola: `C:\xampp\mysql\bin\mysql.exe -u root < database.sql`
4. Abre `http://localhost/sira-lsc/`. Verás la pantalla de inicio de sesión.

Si tu MySQL tiene contraseña, cámbiala en `config/Database.php` (constante `DB_CLAVE`).

## 2. Usuarios de prueba

| Rol           | Usuario      | Contraseña      |
|---------------|--------------|-----------------|
| Administrador | `admin`      | `admin123`      |
| Tutor         | `tutor`      | `tutor123`      |
| Estudiante    | `estudiante` | `estudiante123` |

El tutor de prueba ya está asociado a la estudiante de prueba, que empieza en
Grado 0 con tres lecciones completadas. También se puede entrar con el correo
en lugar del usuario.

## 3. El error de la tabla `grados`

El error `SQLSTATE[42S02]: Table 'sira_lsc.grados' doesn't exist` venía de que
`registro.php` consultaba la tabla antes de que existiera. Ahora:

- `database.sql` crea `grados` como **primera** tabla e inserta Grado 0 a Grado 5.
- La consulta vive en el modelo `models/Grado.php` (`obtenerTodos()`).
- Los controladores piden los grados al modelo y se los pasan a la vista.
- `registro.php` ya no consulta nada: solo redirige al front controller.

## 4. Estructura y MVC

```
sira-lsc/
├── index.php                      Front controller: reparte todas las rutas
├── .htaccess                      Apache (URLs limpias opcionales)
├── database.sql                   Base de datos completa + contenido + usuarios
├── config/
│   ├── Database.php               Única conexión PDO del proyecto
│   ├── conexion.php               Alias de compatibilidad con la versión anterior
│   └── funciones.php              Helpers de vistas, sesión y control de roles
├── controllers/
│   ├── LoginController.php        Inicio y cierre de sesión, reparto por rol
│   ├── RegistroController.php     Registro público (estudiante o tutor)
│   ├── EstudianteController.php   Panel, cursos, lecciones, progreso, perfil
│   ├── TutorController.php        Estudiantes asociados y su seguimiento
│   └── AdministradorController.php Gestión de usuarios, grados y lecciones
├── models/
│   ├── Usuario.php                Cuentas de los tres roles
│   ├── Estudiante.php             Consultas propias del estudiante
│   ├── Tutor.php                  Relación tutor -> estudiante
│   ├── Administrador.php          Cifras y seguimiento global
│   ├── Grado.php  Curso.php  Leccion.php  Progreso.php
├── views/
│   ├── login/        index.php
│   ├── registro/     index.php
│   ├── estudiante/   inicio, cursos, curso, lecciones, leccion, progreso, perfil
│   ├── tutor/        inicio, estudiantes, estudiante, perfil
│   ├── administrador/ inicio, usuarios, usuario_form, grados, lecciones, leccion_form, progreso
│   └── layouts/      header.php, footer.php, error404.php
└── css/estilos.css                Todo el diseño
```

El flujo es siempre el mismo:

```
VISTA -> CONTROLADOR -> MODELO -> BASE DE DATOS
BASE DE DATOS -> MODELO -> CONTROLADOR -> VISTA
```

No hay SQL dentro de las vistas, ni HTML dentro de los modelos, ni lógica de
negocio dentro de `index.php`: el front controller solo enlaza una ruta con el
método de un controlador.

## 5. Base de datos

| Tabla              | Para qué sirve                        | Relación                          |
|--------------------|---------------------------------------|-----------------------------------|
| `grados`           | Grado 0 a Grado 5                     | —                                 |
| `usuarios`         | Los tres roles (columna `rol`)        | `usuarios.id_grado → grados`      |
| `tutor_estudiante` | Qué estudiante acompaña cada tutor    | ambas columnas → `usuarios`       |
| `cursos`           | Unidades de cada grado                | `cursos.id_grado → grados`        |
| `lecciones`        | Contenido y actividad de cada unidad  | `lecciones.id_curso → cursos`     |
| `progreso`         | Lecciones completadas por estudiante  | `progreso.id_usuario → usuarios`, `progreso.id_leccion → lecciones` |

Contenido inicial: 6 grados, 22 unidades y 89 lecciones, todo editable desde el
panel del administrador o desde phpMyAdmin.

## 6. Qué puede hacer cada actor

**Estudiante:** registrarse (o ser registrado por su tutor), iniciar sesión, ver
su información y su grado, recorrer unidades y lecciones, hacer la actividad de
cada lección, marcarla como completada, consultar su porcentaje de avance,
retomar las pendientes y cerrar sesión.

**Tutor:** registrarse, iniciar sesión, registrar un estudiante nuevo o asociar
uno existente, consultar su grado, su progreso, sus lecciones completadas y
pendientes, y cerrar sesión. No tiene permisos de gestión.

**Administrador:** iniciar sesión, crear, editar y eliminar usuarios de
cualquier rol, gestionar grados y lecciones, asociar estudiantes con tutores y
revisar el progreso de todos los estudiantes.

## 7. Seguridad

- Una sola conexión PDO (`config/Database.php`) con `PDO::ERRMODE_EXCEPTION`.
- Todas las consultas con parámetros preparados, sin concatenar datos del usuario.
- Contraseñas con `password_hash()` y `password_verify()`.
- Sesiones PHP con `session_regenerate_id()` al entrar.
- `exigirRol()` protege cada ruta: si un estudiante abre `admin/usuarios`, el
  sistema lo devuelve a su propio panel con un aviso.
- El registro público solo permite crear estudiantes y tutores; el rol
  administrador únicamente se asigna desde el panel de un administrador.
- Salida escapada con `htmlspecialchars()`.

## Actualización de la pantalla de inicio de sesión — SIRA-LSC

La pantalla de acceso fue rediseñada con una estructura **Split-Screen 50/50** y mantiene la arquitectura PHP/MVC existente.

### Cambios realizados

- Panel izquierdo dedicado a identidad, visión y propósito de SIRA-LSC.
- Integración del mensaje: “Transformamos el aprendizaje de la Lengua de Señas Colombiana a través de una plataforma accesible, gratuita e interactiva. Diseñada para conectar a estudiantes y familias eliminando barreras de comunicación paso a paso.”
- Tres pilares visuales: Inclusión Social, Aprendizaje Guiado y Comunidad Unida.
- Placeholder preparado para integrar posteriormente un video o avatar de bienvenida en LSC.
- Panel derecho con tarjeta de autenticación, estados de error y notificación de cierre de sesión.
- Foco visible y navegación por teclado.
- Etiquetas semánticas, `aria-live`, `role=alert`, `autocomplete` y textos alternativos para mejorar la accesibilidad.
- Diseño responsive: en pantallas pequeñas los paneles pasan a una sola columna.
- Se conserva el formulario PHP existente y las rutas MVC; no se modifica la lógica de autenticación.

### Archivos actualizados

- `views/login/index.php` — nueva estructura visual del login.
- `views/layouts/header.php` — los avisos de sesión de las pantallas públicas se muestran dentro de la vista correspondiente.
- `css/estilos.css` — estilos Split-Screen, accesibilidad, responsive y estados de interacción.

El resto de controladores, modelos, API, vistas, JavaScript y base de datos se conserva.

