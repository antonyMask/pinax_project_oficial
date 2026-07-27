// Importamos el pool de conexiones configurado para MySQL.
const { pool } = require('../config/db');

/*
    Constantes de validacion

    Funcion:
    - centralizan los tipos y estados permitidos para los asientos contables.
    - se usan en GET, POST y PUT.
*/
const TIPOS_ASIENTO = ['manual', 'ajuste', 'apertura', 'cierre', 'reversion'];
const ESTADOS_ASIENTO = ['borrador', 'aprobado', 'anulado'];

/*
    Funcion auxiliar: limpiarTexto
    - valida si un valor existe.
    - convierte el valor a texto.
    - elimina espacios al inicio y al final.
    - devuelve null si el texto queda vacio.
*/
const limpiarTexto = (valor) => {
    if (valor === undefined || valor === null) return null;

    const texto = String(valor).trim();
    return texto.length > 0 ? texto : null;
};

/*
    Funcion auxiliar: normalizarTexto
    - limpia un texto.
    - lo convierte a minusculas.
    - ayuda a validar campos como tipo de asiento y estado.
*/
const normalizarTexto = (valor) => {
    const texto = limpiarTexto(valor);
    return texto ? texto.toLowerCase() : null;
};

/*
    Funcion auxiliar: esEntero
    - valida si un valor puede convertirse correctamente a numero entero.
*/
const esEntero = (valor) => {
    return Number.isInteger(Number(valor));
};

/*
    Funcion auxiliar: esEnteroPositivo
    - valida si un valor es un numero entero mayor que cero.
*/
const esEnteroPositivo = (valor) => {
    return esEntero(valor) && Number(valor) > 0;
};

/*
    Funcion auxiliar: convertirBooleanoConsulta
    - convierte un valor recibido por query params a booleano.
    - acepta true, false, 1 y 0 como valores validos.
*/
const convertirBooleanoConsulta = (valor) => {
    const texto = normalizarTexto(valor);

    if (texto === null) return false;
    if (texto === 'true' || texto === '1') return true;
    if (texto === 'false' || texto === '0') return false;

    return null;
};

/*
    Obtiene el siguiente número visible de asiento para un año.
    El sufijo se calcula sobre num_asiento para mantener una secuencia
    independiente por año, incluso cuando cod_asiento es autoincremental.
*/
const obtenerSiguienteNumeroAsiento = async (connection, anio) => {
    const [resultado] = await connection.query(
        `SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(num_asiento, '-', -1) AS UNSIGNED)), 0) + 1 AS cod_asiento
         FROM ga_asiento_contable
         WHERE UPPER(num_asiento) REGEXP ?`,
        [`^ASI-${anio}-[0-9]{4}$`]
    );

    const codAsiento = Number(resultado[0]?.cod_asiento || 1);

    return {
        correlativo: codAsiento,
        numAsiento: `ASI-${anio}-${String(codAsiento).padStart(4, '0')}`
    };
};

/*
    Devuelve la previsualización del siguiente número para un año.
*/
const obtenerSiguienteNumeroAsientoController = async (req, res) => {
    let connection;

    try {
        const anio = limpiarTexto(req.query.anio);

        if (!anio || !/^\d{4}$/.test(anio) || Number(anio) < 1) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parámetro anio debe contener un año válido de cuatro dígitos'
            });
        }

        connection = await pool.getConnection();
        const siguiente = await obtenerSiguienteNumeroAsiento(connection, anio);

        return res.json({
            estado: 'ok',
            num_asiento: siguiente.numAsiento,
            correlativo: siguiente.correlativo,
            anio: Number(anio)
        });
    } catch (error) {
        console.error('Error al obtener el siguiente número de asiento:', error);

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al obtener el siguiente número de asiento'
        });
    } finally {
        if (connection) connection.release();
    }
};


