# 🌸 Bloom Box Spa

## Descripción General

Bloom Box Spa es una plataforma web desarrollada para la gestión integral de sorteos promocionales, permitiendo el registro de participantes, asignación de premios, control de ganadores y administración completa de campañas promocionales desde una interfaz moderna, segura y responsive.

El sistema fue diseñado para ofrecer una experiencia interactiva mediante un juego de cajas sorpresa, garantizando al mismo tiempo la integridad de los datos, la seguridad de los participantes y la trazabilidad de los premios entregados.

---

## 🎯 Objetivos del Proyecto

* Digitalizar la gestión de sorteos promocionales.
* Centralizar la administración de participantes y premios.
* Automatizar la selección y registro de ganadores.
* Mejorar la experiencia de usuario mediante elementos visuales interactivos.
* Garantizar seguridad en el manejo de la información.

---

## 🚀 Características Principales

### Registro de Participantes

* Registro mediante formulario web.
* Validaciones en frontend y backend.
* Control de registros duplicados por correo electrónico y teléfono.
* Gestión automática de sesiones.

### Juego Interactivo

* Selección visual mediante cajas sorpresa.
* Asignación automática de premios.
* Animaciones de celebración.
* Efectos visuales de confeti, serpentinas y globos.
* Experiencia optimizada para dispositivos móviles y escritorio.

### Gestión de Premios

* Creación de premios desde el panel administrativo.
* Activación e inactivación de premios.
* Edición y actualización de información.
* Control de disponibilidad.

### Gestión de Ganadores

* Registro automático de ganadores.
* Consulta histórica.
* Búsquedas y filtros.
* Exportación de información.

### Panel Administrativo

* Autenticación segura.
* Gestión de participantes.
* Gestión de premios.
* Gestión de ganadores.
* Exportación de reportes.
* Control de acceso por roles.

---

## 🔒 Seguridad Implementada

El proyecto incorpora múltiples mecanismos de protección para garantizar la integridad de la información:

* Protección CSRF.
* Validación de sesiones.
* Control de acceso administrativo.
* Prevención de registros duplicados.
* Validación de datos en servidor.
* Sanitización de entradas.
* Consultas preparadas mediante PDO.
* Cabeceras de seguridad HTTP.
* Restricciones de acceso mediante Apache.
* Compatibilidad con HTTPS y certificados SSL.

---

## 🛠️ Tecnologías Utilizadas

### Backend

* PHP 8.x
* MySQL / MariaDB
* PDO

### Frontend

* HTML5
* CSS3
* JavaScript (Vanilla JS)

### Infraestructura

* Apache
* XAMPP
* DuckDNS
* SSL Let's Encrypt

### Herramientas

* Git
* GitHub
* phpMyAdmin

---

## 🏗️ Arquitectura General

```text
Bloom Box Spa
│
├── Frontend Web
│   ├── Landing Page
│   ├── Registro
│   ├── Juego Interactivo
│   └── Consulta de Ganadores
│
├── Backend PHP
│   ├── Registro de Participantes
│   ├── Gestión de Premios
│   ├── Gestión de Ganadores
│   └── Seguridad
│
├── Base de Datos MySQL
│   ├── participantes
│   ├── premios
│   ├── ganadores
│   ├── clientes
│   └── usuarios administrativos
│
└── Panel Administrativo
    ├── Dashboard
    ├── Participantes
    ├── Premios
    ├── Ganadores
    └── Reportes
```

---

## 📂 Estructura del Proyecto

```text
bloom_box_spa/
│
├── admin/
├── css/
├── js/
├── images/
├── includes/
├── errors/
│
├── index.php
├── registro.php
├── juego.php
├── .htaccess
└── README.md
```

---

## ⚙️ Instalación Local

### Requisitos

* PHP 8.x
* MySQL 8.x o MariaDB
* Apache
* XAMPP (Recomendado)

### Clonar el Repositorio

```bash
git clone https://github.com/Escruceria/bloom_box_spa.git
```

### Configurar Base de Datos

Crear la base de datos:

```sql
CREATE DATABASE bloom_box_spa;
```

Importar el archivo SQL correspondiente y configurar las credenciales de conexión en el archivo de configuración del proyecto.

### Ejecutar la Aplicación

Ubicar el proyecto dentro de:

```text
xampp/htdocs/
```

Iniciar los servicios:

* Apache
* MySQL

Abrir en el navegador:

```text
http://localhost/bloom_box_spa
```

---

## 📊 Funcionalidades Administrativas

* Dashboard con estadísticas generales.
* Gestión de participantes.
* Gestión de premios.
* Gestión de ganadores.
* Exportación de reportes en Excel y CSV.
* Consulta detallada de participantes.
* Control de premios activos e inactivos.

---

## 📱 Compatibilidad

El sistema ha sido diseñado para funcionar correctamente en:

* Google Chrome
* Microsoft Edge
* Mozilla Firefox
* Safari

Dispositivos compatibles:

* Computadores de escritorio
* Portátiles
* Tablets
* Teléfonos móviles

---

## 🔮 Mejoras Futuras

* Notificaciones por correo electrónico.
* Dashboard analítico avanzado.
* Estadísticas en tiempo real.
* Exportación PDF.
* Gestión de múltiples campañas promocionales.
* API REST para integraciones externas.
* Integración con WhatsApp Business.

---

## 👨‍💻 Autor

### Antonio José Escrucería Uribe

Ingeniero de Sistemas especializado en infraestructura tecnológica, bases de datos, desarrollo web, administración de servidores Linux y Windows, redes de datos y soluciones empresariales.

GitHub:
https://github.com/Escruceria

---

## 📄 Licencia

Proyecto desarrollado para uso interno y campañas promocionales de Bloom Box Spa.

© 2026 Todos los derechos reservados.
