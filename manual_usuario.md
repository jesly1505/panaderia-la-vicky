# Manual de Usuario - Panadería "La Vicky"

Bienvenido al Manual de Usuario del Sistema de Gestión de la Panadería "La Vicky". Este documento sirve como guía definitiva para el personal administrativo y operativo sobre cómo utilizar cada una de las herramientas del sistema.

---

## 1. Introducción
El sistema "La Vicky" es una plataforma integral diseñada para optimizar los procesos de producción, ventas e inventario de la panadería. Basado en una arquitectura moderna y responsive, permite el acceso desde dispositivos móviles y computadoras de escritorio.

### Objetivos Clave:
*   Control total sobre el **inventario** de materias primas.
*   Automatización del **descuento de insumos** tras la producción.
*   Gestión centralizada de **pedidos y ventas**.
*   Generación de **reportes financieros** en tiempo real.

---

## 2. Acceso al Sistema
Para ingresar al sistema, el usuario debe contar con credenciales activas (correo electrónico y contraseña).

1.  Navegue a la URL del sistema.
2.  Ingrese su correo institucional.
3.  Introduzca su contraseña segura.
4.  Haga clic en **"Iniciar Sesión"**.

> [!NOTE]
> Dependiendo de su rol (Administrador o Cajero), algunas opciones del menú podrían estar restringidas.

---

## 3. Módulos del Sistema

### 3.1. Dashboard (Panel Principal)
Es la primera pantalla al entrar. Proporciona una visión general rápida:
*   **Total de Ventas Mensuales**: Gráfico interactivo.
*   **Pedidos Pendientes**: Cantidad de encargos por entregar.
*   **Alertas de Inventario**: Aviso sobre insumos con stock bajo.

### 3.2. Inventario (Insumos)
Aquí se gestionan las materias primas (harina, azúcar, gas, etc.).
*   **Ver Stock**: Lista detallada de existencias.
*   **Añadir Insumo**: Botón para registrar nuevas materias primas.
*   **Editar/Eliminar**: Permite corregir nombres o medidas (Kg, Lt, Unid).

### 3.3. Catálogo de Productos
Se refiere a lo que la panadería vende al público (Pan de Bono, Baguette, etc.).
*   **Recetas**: Cada producto está ligado a una "receta" que descuenta automáticamente insumos del inventario cuando se registra producción.

### 3.4. Producción Manual
Módulo crítico donde se registra el trabajo de horneado diario.
1.  Seleccione el producto horneado.
2.  Ingrese la cantidad producida.
3.  El sistema calculará automáticamente cuánto insumo se debe descontar.
4.  Haga clic en **"Registrar Producción"**.

### 3.5. Pedidos
Para gestionar encargos especiales de clientes.
*   **Estados**: Pendiente, En Preparación, Listo, Entregado.
*   **Abonos**: Permite registrar pagos parciales.

### 3.6. Ventas
Registro de transacciones rápidas en mostrador.
*   Selección de productos vía menú rápido.
*   Cálculo automático de total e impuestos (si aplica).
*   Generación de factura electrónica o recibo.

---

## 4. Guía de Procesos Clave

### Cómo registrar una venta diaria:
1.  Diríjase al módulo de **Ventas**.
2.  Seleccione los productos que el cliente solicita.
3.  Haga clic en **"Finalizar Venta"**.
4.  Seleccione el método de pago y entregue el recibo.

### Cómo actualizar el inventario (Compra de materia prima):
1.  Vaya a **Inventario**.
2.  Ubique el insumo comprado.
3.  Use la opción **"Editar"** o **"Ajustar Stock"**.
4.  Sume la cantidad adquirida al saldo actual.

---

## 5. Configuración y Personal
Reservado para administradores.
*   **Moneda**: Cambio entre Córdoba y Dólar.
*   **Gestión de Empleados**: Crear nuevos usuarios y asignar roles.
*   **Perfil del Negocio**: Ajustar nombre, dirección y logo en facturas.

---

## Soporte y Mantenimiento
Si experimenta errores de sistema ("500 Internal Server Error" o problemas de base de datos), contacte al desarrollador técnico.

> [!TIP]
> Realice respaldos periódicos de la base de datos desde la herramienta del servidor (CPanel/MySQL) para evitar pérdida de información.
