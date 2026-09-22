-- ============================================================================
-- SIRA-LSC | Plataforma de aprendizaje de Lengua de Señas Colombiana
-- Base de datos completa: estructura + contenido + usuarios de prueba
-- Importar desde phpMyAdmin o con:  mysql -u root < database.sql
--
-- Actores: estudiante, tutor y administrador (columna usuarios.rol)
-- ============================================================================

DROP DATABASE IF EXISTS sira_lsc;
CREATE DATABASE sira_lsc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sira_lsc;

-- ----------------------------------------------------------------------------
-- 1. GRADOS  (se crea PRIMERO: usuarios y cursos dependen de esta tabla)
--    Aqui se corrige el error SQLSTATE[42S02]: la tabla grados no existia
-- ----------------------------------------------------------------------------
CREATE TABLE grados (
    id_grado    INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    orden       INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO grados (id_grado, nombre, descripcion, orden) VALUES
(1, 'Grado 0', 'Primer contacto con la Lengua de Señas Colombiana: alfabeto, saludos y presentación.', 0),
(2, 'Grado 1', 'Vocabulario cotidiano: familia, números, colores y animales.', 1),
(3, 'Grado 2', 'Objetos, lugares, personas y actividades del día a día.', 2),
(4, 'Grado 3', 'Oraciones, preguntas y expresiones para conversar.', 3),
(5, 'Grado 4', 'Conversaciones avanzadas y comprensión en situaciones reales.', 4),
(6, 'Grado 5', 'Comunicación fluida, narración y evaluación final.', 5);

-- ----------------------------------------------------------------------------
-- 2. USUARIOS  (los tres actores viven en la misma tabla, separados por `rol`)
--    id_grado solo se usa cuando el rol es 'estudiante'
-- ----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(80)  NOT NULL,
    apellido       VARCHAR(80)  NOT NULL,
    correo         VARCHAR(150) NOT NULL UNIQUE,
    usuario        VARCHAR(50)  NOT NULL UNIQUE,
    contrasena     VARCHAR(255) NOT NULL,
    rol            ENUM('estudiante','tutor','administrador') NOT NULL DEFAULT 'estudiante',
    id_grado       INT          NULL,
    activo         TINYINT(1)   NOT NULL DEFAULT 1,
    fecha_registro DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_grado
        FOREIGN KEY (id_grado) REFERENCES grados(id_grado)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 3. TUTOR_ESTUDIANTE  (relación tutor -> estudiante, un tutor puede tener varios)
-- ----------------------------------------------------------------------------
CREATE TABLE tutor_estudiante (
    id_tutor       INT NOT NULL,
    id_estudiante  INT NOT NULL,
    fecha_asociado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_tutor, id_estudiante),
    CONSTRAINT fk_te_tutor
        FOREIGN KEY (id_tutor) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_te_estudiante
        FOREIGN KEY (id_estudiante) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 4. CURSOS  (unidades que pertenecen a un grado)
-- ----------------------------------------------------------------------------
CREATE TABLE cursos (
    id_curso    INT AUTO_INCREMENT PRIMARY KEY,
    id_grado    INT          NOT NULL,
    nombre      VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    icono       VARCHAR(8)   NOT NULL DEFAULT 'LSC',
    orden       INT          NOT NULL DEFAULT 0,
    CONSTRAINT fk_cursos_grado
        FOREIGN KEY (id_grado) REFERENCES grados(id_grado)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 5. LECCIONES  (contenido educativo de cada unidad)
-- ----------------------------------------------------------------------------
CREATE TABLE lecciones (
    id_leccion  INT AUTO_INCREMENT PRIMARY KEY,
    id_curso    INT          NOT NULL,
    titulo      VARCHAR(140) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    contenido   TEXT         NOT NULL,
    actividad   TEXT         NULL,
    orden       INT          NOT NULL DEFAULT 0,
    CONSTRAINT fk_lecciones_curso
        FOREIGN KEY (id_curso) REFERENCES cursos(id_curso)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 6. PROGRESO  (una fila por lección completada por un estudiante)
-- ----------------------------------------------------------------------------
CREATE TABLE progreso (
    id_progreso      INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario       INT        NOT NULL,
    id_leccion       INT        NOT NULL,
    completada       TINYINT(1) NOT NULL DEFAULT 1,
    fecha_completada DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uk_progreso UNIQUE (id_usuario, id_leccion),
    CONSTRAINT fk_progreso_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_progreso_leccion
        FOREIGN KEY (id_leccion) REFERENCES lecciones(id_leccion)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- CONTENIDO EDUCATIVO (editable desde el panel del administrador)
-- ============================================================================
-- Cursos (unidades) por grado
INSERT INTO cursos (id_curso, id_grado, nombre, descripcion, icono, orden) VALUES
(1, 1, 'Introducción al lenguaje de señas', 'Qué es la LSC, como se usa el espacio y las manos.', 'IN', 1),
(2, 1, 'Saludos', 'Las primeras frases para iniciar una conversación.', 'SA', 2),
(3, 1, 'Presentación personal', 'Decir tu nombre, tu edad y de dónde eres.', 'PR', 3),
(4, 1, 'Abecedario básico', 'El alfabeto manual letra por letra.', 'AB', 4),
(5, 2, 'Familia', 'Nombrar a las personas de tu hogar.', 'FA', 1),
(6, 2, 'Números', 'Contar del 1 al 100 y usar cantidades.', 'NU', 2),
(7, 2, 'Colores', 'Identificar y describir colores.', 'CO', 3),
(8, 2, 'Animales', 'Vocabulario de animales domesticos y salvajes.', 'AN', 4),
(9, 3, 'Objetos', 'Cosas que usas todos los días.', 'OB', 1),
(10, 3, 'Lugares', 'Nombrar sitios de la ciudad.', 'LU', 2),
(11, 3, 'Personas', 'Describir a quienes te rodean.', 'PE', 3),
(12, 3, 'Actividades cotidianas', 'Verbos del día a día.', 'AC', 4),
(13, 4, 'Oraciones básicas', 'Construir frases completas.', 'OR', 1),
(14, 4, 'Preguntas', 'Pedir información.', 'PG', 2),
(15, 4, 'Expresiones', 'Frases hechas y cortesía.', 'EX', 3),
(16, 4, 'Conversaciones', 'Practicar diálogos cortos.', 'CV', 4),
(17, 5, 'Conversaciones avanzadas', 'Diálogos largos y con matices.', 'CA', 1),
(18, 5, 'Situaciones cotidianas', 'Resolver tramites y visitas.', 'SI', 2),
(19, 5, 'Comprensión', 'Entender a otros señantes.', 'CM', 3),
(20, 6, 'Comunicación avanzada', 'Fluidez y precision.', 'CX', 1),
(21, 6, 'Construcción de conversaciones', 'Diálogos completos y reales.', 'CC', 2),
(22, 6, 'Evaluación final', 'Demuestra todo lo aprendido.', 'EV', 3);

-- Lecciones de cada curso
INSERT INTO lecciones (id_curso, titulo, descripcion, contenido, orden) VALUES
(1, 'Qué es la LSC', 'Historia y reconocimiento legal de la lengua de señas en Colombia.', 'La Lengua de Señas Colombiana es la lengua natural de la comunidad sorda del pais y fue reconocida por la Ley 324 de 1996. No es español con las manos: tiene su propia gramática y su propio orden de las palabras.', 1),
(1, 'Las manos y el espacio', 'Configuracion, ubicación, orientación y movimiento.', 'Cada seña se forma con cuatro elementos: la forma de la mano, el lugar dónde se hace, hacia dónde apuntan los dedos y el movimiento. Cambiar uno solo de ellos puede cambiar por completo el significado.', 2),
(1, 'La expresión facial', 'La cara también habla: cejas, boca y mirada.', 'Las cejas levantadas acompañan las preguntas de si o no; las cejas fruncidas acompañan preguntas con que, quién o dónde. Sin expresión facial la frase queda incompleta.', 3),
(1, 'Normas de comunicación', 'Como llamar la atención y mantener el contacto visual.', 'Para llamar a una persona sorda se toca suavemente el hombro o se agita la mano dentro de su campo visual. Durante la conversación se mantiene el contacto visual: mirar hacia otro lado equivale a interrumpir.', 4),
(2, 'Hola y adios', 'Saludo y despedida básicos.', 'HOLA: mano abierta a la altura de la sien que se separa hacia adelante. ADIOS: mano abierta que se mueve de lado a lado a la altura del hombro.', 1),
(2, 'Buenos días, tardes y noches', 'Saludos según la hora del día.', 'BUENOS DÍAS combina la seña de BUENO con el brazo que se levanta como el sol saliendo. En TARDES el brazo queda inclinado y en NOCHES el brazo baja y la mano se cierra.', 2),
(2, 'Como estas', 'Preguntar por el estado de la otra persona.', 'COMO ESTAS se hace con las manos abiertas frente al pecho que giran hacia adelante, acompañado de cejas fruncidas porque es una pregunta abierta.', 3),
(2, 'Por favor y gracias', 'Formulas de cortesía.', 'GRACIAS: la punta de los dedos toca el mentón y baja hacia adelante. POR FAVOR: la mano abierta gira en circulo sobre el pecho.', 4),
(3, 'Mi nombre es', 'Presentarte usando el deletreo.', 'Se hace la seña YO, luego NOMBRE (dos dedos que golpean sobre los dedos de la otra mano) y después se deletrea el nombre con el alfabeto manual.', 1),
(3, 'Tu nombre de señas', 'Como se asigna un nombre propio en la comunidad sorda.', 'El nombre de señas lo asigna una persona sorda y suele relacionarse con un rasgo físico o de personalidad. No se elige uno mismo.', 2),
(3, 'Mi edad', 'Decir cuántos años tienes.', 'Se une la seña EDAD, hecha en el mentón, con el número correspondiente. Para las decenas el número se hace a la altura del pecho.', 3),
(3, 'De dónde soy', 'Nombrar tu ciudad o departamento.', 'La seña VIVIR se hace con las dos manos sobre el pecho subiendo, seguida del nombre de la ciudad. Muchas ciudades colombianas tienen seña propia, como Bogota o Medellin.', 4),
(4, 'Letras A a la F', 'Primeras seis configuraciones del alfabeto.', 'A: puño cerrado con el pulgar al lado. B: mano plana con pulgar doblado. C: mano en forma de C. D: indice arriba y demas dedos tocando el pulgar. E: dedos doblados sobre el pulgar. F: indice y pulgar unidos, tres dedos arriba.', 1),
(4, 'Letras G a la M', 'Continuacion del alfabeto manual.', 'Estas letras introducen cambios de orientación: la G y la H apuntan hacia el lado, mientras la M esconde el pulgar bajo tres dedos.', 2),
(4, 'Letras N a la S', 'Letras con configuraciones cerradas.', 'La N esconde el pulgar bajo dos dedos, la O forma un circulo completo y la S es un puño con el pulgar al frente.', 3),
(4, 'Letras T a la Z', 'Últimas letras y letras con movimiento.', 'La J y la Z se dibujan en el aire con el indice. La LL y la RR del español tienen seña propia en el alfabeto colombiano.', 4),
(4, 'Deletrear palabras', 'Practicar el deletreo completo.', 'Al deletrear se mantiene la mano a la altura del hombro, quieta, y solo cambian los dedos. La fluidez importa más que la velocidad.', 5),
(5, 'Mama y papa', 'Las dos señas base del parentesco.', 'MAMA se hace tocando la mejilla con la mano abierta; PAPA se hace en la frente. La zona alta corresponde a lo masculino y la baja a lo femenino.', 1),
(5, 'Hermanos y hermanas', 'Hablar de tus hermanos.', 'HERMANO combina la seña de HOMBRE con IGUAL, uniendo los dos indices. HERMANA parte de la seña de MUJER.', 2),
(5, 'Abuelos e hijos', 'Tres generaciones de la familia.', 'ABUELO se hace como PAPA seguido de un movimiento hacia atras que indica el pasado. HIJO se señala con el brazo que mece.', 3),
(5, 'Mi familia completa', 'Construir frases sobre tu familia.', 'Para decir cuántas personas viven contigo se une FAMILIA con el número: FAMILIA CUATRO significa que son cuatro en casa.', 4),
(6, 'Del 1 al 10', 'Los números de una mano.', 'Del 1 al 5 se levantan los dedos con la palma hacia adentro; del 6 al 9 el pulgar toca cada dedo y el 10 se hace agitando el pulgar.', 1),
(6, 'Del 11 al 30', 'Números compuestos.', 'Del 11 al 15 los dedos rebotan hacia arriba. A partir del 20 se combinan las decenas con las unidades en un solo movimiento.', 2),
(6, 'Decenas y centenas', 'Contar hasta cien.', 'Las decenas se marcan con un pequeño movimiento lateral y el 100 se hace con la C seguida del puño.', 3),
(6, 'Precios y cantidades', 'Usar números en la vida diaria.', 'Para decir un precio se une el número con la seña PESOS, que se hace frotando el pulgar sobre los dedos.', 4),
(7, 'Colores primarios', 'Rojo, azul y amarillo.', 'ROJO se hace pasando el indice sobre los labios. AZUL y AMARILLO usan las letras A y Y con un pequeño movimiento giratorio.', 1),
(7, 'Blanco, negro y gris', 'Colores neutros.', 'BLANCO parte del pecho y la mano se cierra hacia adelante. NEGRO se hace pasando el indice por la ceja.', 2),
(7, 'Colores secundarios', 'Verde, naranja, morado y rosado.', 'MORADO se hace con la letra M girando en el aire. VERDE usa la letra V con movimiento corto.', 3),
(7, 'Describir con colores', 'Unir color y objeto.', 'En LSC el color va después del objeto: primero se seña CAMISA y luego MORADO.', 4),
(8, 'Animales de casa', 'Perro, gato y pajaro.', 'PERRO se hace palmeando la pierna y chasqueando los dedos. GATO dibuja los bigotes sobre la mejilla.', 1),
(8, 'Animales de granja', 'Vaca, caballo, gallina y cerdo.', 'VACA se hace con el pulgar en la sien y el meñique levantado imitando el cuerno. CABALLO mueve dos dedos como las orejas.', 2),
(8, 'Animales salvajes', 'Fauna colombiana.', 'Muchas señas imitan un rasgo del animal: el OSO araña el pecho, el TIGRE dibuja las rayas en la cara.', 3),
(8, 'Hablar de mascotas', 'Frases con animales.', 'YO TENER PERRO DOS es la forma correcta de decir que tienes dos perros: primero el objeto y después la cantidad.', 4),
(9, 'En la casa', 'Mesa, silla, cama y puerta.', 'Muchas señas de objetos dibujan su forma en el aire: MESA traza la superficie plana y SILLA imita el acto de sentarse con dos dedos.', 1),
(9, 'En el salón de clase', 'Cuaderno, lápiz, libro y tablero.', 'LIBRO abre las dos palmas juntas. ESCRIBIR simula el movimiento del lápiz sobre la palma contraria.', 2),
(9, 'Ropa', 'Prendas de vestir.', 'ROPA se hace con las dos manos que bajan sobre el pecho. Cada prenda se seña en el lugar del cuerpo dónde se usa.', 3),
(9, 'Tecnología', 'Celular, computador e internet.', 'CELULAR pone la mano en forma de Y junto a la oreja. COMPUTADOR mueve la letra C sobre el antebrazo.', 4),
(10, 'Mi casa', 'Partes de la vivienda.', 'CASA dibuja el techo con las dos manos. Cada habitación se forma uniendo CASA con la actividad que se hace allí.', 1),
(10, 'El colegio', 'Espacios educativos.', 'COLEGIO se hace aplaudiendo una vez con las manos planas. SALÓN delimita un espacio cuadrado en el aire.', 2),
(10, 'La ciudad', 'Parque, tienda, hospital y banco.', 'HOSPITAL dibuja una cruz sobre el brazo. TIENDA imita el intercambio de dinero con las dos manos.', 3),
(10, 'Dar direcciones', 'Explicar como llegar a un lugar.', 'En LSC las direcciones se construyen en el espacio: se ubica el punto de partida y se traza el recorrido con la mano.', 4),
(11, 'Hombre, mujer, niño', 'Señas base para personas.', 'HOMBRE se hace en la frente y MUJER en la mejilla. NINO se marca con la altura de la mano respecto al suelo.', 1),
(11, 'Profesiones', 'Trabajos comunes.', 'Casi toda profesión se forma con la accion más la seña PERSONA, que baja con las dos manos a los lados del cuerpo.', 2),
(11, 'Descripción física', 'Alto, bajo, delgado, cabello.', 'La descripción física sigue el orden: persona, rasgo general y luego detalle.', 3),
(11, 'Emociones', 'Feliz, triste, bravo, cansado.', 'La expresión facial es obligatoria: la seña TRISTE sin cara triste no se entiende como triste.', 4),
(12, 'Verbos de rutina', 'Comer, dormir, estudiar, trabajar.', 'COMER lleva la mano cerrada a la boca. DORMIR cierra la mano abierta frente a la cara mientras los ojos se cierran.', 1),
(12, 'Mi día', 'Contar tu rutina en orden.', 'La LSC marca el tiempo al inicio de la frase: MANANA YO ESTUDIAR significa que mañana estudiaras.', 2),
(12, 'Los días de la semana', 'Lunes a domingo.', 'Los días se forman con la primera letra de su nombre y un pequeño movimiento circular.', 3),
(12, 'La hora', 'Decir a que hora haces algo.', 'Se toca la muneca con el indice y luego se hace el número de la hora.', 4),
(13, 'Orden de la frase', 'Tiempo, sujeto, objeto, verbo.', 'La LSC organiza la información de lo general a lo especifico. HOY YO PAN COMER es una frase bien formada.', 1),
(13, 'Frases afirmativas', 'Decir lo que si ocurre.', 'La afirmación se refuerza con un movimiento corto de cabeza hacia adelante mientras se hace la seña.', 2),
(13, 'Frases negativas', 'Negar con el cuerpo y las manos.', 'La negación se marca moviendo la cabeza de lado a lado; la seña NO puede omitirse si la cara ya lo indica.', 3),
(13, 'Unir dos ideas', 'Frases más largas.', 'Se usa el espacio: una idea se ubica a la derecha y la otra a la izquierda, y la mirada marca el cambio.', 4),
(14, 'Preguntas de si o no', 'Cejas arriba.', 'La pregunta cerrada se marca levantando las cejas y inclinando ligeramente el cuerpo hacia adelante.', 1),
(14, 'Qué, quién, dónde', 'Preguntas abiertas.', 'Las preguntas abiertas llevan cejas fruncidas y la seña interrogativa va al final de la frase.', 2),
(14, 'Cuándo y cuanto', 'Preguntar por tiempo y cantidad.', 'CUÁNTO abre los dedos desde el puño. CUÁNDO gira el indice alrededor del otro indice.', 3),
(14, 'Por qué y como', 'Preguntar por causa y modo.', 'POR QUÉ toca la frente y baja abriendo la mano. La respuesta empieza con PORQUE usando la misma configuración.', 4),
(15, 'Pedir ayuda', 'Frases útiles.', 'AYUDAR pone una mano sobre la otra y las dos suben juntas; la dirección indica quién ayuda a quién.', 1),
(15, 'Disculpas y permiso', 'Resolver situaciones incomodas.', 'PERDON gira el puño cerrado sobre el pecho, con expresión de pena.', 2),
(15, 'Acuerdo y desacuerdo', 'Opinar en una conversación.', 'DE ACUERDO une los dos indices al frente. NO ESTAR DE ACUERDO los separa con gesto de duda.', 3),
(15, 'Expresiones colombianas', 'Modismos de la comunidad sorda local.', 'Cada región tiene variantes: una misma palabra puede señarse distinto en Bogota, Cali o Barranquilla.', 4),
(16, 'Saludar y presentarse', 'Diálogo de dos turnos.', 'Un diálogo empieza con contacto visual, saludo, nombre y una pregunta de retorno.', 1),
(16, 'Hablar del trabajo', 'Contar a que te dedicas.', 'Se usa la estructura YO TRABAJAR más el lugar y después el horario.', 2),
(16, 'Hacer planes', 'Proponer una actividad.', 'La propuesta se marca con cejas levantadas al final: TU YO CINE IR.', 3),
(16, 'Cerrar una conversación', 'Despedirse con naturalidad.', 'Se anuncia el cierre con LISTO o YA antes de la despedida, igual que en español oral.', 4),
(17, 'Cambio de turno', 'Como ceder y tomar la palabra.', 'El turno se cede bajando las manos y manteniendo la mirada; se toma levantando ligeramente una mano.', 1),
(17, 'Contar una anécdota', 'Narrar algo que te paso.', 'La narración usa el cuerpo: cada personaje se ubica en un punto del espacio y el narrador gira hacia el al hablar.', 2),
(17, 'Opinar y argumentar', 'Defender un punto de vista.', 'PENSAR se hace en la sien y encabeza la opinión: YO PENSAR más la idea.', 3),
(17, 'Interrumpir con respeto', 'Normas de la conversación.', 'Se levanta la mano abierta a la altura del pecho y se espera a que la otra persona detenga su seña.', 4),
(18, 'En el médico', 'Explicar un sintoma.', 'DOLER se hace con los dos indices que se acercan sobre la parte del cuerpo afectada.', 1),
(18, 'En el transporte', 'Moverse por la ciudad.', 'BUS mueve la letra B hacia adelante. El destino se ubica primero en el espacio.', 2),
(18, 'En el banco o la tienda', 'Tramites y compras.', 'Los números y precios se combinan con las señas de PAGAR y RECIBO.', 3),
(18, 'En una entrevista', 'Contexto laboral.', 'Se usa un registro formal: señas más amplias, ritmo pausado y expresión neutra.', 4),
(19, 'Velocidad y ritmo', 'Seguir una conversación rápida.', 'La comprensión mejora si se mira la cara y no las manos: la visión periférica capta el movimiento.', 1),
(19, 'Variantes regionales', 'Diferencias entre regiones.', 'Ante una seña desconocida se pide aclaracion con QUÉ SIGNIFICAR en lugar de asumir el sentido.', 2),
(19, 'Clasificadores', 'Manos que representan objetos.', 'Un clasificador es una configuración que representa un objeto y se mueve por el espacio: el indice puede ser una persona caminando.', 3),
(19, 'Práctica de comprensión', 'Ejercicio integrador.', 'Se observa un diálogo completo y se identifica quién hace que, cuando y dónde.', 4),
(20, 'Ritmo natural', 'Senar sin pausas artificiales.', 'La fluidez llega cuando las señas se encadenan sin volver siempre a la posicion de reposo.', 1),
(20, 'Uso del espacio', 'Gramatica espacial completa.', 'Los lugares y las personas se ubican una sola vez y después se señalan; repetir la seña completa resulta redundante.', 2),
(20, 'Registro formal e informal', 'Adaptar tu forma de señar.', 'El registro formal amplia el espacio de señación y reduce los gestos coloquiales.', 3),
(20, 'Errores frecuentes', 'Qué corregir al avanzar.', 'El error más común es señar siguiendo el orden del español en lugar del orden propio de la LSC.', 4),
(21, 'Diálogo de presentación', 'Conocer a alguien nuevo.', 'Se práctica un diálogo de al menos seis turnos con preguntas de retorno.', 1),
(21, 'Diálogo de negociacion', 'Acordar algo con otra persona.', 'Se combinan propuestas, objeciones y acuerdos manteniendo la coherencia espacial.', 2),
(21, 'Narración larga', 'Contar una historia completa.', 'La historia se organiza en introducción, desarrollo y cierre, con cambio de rol para cada personaje.', 3),
(21, 'Exposición ante público', 'Senar para un grupo.', 'Ante un grupo se amplia el espacio, se reduce la velocidad y se repiten las ideas clave.', 4),
(22, 'Repaso de vocabulario', 'Revision de los grados anteriores.', 'Se repasan alfabeto, números, familia, lugares y verbos de rutina.', 1),
(22, 'Repaso de gramática', 'Orden, preguntas y negación.', 'Se verifica el uso correcto de las cejas en preguntas y del movimiento de cabeza en la negación.', 2),
(22, 'Prueba de comprensión', 'Entender un diálogo completo.', 'Se observa una conversación y se resume su contenido.', 3),
(22, 'Prueba de expresión', 'Senar de forma autónoma.', 'El estudiante presenta una narración de dos minutos sobre un tema libre.', 4);

-- Actividad práctica de cada lección (el administrador puede editarla una por una)
UPDATE lecciones SET actividad = CONCAT(
    'Practica frente a un espejo la seña de "', titulo, '" durante dos minutos. ',
    'Después grábate o pídele a alguien que te observe y revisa tres cosas: la forma de la mano, ',
    'el lugar donde haces la seña y tu expresión facial. Marca la lección como completada cuando ',
    'puedas repetirla sin mirar el texto.'
);

-- ============================================================================
-- USUARIOS DE PRUEBA
-- Contraseñas cifradas con password_hash() / bcrypt:
--   admin      -> admin123
--   tutor      -> tutor123
--   estudiante -> estudiante123
-- ============================================================================
INSERT INTO usuarios (id_usuario, nombre, apellido, correo, usuario, contrasena, rol, id_grado) VALUES
(1, 'Sara',   'Moreno',  'admin@siralsc.co',      'admin',      '$2y$10$H4NyWKNamWTSGtfaulz0BuUfo9A73KsR/cHKCIkHwPuQ5l.N2jDZa', 'administrador', NULL),
(2, 'Carlos', 'Rivera',  'tutor@siralsc.co',      'tutor',      '$2y$10$Q3oEjSOw2CGQIcbe/pVha.bdpbedaZgkHD4Q0RQxuHZuP50vDDFiS', 'tutor',         NULL),
(3, 'Mariana','Rivera',  'estudiante@siralsc.co', 'estudiante', '$2y$10$r7VHFCVC7iBfNafuRmL7R.UKzOuL2dtIYFbYu48CDyeSDWL2.cs0W', 'estudiante',    1);

-- El tutor de prueba queda asociado a la estudiante de prueba
INSERT INTO tutor_estudiante (id_tutor, id_estudiante) VALUES (2, 3);

-- Progreso inicial de la estudiante de prueba (primeras lecciones del Grado 0)
INSERT INTO progreso (id_usuario, id_leccion) VALUES (3, 1), (3, 2), (3, 3);
