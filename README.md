# 📝 Task API - Sistema de Gestión de Tareas

API REST profesional construida con **PHP 8.5**, **Slim Framework 4**, **MySQL** y autenticación **JWT**.

## 🚀 Características

- ✅ Autenticación con JSON Web Tokens (JWT)
- ✅ CRUD completo de tareas
- ✅ Sistema de usuarios con registro y login
- ✅ Filtros por estado y fecha límite
- ✅ Paginación de resultados
- ✅ Validaciones y manejo de errores
- ✅ Arquitectura MVC limpia
- ✅ Base de datos relacional (MySQL)

## 🛠️ Stack Tecnológico

- **PHP 8.5+**
- **Slim Framework 4** - Microframework para APIs REST
- **Eloquent ORM** - Manejo de base de datos
- **MySQL 9.6** - Base de datos relacional
- **Firebase JWT** - Autenticación con tokens
- **Composer** - Gestión de dependencias

## 📋 Requisitos Previos

- PHP 8.2 o superior
- MySQL 8.0 o superior
- Composer
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`

## 🔧 Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/task-api.git
cd task-api
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
```

Edita `.env` con tus credenciales de MySQL:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=tu_contraseña

JWT_SECRET=tu_clave_secreta_aqui
JWT_EXPIRATION=3600
```

4. **Crear la base de datos**
```bash
mysql -u root -p
```
```sql
CREATE DATABASE task_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_api;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
    due_date DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

5. **Iniciar el servidor**
```bash
php -S localhost:8080 -t public
```

La API estará disponible en `http://localhost:8080`

## 📚 Documentación de la API

### 🔐 Autenticación

#### Registro de Usuario
```http
POST /auth/register
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "123456"
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "123456"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": { ... }
  }
}
```

### ✅ Gestión de Tareas

**Nota:** Todos los endpoints de tareas requieren el header:
```
Authorization: Bearer {tu_token}
```

#### Listar Tareas
```http
GET /tasks
GET /tasks?status=pending
GET /tasks?page=1&per_page=5
GET /tasks?due_date=2026-03-15
```

#### Crear Tarea
```http
POST /tasks
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Completar proyecto",
  "description": "Terminar la documentación",
  "status": "in_progress",
  "due_date": "2026-03-15"
}
```

#### Ver Tarea Específica
```http
GET /tasks/{id}
Authorization: Bearer {token}
```

#### Actualizar Tarea
```http
PUT /tasks/{id}
Content-Type: application/json
Authorization: Bearer {token}

{
  "status": "completed"
}
```

#### Eliminar Tarea
```http
DELETE /tasks/{id}
Authorization: Bearer {token}
```

## 📂 Estructura del Proyecto
```
task-api/
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   └── TaskController.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Task.php
│   ├── Middleware/
│   │   └── AuthMiddleware.php
│   └── Routes/
│       ├── auth.php
│       └── tasks.php
├── public/
│   └── index.php
├── .env
├── .gitignore
├── composer.json
└── README.md
```

## 🔒 Seguridad

- Contraseñas hasheadas con `bcrypt`
- Autenticación stateless con JWT
- Validación de tokens en cada request protegido
- Protección contra inyección SQL con Eloquent ORM
- Validaciones de datos en los controladores

## 🧪 Testing

Puedes probar la API con:
- **Bruno** (recomendado)
- **Postman**
- **cURL**
- **Thunder Client** (VS Code extension)

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea tu rama de features (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT.

## 👤 Autor

**José Antonio Albert Díaz**

---

⭐ Si este proyecto te fue útil, considera darle una estrella en GitHub
