# LTIC
Sistema de Gestión de Préstamos - LTIC

Descripción del Proyecto

Sistema web desarrollado para gestionar el préstamo de equipos de cómputo en el laboratorio LTIC. Permite el control de estudiantes, inventario, préstamos y devoluciones de manera eficiente y organizada.

 Características Principales
 Autenticación segura con roles (Admin/Encargado)

 Dashboard interactivo con estadísticas en tiempo real

 Gestión de estudiantes con matrícula y roles

 Control de inventario de equipos de cómputo

 Sistema de préstamos con responsables y receptores

 Registro de devoluciones con estado del equipo

 Interfaz responsive compatible con móviles

 Reportes y historial completo de actividades

Stack Tecnológico
Backend
Laravel 10.x - Framework PHP

PHP 8.0+ - Lenguaje de programación

MySQL 8.0 - Base de datos relacional

Composer - Gestor de dependencias

Frontend
Bootstrap 5.x - Framework CSS

JavaScript - Interactividad

Font Awesome - Iconografía

Sass - Preprocesador CSS

Herramientas de Desarrollo
VS Code - Editor de código

Git & GitHub - Control de versiones

Laragon/XAMPP - Servidor local

Node.js & NPM - Gestión de paquetes frontend

Requisitos del Sistema
Software Requerido
PHP >= 8.0

Composer >= 2.0

Node.js >= 16.0

Instalación y Configuración:

1. Clonar el Repositorio

git clone https://github.com/tu-usuario/sistema-prestamos.git

cd sistema-prestamos

2. Instalar Dependencias PHP

composer install

4. Instalar Dependencias Frontend

npm install

5. Configurar Variables de Entorno

cp .env.example .env

php artisan key:generate

7. Configurar Base de Datos

Editar archivo .env:

env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_prestamos
DB_USERNAME=root
DB_PASSWORD=
6. Ejecutar Migraciones

php artisan migrate

7. Compilar Assets

npm run build

8. Iniciar Servidor

php artisan serve

Comandos Útiles

Desarrollo

php artisan serve          # Iniciar servidor

npm run dev               # Compilar assets en desarrollo

npm run build             # Compilar assets para producción

Base de Datos

php artisan migrate        # Ejecutar migraciones

php artisan migrate:reset  # Revertir todas las migraciones

php artisan db:seed        # Poblar base de datos

Mantenimiento

php artisan cache:clear    # Limpiar cache

php artisan config:clear   # Limpiar configuraciones

php artisan route:clear    # Limpiar rutas

php artisan view:clear     # Limpiar vistas
