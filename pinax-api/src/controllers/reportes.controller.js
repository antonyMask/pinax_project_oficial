// Importamos el pool de conexiones configurado para MySQL.
const { pool } = require('../config/db');

/*
    Constantes de validacion
    Funcion:
    - centralizan los valores permitidos para el modulo de reportes financieros.
    - se usan en GET, POST y PUT.
*/
const TIPOS_REPORTE = ['balance_general', 'estado_resultados'];
const ESTADOS_REPORTE = ['generado', 'confirmado', 'anulado'];

/*  Funcion auxiliar: limpiarTexto
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

/*  Funcion auxiliar: normalizarTexto
    - limpia un texto.
    - lo convierte a minusculas.
    - ayuda a validar campos como tipo de reporte y estado.
*/
const normalizarTexto = (valor) => {
    const texto = limpiarTexto(valor);
    return texto ? texto.toLowerCase() : null;
};

/*  Funcion auxiliar: esEntero
    - valida si un valor puede convertirse correctamente a numero entero.
*/
const esEntero = (valor) => {
    return Number.isInteger(Number(valor));
};

/*  Funcion auxiliar: esEnteroPositivo
    - valida si un valor es un numero entero mayor que cero.
*/
const esEnteroPositivo = (valor) => {
    return esEntero(valor) && Number(valor) > 0;
};

/*  Funcion auxiliar: convertirBooleanoConsulta
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

/*  Funcion auxiliar: esNumero
    - valida si un valor puede convertirse correctamente a numero.
    - se usa para validar montos financieros.
*/
const esNumero = (valor) => {
    return valor !== undefined &&
           valor !== null &&
           valor !== '' &&
           Number.isFinite(Number(valor));
};

/*  Funcion auxiliar: convertirBooleanoBody
    - convierte valores del body a booleano.
    - acepta true, false, 1, 0, "true" y "false".
*/
const convertirBooleanoBody = (valor) => {
    if (valor === undefined || valor === null) return true;

    if (valor === true || valor === 1 || valor === '1') return true;
    if (valor === false || valor === 0 || valor === '0') return false;

    const texto = normalizarTexto(valor);

    if (texto === 'true') return true;
    if (texto === 'false') return false;

    return null;
};

/*
    Controlador: obtenerReportesFinancieros

    Funcion:
    - consulta reportes financieros.
    - permite filtrar por cod_reporte, cod_periodo, tip_reporte, ind_estado y cod_user.
    - permite incluir detalle cuando se consulta un reporte especifico.

    Metodo HTTP:
    - GET

    Rutas esperadas:
    - GET /api/reportes
    - GET /api/reportes?cod_reporte=1
    - GET /api/reportes?cod_periodo=1
    - GET /api/reportes?tip_reporte=balance_general
    - GET /api/reportes?tip_reporte=estado_resultados
    - GET /api/reportes?ind_estado=generado
    - GET /api/reportes?cod_reporte=1&incluir_detalle=true
*/

