# Cursolandia.com

Aplicación web donde los usuarios pueden **crear y tomar cursos**, gratuitos o pagos, públicos o privados. Cada curso tiene su propio foro de preguntas y respuestas, material adjunto y notificaciones.

Trabajo integrador de **Ingeniería de Software & Programación III** — Tecnicatura Universitaria en Web.

- **Autor:** Agustín Gabriel Garcés
- **Página en línea:** https://cursolandia-agus.page.gd

---

## Tecnologías utilizadas 

| Capa | Tecnología |
|---|---|
| Servidor | PHP 8.0 |
| Base de datos | MariaDB 10.4 |
| Front-end | HTML, CSS propio, **Bootstrap** y JavaScript |

**No se usaron** frameworks de frontend ni de backend en el desarrollo del proyecto.

- Desarrollado y probado con **PHP 8.0.30** y **MariaDB 10.4.32** (XAMPP).
- Requiere **PHP 8.0 o superior** y **MySQL 8.0.16+ o MariaDB 10.2.1+**: en versiones anteriores las restricciones `CHECK` se ignoran sin aviso.
- El host InfinityFree utiliza **PHP 8.4** y **MariaDB 11.4.13**

## Instalación en entorno local

### Requisitos
- XAMPP (o equivalente) con Apache, PHP 8.0+ y MariaDB 10.2.1+ / MySQL 8.0.16+
- Git

### Pasos

1. **Clonar el repositorio** dentro de `htdocs`:
   ```bash
   cd C:\xampp\htdocs
   git clone <URL_DEL_REPOSITORIO> CursoLandia
   ```

2. **Iniciar Apache y MySQL** desde el panel de XAMPP.

3. **Crear la base de datos vacía** desde phpMyAdmin (`http://localhost/phpmyadmin`):
   - Nombre: `cursolandia_db`
   - Cotejamiento: `utf8mb4_unicode_ci`

4. **Importar el backup:** seleccionar la base `cursolandia_db`, ir a *Importar* y subir `cursolandia_backup.sql`, que está en la raíz del proyecto. Crea las tablas y carga los datos de prueba.
   El archivo no incluye `CREATE DATABASE`, `CREATE USER` ni `GRANT`, por lo que se puede importar en cualquier servidor sobre una base ya creada.

5. **Configurar la conexión:** copiar `php/config.example.php` como `php/config.php` y completar los datos:
   ```php
   <?php
   return [
       'host'     => 'localhost',
       'port'     => 3306,
       'dbname'   => 'cursolandia_db',
       'user'     => 'root',
       'password' => '',
   ];
   ```
   `php/config.php` está en el `.gitignore` y no se sube al repositorio. En XAMPP, el usuario `root` viene sin contraseña por defecto.

6. **Permisos de `uploads/`:** las carpetas `uploads/comprobantes`, `uploads/cursos`, `uploads/materiales` y `uploads/perfiles` deben poder escribirse. En Windows con XAMPP no requiere cambios. En Linux:
   ```bash
   chmod -R 775 uploads/
   ```

7. **Abrir la aplicación:** http://localhost/CursoLandia/

### Límites de subida de archivos

Para que se puedan subir archivos de 5 MB, en `php.ini` conviene verificar que `upload_max_filesize` y `post_max_size` sean de al menos `10M`.

---

## Usuarios de prueba

Hay un usuario por cada rol y plan existente:

| Rol | Plan | Email | Contraseña |
|---|---|---|---|
| Administrador | Pro | `admin@ejemplo.test` | `admin123` |
| Regular | Pro | `pro@ejemplo.test` | `123456` |
| Regular | Gratuito | `gratis@ejemplo.test` | `654321` |

Estas credenciales son solo para pruebas y viven únicamente en el backup SQL (hasheadas). Se recomienda cambiarlas si el sitio se publica con datos reales.

**Datos de ejemplo incluidos:** 3 cursos (uno gratuito y dos pagos), 10 etiquetas, intereses del usuario gratuito, 3 inscripciones (las de cursos pagos en estado pendiente). Las tablas del foro, notificaciones, materiales y solicitudes Pro parten vacías y se completan al usar el sistema.

# Informe de problemas y soluciones — Cursolandia.com

Acá se encuentran resumidos los principales problemas encontrados durante el desarrollo del proyecto y la forma en que se resolvieron:

**1. Las etiquetas no se guardaban al crear ni editar cursos, ni en el perfil**

Dónde ocurría: en nuevoCurso.php, editarCurso.php y perfil.php.

**Problema:** al seleccionar etiquetas y enviar el formulario, estas no se almacenaban en la base de datos. La selección se realiza mediante ventanas modales con casillas de verificación (checkboxes), pero esos modales estaban ubicados fuera de la etiqueta de formulario principal. Como los campos que quedan fuera de un formulario no se incluyen en el envío, las etiquetas elegidas nunca viajaban por POST y el servidor no recibía los datos que debía procesar.

**Solución:** se movieron los modales de etiquetas para que queden dentro del formulario principal. De esta forma, las casillas seleccionadas se envían junto con el resto de los campos, y el código PHP puede guardarlas correctamente en las tablas curso_etiquetas y usuario_intereses.

**2. Scripts escritos directamente en las páginas en lugar de un archivo JavaScript común**

**Problema:** parte del código JavaScript se había escrito directamente dentro de las páginas PHP, en lugar de centralizarlo en el archivo js/comp.js. Esto dispersaba la lógica del front-end en varios archivos y dificultaba su mantenimiento.

**Solución:** se trasladaron los scripts a js/comp.js, de modo que las páginas solo lo incluyan. Así el código JavaScript queda concentrado en un único lugar, es más fácil de leer y de modificar, y se evita repetir lógica entre páginas.

**3. Activación automática de cursos con fecha de inicio futura**

**Problema:** un curso puede estar inactivo porque su creador lo desactivó manualmente, pero también puede estar inactivo porque todavía no llegó su fecha de inicio. Con la estructura original no había forma de distinguir ambos casos: un curso con activo = 0 podía ser una desactivación voluntaria o uno que debía comenzar más adelante, por lo que no se podía automatizar su activación sin riesgo de reactivar cursos que el creador había desactivado a propósito.

**Solución:** se agregó la columna programado a la tabla cursos. Un curso con activo = 0 y programado = 1 queda a la espera de su fecha de inicio. En conexion.php, que se carga en cada petición, se busca esa combinación y, cuando la fecha de inicio ya llegó, el curso pasa a activo = 1 y programado = 0. Los cursos desactivados manualmente (programado = 0) no se tocan.

Esa misma lógica respeta los límites de cada plan: si el creador es de plan gratuito y ya tiene 3 cursos activos, el curso programado no se activa y pasa a borrador. También se reutiliza cuando vence un plan Pro, para pasar a borrador los cursos que exceden el límite del plan gratuito.


```
Notas

- El proyecto está pensado para ejecutarse tanto en local (XAMPP) como en un hosting compartido (probado en InfinityFree). En el hosting, el host de MySQL no es `localhost` y la base tiene un prefijo de cuenta, por lo que `php/config.php` debe completarse con los datos que indica el panel del proveedor.
- La zona horaria de la aplicación está fijada en `America/Argentina/Buenos_Aires`.
- Los problemas encontrados durante el desarrollo y su resolución están en [INFORME.md](INFORME.md).


