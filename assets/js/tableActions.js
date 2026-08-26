// assets/js/tableActions.js
// Botones centralizados para acciones de tabla.
// Uso:  TA.view('viewHistory(1)')  TA.edit('openEdit(1)')  TA.remove('deleteItem(1)')
const TA = {
    _btn(cls, icon, onclick, title) {
        return `<button class="btn btn-sm ${cls}" onclick="${onclick}" title="${escapeHtml(title)}"><i class="fas ${icon}"></i></button>`;
    },

    /** Ver / Detalles / Historial */
    view(onclick, title = 'Ver detalles') {
        return this._btn('btn-outline-info me-1', 'fa-eye', onclick, title);
    },

    /** Editar */
    edit(onclick, title = 'Editar') {
        return this._btn('btn-outline-primary me-1', 'fa-pen', onclick, title);
    },

    /** Eliminar / Borrar */
    remove(onclick, title = 'Eliminar') {
        return this._btn('btn-outline-danger', 'fa-trash-alt', onclick, title);
    },

    /** Accion personalizada (clase, icono, onclick, titulo) */
    custom(onclick, icon, title, cls = 'btn-outline-secondary me-1') {
        return this._btn(cls, icon, onclick, title);
    }
};