const obtenerReportesFinancieros = async (req, res) => {
    try {
        // Extraemos los filtros enviados por query params.
        const {
            cod_reporte,
            cod_periodo,
            tip_reporte,
            ind_estado,
            cod_user,
            incluir_detalle
        } = req.query;

        // Validamos cod_reporte si fue enviado.
        if (cod_reporte && !esEnteroPositivo(cod_reporte)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_reporte debe ser numerico y positivo'
            });
        }

        // Validamos cod_periodo si fue enviado.
        if (cod_periodo && !esEnteroPositivo(cod_periodo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_periodo debe ser numerico y positivo'
            });
        }

        // Validamos cod_user si fue enviado.
        if (cod_user && !esEnteroPositivo(cod_user)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_user debe ser numerico y positivo'
            });
        }

        // Normalizamos el tipo de reporte.
        const tipReporteParam = normalizarTexto(tip_reporte);

        // Validamos tip_reporte si fue enviado.
        if (tipReporteParam && !TIPOS_REPORTE.includes(tipReporteParam)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro tip_reporte solo permite balance_general o estado_resultados'
            });
        }

        // Normalizamos el estado del reporte.
        const indEstadoParam = normalizarTexto(ind_estado);

        // Validamos ind_estado si fue enviado.
        if (indEstadoParam && !ESTADOS_REPORTE.includes(indEstadoParam)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro ind_estado solo permite generado, confirmado o anulado'
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

        if (incluirDetalleParam && !cod_reporte) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Para incluir detalle debe enviar tambien el parametro cod_reporte'
            });
        }

        // Convertimos los parametros.
        const codReporteParam = cod_reporte ? Number(cod_reporte) : null;
        const codPeriodoParam = cod_periodo ? Number(cod_periodo) : null;
        const codUserParam = cod_user ? Number(cod_user) : null;

        /*
            Ejecutamos el procedimiento almacenado.

            Orden de parametros:
            1. cod_reporte
            2. cod_periodo
            3. tip_reporte
            4. ind_estado
            5. cod_user
            6. incluir_detalle
        */
        const [resultado] = await pool.query(
            'CALL rf_sel_modulo_reportes(?, ?, ?, ?, ?, ?)',
            [
                codReporteParam,
                codPeriodoParam,
                tipReporteParam,
                indEstadoParam,
                codUserParam,
                incluirDetalleParam
            ]
        );

        /*
            MySQL devuelve los resultados del CALL en arreglos.

            resultado[0] contiene la cabecera de los reportes.
            resultado[1] puede contener el detalle si incluir_detalle es true.
        */
        const cabecera = resultado[0] || [];
        const detalle = incluirDetalleParam ? (resultado[1] || []) : undefined;

        /*
            ✅ VALIDACION DE BALANCE GENERAL (CORREGIDA)
            - Sin usar estado_validacion
            - Verifica si Activo = Pasivo + Patrimonio
            - Soporta tanto 'tip_reporte' como 'tp_reporte'
        */
        let balanceValido = true;
        let mensajeValidacion = 'Consulta realizada correctamente';

        // Función para verificar si un balance está cuadrado
        const verificarBalanceCuadrado = (reporte) => {
            // Usar tip_reporte o tp_reporte (cualquiera que venga del SP)
            const tipoReporte = reporte.tip_reporte || reporte.tp_reporte;
            
            if (tipoReporte !== 'balance_general') return true;
            
            const activo = parseFloat(reporte.tot_activo) || 0;
            const pasivo = parseFloat(reporte.tot_pasivo) || 0;
            const patrimonio = parseFloat(reporte.tot_patrimonio) || 0;
            
            // Si la diferencia es mayor a 0.01, consideramos descuadrado
            return Math.abs(activo - (pasivo + patrimonio)) <= 0.01;
        };

        /*
        * Solo se consideran para la alerta los balances vigentes.
        * Los anulados permanecen visibles por trazabilidad,
        * pero ya no participan en la validación contable global.
        */
        const balancesGeneralesVigentes = cabecera.filter((reporte) => {
            const tipoReporte =
                reporte.tip_reporte || reporte.tp_reporte;

            const estadoReporte =
                String(reporte.ind_estado || '').toLowerCase();

            return tipoReporte === 'balance_general'
                && estadoReporte !== 'anulado';
        });

/*
 * Identifica únicamente los balances vigentes
 * que incumplen la ecuación contable.
 */
const balancesDescuadrados = balancesGeneralesVigentes.filter(
    (reporte) => !verificarBalanceCuadrado(reporte)
);

