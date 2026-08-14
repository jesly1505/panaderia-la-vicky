# Panadería La Vicky - ERP de Gestión Integral

Este es un sistema de gestión empresarial (ERP) diseñado específicamente para panaderías artesanales, enfocado en el control de inventarios, recetas digitales, producción manual y ventas.

## 🚀 Innovación: Arquitectura SOLID

A diferencia de un sistema administrativo convencional, este repositorio ha sido refactorizado bajo los **5 Principios de Diseño SOLID**, garantizando un código de grado industrial:

1.  **S (Single Responsibility):** Cada clase tiene una única razón para cambiar (Modelos vs Controladores especializados).
2.  **O (Open/Closed):** Sistema modular que permite añadir funcionalidades sin alterar el núcleo.
3.  **L (Liskov Substitution):** Uso de interfaces que aseguran que las implementaciones sean intercambiables.
4.  **I (Interface Segregation):** Contratos específicos para cada módulo (`InsumoRepositoryInterface`, `DatabaseInterface`).
5.  **D (Dependency Inversion):** Implementación de **Inyección de Dependencias** (contenedor DI) para desacoplar la base de datos de la lógica de negocio.

## 🆕 Implementaciones Recientes

### Robusteza de arquitectura (backend)

*   **Router con métodos HTTP:** Cada ruta declara su método (`GET`/`POST`) y devuelve `405 Method Not Allowed` ante llamadas incorrectas. `HEAD` se resuelve como `GET` (RFC 9110).
*   **Manejo de dinero centralizado (`App\Core\Money`):** Redondeo consistente de montos en ventas, pedidos, gastos, insumos y productos (evita imprecisiones de coma flotante).
*   **Dashboard unificado:** Una sola consulta calcula ventas del día (excluyendo canceladas), pedidos pendientes, catálogo y clientes; más listas de últimos pedidos y alertas de stock.
*   **Subquery de costos compartido (`ProductoModel::COSTO_VENTA_SUBQUERY`):** El costo real de producción se calcula desde un único lugar y se reutiliza en reportes.
*   **Eliminación segura de productos:** Antes de borrar se verifica si el producto aparece en ventas o pedidos; si está en uso, la API responde un mensaje claro.
*   **Codificación `utf8mb4`** en la conexión PDO (soporte completo de caracteres especiales y emojis).

### Calidad y arquitectura (testing)

*   **Validador de entrada (`App\Core\Validator`):** Validación central de campos obligatorios, numéricos, rangos, correos, fechas y listas blancas (estados de pedidos, métodos de pago) en todos los controllers de escritura.
*   **Modelos sin dependencia de sesión:** `VentaModel` y `PedidoModel` reciben el `usuario_id` por parámetro en lugar de leer `$_SESSION` (lógica de negocio reutilizable y testeable).
*   **Base de datos versionada:** `database/schema.sql` (estructura) y `database/seed.sql` (roles y usuarios demo) incluidos en el repositorio.
*   **Pruebas automatizadas (PHPUnit 10.5):** 34 tests / 62 aserciones sobre `Validator`, `Money`, `Security` y `UnitConverter`, con cobertura de casos límite.
*   **Scripts Composer:** `composer lint`, `composer test` y `composer check` (lint + test).
*   **Cliente API compartido (`assets/js/api.js`):** Helper `api()` con soporte GET/POST (JSON o FormData), `formatMoney()` y `escapeHtml()` para mitigar XSS al renderizar datos dinámicos.

## 🛠️ Tecnologías Utilizadas

*   **Backend:** PHP 8.1+ con arquitectura **MVC**, **PDO** (anti SQL Injection), contenedor de dependencias y router centralizado.
*   **Base de Datos:** MySQL/MariaDB (Motor InnoDB, transacciones, codificación `utf8mb4`).
*   **Frontend:** HTML5, CSS3 y JavaScript Vanilla (Diseño Responsivo).
*   **Testing:** PHPUnit 10.5 con integración a Composer.
*   **Servidor:** Entorno XAMPP (Apache + MySQL) o PHP built-in server.

## 📦 Módulos del Sistema

*   **Inventario Inteligente:** Control de stock de materia prima con alertas de stock mínimo y compras a proveedores.
*   **Recetario Digital:** Vinculación de productos finales con sus insumos necesarios.
*   **Producción Manual:** Registro de horneadas con descuento automático de inventario y conversión de unidades.
*   **Punto de Venta (PV):** Registro de ventas directas y gestión de pedidos con trazabilidad.
*   **Reportería Financiera:** Cálculo de ganancias netas descontando el costo real de producción.
*   **Gestión de Usuarios y Auditoría:** Roles (Administrador, Cajero, Panadero), bitácora de eventos y registro de accesos denegados.

## 📂 Estructura del Repositorio

```text
/backend
  /Controllers  - Lógica de flujo (inyectan dependencias).
  /Models       - Lógica de datos (implementan repositorios).
  /Core         - Contenedor DI, Router, Validator, Money, Security, AuditService, Database.
    /Interfaces - Contratos SOLID (InsumoRepositoryInterface, DatabaseInterface).
  /Helpers      - Utilidades (UnitConverter).
  /Utils        - Lógica de inventario (descuento/reversión de stock).
  api.php       - Punto de entrada único de la API (enrutado por ?route=...).
/config         - Configuración de base de datos y carga de variables de entorno (.env).
/database       - Schema y seed versionados (schema.sql, seed.sql).
/frontend       - Vistas e interfaz de usuario.
/assets         - CSS y JavaScript compartidos (incluye api.js).
/tests          - Suite PHPUnit (tests/run.php, tests/Unit/*Test.php).
/tools          - Scripts de desarrollo (lint.php).
/dev_tools      - Scripts de mantenimiento de la base de datos.
```

## 🧪 Pruebas y Calidad

```bash
composer install          # instala dependencias (incluye PHPUnit 10.5)
composer lint             # valida sintaxis de todo el código PHP
composer test             # ejecuta la suite PHPUnit (34 tests)
composer check            # ejecuta lint + test en conjunto
```

## 🔧 Instalación

1.  Clonar/copiar el repositorio en `C:\xampp\htdocs\la-vicky-sistema`.
2.  Crear la base de datos e importar la estructura y datos iniciales:
    *   Importar `database/schema.sql` (crea `la_vicky_db` y todas las tablas).
    *   Importar `database/seed.sql` (roles y usuarios de ejemplo).
3.  Copiar `.env.example` como `.env` y configurar las credenciales de tu base de datos.
4.  Ejecutar `composer install` dentro del proyecto.
5.  Asegurarse de que Apache y MySQL estén activos en el panel de XAMPP.
6.  Acceder vía `http://localhost/la-vicky-sistema/`.
7.  Iniciar sesión con las credenciales por defecto: `admin@lavicky.com` / `admin123` (¡cambiar la contraseña tras el primer ingreso!).

---
**Desarrollador:** Equipo Antigravity (Powered by Google Deepmind)
**Objetivo:** Transformar panaderías locales en negocios rentables y tecnológicos.
