const express = require('express');

const {
    rateLimit
} = require('express-rate-limit');

const {
    login,
    me
} = require('../controllers/auth.controller');

// Importamos el registro público de nuevas cuentas.
const {
    registrar
} = require('../controllers/registro.controller');

const {
    verificarToken
} = require('../middlewares/auth.middleware');

const router = express.Router();


// Limita los intentos fallidos para dificultar ataques de fuerza bruta.

const limitarLogin = rateLimit({
    windowMs: 15 * 60 * 1000,
    limit: 10,
    standardHeaders: 'draft-8',
    legacyHeaders: false,
    skipSuccessfulRequests: true,
    message: {
        estado: 'error',
        mensaje: 'Demasiados intentos. Intente nuevamente en 15 minutos.'
    }
});

/*
 * Limita la creación masiva de cuentas desde una misma dirección.
 * La API seguirá validando todos los datos aunque la solicitud venga
 * directamente de otro cliente distinto a Laravel.
 */
const limitarRegistro = rateLimit({
    windowMs: 60 * 60 * 1000,
    limit: 5,
    standardHeaders: 'draft-8',
    legacyHeaders: false,
    message: {
        estado: 'error',
        mensaje: 'Se alcanzó el límite de registros. Intente nuevamente en una hora.'
    }
});

// El login es público porque todavía no existe un token.
router.post('/login', limitarLogin, login);

// El registro también es público porque todavía no existe una sesión.
router.post('/register', limitarRegistro, registrar);

// La consulta de sesión requiere un Bearer Token válido.
router.get('/me', verificarToken, me);

module.exports = router;