if (balancesDescuadrados.length > 0) {
    balanceValido = false;
    mensajeValidacion =
        'Uno o más balances generales vigentes presentan inconsistencias contables';
} else if (balancesGeneralesVigentes.length > 0) {
    mensajeValidacion =
        'Todos los balances generales vigentes están cuadrados';
} else {
    /*
     * No debe mostrarse una validación positiva si no existen
     * balances vigentes dentro de la consulta realizada.
     */
    mensajeValidacion = null;
}

        if (balancesDescuadrados.length > 0) {
            balanceValido = false;
            mensajeValidacion = 'Uno o mas balances generales presentan inconsistencias contables';
        } else {
            // Verificar si hay algún balance general
            const hayBalanceGeneral = cabecera.some((reporte) => {
                const tipoReporte = reporte.tip_reporte || reporte.tp_reporte;
                return tipoReporte === 'balance_general';
            });
            
            if (hayBalanceGeneral) {
                mensajeValidacion = 'Todos los balances generales consultados estan cuadrados';
            }
        }

        // Construimos la respuesta base.
        const respuesta = {
            estado: 'ok',
            total_reportes: cabecera.length,
            incluye_detalle: incluirDetalleParam,
            balance_valido: balanceValido,
            mensaje_validacion: mensajeValidacion,
            cabecera: cabecera
        };

        // Agregamos el detalle solo cuando fue solicitado.
        if (incluirDetalleParam) {
            respuesta.detalle = detalle;
        }

        return res.status(200).json(respuesta);

    } catch (error) {
        console.error('Error al obtener reportes financieros:', {
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
            mensaje: 'Error interno al consultar reportes financieros'
        });
    }
};

