<?php
/**
 * ============================================================================
 * DIAGNÓSTICO DE INVESTIGACIÓN - DB_PASS
 * Sistema: La Vicky
 * ============================================================================
 * Investiga por qué DB_PASS llega vacía al constructor PDO en InfinityFree.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

// ── PASO 1: Verificar existencia física de archivos ANTES de cargar database.php ──
$root = __DIR__;
$envLocalPath = $root . '/.env';
$envInfinityPath = $root . '/.env.infinityfree';

$envLocalExists = file_exists($envLocalPath);
$envInfinityExists = file_exists($envInfinityPath);

// ── PASO 2: Leer .env.infinityfree directamente (sin database.php) para verificar parsing ──
$rawDbPass = null;
$rawDbPassLen = 0;
$rawFileLines = 0;
$rawParseError = null;
$allRawVars = [];

if ($envInfinityExists) {
    try {
        $rawLines = file($envInfinityPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rawFileLines = count($rawLines);
        foreach ($rawLines as $rawLine) {
            $rawLine = trim($rawLine);
            if ($rawLine === '' || strpos($rawLine, '#') === 0) continue;
            $eqPos = strpos($rawLine, '=');
            if ($eqPos !== false) {
                $k = trim(substr($rawLine, 0, $eqPos));
                $v = substr($rawLine, $eqPos + 1);
                // Remover comillas envolventes
                if (strlen($v) >= 2) {
                    $first = $v[0];
                    $last = $v[strlen($v) - 1];
                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $v = substr($v, 1, -1);
                    }
                }
                $allRawVars[$k] = $v;
                if ($k === 'DB_PASS') {
                    $rawDbPass = $v;
                    $rawDbPassLen = strlen($v);
                }
            }
        }
    } catch (Throwable $e) {
        $rawParseError = $e->getMessage();
    }
}

// ── PASO 3: Cargar config/database.php (ejecuta el env loader del sistema) ──
$configDbPath = $root . '/config/database.php';
$configDbExists = file_exists($configDbPath);
$configLoadError = null;

if ($configDbExists) {
    try {
        require_once $configDbPath;
    } catch (Throwable $e) {
        $configLoadError = $e->getMessage();
    }
}

// ── PASO 4: Verificar en qué storage sobrevivió DB_PASS tras database.php ──
global $_LOADED_ENV, $_LOADED_ENV_FILE;

$storageCheck = [
    '$_LOADED_ENV' => [
        'isset' => isset($_LOADED_ENV['DB_PASS']),
        'value_len' => isset($_LOADED_ENV['DB_PASS']) ? strlen($_LOADED_ENV['DB_PASS']) : 0,
        'empty' => isset($_LOADED_ENV['DB_PASS']) ? empty($_LOADED_ENV['DB_PASS']) : true,
    ],
    '$_ENV' => [
        'isset' => isset($_ENV['DB_PASS']),
        'value_len' => isset($_ENV['DB_PASS']) ? strlen($_ENV['DB_PASS']) : 0,
        'empty' => isset($_ENV['DB_PASS']) ? empty($_ENV['DB_PASS']) : true,
    ],
    '$_SERVER' => [
        'isset' => isset($_SERVER['DB_PASS']),
        'value_len' => isset($_SERVER['DB_PASS']) ? strlen($_SERVER['DB_PASS']) : 0,
        'empty' => isset($_SERVER['DB_PASS']) ? empty($_SERVER['DB_PASS']) : true,
    ],
    'getenv()' => [
        'isset' => @getenv('DB_PASS') !== false,
        'value_len' => @getenv('DB_PASS') !== false ? strlen(@getenv('DB_PASS')) : 0,
        'empty' => @getenv('DB_PASS') !== false ? empty(@getenv('DB_PASS')) : true,
    ],
    'env_get()' => [
        'isset' => function_exists('env_get'),
        'value_len' => function_exists('env_get') ? strlen(env_get('DB_PASS', '')) : 0,
        'empty' => function_exists('env_get') ? empty(env_get('DB_PASS', '')) : true,
    ],
];

// Verificar si putenv está disponible
$putenvAvailable = function_exists('putenv');
$putenvDisabled = false;
if ($putenvAvailable) {
    $disabledFns = ini_get('disable_functions');
    if (stripos($disabledFns, 'putenv') !== false) {
        $putenvDisabled = true;
    }
}

// ── PASO 5: Verificar lo que la clase Database recibe ──
$dbHost = '';
$dbName = '';
$dbUser = '';
$dbPassLen = 0;
$dbPassEmpty = true;
$pdoSuccess = false;
$pdoError = null;

if (class_exists('Database')) {
    $db = new Database();
    $dbHost = $db->getHost();
    $dbName = $db->getDbName();
    $dbUser = $db->getUsername();
    $dbPassword = $db->getPassword();
    $dbPassLen = strlen($dbPassword);
    $dbPassEmpty = empty($dbPassword);

    try {
        $conn = $db->getConnection();
        if ($conn instanceof PDO) {
            $pdoSuccess = true;
        }
    } catch (Throwable $e) {
        $pdoError = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico DB_PASS - La Vicky</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f9; color: #333; margin: 0; padding: 30px 15px; }
        .container { max-width: 750px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 28px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        h1 { color: #1a73e8; margin-top: 0; font-size: 1.4rem; border-bottom: 2px solid #e8eaed; padding-bottom: 10px; }
        h2 { color: #202124; font-size: 1.1rem; margin-top: 22px; border-left: 4px solid #1a73e8; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9rem; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e8eaed; }
        th { background: #f8f9fa; color: #5f6368; font-weight: 600; }
        code { background: #e8f0fe; color: #1a73e8; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 0.9rem; }
        .ok { color: #137333; font-weight: 600; }
        .fail { color: #c5221f; font-weight: 600; }
        .warn { color: #e37400; font-weight: 600; }
        .error-box { margin-top: 14px; background: #fce8e6; border: 1px solid #fad2cf; border-radius: 6px; padding: 12px; color: #c5221f; font-size: 0.85rem; }
        .error-box pre { margin: 6px 0 0 0; white-space: pre-wrap; word-break: break-all; font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Investigación: ¿Por qué DB_PASS llega vacía?</h1>

    <!-- PASO 1: Archivos físicos -->
    <h2>1. Archivos .env en la raíz</h2>
    <table>
        <tr>
            <td><code>.env</code></td>
            <td><?= $envLocalExists ? '<span class="ok">✓ Existe</span>' : '<span class="fail">✗ No existe</span>' ?></td>
            <td><code><?= htmlspecialchars($envLocalPath) ?></code></td>
        </tr>
        <tr>
            <td><code>.env.infinityfree</code></td>
            <td><?= $envInfinityExists ? '<span class="ok">✓ Existe</span>' : '<span class="fail">✗ No existe</span>' ?></td>
            <td><code><?= htmlspecialchars($envInfinityPath) ?></code></td>
        </tr>
    </table>

    <!-- PASO 2: Lectura directa del archivo -->
    <h2>2. Lectura directa de .env.infinityfree (sin database.php)</h2>
    <?php if (!$envInfinityExists): ?>
        <p class="fail">⛔ El archivo .env.infinityfree NO EXISTE en el servidor. Esta es la causa del problema.</p>
    <?php elseif ($rawParseError): ?>
        <p class="fail">⛔ Error al leer: <?= htmlspecialchars($rawParseError) ?></p>
    <?php else: ?>
        <table>
            <tr><th>Dato</th><th>Resultado</th></tr>
            <tr><td>Líneas totales (sin vacías)</td><td><code><?= $rawFileLines ?></code></td></tr>
            <tr><td>Variables parseadas</td><td><code><?= count($allRawVars) ?></code> (<?= htmlspecialchars(implode(', ', array_keys($allRawVars))) ?>)</td></tr>
            <tr>
                <td>DB_PASS en archivo</td>
                <td>
                    <?php if ($rawDbPass !== null && $rawDbPass !== ''): ?>
                        <span class="ok">✓ CONFIGURADA</span> — longitud: <code><?= $rawDbPassLen ?></code> caracteres
                    <?php elseif ($rawDbPass === ''): ?>
                        <span class="fail">✗ VACÍA</span> — la línea DB_PASS= no tiene valor después del signo =
                    <?php else: ?>
                        <span class="fail">✗ NO ENCONTRADA</span> — la clave DB_PASS no existe en el archivo
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <!-- PASO 3: Qué archivo cargó database.php -->
    <h2>3. Archivo que cargó config/database.php</h2>
    <table>
        <tr><td>config/database.php existe</td><td><?= $configDbExists ? '<span class="ok">✓ Sí</span>' : '<span class="fail">✗ No</span>' ?></td></tr>
        <?php if ($configLoadError): ?>
            <tr><td>Error al cargar</td><td class="fail"><?= htmlspecialchars($configLoadError) ?></td></tr>
        <?php endif; ?>
        <tr>
            <td>Archivo de entorno seleccionado</td>
            <td><code><?= isset($_LOADED_ENV_FILE) && $_LOADED_ENV_FILE ? htmlspecialchars($_LOADED_ENV_FILE) : '<span class="fail">NINGUNO</span>' ?></code></td>
        </tr>
        <tr>
            <td>¿Es .env.infinityfree?</td>
            <td><?= (isset($_LOADED_ENV_FILE) && strpos($_LOADED_ENV_FILE, '.env.infinityfree') !== false) ? '<span class="ok">✓ Sí</span>' : '<span class="fail">✗ No</span>' ?></td>
        </tr>
    </table>

    <!-- PASO 4: Sobrevivencia de DB_PASS en cada storage -->
    <h2>4. ¿DB_PASS sobrevivió en cada almacén?</h2>
    <table>
        <tr><th>Almacén</th><th>isset</th><th>Longitud</th><th>¿Vacía?</th></tr>
        <?php foreach ($storageCheck as $store => $info): ?>
        <tr>
            <td><code><?= htmlspecialchars($store) ?></code></td>
            <td><?= $info['isset'] ? '<span class="ok">sí</span>' : '<span class="fail">no</span>' ?></td>
            <td><code><?= $info['value_len'] ?></code></td>
            <td><?= $info['empty'] ? '<span class="fail">VACÍA</span>' : '<span class="ok">tiene valor</span>' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p>
        <code>putenv()</code> disponible: <?= $putenvAvailable ? ($putenvDisabled ? '<span class="warn">⚠ En disable_functions</span>' : '<span class="ok">✓ Sí</span>') : '<span class="fail">✗ No</span>' ?>
        &nbsp;|&nbsp;
        <code>disable_functions</code>: <code><?= htmlspecialchars(ini_get('disable_functions') ?: '(ninguna)') ?></code>
    </p>

    <!-- PASO 5: Lo que la clase Database recibió -->
    <h2>5. Valores que recibe la clase Database y conexión PDO</h2>
    <table>
        <tr><th>Parámetro</th><th>Valor / Estado</th></tr>
        <tr><td><strong>DB_HOST</strong></td><td><code><?= htmlspecialchars($dbHost) ?></code></td></tr>
        <tr><td><strong>DB_NAME</strong></td><td><code><?= htmlspecialchars($dbName) ?></code></td></tr>
        <tr><td><strong>DB_USER</strong></td><td><code><?= htmlspecialchars($dbUser) ?></code></td></tr>
        <tr>
            <td><strong>DB_PASS</strong></td>
            <td>
                <?php if (!$dbPassEmpty): ?>
                    <span class="ok">✓ Configurada (******)</span> — longitud: <code><?= $dbPassLen ?></code>
                <?php else: ?>
                    <span class="fail">✗ VACÍA (longitud: 0)</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Conexión PDO</strong></td>
            <td>
                <?php if ($pdoSuccess): ?>
                    <span class="ok">✓ Conexión PDO exitosa</span>
                <?php else: ?>
                    <span class="fail">✗ Error de conexión</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <?php if (!$pdoSuccess && $pdoError): ?>
        <div class="error-box">
            <strong>Detalle del error PDO:</strong>
            <pre><?= htmlspecialchars($pdoError) ?></pre>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
