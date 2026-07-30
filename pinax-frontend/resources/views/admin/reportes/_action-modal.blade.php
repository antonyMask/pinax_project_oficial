{{--
    Modal reutilizable para confirmar cambios de estado.

    El contenido concreto se establece desde public/js/reportes.js usando
    atributos data-* controlados por cada formulario.
--}}
<div class="modal fade reports-modal" id="reportActionModal" tabindex="-1" role="dialog"
    aria-labelledby="reportActionModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <span class="reports-modal__icon" data-report-modal-icon>
                    <i class="fas fa-check" aria-hidden="true"></i>
                </span>

                <h2 id="reportActionModalTitle">
                    Confirmar acción
                </h2>

                <p data-report-modal-message>
                    Verifica la acción antes de continuar.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="reports-button reports-button--ghost"
                    data-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="reports-button reports-button--confirm"
                    data-report-modal-confirm>
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