/*
    Funcion auxiliar: esNumero
    - valida si un valor puede convertirse correctamente a numero.
    - se usa para validar montos contables.
*/
const esNumero = (valor) => {
    return valor !== undefined &&
           valor !== null &&
           valor !== '' &&
           Number.isFinite(Number(valor));
};

/*
    Funcion auxiliar: esNumeroNoNegativo
    - valida si un valor numerico es mayor o igual que cero.
    - se usa para mon_debe, mon_haber, tot_debe y tot_haber.
*/
const esNumeroNoNegativo = (valor) => {
    return esNumero(valor) && Number(valor) >= 0;
};

/*
    Funcion auxiliar: esFechaValida
    - valida si una fecha puede ser interpretada correctamente.
    - se usa para fec_asiento.
*/
const esFechaValida = (valor) => {
    if (!valor) return false;

    const fecha = new Date(valor);
    return !Number.isNaN(fecha.getTime());
};

/*
    Controlador: obtenerAsientos
    - consulta asientos contables.
    - permite listar todos los asientos.
    - permite filtrar por cod_asiento.
    - permite filtrar por cod_periodo.
    - permite filtrar por cod_user.
    - permite filtrar por tip_asiento.
    - permite filtrar por ind_estado.
    - permite incluir detalle cuando se consulta un asiento especifico.

    Metodo HTTP:
    - GET

    Rutas esperadas:
    - GET /api/asientos
    - GET /api/asientos?cod_asiento=1
    - GET /api/asientos?cod_periodo=1
    - GET /api/asientos?cod_user=1
    - GET /api/asientos?tip_asiento=manual
    - GET /api/asientos?ind_estado=aprobado
    - GET /api/asientos?cod_asiento=1&incluir_detalle=true
*/
const obtenerAsientos = async (req, res) => {
    try {
        // Extraemos los filtros enviados por query params.
        const {
            cod_asiento,
            cod_periodo,
            cod_user,
            tip_asiento,
            ind_estado,
            incluir_detalle
        } = req.query;

        // Limpiamos los parametros numericos.
        const codAsientoTexto = limpiarTexto(cod_asiento);
        const codPeriodoTexto = limpiarTexto(cod_periodo);
        const codUserTexto = limpiarTexto(cod_user);

        // Validamos cod_asiento si fue enviado.
        if (codAsientoTexto && !esEnteroPositivo(codAsientoTexto)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_asiento debe ser numerico y positivo'
            });
        }

        // Validamos cod_periodo si fue enviado.
        if (codPeriodoTexto && !esEnteroPositivo(codPeriodoTexto)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_periodo debe ser numerico y positivo'
            });
        }

        // Validamos cod_user si fue enviado.
        if (codUserTexto && !esEnteroPositivo(codUserTexto)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_user debe ser numerico y positivo'
            });
        }

        // Normalizamos el tipo de asiento.
        const tipAsientoParam = normalizarTexto(tip_asiento);

        // Validamos tip_asiento si fue enviado.
        if (tipAsientoParam && !TIPOS_ASIENTO.includes(tipAsientoParam)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro tip_asiento solo permite: manual, ajuste, apertura, cierre o reversion'
            });
        }

        // Normalizamos el estado del asiento.
        const indEstadoParam = normalizarTexto(ind_estado);

        // Validamos ind_estado si fue enviado.
        if (indEstadoParam && !ESTADOS_ASIENTO.includes(indEstadoParam)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro ind_estado solo permite: borrador, aprobado o anulado'
            });
        }

        // Convertimos incluir_detalle a booleano.
        const incluirDetalleParam = convertirBooleanoConsulta(incluir_detalle);

        // Validamos incluir_detalle si fue enviado con un valor no permitido.
        if (incluirDetalleParam === null) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro incluir_detalle solo permite true, false, 1 o 0'
            });
        }

        /*
            Validacion importante:

            El procedimiento solo devuelve detalle cuando:
            - incluir_detalle = true
            - cod_asiento no viene null

            Por eso no permitimos pedir detalle sin indicar un asiento especifico.
        */
        if (incluirDetalleParam && !codAsientoTexto) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Para incluir detalle debe enviar tambien el parametro cod_asiento'
            });
        }

        /*
            Convertimos los parametros.

            Si un filtro no viene informado, se envia null.
            El procedimiento almacenado interpreta null como no aplicar filtro.
        */
        const codAsientoParam = codAsientoTexto ? Number(codAsientoTexto) : null;
        const codPeriodoParam = codPeriodoTexto ? Number(codPeriodoTexto) : null;
        const codUserParam = codUserTexto ? Number(codUserTexto) : null;

        /*
            Ejecutamos el procedimiento almacenado.
            Orden de parametros:
            1. cod_asiento
            2. cod_periodo
            3. cod_user
            4. tip_asiento
            5. ind_estado
            6. incluir_detalle
        */
        const [resultado] = await pool.query(
            'CALL ga_sel_modulo_asientos(?, ?, ?, ?, ?, ?)',
            [
                codAsientoParam,
                codPeriodoParam,
                codUserParam,
                tipAsientoParam,
                indEstadoParam,
                incluirDetalleParam
            ]
        );

        /*
            MySQL devuelve los resultados del CALL en arreglos.

            resultado[0] contiene la cabecera de los asientos.
            resultado[1] puede contener el detalle si incluir_detalle es true.
        */
        const cabecera = resultado[0] || [];
        const detalle = incluirDetalleParam ? (resultado[1] || []) : undefined;

        // Construimos la respuesta base.
        const respuesta = {
            estado: 'ok',
            total: cabecera.length,
            incluye_detalle: incluirDetalleParam,
            datos: cabecera
        };

        // Agregamos el detalle solo cuando fue solicitado.
        if (incluirDetalleParam) {
            respuesta.detalle = detalle;
        }

        // Respondemos con los datos obtenidos.
        return res.status(200).json(respuesta);

    } catch (error) {
        // Mostramos el error tecnico en consola para depuracion.
        console.error('Error al obtener asientos contables:', {
            codigo: error.code,
            estadoSql: error.sqlState,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Capturamos errores controlados enviados desde MySQL.
        if (error.sqlState === '45000') {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.sqlMessage || 'Error de validacion en la base de datos'
            });
        }

        // Respondemos con un mensaje controlado para el cliente.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al consultar los asientos contables'
        });
    }
};


