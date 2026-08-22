<?php
// frontend/includes/footer.php
?>
    <!-- Sistema Centralizado de Alertas y Confirmaciones -->
    <div class="modal fade" id="sysModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body text-center p-4">
                    <div id="sysModalIcon" class="mb-3"></div>
                    <h5 id="sysModalTitle" class="fw-bold mb-2"></h5>
                    <p id="sysModalMessage" class="text-muted mb-4"></p>
                    <div class="d-flex justify-content-center gap-2" id="sysModalButtons">
                        <button type="button" class="btn btn-secondary px-4 fw-bold" id="sysModalBtnCancel" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary px-4 fw-bold" id="sysModalBtnConfirm">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor de Toasts (Notificaciones) -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 10000;" id="sysToastContainer">
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
</body>
</html>
