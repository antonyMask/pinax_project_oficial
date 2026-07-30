// Importamos bcrypt para guardar contraseñas mediante un hash seguro.
const bcrypt = require('bcryptjs');

// Importamos crypto para generar el código NUMERICO obligatorio.
const crypto = require('crypto');

// Importamos el pool de conexiones de MySQL.
const { pool } = require('../config/db');

// Importamos la configuración central de autenticación.
const { obtenerConfiguracionAuth } = require('../config/auth');

/*
 * Limpia valores de texto eliminando espacios al inicio y al final.
 * Si llega un dato que no es texto, devuelve una cadena vacía.
 */
const limpiarTexto = (valor) => {
    return typeof valor === 'string' ? valor.trim() : '';
};

/*
 * Genera un código NUMERICO único de seis dígitos.
 *
 * La tabla users lo requiere, aunque el login actual no lo use directamente.
 */
const generarCodigoNumerico = async (connection) => {
    // Intentamos generar un código disponible hasta 12 veces.
    for (let intento = 0; intento < 12; intento += 1) {
        const codigo = crypto
            .randomInt(100000, 1000000)
            .toString();

        // Verificamos que el código no esté asignado a otro usuario.
        const [coincidencias] = await connection.query(
            `
                SELECT COD_USER
                FROM users
                WHERE NUMERICO = ?
                LIMIT 1
            `,
            [codigo]
        );

        // Si no existe, podemos utilizarlo.
        if (coincidencias.length === 0) {
            return codigo;
        }
    }

    // Evitamos continuar si no se pudo encontrar un código disponible.
    throw new Error('No fue posible generar un código numérico disponible');
};

/*
 * POST /api/auth/register
 *
 * Registra una cuenta desde la pantalla pública de inicio de sesión.
 * Por requerimiento actual del proyecto, todo usuario nuevo recibirá
 * el código de tipo 1, correspondiente a Administrador.
 */
const registrar = async (req, res) => {
    let connection;
    let transaccionIniciada = false;

    try {
        // Extraemos los campos esperados del formulario.
        const {
            name,
            password,
            password_confirmation: passwordConfirmation
        } = req.body || {};

        /*
         * Normalizamos el usuario en minúsculas para evitar registros
         * equivalentes como "Jorge" y "jorge".
         */
        const nombreUsuario = limpiarTexto(name).toLowerCase();

        /*
         * No modificamos la contraseña, ya que todos sus caracteres
         * forman parte de la credencial elegida por el usuario.
         */
        const clave = typeof password === 'string' ? password : '';
        const confirmacion = typeof passwordConfirmation === 'string'
            ? passwordConfirmation
            : '';

        /*
         * Validamos el formato del nombre de usuario.
         */
        if (!/^[a-z0-9._-]{3,50}$/.test(nombreUsuario)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El usuario debe tener entre 3 y 50 caracteres y solo puede usar letras, números, punto, guion o guion bajo'
            });
        }

        /*
         * bcrypt acepta como máximo 72 bytes de contraseña.
         */
        if (
            clave.length < 8
            || Buffer.byteLength(clave, 'utf8') > 72
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'La contraseña debe tener al menos 8 caracteres y no superar 72 bytes'
            });
        }

        /*
         * Exigimos una estructura mínima de contraseña.
         */
        if (
            !/[a-z]/.test(clave)
            || !/[A-Z]/.test(clave)
            || !/[0-9]/.test(clave)
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'La contraseña debe incluir una mayúscula, una minúscula y un número'
            });
        }

        // Confirmamos que ambas contraseñas sean iguales.
        if (clave !== confirmacion) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'La confirmación no coincide con la contraseña'
            });
        }

        // Abrimos una conexión y una transacción de MySQL.
        connection = await pool.getConnection();
        await connection.beginTransaction();
        transaccionIniciada = true;

        /*
         * Configuración actual de Pinax:
         *
         * COD_TIPUSERS = 1 representa el rol Administrador.
         *
         * Se asigna directamente porque en tu base este valor ya se
         * maneja en la tabla users.
         */
        const codigoTipoUsuario = 1;
        const nombreTipoUsuario = 'Administrador';

        /*
         * Verificamos si el nombre de usuario ya existe.
         */
        const [usuariosDuplicados] = await connection.query(
            `
                SELECT COD_USER
                FROM users
                WHERE NAME = ?
                LIMIT 1
            `,
            [nombreUsuario]
        );

        // Rechazamos nombres que ya estén registrados.
        if (usuariosDuplicados.length > 0) {
            await connection.rollback();
            transaccionIniciada = false;

            return res.status(409).json({
                estado: 'error',
                mensaje: 'El nombre de usuario ya está registrado'
            });
        }

        // Obtenemos el costo bcrypt configurado en el proyecto.
        const configAuth = obtenerConfiguracionAuth();

        // Generamos el hash seguro de la contraseña.
        const claveSegura = await bcrypt.hash(
            clave,
            configAuth.bcryptRounds
        );

        // Generamos el código NUMERICO obligatorio.
        const codigoNumerico = await generarCodigoNumerico(connection);

        /*
         * Creamos el usuario.
         *
         * COD_PEOPLE permanece NULL porque el registro crea credenciales,
         * sin crear ni modificar una persona en el módulo Personas.
         */
        const [resultadoInsercion] = await connection.query(
            `
                INSERT INTO users (
                    COD_PEOPLE,
                    COD_TIPUSERS,
                    NAME,
                    CLAVE,
                    NUMERICO,
                    IND_USR,
                    IND_INS,
                    USR_ADD,
                    DAT_ADD
                )
                VALUES (
                    NULL,
                    ?,
                    ?,
                    ?,
                    ?,
                    '1',
                    '0',
                    'registro_publico',
                    NOW()
                )
            `,
            [
                codigoTipoUsuario,
                nombreUsuario,
                claveSegura,
                codigoNumerico
            ]
        );

        // Confirmamos la transacción.
        await connection.commit();
        transaccionIniciada = false;

        /*
         * Nunca enviamos al navegador la contraseña, el hash
         * ni el código NUMERICO.
         */
        return res.status(201).json({
            estado: 'ok',
            mensaje: 'Usuario creado correctamente',
            usuario: {
                cod_user: Number(resultadoInsercion.insertId),
                name: nombreUsuario,
                role: nombreTipoUsuario,
                ind_usr: '1'
            }
        });
    } catch (error) {
        // Revertimos cambios pendientes si ocurrió un error.
        if (connection && transaccionIniciada) {
            await connection.rollback();
        }

        /*
         * También controlamos duplicados si luego agregas un índice UNIQUE
         * sobre la columna NAME.
         */
        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(409).json({
                estado: 'error',
                mensaje: 'El nombre de usuario ya está registrado'
            });
        }

        // El detalle técnico se conserva solamente en la terminal de Node.
        console.error('Error al registrar usuario público:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message
        });

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al crear la cuenta'
        });
    } finally {
        // Liberamos siempre la conexión hacia el pool.
        if (connection) {
            connection.release();
        }
    }
};

// Exportamos el controlador para utilizarlo desde auth.routes.js.
module.exports = {
    registrar
};