/*
    Controlador: crearAsiento
    - registra un asiento contable.
    - registra la cabecera del asiento.
    - registra el detalle de movimientos contables.
    - valida que el total debe sea igual al total haber.
    - valida que el detalle venga correctamente estructurado.

    Metodo HTTP:
    - POST

    Ruta esperada:
    - POST /api/asientos
*/
const crearAsiento = async (req, res) => {
    let connection;

    try {
        // Capturamos los datos enviados en el body de la peticion.
        const bodyData = req.body || {};

        let numAsiento;
        const codPeriodo = bodyData.cod_periodo;
        const codUser = bodyData.cod_user;
        const fecAsiento = limpiarTexto(bodyData.fec_asiento);
        const desAsiento = limpiarTexto(bodyData.des_asiento);
        const tipAsiento = normalizarTexto(bodyData.tip_asiento);
        const totDebe = bodyData.tot_debe;
        const totHaber = bodyData.tot_haber;
        const indEstado = normalizarTexto(bodyData.ind_estado) || 'borrador';
        const usrAdicion = limpiarTexto(bodyData.usr_adicion) || 'sistema';
        const detalleJson = bodyData.detalle_json;

        // Validamos campos obligatorios de cabecera.
        if (
            codPeriodo === undefined ||
            codUser === undefined ||
            !fecAsiento ||
            !desAsiento ||
            !tipAsiento ||
            totDebe === undefined ||
            totHaber === undefined ||
            detalleJson === undefined
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Faltan campos obligatorios para registrar el asiento contable'
            });
        }

        // Validamos que cod_periodo sea entero positivo.
        if (!esEnteroPositivo(codPeriodo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_periodo debe ser numerico y positivo'
            });
        }

        // Validamos que cod_user sea entero positivo.
        if (!esEnteroPositivo(codUser)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_user debe ser numerico y positivo'
            });
        }

        // Validamos la fecha del asiento.
        if (!esFechaValida(fecAsiento)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo fec_asiento debe contener una fecha valida'
            });
        }

        // Validamos el tipo de asiento.
        if (!TIPOS_ASIENTO.includes(tipAsiento)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_asiento solo permite: manual, ajuste, apertura, cierre o reversion'
            });
        }

        // Validamos el estado del asiento.
        if (!ESTADOS_ASIENTO.includes(indEstado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite: borrador, aprobado o anulado'
            });
        }

        // Validamos los totales de cabecera.
        if (!esNumeroNoNegativo(totDebe)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_debe debe ser numerico y mayor o igual que cero'
            });
        }

        if (!esNumeroNoNegativo(totHaber)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_haber debe ser numerico y mayor o igual que cero'
            });
        }

        /*
            Validacion contable principal.
            En un asiento contable correcto:
            total debe = total haber
        */
        if (Number(totDebe) !== Number(totHaber)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El total debe debe ser igual al total haber'
            });
        }

        // Validamos que el detalle sea un arreglo con al menos dos lineas.
        if (!Array.isArray(detalleJson) || detalleJson.length < 2) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El detalle_json debe ser un arreglo con al menos dos movimientos contables'
            });
        }

        let sumaDebeDetalle = 0;
        let sumaHaberDetalle = 0;

        // Validamos cada linea del detalle.
        for (let i = 0; i < detalleJson.length; i++) {
            const linea = detalleJson[i];

            const codCuenta = linea.cod_cuenta;
            const numLinea = linea.num_linea;
            const desLinea = limpiarTexto(linea.des_linea);
            const monDebe = linea.mon_debe;
            const monHaber = linea.mon_haber;

            if (!esEnteroPositivo(codCuenta)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La linea ${i + 1} debe tener un cod_cuenta numerico y positivo`
                });
            }

            if (!esEnteroPositivo(numLinea)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La linea ${i + 1} debe tener un num_linea numerico y positivo`
                });
            }

            if (!desLinea) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La linea ${i + 1} debe tener una descripcion`
                });
            }

            if (!esNumeroNoNegativo(monDebe)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La linea ${i + 1} debe tener mon_debe numerico y mayor o igual que cero`
                });
            }

            if (!esNumeroNoNegativo(monHaber)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La linea ${i + 1} debe tener mon_haber numerico y mayor o igual que cero`
                });
            }

            /*
                Validamos que una linea no cargue debe y haber al mismo tiempo.

                Una linea contable normalmente afecta un lado:
                - debe
                - haber
            */
            if (Number(monDebe) > 0 && Number(monHaber) > 0) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La linea ${i + 1} no puede tener debe y haber al mismo tiempo`
                });
            }

            if (Number(monDebe) === 0 && Number(monHaber) === 0) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La linea ${i + 1} debe tener un valor en debe o en haber`
                });
            }

            sumaDebeDetalle += Number(monDebe);
            sumaHaberDetalle += Number(monHaber);
        }

        /*
            Validamos que el detalle cuadre internamente.
            La suma del debe del detalle debe ser igual a la suma del haber del detalle.
        */
        if (sumaDebeDetalle !== sumaHaberDetalle) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El detalle del asiento esta descuadrado. La suma del debe debe ser igual a la suma del haber'
            });
        }

        /*
            Validamos que los totales de cabecera coincidan con el detalle.
            Esto evita que la cabecera diga un total y el detalle tenga otro.
        */
        if (Number(totDebe) !== sumaDebeDetalle || Number(totHaber) !== sumaHaberDetalle) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Los totales de cabecera no coinciden con las sumas del detalle'
            });
        }

        /*
            Obtenemos una conexion individual.
            Esto es necesario porque el procedimiento usa un parametro OUT.
            El SELECT de @p_cod_asiento_generado debe ejecutarse en la misma conexion.
        */
        connection = await pool.getConnection();

        // Validamos que el periodo contable exista.
        const [periodoExiste] = await connection.query(
            'SELECT cod_periodo FROM ga_periodo_contable WHERE cod_periodo = ? LIMIT 1',
            [Number(codPeriodo)]
        );

        if (periodoExiste.length === 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El periodo contable indicado no existe'
            });
        }

        // El número visible se genera en el backend y no se confía en el valor del formulario.
        const anioAsiento = new Date(`${fecAsiento}T00:00:00Z`).getUTCFullYear();
        const siguienteNumero = await obtenerSiguienteNumeroAsiento(connection, anioAsiento);
        numAsiento = siguienteNumero.numAsiento;

        // Validamos que no exista otro asiento con el mismo numero.
        const [asientoDuplicado] = await connection.query(
            'SELECT cod_asiento FROM ga_asiento_contable WHERE num_asiento = ? LIMIT 1',
            [numAsiento]
        );

        if (asientoDuplicado.length > 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe un asiento contable con ese numero de asiento'
            });
        }

        /*
            Validamos que todas las cuentas del detalle existan.
            Aunque el procedimiento tambien valida, hacerlo aqui permite
            devolver un mensaje mas claro antes de ejecutar la transaccion.
        */
        for (let i = 0; i < detalleJson.length; i++) {
            const [cuentaExiste] = await connection.query(
                'SELECT cod_cuenta FROM cc_catalogo_cuenta WHERE cod_cuenta = ? LIMIT 1',
                [Number(detalleJson[i].cod_cuenta)]
            );

            if (cuentaExiste.length === 0) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: `La cuenta contable de la linea ${i + 1} no existe`
                });
            }
        }

        /*
            Ejecutamos el procedimiento almacenado real.
            Orden de parametros:
            1. num_asiento
            2. cod_periodo
            3. cod_user
            4. fec_asiento
            5. des_asiento
            6. tip_asiento
            7. tot_debe
            8. tot_haber
            9. ind_estado
            10. usr_adicion
            11. detalle_json
            12. OUT cod_asiento_generado
        */
        await connection.query(
            'CALL ga_ins_modulo_asientos(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @p_cod_asiento_generado)',
            [
                numAsiento,
                Number(codPeriodo),
                Number(codUser),
                fecAsiento,
                desAsiento,
                tipAsiento,
                Number(totDebe),
                Number(totHaber),
                indEstado,
                usrAdicion,
                JSON.stringify(detalleJson)
            ]
        );

        // Recuperamos el parametro OUT generado por el procedimiento.
        const [resultadoSalida] = await connection.query(
            'SELECT @p_cod_asiento_generado AS cod_asiento'
        );

        const codAsientoGenerado = resultadoSalida[0]?.cod_asiento;

        // Validamos que el procedimiento haya devuelto un codigo generado.
        if (!codAsientoGenerado) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se pudo registrar el asiento contable. Revise las restricciones de la base de datos.'
            });
        }

        // Respondemos con el codigo generado.
        return res.status(201).json({
            estado: 'ok',
            mensaje: 'Asiento contable registrado correctamente',
            cod_asiento: codAsientoGenerado
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al crear asiento contable:', {
            codigo: error.code,
            estadoSql: error.sqlState,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Capturamos errores controlados enviados desde MySQL.
        if (error.sqlState === '45000') {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.sqlMessage || 'Error de validacion en la base de datos'
            });
        }

        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe un registro duplicado en la base de datos'
            });
        }

        // Respuesta general para errores no controlados.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al registrar el asiento contable'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};


/*
    Controlador: actualizarAsiento

    Funcion:
    - actualiza la cabecera de un asiento contable.
    - permite modificar datos generales del asiento.
    - permite aplicar soft delete cambiando ind_estado a "anulado".
    - no modifica el detalle del asiento contable.

    Metodo HTTP:
    - PUT

    Ruta esperada:
    - PUT /api/asientos/:cod_asiento
*/
const actualizarAsiento = async (req, res) => {
    let connection;

    try {
        // Extraemos el codigo del asiento desde la URL.
        const { cod_asiento } = req.params;

        // Capturamos los datos enviados en el body de la peticion.
        const bodyData = req.body || {};

        // Validamos que cod_asiento sea numerico y positivo.
        if (!esEnteroPositivo(cod_asiento)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_asiento debe ser numerico y positivo'
            });
        }

        /*
            Validamos que se envie al menos un campo para actualizar.

            Para soft delete se puede enviar solamente:
            {
                "ind_estado": "anulado"
            }
        */
        if (Object.keys(bodyData).length === 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Debe enviar al menos un campo para actualizar'
            });
        }

        // Convertimos el codigo del asiento a numero.
        const codAsiento = Number(cod_asiento);

        // Obtenemos una conexion del pool.
        connection = await pool.getConnection();

        /*
            Consultamos el asiento actual.

            Esto nos permite:
            - validar que existe.
            - conservar valores actuales si el usuario no envia todos los campos.
            - permitir actualizaciones parciales.
        */
        const [asientoActual] = await connection.query(
            `
            SELECT 
                cod_asiento,
                num_asiento,
                cod_periodo,
                cod_user,
                fec_asiento,
                des_asiento,
                tip_asiento,
                tot_debe,
                tot_haber,
                ind_estado
            FROM ga_asiento_contable
            WHERE cod_asiento = ?
            LIMIT 1
            `,
            [codAsiento]
        );

        // Si no existe, respondemos 404.
        if (asientoActual.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'El asiento contable indicado no existe'
            });
        }

        const asiento = asientoActual[0];

        /*
            Validamos que no se modifique un asiento anulado.

            En este modulo, "anulado" funciona como soft delete.
        */
        if (asiento.ind_estado === 'anulado') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se puede actualizar un asiento que ya esta anulado'
            });
        }

        /*
            Construimos los valores finales.

            Si un campo viene en el body, usamos el valor nuevo.
            Si no viene, conservamos el valor actual del asiento.
        */
        const numAsiento = limpiarTexto(bodyData.num_asiento) || asiento.num_asiento;

        const codPeriodo = bodyData.cod_periodo !== undefined
            ? bodyData.cod_periodo
            : asiento.cod_periodo;

        const codUser = bodyData.cod_user !== undefined
            ? bodyData.cod_user
            : asiento.cod_user;

        const fecAsiento = limpiarTexto(bodyData.fec_asiento) || asiento.fec_asiento;

        const desAsiento = limpiarTexto(bodyData.des_asiento) || asiento.des_asiento;

        const tipAsiento = bodyData.tip_asiento !== undefined
            ? normalizarTexto(bodyData.tip_asiento)
            : asiento.tip_asiento;

        const totDebe = bodyData.tot_debe !== undefined
            ? bodyData.tot_debe
            : asiento.tot_debe;

        const totHaber = bodyData.tot_haber !== undefined
            ? bodyData.tot_haber
            : asiento.tot_haber;

        const indEstado = bodyData.ind_estado !== undefined
            ? normalizarTexto(bodyData.ind_estado)
            : asiento.ind_estado;

        // Validamos que num_asiento no quede vacio.
        if (!numAsiento) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo num_asiento no puede quedar vacio'
            });
        }

        // Validamos que cod_periodo sea entero positivo.
        if (!esEnteroPositivo(codPeriodo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_periodo debe ser numerico y positivo'
            });
        }

        // Validamos que cod_user sea entero positivo.
        if (!esEnteroPositivo(codUser)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_user debe ser numerico y positivo'
            });
        }

        // Validamos la fecha del asiento.
        if (!esFechaValida(fecAsiento)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo fec_asiento debe contener una fecha valida'
            });
        }

        // Validamos que la descripcion no quede vacia.
        if (!desAsiento) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo des_asiento no puede quedar vacio'
            });
        }

        // Validamos el tipo de asiento.
        if (!TIPOS_ASIENTO.includes(tipAsiento)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_asiento solo permite: manual, ajuste, apertura, cierre o reversion'
            });
        }

        // Validamos el estado del asiento.
        if (!ESTADOS_ASIENTO.includes(indEstado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite: borrador, aprobado o anulado'
            });
        }

        // Validamos total debe.
        if (!esNumeroNoNegativo(totDebe)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_debe debe ser numerico y mayor o igual que cero'
            });
        }

        // Validamos total haber.
        if (!esNumeroNoNegativo(totHaber)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_haber debe ser numerico y mayor o igual que cero'
            });
        }

        /*
            Validacion contable principal.

            Para que un asiento sea valido:
            total debe = total haber
        */
        if (Number(totDebe) !== Number(totHaber)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El total debe debe ser igual al total haber'
            });
        }

        // Validamos que el periodo contable exista.
        const [periodoExiste] = await connection.query(
            'SELECT cod_periodo FROM ga_periodo_contable WHERE cod_periodo = ? LIMIT 1',
            [Number(codPeriodo)]
        );

        if (periodoExiste.length === 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El periodo contable indicado no existe'
            });
        }

        /*
            Validamos que no exista otro asiento con el mismo numero.

            Permitimos que el mismo asiento conserve su propio num_asiento.
        */
        const [asientoDuplicado] = await connection.query(
            `
            SELECT cod_asiento
            FROM ga_asiento_contable
            WHERE num_asiento = ?
              AND cod_asiento <> ?
            LIMIT 1
            `,
            [numAsiento, codAsiento]
        );

        if (asientoDuplicado.length > 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe otro asiento contable con ese numero de asiento'
            });
        }

        /*
            Ejecutamos el procedimiento almacenado real.

            Orden de parametros:
            1. cod_asiento
            2. num_asiento
            3. cod_periodo
            4. cod_user
            5. fec_asiento
            6. des_asiento
            7. tip_asiento
            8. tot_debe
            9. tot_haber
            10. ind_estado
        */
        await connection.query(
            'CALL ga_upd_modulo_asientos(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                codAsiento,
                numAsiento,
                Number(codPeriodo),
                Number(codUser),
                fecAsiento,
                desAsiento,
                tipAsiento,
                Number(totDebe),
                Number(totHaber),
                indEstado
            ]
        );

        // Consultamos el asiento actualizado para devolver datos confirmados.
        const [asientoActualizado] = await connection.query(
            `
            SELECT 
                cod_asiento,
                num_asiento,
                cod_periodo,
                cod_user,
                fec_asiento,
                des_asiento,
                tip_asiento,
                tot_debe,
                tot_haber,
                ind_estado
            FROM ga_asiento_contable
            WHERE cod_asiento = ?
            LIMIT 1
            `,
            [codAsiento]
        );

        // Respondemos confirmando la actualizacion o anulacion logica.
        return res.status(200).json({
            estado: 'ok',
            mensaje: indEstado === 'anulado'
                ? 'Asiento contable anulado correctamente como soft delete logico'
                : 'Asiento contable actualizado correctamente',
            estado_anterior: asiento.ind_estado,
            datos: asientoActualizado[0]
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al actualizar asiento contable:', {
            codigo: error.code,
            estadoSql: error.sqlState,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Capturamos errores controlados enviados desde MySQL.
        if (error.sqlState === '45000') {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.sqlMessage || 'Error de validacion en la base de datos'
            });
        }

        // Respuesta general para errores no controlados.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al actualizar el asiento contable'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};



// Exportamos los metodos del controlador de asientos.
module.exports = {
    obtenerAsientos,
    obtenerSiguienteNumeroAsiento: obtenerSiguienteNumeroAsientoController,
    crearAsiento,
    actualizarAsiento
};