/*
|--------------------------------------------------------------------------
| Interacciones del módulo de Reportes Financieros
|--------------------------------------------------------------------------
| El archivo administra:
| - selección visual del tipo de reporte;
| - confirmación accesible de cambios de estado;
| - impresión del documento financiero.
|
| No calcula importes ni modifica datos contables en el navegador.
*/

document.addEventListener('DOMContentLoaded', () => {
    /*
     * Sincroniza el estilo de las tarjetas con el radio seleccionado.
     * El input real permanece como fuente de verdad para el formulario.
     */
    const opcionesTipo = Array.from(
        document.querySelectorAll('[data-report-type-option]')
    );

    const actualizarOpcionesTipo = () => {
        opcionesTipo.forEach((opcion) => {
            const radio = opcion.querySelector('input[type="radio"]');
            const seleccionada = Boolean(radio?.checked);

            opcion.classList.toggle('is-selected', seleccionada);
            opcion.setAttribute('aria-checked', String(seleccionada));
        });
    };

    opcionesTipo.forEach((opcion) => {
        const radio = opcion.querySelector('input[type="radio"]');

        if (radio) {
            radio.addEventListener('change', actualizarOpcionesTipo);
        }
    });

    actualizarOpcionesTipo();

    /*
     * Intercepta formularios sensibles para mostrar una confirmación clara.
     * Si Bootstrap/jQuery no están disponibles, utiliza confirm como respaldo.
     */
    const formulariosAccion = Array.from(
        document.querySelectorAll('form[data-report-action]')
    );

    const modal = document.getElementById('reportActionModal');
    const tituloModal = modal?.querySelector('#reportActionModalTitle');
    const mensajeModal = modal?.querySelector(
        '[data-report-modal-message]'
    );
    const iconoModal = modal?.querySelector(
        '[data-report-modal-icon]'
    );
    const botonConfirmar = modal?.querySelector(
        '[data-report-modal-confirm]'
    );

    let formularioPendiente = null;

    const enviarFormularioPendiente = () => {
        if (!formularioPendiente) {
            return;
        }

        /*
         * submit() evita volver a disparar el listener de submit.
         * El navegador conserva los campos CSRF y el método simulado.
         */
        HTMLFormElement.prototype.submit.call(formularioPendiente);
        formularioPendiente = null;
    };

    formulariosAccion.forEach((formulario) => {
        formulario.addEventListener('submit', (evento) => {
            evento.preventDefault();

            const tipoAccion =
                formulario.dataset.actionKind || 'confirm';
            const titulo =
                formulario.dataset.actionTitle || 'Confirmar acción';
            const mensaje =
                formulario.dataset.actionMessage
                || 'Verifica la acción antes de continuar.';

            const disponeDeModal = Boolean(
                modal
                && window.jQuery
                && typeof window.jQuery.fn.modal === 'function'
            );

            if (!disponeDeModal) {
                if (window.confirm(`${titulo}\n\n${mensaje}`)) {
                    HTMLFormElement.prototype.submit.call(formulario);
                }

                return;
            }

            formularioPendiente = formulario;

            if (tituloModal) {
                tituloModal.textContent = titulo;
            }

            if (mensajeModal) {
                mensajeModal.textContent = mensaje;
            }

            modal.classList.toggle(
                'is-void',
                tipoAccion === 'void'
            );

            if (iconoModal) {
                iconoModal.innerHTML = tipoAccion === 'void'
                    ? '<i class="fas fa-ban" aria-hidden="true"></i>'
                    : '<i class="fas fa-check" aria-hidden="true"></i>';
            }

            if (botonConfirmar) {
                botonConfirmar.textContent = tipoAccion === 'void'
                    ? 'Anular reporte'
                    : 'Confirmar reporte';

                botonConfirmar.classList.toggle(
                    'reports-button--danger',
                    tipoAccion === 'void'
                );

                botonConfirmar.classList.toggle(
                    'reports-button--confirm',
                    tipoAccion !== 'void'
                );
            }

            window.jQuery(modal).modal('show');
        });
    });

    botonConfirmar?.addEventListener(
        'click',
        enviarFormularioPendiente
    );

    if (modal && window.jQuery) {
        window.jQuery(modal).on('hidden.bs.modal', () => {
            formularioPendiente = null;
        });
    }

    /*
     * La salida impresa se controla mediante los estilos @media print.
     */
    document
        .querySelectorAll('[data-print-report]')
        .forEach((boton) => {
            boton.addEventListener('click', () => {
                window.print();
            });
        });
});
