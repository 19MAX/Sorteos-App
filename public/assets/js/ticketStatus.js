/**
 * ticketStatus.js
 *
 * Espeja el helper PHP ticket_status_badge / ticket_status_label.
 * Úsalo en cualquier vista que necesite formatear estados de boletos.
 *
 * Uso (DataTables):
 *   import { renderBadge, getLabel } from './ticketStatus.js';
 *
 * Uso (script clásico en el <head>):
 *   <script src="ticketStatus.js"></script>
 *   TicketStatus.renderBadge('pagado')
 */

const TicketStatus = (() => {

    /** @type {Record<string, { classes: string, label: string }>} */
    const STATUS_MAP = {
        disponible : { classes: 'bg-primary',              label: 'Disponible' },
        reservado  : { classes: 'bg-warning text-dark',    label: 'Reservado'  },
        procesando : { classes: 'bg-info text-dark',       label: 'Procesando' },
        vendido    : { classes: 'bg-dark',                 label: 'Vendido'    },
        pagado     : { classes: 'bg-success',              label: 'Pagado'     },
        asignado   : { classes: 'bg-purple',               label: 'Asignado'   },
        expirado   : { classes: 'bg-secondary',            label: 'Expirado'   },
    };

    /**
     * Devuelve el badge HTML de Bootstrap para un estado.
     *
     * @param  {string} status
     * @returns {string} HTML del badge
     */
    function renderBadge(status) {
        const entry = STATUS_MAP[status];
        const classes = entry?.classes ?? 'bg-secondary';
        const label   = entry?.label   ?? _capitalize(status);
        return `<span class="badge ${classes}">${label}</span>`;
    }

    /**
     * Devuelve solo el nombre legible del estado.
     *
     * @param  {string} status
     * @returns {string}
     */
    function getLabel(status) {
        return STATUS_MAP[status]?.label ?? _capitalize(status);
    }

    /**
     * Devuelve las clases CSS del estado (útil si construís el HTML manualmente).
     *
     * @param  {string} status
     * @returns {string}
     */
    function getClasses(status) {
        return STATUS_MAP[status]?.classes ?? 'bg-secondary';
    }

    /** @param {string} str */
    function _capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    return { renderBadge, getLabel, getClasses };

})();

// ── Soporte dual: ES Module + script clásico ──────────────────────────────────
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TicketStatus;                        // CommonJS / Node
}
if (typeof window !== 'undefined') {
    window.TicketStatus = TicketStatus;                   // script clásico
}
export default TicketStatus;                              // ES Module