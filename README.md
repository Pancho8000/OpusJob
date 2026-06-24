# PegaTinder (OpusJob)

Proyecto de titulación: Aplicación tipo Tinder para búsqueda de empleo y gestión de reclutamiento.

## Estructura del Proyecto (MVC)

El proyecto sigue el patrón de diseño Modelo-Vista-Controlador (MVC) para asegurar escalabilidad y separación de responsabilidades.

### Directorios

- **public/**: Directorio público accesible desde el navegador.
  - `index.php`: Punto de entrada único de la aplicación.
  - `css/`: Hojas de estilo.
  - `js/`: Scripts de JavaScript.
  - `img/`: Imágenes y assets.
  
- **app/**: Lógica de la aplicación (Backend).
  - `config/`: Archivos de configuración (Base de datos, constantes).
  - `controllers/`: Controladores que manejan las peticiones del usuario.
  - `models/`: Modelos operativos que interactúan con la base de datos (ej. Job, User, Candidate).
  - `views/`: Plantillas HTML para la interfaz de usuario.
  - `core/`: Núcleo del framework (Enrutador, Controlador base).

## Instalación

1. Asegúrate de tener XAMPP/WAMP/LAMP instalado.
2. Clona o coloca este proyecto en `htdocs`.
3. Configura la base de datos ejecutando en orden: `setup_db.php`, `setup_recruiter_db.php`, y `update_users_db.php`.
4. Configura `app/config/config.php` si cambias la URL base.
5. Accede a `http://localhost/pegaTinder`.

## Tecnologías

- PHP (Backend)
- HTML5/CSS3 (Frontend)
- JavaScript (Interacciones)
- MySQL (Base de datos)

## Documentación técnica

- Ver [TECHNICAL.md](file:///c:/xampp/htdocs/pegaTinder/docs/TECHNICAL.md)

## Tests

- Ejecutar: `c:\xampp\php\php.exe c:\xampp\htdocs\pegaTinder\tests\run.php`
