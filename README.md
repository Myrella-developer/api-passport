# Proyecto de Gestión de Juegos de Dados

## Descripción

Este proyecto es una API RESTful creada con Laravel, diseñada para permitir a los usuarios jugar un juego de dados. Los jugadores pueden registrar sus partidas, consultar estadísticas y eliminar sus juegos. Además, se implementa un sistema de roles para la gestión de usuarios, permitiendo diferentes niveles de acceso.

## Funcionalidades

- **Registro de Usuarios**: Los usuarios pueden registrarse con un email único y un nickname. Si no proporcionan un nombre, se les asigna "Anónimo" por defecto.
- **Juego de Dados**: Los jugadores pueden lanzar dos dados. Si la suma es 7, el juego es considerado ganado; de lo contrario, es perdido.
- **Historial de Juegos**: Los jugadores pueden ver todas sus tiradas, junto con su porcentaje de éxito.
- **Eliminación de Juegos**: Los jugadores pueden eliminar todas sus partidas, pero no juegos individuales.
- **Gestión de Roles**: Un sistema de roles permite que los administradores visualicen todos los jugadores y sus estadísticas.

## Endpoints

### Rutas Abiertas

- `POST /api/register`: Registro de un nuevo usuario.
- `POST /api/login`: Autenticación de usuario.

### Rutas Protegidas

- `POST /api/players/{id}/play`: Jugar un juego de dados.
- `GET /api/players/{id}/games`: Mostrar los juegos del jugador.
- `DELETE /api/players/{id}/games`: Eliminar todos los juegos de un jugador.
- `GET /api/logout`: Cerrar sesión.

### Rutas de Administrador

- `GET /api/players`: Ver todos los jugadores.
- `GET /api/players/ranking`: Obtener el porcentaje de éxito promedio de todos los jugadores.
- `GET /api/players/ranking/loser`: Obtener el jugador con el porcentaje de éxito más bajo.
- `GET /api/players/ranking/winner`: Obtener el jugador con el porcentaje de éxito más alto.

## Instalación
1. **Clona el repositorio**
   
    ```bash
   <https://github.com/Myrella-developer/api-passport.git>

3. **Accede al directorio del proyecto**

    Cambia al directorio del proyecto

    ```bash
    cd api-passport

4. **Instala las dependencias de PHP**

    Ejecuta el siguiente comando para instalar las dependencias de PHP usando Composer:

    ```bash
    composer install

5. **Copia el archivo de entorno**

    Crea un archivo .env a partir del archivo de ejemplo:

    ```bash
    cp .env.example .env

6. **Genera la clave de la aplicación**

    Ejecuta el siguiente comando para generar la clave de tu aplicación:

    ```bash
    php artisan key:generate

7. **Configura la base de datos**

    Asegúrate de configurar tu base de datos en el archivo .env

8. **Ejecuta las migraciones**

    ```bash
    php artisan migrate

9. **Configuración de Passport**
   
    php artisan passport:install
    

10. **Rellena la base de datos con datos de prueba**

    ```bash
    php artisan db:seed

11. **Inicia el servidor de desarrollo**

    ```bash
    php artisan serve

12. **Crea un cliente personal para Passport**

    ```bash
    php artisan passport:client --personal
