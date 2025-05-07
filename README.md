# Kanaroo Backend API

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)

## 📋 Descripción

Kanaroo Backend es una API RESTful desarrollada con Laravel que sirve como el núcleo del sistema Kanaroo. Esta API proporciona endpoints seguros y robustos para gestionar proyectos, tareas, usuarios y más, implementando las mejores prácticas de desarrollo y patrones de diseño.

> 💡 Este es el repositorio del backend. Si quieres ver el frontend de la aplicación, puedes encontrarlo en [kanaroo-frontend](https://github.com/aarxnmendez/kanaroo-frontend).

## 🚀 Características Principales

-   🔐 Autenticación y Autorización robusta
-   📊 Gestión de Proyectos y Tareas
-   👥 Sistema de Usuarios y Roles
-   🔄 Patrones de Diseño (Repository, Service, Form Request)
-   📝 Documentación de API
-   🧪 Testing automatizado

## 🛠 Tecnologías Utilizadas

-   **Framework Principal:** Laravel 12.x
-   **Base de Datos:** MySQL
-   **Autenticación:** Laravel Sanctum + Laravel Breeze
-   **Testing:** PHPUnit

## 📁 Estructura del Proyecto

```
kanaroo-backend/
├── app/                  # Contiene el código principal de la aplicación
│   ├── Http/             # Controladores, middleware, requests y resources
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/           # Modelos de la base de datos
│   ├── Policies/         # Políticas de autorización
│   ├── Providers/        # Proveedores de servicios
│   └── Repositories/     # Capa de abstracción para el acceso a datos
├── bootstrap/            # Archivos de inicialización de la aplicación
├── config/               # Archivos de configuración
├── database/             # Migraciones y seeders
├── public/               # Punto de entrada y assets públicos
├── resources/
│   └── lang/             # Archivos de idioma y traducciones
├── routes/               # Definición de rutas de la API
├── storage/              # Archivos generados por la aplicación
└── tests/                # Tests automatizados
```

## 📝 Patrones de Diseño Implementados

-   **Repository Pattern:** Para la abstracción de la capa de datos
-   **Service Pattern:** Para la lógica de negocio
-   **Form Request:** Para la validación de datos
-   **Resource:** Para la transformación de datos
-   **Policy:** Para la autorización

## 👥 Autores

-   **Aaron Mendez** - _Desarrollo Inicial_ - [aarxnmendez](https://github.com/aarxnmendez)
