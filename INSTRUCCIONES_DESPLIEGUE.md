# Guía de Despliegue - Sistema "La Vicky"

Esta guía detalla los pasos para publicar el sistema "La Vicky" en un servidor de hosting web (como cPanel, VPS o hosting compartido con PHP y MySQL).

---

## 1. Copias de Seguridad y Restauración (Local)

Si estás trabajando en local y necesitas restaurar los archivos originales modificados durante esta preparación:
* **Base de datos original**: Se encuentra respaldada en el archivo `schema_dump.sql` de la raíz del proyecto.
* **Archivos PHP de configuración y lógica**:
  * Respaldado `config/database.php` en: [database.php.bak](file:///c:/xampp/htdocs/la-vicky-sistema/config/database.php.bak)
  * Respaldado `backend/Controllers/CmmiController.php` en: [CmmiController.php.bak](file:///c:/xampp/htdocs/la-vicky-sistema/backend/Controllers/CmmiController.php.bak)
  * *Para restaurar cualquiera de estos en local, simplemente borra el archivo modificado y renombra el correspondiente `.bak` quitándole la extensión `.bak`*.

---

## 2. Configuración de la Base de Datos en el Hosting

1. **Crear una Base de Datos**:
   * Entra a tu panel de hosting (ej. cPanel).
   * Ve a **Bases de datos MySQL** o **Asistente de bases de datos MySQL**.
   * Crea una nueva base de datos (anota el nombre completo, ej. `usuario_lavicky`).
2. **Crear un Usuario de Base de Datos**:
   * En la misma sección, crea un nuevo usuario de base de datos con una contraseña segura.
   * Anota el usuario completo (ej. `usuario_adminvicky`) y la contraseña.
3. **Asociar Usuario a la Base de Datos**:
   * Añade el usuario a la base de datos recién creada.
   * Asígnale **Todos los privilegios** (All Privileges) y guarda los cambios.
4. **Importar el Esquema SQL**:
   * Entra a **phpMyAdmin** desde tu hosting.
   * Selecciona la base de datos que creaste.
   * Ve a la pestaña **Importar** (Import).
   * Haz clic en *Seleccionar archivo* y elige el archivo `schema_dump.sql` ubicado en la raíz del proyecto.
   * Haz clic en **Importar** (o *Continuar*) al final de la página. Esto creará todas las tablas del sistema.

---

## 3. Subir los Archivos al Servidor

1. **Comprimir los archivos**: Comprime todo el contenido de la carpeta `la-vicky-sistema` en un archivo `.zip`.
2. **Subir al servidor**:
   * En el panel de control de tu hosting, abre el **Administrador de Archivos**.
   * Dirígete a la carpeta raíz de tu dominio (usualmente `public_html`).
   * Sube el archivo `.zip`.
   * Extrae el contenido del archivo `.zip` en ese directorio.
   * *Alternativamente, puedes usar un cliente FTP (como FileZilla) para subir los archivos individualmente.*

---

## 4. Configurar las Variables de Entorno en Producción

1. **Crear el archivo `.env`**:
   * En el Administrador de Archivos de tu hosting, entra a la carpeta raíz donde subiste el sistema.
   * Crea un nuevo archivo llamado exactamente `.env`.
   * Copia el contenido de `.env.example` en él y configúralo con los datos de producción:
     ```env
     # Configuración de Base de Datos del Hosting
     DB_HOST=localhost       # En la mayoría de hostings compartidos es localhost
     DB_NAME=usuario_lavicky # Reemplazar con el nombre completo creado
     DB_USER=usuario_adminvicky # Reemplazar con el usuario creado
     DB_PASS=mi_password_seguro # Reemplazar con la contraseña del usuario

     # Entorno del sistema (production oculta los errores al usuario)
     APP_ENV=production

     # Ruta de mysqldump en hosting Linux (opcional, en cPanel dejar vacío ya que se detecta del PATH)
     MYSQLDUMP_PATH=
     ```
2. Guardar los cambios.

---

## 5. Medidas de Seguridad Críticas en Producción

Para mitigar riesgos de seguridad al exponer el sistema a Internet:
1. **Eliminar Archivos de Diagnóstico y Desarrollo**:
   * Después de comprobar que puedes ingresar correctamente por primera vez, **elimina** de tu servidor los siguientes archivos de la raíz por seguridad:
     * `reset_admin.php` (Evita que otros puedan cambiar la contraseña de administración a 'admin123').
     * `check_users.php` (Evita que otros puedan listar tus usuarios por HTTP).
     * `db_check.php` y `db_check_out.txt`
     * `read_logs.php`
     * `lint.php` y `lint_results.txt`
     * Cualquier archivo de actualización como `update_*.php` una vez que hayan sido ejecutados localmente.
     * `schema_dump.sql` y `db_schema_usuarios.txt`
2. **Verificar los Archivos `.htaccess`**:
   * Asegúrate de que los archivos `.htaccess` creados se subieron correctamente en:
     * La raíz del proyecto (bloquea el listado de carpetas y acceso a archivos sensibles).
     * La carpeta `config/` (deniega acceso HTTP completo).
     * La carpeta `backend/` y sus subcarpetas (deniega acceso a lógica y modelos, permitiendo solo a `CmmiController.php` y `api.php`).
3. **Cambiar la Contraseña por Defecto**:
   * Inicia sesión en el sistema usando el dominio o enlace.
   * Las credenciales por defecto son:
     * **Usuario**: `admin@lavicky.com`
     * **Contraseña**: `admin123`
   * Ve al módulo de configuración/usuarios y **cambia inmediatamente la contraseña** por una fuerte.
