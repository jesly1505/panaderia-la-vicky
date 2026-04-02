# Panadería La Vicky - ERP de Gestión Integral

Este es un sistema de gestión empresarial (ERP) diseñado específicamente para panaderías artesanales, enfocado en el control de inventarios, recetas digitales, producción manual y ventas.

## 🚀 Innovación: Arquitectura SOLID

A diferencia de un sistema administrativo convencional, este repositorio ha sido refactorizado bajo los **5 Principios de Diseño SOLID**, garantizando un código de grado industrial:

1.  **S (Single Responsibility):** Cada clase tiene una única razón para cambiar (Modelos vs Controladores especializados).
2.  **O (Open/Closed):** Sistema modular que permite añadir funcionalidades sin alterar el núcleo.
3.  **L (Liskov Substitution):** Uso de interfaces que aseguran que las implementaciones sean intercambiables.
4.  **I (Interface Segregation):** Contratos específicos para cada módulo (`InsumoRepositoryInterface`, etc.).
5.  **D (Dependency Inversion):** Implementación de **Inyección de Dependencias** para desacoplar la base de datos de la lógica de negocio.

## 🛠️ Tecnologías Utilizadas

*   **Backend:** PHP 8.x con arquitectura **MVC** y **PDO** para seguridad ante Inyecciones SQL.
*   **Base de Datos:** MySQL/MariaDB (Motor InnoDB con soporte para transacciones).
*   **Frontend:** HTML5, CSS3 y JavaScript Vanilla (Diseño Responsivo).
*   **Servidor:** Entorno XAMPP (Apache + MySQL).

## 📦 Módulos del Sistema

*   **Inventario Inteligente:** Control de stock de materia prima con alertas de stock mínimo.
*   **Recetario Digital:** Vinculación de productos finales con sus insumos necesarios.
*   **Producción Manual:** Registro de horneadas con descuento automático de inventario.
*   **Punto de Venta (PV):** Registro de ventas directas y gestión de pedidos con trazabilidad.
*   **Reportería Financiera:** Cálculo de ganancias netas descontando el costo real de producción.

## 📂 Estructura del Repositorio

```text
/backend
  /Controllers  - Lógica de flujo (Inyectan dependencias).
  /Models       - Lógica de datos (Implementan repositorios).
  /Core
    /Interfaces - Contratos SOLID que aseguran escalabilidad.
/config         - Configuración de base de datos e interfaces globales.
/frontend       - Vistas e interfaz de usuario.
/tmp            - Archivos temporales de diagnóstico.
/brain          - Documentación evolutiva del proyecto.
```

## 🔧 Instalación

1.  Clonar el repositorio en `C:\xampp\htdocs\la-vicky-sistema`.
2.  Importar el archivo `schema_dump.sql` en tu MySQL (la_vicky_db).
3.  Asegurarse de que Apache y MySQL estén activos en el panel de XAMPP.
4.  Acceder via `http://localhost/la-vicky-sistema/`.

---
**Desarrollador:** Equipo Antigravity (Powered by Google Deepmind)
**Objetivo:** Transformar panaderías locales en negocios rentables y tecnológicos.