/*
    Controlador: crearReporteFinanciero

    Funcion:
    - genera un nuevo reporte financiero.
    - permite generar Balance General.
    - permite generar Estado de Resultados.
    - puede calcular automaticamente los totales desde la base de datos.
    - tambien permite enviar totales manuales si calcular_automaticamente es false.

    Metodo HTTP:
    - POST

    Ruta esperada:
    - POST /api/reportes
*/
const crearReporteFinanciero = async (req, res) => {
    let connection;

    try {
        // Capturamos los datos enviados en el body de la peticion.
        const bodyData = req.body || {};

        const codPeriodo = bodyData.cod_periodo;
        const codUser = bodyData.cod_user;
        const tipReporte = normalizarTexto(bodyData.tip_reporte);

        /*
            calcular_automaticamente define si el procedimiento debe calcular
            los totales desde los saldos contables o si se enviaran manualmente.
        */
        const calcularAutomaticamente = convertirBooleanoBody(bodyData.calcular_automaticamente);

        const totActivo = bodyData.tot_activo;
        const totPasivo = bodyData.tot_pasivo;
        const totPatrimonio = bodyData.tot_patrimonio;
        const monUtilidadPerdida = bodyData.mon_utilidad_perdida;

        // Validamos campos obligatorios.
        if (
            codPeriodo === undefined ||
            codUser === undefined ||
            !tipReporte
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Los campos cod_periodo, cod_user y tip_reporte son obligatorios'
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

        // Validamos el tipo de reporte.
        if (!TIPOS_REPORTE.includes(tipReporte)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_reporte solo permite balance_general o estado_resultados'
            });
        }

        // Validamos calcular_automaticamente.
        if (calcularAutomaticamente === null) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo calcular_automaticamente debe ser booleano'
            });
        }

        /*
            Si calcular_automaticamente es false, permitimos que los totales
            sean enviados manualmente, pero deben ser numericos si vienen.
        */
        if (calcularAutomaticamente === false) {
            if (totActivo !== undefined && !esNumero(totActivo)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo tot_activo debe ser numerico'
                });
            }

            if (totPasivo !== undefined && !esNumero(totPasivo)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo tot_pasivo debe ser numerico'
                });
            }

            if (totPatrimonio !== undefined && !esNumero(totPatrimonio)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo tot_patrimonio debe ser numerico'
                });
            }

            if (monUtilidadPerdida !== undefined && !esNumero(monUtilidadPerdida)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo mon_utilidad_perdida debe ser numerico'
                });
            }
        }

        /*
            Convertimos los valores finales que se enviaran al procedimiento.

            Si calcular_automaticamente es true, los totales se mandan como null
            para que el procedimiento los calcule internamente.
        */
        const codPeriodoParam = Number(codPeriodo);
        const codUserParam = Number(codUser);

        const totActivoParam = calcularAutomaticamente ? null : (
            totActivo !== undefined ? Number(totActivo) : null
        );

        const totPasivoParam = calcularAutomaticamente ? null : (
            totPasivo !== undefined ? Number(totPasivo) : null
        );

        const totPatrimonioParam = calcularAutomaticamente ? null : (
            totPatrimonio !== undefined ? Number(totPatrimonio) : null
        );

        const monUtilidadPerdidaParam = calcularAutomaticamente ? null : (
            monUtilidadPerdida !== undefined ? Number(monUtilidadPerdida) : null
        );

        /*
            Obtenemos una conexion individual.

            Esto es necesario porque el procedimiento usa parametros OUT.
            Las variables OUT deben leerse en la misma conexion donde se ejecuto el CALL.
        */
        connection = await pool.getConnection();

        /*
            Validamos que el periodo contable exista.

            Esto nos permite dar un mensaje mas claro antes de ejecutar el procedimiento.
        */
        const [periodoExiste] = await connection.query(
            'SELECT cod_periodo FROM ga_periodo_contable WHERE cod_periodo = ? LIMIT 1',
            [codPeriodoParam]
        );

        if (periodoExiste.length === 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El periodo contable indicado no existe'
            });
        }

        /*
            Ejecutamos el procedimiento almacenado real.

            Orden de parametros:
            1. cod_periodo
            2. cod_user
            3. tip_reporte
            4. calcular_automaticamente
            5. tot_activo
            6. tot_pasivo
            7. tot_patrimonio
            8. mon_utilidad_perdida
            9. OUT cod_reporte_generado
            10. OUT mensaje
        */
        await connection.query(
            'CALL rf_ins_modulo_reportes(?, ?, ?, ?, ?, ?, ?, ?, @p_cod_reporte_generado, @p_mensaje)',
            [
                codPeriodoParam,
                codUserParam,
                tipReporte,
                calcularAutomaticamente,
                totActivoParam,
                totPasivoParam,
                totPatrimonioParam,
                monUtilidadPerdidaParam
            ]
        );

        // Recuperamos los parametros OUT del procedimiento.
        const [resultadoOut] = await connection.query(
            'SELECT @p_cod_reporte_generado AS cod_reporte, @p_mensaje AS mensaje'
        );

        const codReporteGenerado = resultadoOut[0]?.cod_reporte;
        const mensajeSp = resultadoOut[0]?.mensaje;

        // Validamos que el procedimiento haya generado un codigo.
        if (!codReporteGenerado) {
            return res.status(400).json({
                estado: 'error',
                mensaje: mensajeSp || 'No se pudo generar el reporte financiero'
            });
        }

        // Respondemos con el reporte generado.
        return res.status(201).json({
            estado: 'ok',
            mensaje: mensajeSp || 'Reporte financiero generado correctamente',
            cod_reporte: codReporteGenerado,
            tip_reporte: tipReporte,
            cod_periodo: codPeriodoParam
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al crear reporte financiero:', {
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
            mensaje: 'Error interno al generar reporte financiero'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};

/*
    Controlador: actualizarReporteFinanciero

    Funcion:
    - actualiza un reporte financiero existente.
    - permite modificar el estado del reporte.
    - permite actualizar totales financieros manualmente.
    - permite aplicar soft delete cambiando ind_estado a "anulado".

    Metodo HTTP:
    - PUT

    Ruta esperada:
    - PUT /api/reportes/:cod_reporte
*/
const actualizarReporteFinanciero = async (req, res) => {
    let connection;

    try {
        // Extraemos el codigo del reporte desde la URL.
        const { cod_reporte } = req.params;

        // Capturamos los datos enviados en el body de la peticion.
        const bodyData = req.body || {};

        const indEstado = normalizarTexto(bodyData.ind_estado);
        const totActivo = bodyData.tot_activo;
        const totPasivo = bodyData.tot_pasivo;
        const totPatrimonio = bodyData.tot_patrimonio;
        const monUtilidadPerdida = bodyData.mon_utilidad_perdida;

        // Validamos que cod_reporte sea numerico y positivo.
        if (!esEnteroPositivo(cod_reporte)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_reporte debe ser numerico y positivo'
            });
        }

        /*
            Validamos que se envie al menos un campo para actualizar.

            Para soft delete se envia:
            {
                "ind_estado": "anulado"
            }
        */
        if (
            !indEstado &&
            totActivo === undefined &&
            totPasivo === undefined &&
            totPatrimonio === undefined &&
            monUtilidadPerdida === undefined
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Debe enviar al menos un campo para actualizar'
            });
        }

        // Validamos ind_estado si fue enviado.
        if (indEstado && !ESTADOS_REPORTE.includes(indEstado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite generado, confirmado o anulado'
            });
        }

        // Validamos tot_activo si fue enviado.
        if (totActivo !== undefined && !esNumero(totActivo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_activo debe ser numerico'
            });
        }

        // Validamos tot_pasivo si fue enviado.
        if (totPasivo !== undefined && !esNumero(totPasivo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_pasivo debe ser numerico'
            });
        }

        // Validamos tot_patrimonio si fue enviado.
        if (totPatrimonio !== undefined && !esNumero(totPatrimonio)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_patrimonio debe ser numerico'
            });
        }

        // Validamos mon_utilidad_perdida si fue enviado.
        if (monUtilidadPerdida !== undefined && !esNumero(monUtilidadPerdida)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo mon_utilidad_perdida debe ser numerico'
            });
        }

        // Convertimos el codigo del reporte.
        const codReporte = Number(cod_reporte);

        /*
            Convertimos los montos.

            Si un monto no viene en el body, se envia null.
            El procedimiento decide como manejar los campos nulos.
        */
        const totActivoParam = totActivo !== undefined ? Number(totActivo) : null;
        const totPasivoParam = totPasivo !== undefined ? Number(totPasivo) : null;
        const totPatrimonioParam = totPatrimonio !== undefined ? Number(totPatrimonio) : null;
        const monUtilidadPerdidaParam = monUtilidadPerdida !== undefined
            ? Number(monUtilidadPerdida)
            : null;

        /*
            Obtenemos una conexion individual.

            Esto es necesario porque el procedimiento usa un parametro OUT.
            El SELECT de @p_mensaje debe ejecutarse en la misma conexion.
        */
        connection = await pool.getConnection();

        // Validamos que el reporte exista antes de actualizar.
        const [reporteActual] = await connection.query(
            `
            SELECT 
                cod_reporte,
                tip_reporte,
                ind_estado
            FROM rf_reporte_financiero
            WHERE cod_reporte = ?
            LIMIT 1
            `,
            [codReporte]
        );

        if (reporteActual.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'No existe un reporte financiero con ese codigo'
            });
        }

        /*
            Validamos que no se modifique un reporte ya anulado.
            En este modulo, "anulado" funciona como soft delete.
        */
        if (reporteActual[0].ind_estado === 'anulado') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se puede actualizar un reporte que ya esta anulado'
            });
        }

        /*
            Ejecutamos el procedimiento almacenado.

            Orden de parametros:
            1. cod_reporte
            2. ind_estado
            3. tot_activo
            4. tot_pasivo
            5. tot_patrimonio
            6. mon_utilidad_perdida
            7. OUT mensaje
        */
        await connection.query(
            'CALL rf_upd_modulo_reportes(?, ?, ?, ?, ?, ?, @p_mensaje)',
            [
                codReporte,
                indEstado,
                totActivoParam,
                totPasivoParam,
                totPatrimonioParam,
                monUtilidadPerdidaParam
            ]
        );

        // Recuperamos el mensaje OUT del procedimiento.
        const [resultadoMensaje] = await connection.query(
            'SELECT @p_mensaje AS mensaje'
        );

        const mensajeSp = resultadoMensaje[0]?.mensaje;

        // Consultamos el reporte actualizado para devolver datos confirmados.
        const [reporteActualizado] = await connection.query(
            `
            SELECT 
                cod_reporte,
                cod_periodo,
                cod_user,
                tip_reporte,
                tot_activo,
                tot_pasivo,
                tot_patrimonio,
                mon_utilidad_perdida,
                ind_estado,
                fec_generacion
            FROM rf_reporte_financiero
            WHERE cod_reporte = ?
            LIMIT 1
            `,
            [codReporte]
        );

        // Respondemos confirmando la actualizacion o anulacion logica.
        return res.status(200).json({
            estado: 'ok',
            mensaje: indEstado === 'anulado'
                ? 'Reporte financiero anulado correctamente como soft delete logico'
                : mensajeSp || 'Reporte financiero actualizado correctamente',
            estado_anterior: reporteActual[0].ind_estado,
            datos: reporteActualizado[0]
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al actualizar reporte financiero:', {
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
            mensaje: 'Error interno al actualizar reporte financiero'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};

/*
    Controlador: obtenerDetalleReporte

    Funcion:
    - obtiene solamente el detalle de un reporte financiero especifico.
    - consulta la cabecera para validar que el reporte exista.
    - devuelve las lineas del detalle financiero.
    - usa el procedimiento rf_sel_modulo_reportes con incluir_detalle = true.

    Metodo HTTP:
    - GET

    Ruta esperada:
    - GET /api/reportes/:cod_reporte/detalle
*/
const obtenerDetalleReporte = async (req, res) => {
    try {
        // Extraemos el codigo del reporte desde la URL.
        const { cod_reporte } = req.params;

        // Validamos que cod_reporte sea numerico y positivo.
        if (!esEnteroPositivo(cod_reporte)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_reporte debe ser numerico y positivo'
            });
        }

        // Convertimos el codigo del reporte a numero.
        const codReporte = Number(cod_reporte);

        /*
            Ejecutamos el procedimiento almacenado.

            Orden de parametros:
            1. cod_reporte
            2. cod_periodo
            3. tip_reporte
            4. ind_estado
            5. cod_user
            6. incluir_detalle

            En este caso:
            - enviamos cod_reporte para buscar un reporte especifico.
            - enviamos incluir_detalle = true para obtener las lineas del detalle.
        */
        const [resultado] = await pool.query(
            'CALL rf_sel_modulo_reportes(?, ?, ?, ?, ?, ?)',
            [
                codReporte,
                null,
                null,
                null,
                null,
                true
            ]
        );

        /*
            MySQL devuelve varios resultsets.

            resultado[0] = cabecera del reporte.
            resultado[1] = detalle del reporte.
        */
        const cabecera = resultado[0] || [];
        const detalle = resultado[1] || [];

        // Validamos que el reporte exista.
        if (cabecera.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'No existe un reporte financiero con ese codigo'
            });
        }

        /*
            Validamos que exista detalle.

            Puede ocurrir que la cabecera exista, pero el detalle no tenga lineas.
            En ese caso se devuelve 404 para indicar que no hay detalle disponible.
        */
        if (detalle.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'No se encontro detalle para el reporte especificado'
            });
        }

        // Respondemos solamente con el detalle y una cabecera resumida.
        return res.status(200).json({
            estado: 'ok',
            cod_reporte: codReporte,
            tip_reporte: cabecera[0].tip_reporte || cabecera[0].tp_reporte,
            ind_estado: cabecera[0].ind_estado,
            total_lineas: detalle.length,
            detalle: detalle
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al obtener detalle del reporte:', {
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
            mensaje: 'Error interno al obtener detalle del reporte'
        });
    }
};

// Exportamos los metodos del controlador de reportes financieros.
module.exports = {
    obtenerReportesFinancieros,
    crearReporteFinanciero,
    actualizarReporteFinanciero,
    obtenerDetalleReporte
};