# Panaderia La Vicky - ERP de Gestion Integral

Sistema de gestion empresarial (ERP) disenado para panaderias artesanales, enfocado en control de inventarios, recetas digitales, produccion manual y ventas.

## Modulos del Sistema

- **Inventario Inteligente:** Control de stock con alertas de stock minimo y compras a proveedores.
- **Recetario Digital:** Vinculacion de productos finales con sus insumos necesarios.
- **Produccion Manual:** Registro de horneadas con descuento automatico de inventario.
- **Punto de Venta (PV):** Registro de ventas directas y gestion de pedidos.
- **Reporteria Financiera:** Calculo de ganancias netas descontando costo real de produccion.
- **Gestion de Usuarios y Auditoria:** Roles (Administrador, Cajero, Panadero), bitacora y accesos denegados.
- **Configuracion:** Perfil de empresa, empleados, roles/permisos RBAC y respaldo de base de datos.

## Stack Tecnologico

- **Backend:** PHP 8.1+ con arquitectura MVC, PDO, contenedor DI y router centralizado.
- **Base de Datos:** MySQL 8.0 / MariaDB (InnoDB, utf8mb4).
- **Frontend:** HTML5, CSS3, JavaScript Vanilla, Bootstrap 5.3, FontAwesome 6.4.
- **Testing:** PHPUnit 10.5 (34 tests / 62 aserciones).

## Estructura del Repositorio

```
la-vicky-sistema/
├── backend/
│   ├── Controllers/     # Logica de flujo (inyectan dependencias)
│   ├── Models/          # Logica de datos (consultas SQL)
│   ├── Core/            # Contenedor DI, Router, Validator, Money, Security, AuditService, Database
│   │   └── Interfaces/  # Contratos SOLID (InsumoRepositoryInterface, DatabaseInterface)
│   ├── Helpers/         # Utilidades (UnitConverter)
│   ├── Utils/           # Logica de inventario (descuento/reversion de stock)
│   └── api.php          # Punto de entrada unico de la API
├── config/              # Configuracion de BD y variables de entorno (.env)
├── database/
│   ├── schema.sql       # Estructura de todas las tablas
│   ├── seed.sql         # Datos base: roles, usuarios, permisos, empresa
│   ├── seed_dummy.sql   # 50 registros dummy por tabla (proveedores, insumos, productos, clientes, pedidos, gastos)
│   └── migraciones/     # Scripts de migracion versionados
│       └── 001_agregar_eliminado.sql
├── frontend/            # Vistas e interfaz de usuario
│   └── includes/        # sidebar.php, navbar.php, permisos.php, footer.php, header.php
├── assets/              # CSS y JavaScript compartidos
│   ├── css/style.css    # Estilos principales (sidebar, cards, layout)
│   └── js/              # api.js, common.js, auth.js
├── tests/               # Suite PHPUnit
└── dev_tools/           # Scripts de mantenimiento
```

## Instalacion

### Requisitos

- PHP 8.1+
- MySQL 8.0+ o MariaDB 10.5+
- Apache (XAMPP) o PHP built-in server
- Composer

### Pasos

1. **Clonar el repositorio:**
   ```bash
   git clone <url-del-repositorio>
   cd la-vicky-sistema
   ```

2. **Instalar dependencias:**
   ```bash
   composer install
   ```

3. **Configurar base de datos:**
   Copiar `.env.example` como `.env` y editar las credenciales:
   ```
   DB_HOST=localhost
   DB_NAME=la_vicky_db
   DB_USER=root
   DB_PASS=tu_password
   ```

4. **Crear la base de datos y tablas:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

5. **Insertar datos base (roles, usuarios, permisos):**
   ```bash
   mysql -u root -p la_vicky_db < database/seed.sql
   ```

6. **(Opcional) Insertar datos dummy para pruebas:**
   ```bash
   mysql -u root -p la_vicky_db < database/seed_dummy.sql
   ```

7. **(Si la BD ya existe) Ejecutar migraciones pendientes:**
   ```bash
   mysql -u root -p la_vicky_db < database/migraciones/001_agregar_eliminado.sql
   ```

8. **Iniciar el servidor:**
   ```bash
   # Opcion A: Apache via XAMPP
   # Asegurar que Apache y MySQL esten activos en el panel de XAMPP

   # Opcion B: PHP built-in server
   php -S localhost:8000 -t frontend/
   ```

9. **Acceder:**
   Abrir `http://localhost/la-vicky-sistema/` en el navegador.

10. **Credenciales por defecto:**
    | Usuario | Contrasena | Rol |
    |---------|------------|-----|
    | admin@lavicky.com | admin123 | Administrador |
    | cajero@lavicky.com | admin123 | Cajero |
    | panadero.test@lavicky.com | admin123 | Panadero |

    > Cambiar la contrasena despues del primer ingreso.

## Comandos Utiles

```bash
# Instalacion completa (schema + seed base)
mysql -u root -p < database/schema.sql
mysql -u root -p la_vicky_db < database/seed.sql

# Datos dummy para pruebas (50 registros por tabla)
mysql -u root -p la_vicky_db < database/seed_dummy.sql

# Migraciones
mysql -u root -p la_vicky_db < database/migraciones/001_agregar_eliminado.sql

# Testing y calidad
composer install          # Instala dependencias (incluye PHPUnit 10.5)
composer lint             # Valida sintaxis PHP
composer test             # Ejecuta suite PHPUnit (34 tests)
composer check            # lint + test en conjunto
```

## Modelo de Borrado Logico

Las tablas principales utilizan un sistema de borrado logico dual:

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `eliminado` | `tinyint(1) DEFAULT 0` | Campo booleano: `0` = activo, `1` = eliminado |
| `deleted_at` | `datetime DEFAULT NULL` | Timestamp de cuando se elimino |

**Tablas con borrado logico:** `usuarios`, `clientes`, `productos`, `proveedores`, `insumos`, `pedidos`, `gastos`

**Tablas sin borrado logico:** `ventas` (usa estado `completado`/`cancelado`), `roles` (DELETE fisico protegido), tablas de auditoria, registros historicos.

## Sistema RBAC (Roles y Permisos)

25 permisos distribuidos en 3 roles:

| Rol | Permisos | Descripcion |
|-----|----------|-------------|
| Administrador | 25 | Acceso total al sistema |
| Cajero | 9 | Dashboard, productos, clientes, pedidos, ventas, gastos |
| Panadero | 11 | Dashboard, inventario, proveedores, productos, produccion |

**Desarrollador:** Equipo Antigravity
