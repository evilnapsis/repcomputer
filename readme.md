# RepComputer 💻📱🎮

**RepComputer** es un sistema gratuito de gestión para talleres de servicio técnico: reparación de computadoras, laptops, smartphones, tablets, consolas de videojuegos y línea blanca.

Cubre el ciclo completo de una reparación: recibes el equipo, lo registras a nombre del cliente, llevas el estado de la orden, cobras los servicios y entregas al cliente su orden impresa en PDF.

Construido con arquitectura MVC en PHP 7.4+, enrutamiento limpio, plantillas Twig, generación nativa de PDF e interfaz CoreUI (Bootstrap 5).

---

## 🚀 Qué incluye

### 1. Dispositivos y Equipos (`/devices`)
Catálogo de equipos recibidos, con los datos que realmente importan en electrónica:

* **Folio / ID de Registro** (`plate`) y **Número de Serie S/N** (`vin`)
* **IMEI** (opcional, para celulares y tablets con SIM)
* **Dirección MAC** y **Capacidad / Almacenamiento** (ej. "128 GB", "1 TB")
* **Horas de Uso / Antigüedad**, marca, modelo, año y color
* Tipo de equipo: Smartphone, Tablet, Consola, Laptop, PC de Escritorio, Línea Blanca, Smartwatch
* Cliente propietario y notas de inspección física

### 2. Órdenes de Servicio (`/operations`)
* Recepción de equipos con seguimiento de estado: *Recibido*, *En Diagnóstico*, *En Reparación*, *Esperando Refacciones*, *Terminado* y *Entregado*
* Desglose de servicios cobrados por orden, con cantidad, precio y descuento
* Cálculo automático del total de la orden
* **Pagos y abonos**: registre cobros parciales por orden con forma de pago y referencia; el sistema lleva el total cobrado y el saldo pendiente
* Impresión de la orden en PDF tamaño carta, con el desglose de pagos y el saldo, lista para firma del cliente

### 3. Clientes (`/contacts`)
Directorio de clientes con datos de contacto, empresa, dirección y notas.

### 4. Catálogo de Servicios (`/services`)
Lista de servicios del taller con su precio. Al agregar un servicio a una orden, el precio se precarga automáticamente.

### 5. Usuarios (`/users`)
Alta de los usuarios que operan el sistema. En esta edición **todos los usuarios tienen permisos de administrador**, pensado para el dueño del taller y las personas de su confianza.

### 6. Reportes (`/reports`)
* **Órdenes de Servicio**: filtra por rango de fechas, estado y cliente, mostrando lo facturado, lo cobrado y el saldo de cada orden
* **Ingresos / Pagos**: los cobros realmente registrados en el periodo, con filtro por cliente y forma de pago
* Exportación a PDF de ambos reportes

### 7. Ajustes del Taller (`/settings`)
Nombre del taller, responsable, dirección y teléfono. Estos datos aparecen en el encabezado de las órdenes y los reportes PDF.

---

## 🛠️ Arquitectura y Tecnologías

* **Backend**: PHP 7.4+ (orientado a objetos con autoloader PSR)
* **Plantillas**: Twig 3.x (vistas en `public/`)
* **Enrutador**: FastRoute (URLs limpias)
* **Base de Datos**: MySQL / MariaDB (acceso PDO con consultas preparadas)
* **Frontend**: CoreUI (Bootstrap 5) con estilos propios
* **PDF**: FPDF (generación nativa en servidor)
* **Librerías JS**: DataTables, Select2, SweetAlert2, jQuery

---

## 📁 Estructura del Proyecto

```
repcomputer/
├── assets/                  # Recursos web estáticos (CSS, JS, iconos, vendors)
├── core/
│   ├── app/
│   │   ├── controller/      # Controladores MVC
│   │   ├── model/           # Modelos de datos PDO
│   │   ├── service/         # Servicios de negocio
│   │   ├── routes.php       # Definición de rutas FastRoute
│   │   └── autoload.php     # Autoload del núcleo
│   └── controller/          # Motores del framework (ViewEngine, Req, Session, etc.)
├── public/                  # Plantillas Twig por módulo
├── report/                  # Generadores de PDF (FPDF)
├── schema.sql               # Estructura de tablas y datos demo
├── index.php                # Front Controller y punto de entrada único
└── composer.json            # Dependencias PHP
```

---

## 🌐 Rutas Principales

| Módulo | Ruta URL | Descripción |
| :--- | :--- | :--- |
| **Inicio** | `/home` | Panel con indicadores del taller |
| **Órdenes** | `/operations` | Órdenes de servicio |
| **Nueva Orden** | `/operation/new` | Formulario de recepción |
| **Dispositivos** | `/devices` | Catálogo de equipos |
| **Clientes** | `/contacts` | Directorio de clientes |
| **Servicios** | `/services` | Catálogo de servicios y precios |
| **Reportes** | `/reports` | Órdenes de servicio e ingresos |
| **Usuarios** | `/users` | Usuarios del sistema |
| **Ajustes** | `/settings` | Datos del taller |

---

## 🚀 Instalación

1. **Requisitos**
   * Apache / Nginx con `mod_rewrite` habilitado
   * PHP 7.4 o superior con `pdo_mysql`, `mbstring` y `json` activas
   * MySQL / MariaDB

2. **Base de datos**

   Importa el archivo `schema.sql`, que ya crea la base de datos `repcomputer` con sus tablas y datos de ejemplo:

   ```bash
   mysql -u root -p < schema.sql
   ```

3. **Conexión**

   Ajusta los datos de acceso en `core/controller/Database.php`:

   ```php
   $this->user = "root";
   $this->pass = "";
   $this->host = "localhost";
   $this->ddbb = "repcomputer";
   ```

4. **Dependencias**

   ```bash
   composer install
   ```

5. **Credenciales por defecto**
   * **Usuario**: `admin`
   * **Contraseña**: `admin`

   Cámbiala desde `/profile` en cuanto entres.

---

## ⬆️ RepComputer Pro

Si tu taller necesita más que mano de obra, **RepComputer Pro** agrega:

* **Inventario completo**: refacciones, categorías, stock mínimo, lista de compras y alertas de stock bajo
* **Punto de Venta (POS)** con búsqueda en vivo y ticket térmico de 58 mm
* **Ventas directas de mostrador** y **compras a proveedores** con entrada automática a inventario
* **Asignación de tareas por técnico** con tiempos estimados y áreas de trabajo
* **Roles de usuario**: técnico con su panel de tareas y portal de consulta para el cliente
* **Cotizaciones y presupuestos** con fecha de vencimiento y estatus de aprobación
* **Historial de reparación auditable** y barra de progreso por orden
* **Espacios y bahías de trabajo**, y **estados de orden personalizables**
* **Tu logotipo** en órdenes y reportes PDF
* **Exportación a CSV** y PDF de todos los catálogos
* **Reportes de ventas y compras** además de los de órdenes e ingresos

---
Desarrollado con ❤️ para centros de servicio técnico y reparación electrónica.
