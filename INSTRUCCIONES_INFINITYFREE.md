# Guía de Despliegue en InfinityFree - "La Vicky"

Esta guía explica paso a paso cómo subir y configurar el sistema "La Vicky" en el hosting gratuito **InfinityFree**.

---

## 1. Obtener Datos del Panel de InfinityFree

Inicia sesión en tu cuenta de **InfinityFree** y entra al panel de tu cuenta (Account Details). Necesitarás anotar la siguiente información para la base de datos y la conexión FTP:

* **MySQL Hostname**: Host del servidor de base de datos (ej. `sql302.infinityfree.com`).
* **MySQL Username**: Nombre de usuario principal (ej. `if0_38123456`).
* **MySQL Password**: Contraseña principal de la cuenta (en Account Details haz clic en *Show/Hide Password*).
* **FTP Hostname**: Host de conexión FTP (ej. `ftpupload.net`).
* **FTP Username**: Usuario de FTP (suele ser el mismo usuario principal `if0_38123456`).

---

## 2. Crear la Base de Datos e Importar Tablas

1. Ve al panel de control (**Control Panel**) de tu cuenta de InfinityFree (ej. cPanel/VistaPanel).
2. Haz clic en **MySQL Databases**.
3. En **Create New Database**, introduce un nombre identificador (ej. `la_vicky_db`) y haz clic en **Create Database**.
4. Verás la nueva base de datos creada en la lista (ej. `if0_38123456_la_vicky_db`). Anota el nombre completo.
5. Al lado de la base de datos, haz clic en **Admin** para abrir **phpMyAdmin**.
6. Selecciona tu base de datos de la lista de la izquierda.
7. Ve a la pestaña **Import** (Importar).
8. Haz clic en *Seleccionar archivo* y elige el archivo `schema_dump.sql` (que se encuentra en la raíz del proyecto).
9. Haz clic en **Go** (o *Continuar*) al final de la página para crear todas las tablas del sistema.

---

## 3. Preparar y Subir los Archivos

> [!IMPORTANT]
> En InfinityFree, la carpeta pública de tu sitio es **`htdocs/`**. Todos los archivos deben estar colocados dentro de esa carpeta en el servidor. Si subes los archivos fuera de ella, la página mostrará un error.

### Método A: Usando FileZilla (FTP) - Recomendado
1. Abre FileZilla y conéctate usando tus datos de FTP:
   * **Servidor**: `ftpupload.net`
   * **Usuario**: Tu usuario `if0_XXXXXXXX`
   * **Contraseña**: Tu contraseña de la cuenta
   * **Puerto**: `21`
2. En el panel derecho (servidor remoto), entra a la carpeta **`htdocs`**.
3. En el panel izquierdo (servidor local), selecciona todos los archivos y carpetas del proyecto **excepto** la carpeta `dev_tools` (por seguridad).
4. Arrastra los archivos locales hacia el panel derecho dentro de `htdocs`.

### Método B: Usando el Administrador de Archivos Web
1. Comprime todos los archivos del proyecto (a excepción de la carpeta `dev_tools/` que debe quedarse en local o borrarse) en un archivo `.zip`.
2. En el panel de control de InfinityFree, entra a **Online File Manager** (Monsta FTP).
3. Entra a la carpeta **`htdocs`**.
4. Haz clic en **Upload** (Subir) y selecciona el archivo `.zip`.
5. Una vez subido, haz clic derecho sobre el archivo `.zip` y selecciona **Extract** (Extraer).

---

## 4. Configurar las Variables de Entorno en el Servidor

1. Una vez subidos todos los archivos a la carpeta `htdocs`, busca el archivo llamado `.env.infinityfree`.
2. Renombra el archivo a **`.env`** (quítale la extensión `.infinityfree`).
3. Abre el archivo y edita las credenciales con los datos reales que anotaste en el **Paso 1**:
   ```env
   DB_HOST=sqlXXX.infinityfree.com
   DB_NAME=if0_XXXXXXXX_la_vicky_db
   DB_USER=if0_XXXXXXXX
   DB_PASS=tu_contrasena_de_panel
   APP_ENV=production
   MYSQLDUMP_PATH=
   ```
4. Guarda y cierra el archivo.

---

## 5. Acceder al Sistema e Iniciar Sesión

1. Abre tu navegador y escribe la URL de tu sitio provista por InfinityFree (ej. `http://tusitio.infinityfreeapp.com`).
2. El sistema te redireccionará automáticamente a la pantalla de Login en `frontend/login.php`.
3. Inicia sesión con las credenciales por defecto:
   * **Usuario**: `admin@lavicky.com`
   * **Contraseña**: `admin123`
4. **Importante**: Ve a la sección de Configuración/Usuarios y cambia la contraseña de administrador por una contraseña fuerte.
