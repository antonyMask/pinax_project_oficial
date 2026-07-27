const express = require('express');

// Importamos el metodo GET del controlador de asientos.
const {
    obtenerAsientos,
      obtenerSiguienteNumeroAsiento,
    crearAsiento,
    actualizarAsiento
} = require('../controllers/asientos.controller');

const router = express.Router();

/* Ruta: GET /api/asientos
   Funcion:
   - consulta asientos contables.
   - permite filtrar por cod_asiento, cod_periodo, cod_user, tip_asiento e ind_estado.
   - permite incluir detalle cuando se envia cod_asiento.
*/
router.get('/', obtenerAsientos);

/* Ruta: GET /api/asientos/siguiente?anio=2026
   Funcion:
   - devuelve la previsualización del siguiente número anual.
*/
router.get('/siguiente', obtenerSiguienteNumeroAsiento);

/* Ruta: POST /api/asientos
   Funcion:
   - registra un asiento contable con su detalle.
*/
router.post('/', crearAsiento);

/* Ruta: PUT /api/asientos
   Funcion:
   - actualiza la cabecera de un asiento contable.
   - permite soft delete cambiando ind_estado a anulado.
*/
router.put('/:cod_asiento', actualizarAsiento);

// Exportamos las rutas del modulo de asientos.
module.exports = router;