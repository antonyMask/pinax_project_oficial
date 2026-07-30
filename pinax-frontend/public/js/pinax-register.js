document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('pinax-register-form');
    const clave = document.getElementById('password');
    const confirmacion = document.getElementById('password_confirmation');
    const mensajeCoincidencia = document.getElementById(
        'pinax-password-match'
    );
    const botonEnviar = document.getElementById('pinax-register-submit');

    /*
     * Permite mostrar u ocultar cada contraseña sin duplicar la lógica para
     * los dos campos.
     */
    document.querySelectorAll('[data-password-toggle]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const objetivo = boton.dataset.passwordToggle;
            const campo = document.getElementById(objetivo);
            const icono = boton.querySelector('i');

            if (!campo) {
                return;
            }

            const mostrar = campo.type === 'password';

            campo.type = mostrar ? 'text' : 'password';

            icono?.classList.toggle('fa-eye', !mostrar);
            icono?.classList.toggle('fa-eye-slash', mostrar);

            boton.setAttribute(
                'aria-label',
                mostrar
                    ? 'Ocultar contraseña'
                    : 'Mostrar contraseña'
            );
        });
    });

    /*
     * Reglas visibles del formulario. Cada función recibe la contraseña y
     * devuelve si el requisito ya se cumple.
     */
    const reglas = {
        length: (valor) => valor.length >= 8,
        lowercase: (valor) => /[a-z]/.test(valor),
        uppercase: (valor) => /[A-Z]/.test(valor),
        number: (valor) => /[0-9]/.test(valor)
    };

    const actualizarReglas = () => {
        const valor = clave?.value || '';

        Object.entries(reglas).forEach(([nombre, validar]) => {
            const elemento = document.querySelector(
                `[data-password-rule="${nombre}"]`
            );

            elemento?.classList.toggle('is-valid', validar(valor));
        });
    };

    const actualizarCoincidencia = () => {
        if (!mensajeCoincidencia || !confirmacion) {
            return;
        }

        const valorClave = clave?.value || '';
        const valorConfirmacion = confirmacion.value;

        mensajeCoincidencia.classList.remove('is-valid', 'is-invalid');

        if (valorConfirmacion === '') {
            mensajeCoincidencia.textContent = '';
            return;
        }

        const coinciden = valorClave === valorConfirmacion;

        mensajeCoincidencia.textContent = coinciden
            ? 'Las contraseñas coinciden.'
            : 'Las contraseñas todavía no coinciden.';

        mensajeCoincidencia.classList.add(
            coinciden ? 'is-valid' : 'is-invalid'
        );
    };

    clave?.addEventListener('input', () => {
        actualizarReglas();
        actualizarCoincidencia();
    });

    confirmacion?.addEventListener('input', actualizarCoincidencia);

    /*
     * Evita envíos duplicados después de que el navegador confirme que todos
     * los campos cumplen sus restricciones HTML.
     */
    formulario?.addEventListener('submit', () => {
        if (!botonEnviar) {
            return;
        }

        botonEnviar.disabled = true;
        botonEnviar.querySelector('span').textContent = 'Creando cuenta...';
    });

    // Refleja valores restaurados por el navegador o por autocompletado.
    actualizarReglas();
    actualizarCoincidencia();
});
