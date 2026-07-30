// Importamos Express para crear la API.
const express = require('express');

// Importamos CORS para permitir solicitudes desde otros orígenes,
// por ejemplo, desde el frontend Laravel.
const cors = require('cors');

// Importamos Helmet para agregar cabeceras básicas de seguridad HTTP.
const helmet = require('helmet');

// Importamos las rutas del módulo de autenticación.
const authRoutes = require('./routes/auth.routes');

// Importamos las rutas del módulo de personas.
const personasRoutes = require('./routes/personas.routes');

// Importamos las rutas del módulo de catálogo de cuentas.
const catalogoRoutes = require('./routes/catalogo.routes');

// Importamos las rutas del módulo de cuentas T y mayorización.
const mayorizacionRoutes = require('./routes/mayorizacion.routes');

// Importamos las rutas del módulo de reportes financieros.
const reportesRoutes = require('./routes/reportes.routes');

// Importamos las rutas del módulo de asientos contables.
const asientosRoutes = require('./routes/asientos.routes');

// Creamos la aplicación principal de Express.
const app = express();

// Aplicamos cabeceras básicas de seguridad.
app.use(helmet());

// Permitimos solicitudes externas.
// En producción, conviene limitar esto al dominio del frontend.
app.use(cors());

// Permitimos que Express interprete cuerpos de solicitudes JSON.
app.use(express.json());

// Registramos las rutas de autenticación.
// Todas comenzarán con: /api/auth
app.use('/api/auth', authRoutes);

// Registramos las rutas del módulo de personas.
// Todas comenzarán con: /api/personas
app.use('/api/personas', personasRoutes);

// Registramos las rutas del catálogo de cuentas.
// Todas comenzarán con: /api/catalogo
app.use('/api/catalogo', catalogoRoutes);

// Registramos las rutas de cuentas T y mayorización.
// Todas comenzarán con: /api/mayorizacion
app.use('/api/mayorizacion', mayorizacionRoutes);

// Registramos las rutas del módulo de reportes financieros.
// Todas comenzarán con: /api/reportes
app.use('/api/reportes', reportesRoutes);

// Registramos las rutas del módulo de asientos contables.
// Todas comenzarán con: /api/asientos
app.use('/api/asientos', asientosRoutes);

// Ruta inicial de prueba.
// Sirve para comprobar que la API está funcionando correctamente.
app.get('/', (req, res) => {
    res.status(200).json({
        estado: 'ok',
        mensaje: 'API Pinax funcionando correctamente'
    });
});

// Exportamos la aplicación para que server.js pueda levantar el servidor.
module.exports = app;