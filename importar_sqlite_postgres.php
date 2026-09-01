<?php

echo "=============================================\n";
echo "  IMPORTACION SQLITE -> POSTGRESQL\n";
echo "=============================================\n\n";

$sqliteFile = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';

if (!file_exists($sqliteFile)) {
    die("ERROR: No se encontro database/database.sqlite\n");
}

echo "SQLite encontrado correctamente.\n";

// ---------------------------------------------------------
// CONEXIONES
// ---------------------------------------------------------

try {
    $sqlite = new PDO('sqlite:' . $sqliteFile);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexion SQLite: OK\n";

    // Leemos las variables del .env de Laravel
    $envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';

    if (!file_exists($envFile)) {
        die("ERROR: No se encontro el archivo .env\n");
    }

    $env = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $config = [];

    foreach ($env as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $value = trim($value);

        if (
            strlen($value) >= 2 &&
            (
                ($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                ($value[0] === "'" && $value[strlen($value) - 1] === "'")
            )
        ) {
            $value = substr($value, 1, -1);
        }

        $config[$key] = $value;
    }

    $host = $config['DB_HOST'] ?? '127.0.0.1';
    $port = $config['DB_PORT'] ?? '5432';
    $database = $config['DB_DATABASE'] ?? '';
    $username = $config['DB_USERNAME'] ?? '';
    $password = $config['DB_PASSWORD'] ?? '';

    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

    $pgsql = new PDO($dsn, $username, $password);
    $pgsql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pgsql->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    echo "Conexion PostgreSQL: OK\n";
    echo "Base: {$database}\n";
    echo "Host: {$host}:{$port}\n\n";

} catch (Throwable $e) {
    die("ERROR DE CONEXION:\n" . $e->getMessage() . "\n");
}

// ---------------------------------------------------------
// TABLAS SQLITE
// ---------------------------------------------------------

$tables = $sqlite->query("
    SELECT name
    FROM sqlite_master
    WHERE type = 'table'
      AND name NOT LIKE 'sqlite_%'
      AND name <> 'migrations'
    ORDER BY name
")->fetchAll(PDO::FETCH_COLUMN);

if (!$tables) {
    die("No se encontraron tablas en SQLite.\n");
}

echo "Tablas encontradas en SQLite: " . count($tables) . "\n\n";

// ---------------------------------------------------------
// FUNCIONES
// ---------------------------------------------------------

function quotePgIdentifier(string $name): string
{
    return '"' . str_replace('"', '""', $name) . '"';
}

function normalizeValue($value, string $pgType)
{
    if ($value === null) {
        return null;
    }

    $type = strtolower($pgType);

    // PostgreSQL boolean
    if (
        str_contains($type, 'boolean') ||
        $type === 'bool'
    ) {
        if ($value === true || $value === 1 || $value === '1' || strtolower((string) $value) === 'true') {
            return 'true';
        }

        if ($value === false || $value === 0 || $value === '0' || strtolower((string) $value) === 'false') {
            return 'false';
        }
    }

    return $value;
}

// ---------------------------------------------------------
// DESACTIVAR TEMPORALMENTE FOREIGN KEYS/TRIGGERS
// ---------------------------------------------------------

try {
    echo "Desactivando temporalmente restricciones de PostgreSQL...\n";

    $pgsql->exec("SET session_replication_role = 'replica'");

    echo "OK.\n\n";

} catch (Throwable $e) {
    die("ERROR al preparar PostgreSQL:\n" . $e->getMessage() . "\n");
}

// ---------------------------------------------------------
// IMPORTACION
// ---------------------------------------------------------

$totalGeneral = 0;
$resultados = [];

foreach ($tables as $table) {

    echo "---------------------------------------------\n";
    echo "Tabla: {$table}\n";

    try {

        // Verificar que la tabla exista en PostgreSQL
        $check = $pgsql->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = 'public'
              AND table_name = ?
        ");

        $check->execute([$table]);

        if ((int) $check->fetchColumn() === 0) {
            echo "ADVERTENCIA: La tabla no existe en PostgreSQL. Se omite.\n";
            $resultados[$table] = 'OMITIDA';
            continue;
        }

        // Columnas SQLite
        $sqliteColumns = $sqlite
            ->query('PRAGMA table_info(' . quotePgIdentifier($table) . ')')
            ->fetchAll(PDO::FETCH_ASSOC);

        $sqliteColumnNames = [];

        foreach ($sqliteColumns as $column) {
            $sqliteColumnNames[] = $column['name'];
        }

        // Columnas PostgreSQL
        $pgColumnsStmt = $pgsql->prepare("
            SELECT
                column_name,
                data_type,
                udt_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = ?
            ORDER BY ordinal_position
        ");

        $pgColumnsStmt->execute([$table]);

        $pgColumns = $pgColumnsStmt->fetchAll(PDO::FETCH_ASSOC);

        $pgColumnMap = [];

        foreach ($pgColumns as $column) {
            $pgColumnMap[$column['column_name']] = $column;
        }

        // Solo utilizamos columnas que existen en ambas bases.
        $columns = [];

        foreach ($sqliteColumnNames as $columnName) {
            if (isset($pgColumnMap[$columnName])) {
                $columns[] = $columnName;
            }
        }

        if (!$columns) {
            echo "ADVERTENCIA: No hay columnas compatibles. Se omite.\n";
            $resultados[$table] = 'OMITIDA';
            continue;
        }

        echo "Columnas a importar: " . count($columns) . "\n";

        // Cantidad de registros SQLite
        $countStmt = $sqlite->query(
            'SELECT COUNT(*) FROM ' . quotePgIdentifier($table)
        );

        $sqliteCount = (int) $countStmt->fetchColumn();

        echo "Registros SQLite: {$sqliteCount}\n";

        if ($sqliteCount === 0) {
            echo "Tabla vacia. No hay nada que importar.\n";
            $resultados[$table] = 0;
            continue;
        }

        // Construir SELECT
        $selectColumns = implode(
            ', ',
            array_map('quotePgIdentifier', $columns)
        );

        $selectStmt = $sqlite->query(
            'SELECT ' . $selectColumns .
            ' FROM ' . quotePgIdentifier($table)
        );

        // Construir INSERT
        $quotedColumns = implode(
            ', ',
            array_map('quotePgIdentifier', $columns)
        );

        $placeholders = implode(
            ', ',
            array_fill(0, count($columns), '?')
        );

        $insertSql = "
            INSERT INTO " . quotePgIdentifier($table) . "
            ({$quotedColumns})
            VALUES ({$placeholders})
        ";

        $insertStmt = $pgsql->prepare($insertSql);

        $inserted = 0;

        while ($row = $selectStmt->fetch(PDO::FETCH_ASSOC)) {

            $values = [];

            foreach ($columns as $columnName) {

                $value = $row[$columnName];

                $pgType = $pgColumnMap[$columnName]['data_type'] ?? '';

                $value = normalizeValue($value, $pgType);

                $values[] = $value;
            }

            $insertStmt->execute($values);

            $inserted++;

            if ($inserted % 1000 === 0) {
                echo "  Importados: {$inserted}/{$sqliteCount}\n";
            }
        }

        echo "Importados correctamente: {$inserted}\n";

        $resultados[$table] = $inserted;
        $totalGeneral += $inserted;

    } catch (Throwable $e) {

        echo "\nERROR EN TABLA {$table}:\n";
        echo $e->getMessage() . "\n";

        echo "\nSe detendra la importacion para evitar datos incompletos.\n";

        $pgsql->rollBack();

        die("\nIMPORTACION DETENIDA.\n");
    }
}

// ---------------------------------------------------------
// REACTIVAR RESTRICCIONES
// ---------------------------------------------------------

try {

    echo "\n=============================================\n";
    echo "Reactivando restricciones PostgreSQL...\n";

    $pgsql->exec("SET session_replication_role = 'origin'");

    echo "OK.\n";

} catch (Throwable $e) {

    echo "ADVERTENCIA: No se pudo restaurar session_replication_role.\n";
    echo $e->getMessage() . "\n";
}

// ---------------------------------------------------------
// AJUSTAR SECUENCIAS
// ---------------------------------------------------------

echo "\n=============================================\n";
echo "AJUSTANDO SECUENCIAS / AUTOINCREMENTOS\n";
echo "=============================================\n";

try {

    foreach ($tables as $table) {

        if (!isset($resultados[$table]) || $resultados[$table] === 'OMITIDA') {
            continue;
        }

        // Obtener columnas del tipo serial/identity
        $identityStmt = $pgsql->prepare("
            SELECT
                column_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND table_name = ?
              AND (
                    is_identity = 'YES'
                    OR column_default LIKE 'nextval(%'
                  )
        ");

        $identityStmt->execute([$table]);

        $identityColumns = $identityStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($identityColumns as $column) {

            $sequenceStmt = $pgsql->prepare(
                "SELECT pg_get_serial_sequence(?, ?)"
            );

            $sequenceStmt->execute([
                'public.' . $table,
                $column
            ]);

            $sequence = $sequenceStmt->fetchColumn();

            if (!$sequence) {
                continue;
            }

            $maxStmt = $pgsql->query(
                'SELECT MAX(' . quotePgIdentifier($column) . ') FROM ' .
                quotePgIdentifier($table)
            );

            $max = $maxStmt->fetchColumn();

            if ($max !== null) {

                $setval = $pgsql->prepare(
                    "SELECT setval(?, ?, true)"
                );

                $setval->execute([
                    $sequence,
                    (int) $max
                ]);

                echo "  {$table}.{$column} -> {$max}\n";
            }
        }
    }

} catch (Throwable $e) {

    echo "ADVERTENCIA al ajustar secuencias:\n";
    echo $e->getMessage() . "\n";
}

// ---------------------------------------------------------
// RESUMEN
// ---------------------------------------------------------

echo "\n=============================================\n";
echo "           IMPORTACION TERMINADA\n";
echo "=============================================\n\n";

foreach ($resultados as $table => $count) {
    if ($count === 'OMITIDA') {
        echo str_pad($table, 35) . " OMITIDA\n";
    } else {
        echo str_pad($table, 35) . " {$count} registros\n";
    }
}

echo "\nTOTAL DE REGISTROS IMPORTADOS: {$totalGeneral}\n";

echo "\n=============================================\n";
echo "IMPORTACION FINALIZADA CORRECTAMENTE\n";
echo "=============================================\n";