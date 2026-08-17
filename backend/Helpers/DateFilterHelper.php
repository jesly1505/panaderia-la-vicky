<?php
namespace App\Helpers;

class DateFilterHelper {
    
    /**
     * Retorna una condición SQL válida basada en un filtro de tiempo.
     * 
     * @param string $dateColumn Nombre de la columna de fecha (ej. 'fecha_venta', 'fecha_hora')
     * @param string $filterType 'today', 'week', 'month', 'year', 'custom', o 'all'
     * @param string $startDate Fecha inicio (YYYY-MM-DD) si es custom
     * @param string $endDate Fecha fin (YYYY-MM-DD) si es custom
     * @return string Condición SQL segura
     */
    public static function getSqlCondition($dateColumn, $filterType = 'all', $startDate = '', $endDate = '') {
        $condition = "1=1";
        
        switch ($filterType) {
            case 'today':
                $condition = "DATE($dateColumn) = CURDATE()";
                break;
            case 'week':
                $condition = "YEARWEEK($dateColumn, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'month':
                $condition = "MONTH($dateColumn) = MONTH(CURDATE()) AND YEAR($dateColumn) = YEAR(CURDATE())";
                break;
            case 'year':
                $condition = "YEAR($dateColumn) = YEAR(CURDATE())";
                break;
            case 'custom':
                if (!empty($startDate) && !empty($endDate)) {
                    $sd = preg_replace('/[^0-9\-]/', '', $startDate);
                    $ed = preg_replace('/[^0-9\-]/', '', $endDate);
                    $condition = "DATE($dateColumn) BETWEEN '$sd' AND '$ed'";
                } else if (!empty($startDate)) {
                    $sd = preg_replace('/[^0-9\-]/', '', $startDate);
                    $condition = "DATE($dateColumn) >= '$sd'";
                } else if (!empty($endDate)) {
                    $ed = preg_replace('/[^0-9\-]/', '', $endDate);
                    $condition = "DATE($dateColumn) <= '$ed'";
                }
                break;
            default:
                // Sin filtro temporal
                break;
        }
        return $condition;
    }
    
    /**
     * Genera la interfaz gráfica del filtro de fechas en HTML
     */
    public static function getFilterUI($currentFilter, $startDate, $endDate, $actionUrl = "") {
        $cleanAction = htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8');
        $cleanStart = htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8');
        $cleanEnd = htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8');

        $html = '
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-3">
                <form method="GET" action="'.$cleanAction.'" class="row g-3 align-items-center">
                    <div class="col-12 col-md-auto">
                        <label class="form-label small fw-bold text-muted mb-1">Filtrar por periodo:</label>
                        <select name="filter" class="form-select form-select-sm" id="filterSelect" onchange="toggleCustomDates()">
                            <option value="all" '.($currentFilter == 'all' || empty($currentFilter) ? 'selected' : '').'>Todo el Historial</option>
                            <option value="today" '.($currentFilter == 'today' ? 'selected' : '').'>Hoy</option>
                            <option value="week" '.($currentFilter == 'week' ? 'selected' : '').'>Esta Semana</option>
                            <option value="month" '.($currentFilter == 'month' ? 'selected' : '').'>Este Mes</option>
                            <option value="year" '.($currentFilter == 'year' ? 'selected' : '').'>Este Año</option>
                            <option value="custom" '.($currentFilter == 'custom' ? 'selected' : '').'>Rango Personalizado</option>
                        </select>
                    </div>
                    
                    <div class="col-12 col-md-auto custom-date-group" style="'.($currentFilter == 'custom' ? 'display:block;' : 'display:none;').'">
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <label class="form-label small fw-bold text-muted mb-1">Inicio:</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="'.$cleanStart.'">
                            </div>
                            <div>
                                <label class="form-label small fw-bold text-muted mb-1">Fin:</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="'.$cleanEnd.'">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-auto mt-md-4">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                        '.(($currentFilter !== 'all' && !empty($currentFilter)) ? '<a href="'.$cleanAction.'" class="btn btn-outline-secondary btn-sm ms-2">Limpiar</a>' : '').'
                    </div>
                </form>
            </div>
        </div>
        <script>
            function toggleCustomDates() {
                var select = document.getElementById("filterSelect");
                var customGroups = document.querySelectorAll(".custom-date-group");
                if (!select) return;
                if (select.value === "custom") {
                    customGroups.forEach(function(el) { el.style.display = "block"; });
                } else {
                    customGroups.forEach(function(el) { el.style.display = "none"; });
                }
            }
        </script>
        ';
        return $html;
    }
}

// Alias global para compatibilidad de vistas
if (!class_exists('DateFilterHelper')) {
    class_alias('App\Helpers\DateFilterHelper', 'DateFilterHelper');
}
