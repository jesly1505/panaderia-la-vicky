<?php
/**
 * Retrocompatibilidad: expone la clase global `Database` como alias de App\Core\Database.
 */
require_once __DIR__ . '/../autoload.php';

class_alias(\App\Core\Database::class, 'Database');
