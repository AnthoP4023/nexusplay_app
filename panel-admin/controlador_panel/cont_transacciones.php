<?php
require_once __DIR__ . '/../functions_panel/fun_transacciones.php';

$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 20;
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$transacciones = getTransacciones($pagina_actual, $por_pagina, $filtro_tipo, $filtro_fecha, $busqueda);
$total_transacciones = getTotalTransacciones($filtro_tipo, $filtro_fecha, $busqueda);
$total_paginas = ceil($total_transacciones / $por_pagina);
$stats = getEstadisticasTransacciones();

if (isset($_POST['exportar'])) {
    exportarTransaccionesCSV($filtro_tipo, $filtro_fecha, $busqueda);
}
?>