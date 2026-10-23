/*
* Base de datos Proyecto RepComputer (Edicion Gratuita)
* Author @evilnapsis
*
* 9 tablas: user, contact, device, service, status, operation,
*           operation_detail, payment, configuration.
*/
set names utf8mb4;

create database repcomputer default character set utf8mb4 collate utf8mb4_unicode_ci;
use repcomputer;

create table user (
	id int not null auto_increment primary key,
	username varchar(50),
	name varchar(50),
	lastname varchar(50),
	email varchar(255),
	password varchar(60),
	is_active boolean not null default 1,
	type int not null default 1, /* Esta edicion solo usa el tipo 1 (administrador) */
	created_at datetime
);

insert into user (email,password,type,created_at) value ("admin",sha1(md5("admin")),1,NOW());

create table contact (
	id int not null auto_increment primary key,
	name varchar(50),
	lastname varchar(50),
	email varchar(255),
	address varchar(255),
	phone varchar(255),
	image varchar(255),
	company varchar(255),
	phone2 varchar(255),
	cp varchar(255),
	city varchar(255),
	description text,
	type int default 1, /* Esta edicion solo usa el tipo 1 (cliente) */
	is_active boolean not null default 1,
	created_at datetime not null
);

create table device (
	id int not null auto_increment primary key,
	plate varchar(50), /* Folio / ID de Registro */
	vin varchar(50), /* Numero de Serie (S/N) */
	brand varchar(50),
	model varchar(50),
	year int,
	color varchar(50),
	type varchar(50), /* Smartphone, Tablet, Consola de Videojuegos, Laptop, PC Escritorio, Linea Blanca, etc. */
	imei varchar(100), /* Opcional */
	mac_address varchar(100),
	capacity varchar(50), /* Capacidad / Almacenamiento (ej. 128 GB, 1 TB) */
	hours_meter int default 0,
	contact_id int,
	description text,
	created_at datetime,
	foreign key (contact_id) references contact(id)
);

create table service (
	id int not null auto_increment primary key,
	name varchar(255),
	description text,
	price float,
	is_active boolean not null default 1
);

create table status (
	id int not null auto_increment primary key,
	name varchar(50),
	color varchar(20)
);

/* Estados de orden por defecto (fijos en esta edicion) */
insert into status (name, color) values ('Recibido', '#6c757d'), ('En Diagnóstico', '#17a2b8'), ('En Reparación', '#007bff'), ('Esperando Refacciones', '#ffc107'), ('Terminado', '#28a745'), ('Entregado', '#6610f2');

create table operation (
	id int not null auto_increment primary key,
	description text,
	device_id int,
	contact_id int,
	user_id int,
	status_id int,
	start_date date,
	end_date date,
	total float default 0,
	created_at datetime,
	foreign key (device_id) references device(id),
	foreign key (contact_id) references contact(id),
	foreign key (user_id) references user(id),
	foreign key (status_id) references status(id)
);

create table operation_detail (
	id int not null auto_increment primary key,
	operation_id int,
	service_id int,
	quantity int,
	price float,
	discount float default 0,
	foreign key (operation_id) references operation(id),
	foreign key (service_id) references service(id)
);

create table payment (
	id int not null auto_increment primary key,
	operation_id int not null,
	amount float not null default 0,
	method varchar(30) default 'Efectivo', /* Efectivo, Tarjeta, Transferencia, Deposito, Otro */
	reference varchar(100), /* Folio de voucher, transferencia, etc. */
	notes varchar(255),
	user_id int, /* Quien registro el cobro */
	created_at datetime,
	foreign key (operation_id) references operation(id),
	foreign key (user_id) references user(id)
);

create table configuration(
	id int not null auto_increment primary key,
	name varchar(255),
	short varchar(255),
	val text
);

insert into configuration (name,short,val) values
("Nombre del Taller", "workshop_name", "RepComputer"),
("Dirección", "workshop_address", "Av. Siempre Viva 123"),
("Teléfono", "workshop_phone", "555-1234"),
("Persona Responsable", "workshop_manager", "Isaac Newton");

-- ====================================================================
-- DATOS DEMO
-- ====================================================================

-- 1. Usuarios demo (todos administradores en esta edicion)
INSERT INTO user (username, name, lastname, email, password, is_active, type, created_at) VALUES
('roberto', 'Roberto', 'Gómez', 'roberto@repcomputer.com', SHA1(MD5('12345')), 1, 1, NOW()),
('carlos', 'Carlos', 'Mendoza', 'carlos@repcomputer.com', SHA1(MD5('12345')), 1, 1, NOW());

-- 2. Clientes demo
INSERT INTO contact (name, lastname, email, address, phone, company, cp, city, description, type, is_active, created_at) VALUES
('Juan', 'Pérez', 'juan.perez@gmail.com', 'Calle Hidalgo 45', '555-0101', 'Particular', '06000', 'Ciudad de México', 'Cliente preferencial', 1, 1, NOW()),
('María', 'Rodríguez', 'maria.rod@hotmail.com', 'Av. Reforma 120', '555-0102', 'Tecnología Integral S.A.', '06500', 'Ciudad de México', 'Cliente corporativo', 1, 1, NOW()),
('Alejandro', 'Silva', 'asilva@yahoo.com', 'Calle Morelos 88', '555-0103', 'Particular', '64000', 'Monterrey', 'Cliente frecuente', 1, 1, NOW()),
('Laura', 'Benítez', 'lbenitez@outlook.com', 'Av. Universidad 500', '555-0104', 'Particular', '04510', 'Ciudad de México', 'Cliente nuevo', 1, 1, NOW()),
('Gabriel', 'Mendoza', 'gmendoza@gmail.com', 'Calle Juarez 12', '555-0105', 'Particular', '44100', 'Guadalajara', 'Atención prioritaria', 1, 1, NOW()),
('Servicios Digitales', 'S.A. de C.V.', 'contacto@serviciosdigitales.com', 'Av. Insurgentes Sur 1400', '555-0106', 'Servicios Digitales S.A.', '03920', 'Ciudad de México', 'Flotilla de laptops corporativas', 1, 1, NOW());

-- 3. Dispositivos demo
INSERT INTO device (plate, vin, brand, model, year, color, type, imei, mac_address, capacity, hours_meter, contact_id, description, created_at) VALUES
('DEV-1001', 'SN-IP13PRO-8891', 'Apple', 'iPhone 13 Pro', 2022, 'Grafito', 'Smartphone', '356789012345678', 'A4:C3:F0:12:34:56', '256 GB', 0, 1, 'Pantalla estrellada y cambio de módulo de batería', NOW()),
('DEV-1002', 'SN-PS5-908123', 'Sony', 'PlayStation 5', 2021, 'Blanco / Negro', 'Consola de Videojuegos', '', 'B8:27:EB:45:67:89', '825 GB', 450, 2, 'Sobrecalentamiento y apagado aleatorio. Mantenimiento de metal líquido', NOW()),
('DEV-1003', 'SN-MBP16-3321', 'Apple', 'MacBook Pro 16 M1', 2021, 'Gris Espacial', 'Laptop', '', '70:F8:90:AB:CD:EF', '512 GB', 1200, 3, 'Teclado con teclas pegajosas y puerto USB-C flojo', NOW()),
('DEV-1004', 'SN-S22U-7712', 'Samsung', 'Galaxy S22 Ultra', 2022, 'Negro Phantom', 'Smartphone', '359876543210987', 'CC:98:8B:11:22:33', '512 GB', 0, 4, 'Falla en centro de carga (puerto Type-C)', NOW()),
('DEV-1005', 'SN-XBSX-4412', 'Microsoft', 'Xbox Series X', 2020, 'Negro Mate', 'Consola de Videojuegos', '', 'E4:5F:01:88:99:AA', '1 TB', 600, 5, 'Lector de discos no reconoce juegos físicos', NOW()),
('DEV-1006', 'SN-IPAD-5543', 'Apple', 'iPad Pro 11"', 2022, 'Plata', 'Tablet', '', '88:E9:FE:66:55:44', '128 GB', 300, 6, 'Digitalizador táctil no responde en zona inferior', NOW()),
('DEV-1007', 'SN-DELL-8871', 'Dell', 'XPS 15 9520', 2022, 'Plata', 'Laptop', '', '00:1A:2B:3C:4D:5E', '1 TB', 850, 1, 'Reemplazo de pasta térmica y limpieza de ventiladores', NOW()),
('DEV-1008', 'SN-NSW-6612', 'Nintendo', 'Switch OLED', 2021, 'Blanco', 'Consola de Videojuegos', '', '14:91:82:73:64:55', '64 GB', 200, 3, 'Drift en Joy-Con izquierdo y riel dañado', NOW()),
('DEV-1009', 'SN-LG-WASH-11', 'LG', 'Lavadora Inverter 22kg', 2020, 'Gris Acero', 'Línea Blanca', '', '', '22 kg', 0, 4, 'Falla en tarjeta lógica principal / error de centrifugado', NOW()),
('DEV-1010', 'SN-HP-VICT-99', 'HP', 'Victus 16 Gaming', 2023, 'Azul Oscuro', 'Laptop', '', '2C:33:77:88:99:00', '512 GB', 150, 6, 'Formateo e instalación de SSD NVMe adicional', NOW());

-- 4. Catalogo de servicios
INSERT INTO service (name, description, price, is_active) VALUES
('Mantenimiento Preventivo / Limpieza', 'Desensamble completo, limpieza con alcohol isopropílico y cambio de pasta térmica', 650.00, 1),
('Cambio de Pantalla / Módulo Touch', 'Reemplazo de display completo táctil con refacción original u homologada', 1450.00, 1),
('Reparación de Centro de Carga', 'Micro-soldadura de pin de carga Type-C / Lightning / MicroUSB', 550.00, 1),
('Mantenimiento Térmico Consolas', 'Limpieza interna, sustitución de almohadillas térmicas y metal líquido', 850.00, 1),
('Reparación de Tarjeta Madre / Reballing', 'Diagnóstico electrónico a nivel componente y reballing de chip de video/procesador', 2200.00, 1),
('Formateo y Reinstalación de SO', 'Instalación limpia de sistema operativo, drivers y software básico', 450.00, 1),
('Reparación de Fuente de Poder', 'Reparación o reemplazo de fuente interna de alimentación', 950.00, 1);

-- 5. Ordenes de servicio demo
INSERT INTO operation (description, device_id, contact_id, user_id, status_id, start_date, end_date, total, created_at) VALUES
('Cambio de pantalla y módulo de batería para iPhone 13 Pro', 1, 1, 1, 5, '2026-07-15', '2026-07-16', 1450.00, NOW()),
('Mantenimiento térmico de metal líquido y solución a apagados en PS5', 2, 2, 1, 3, '2026-07-20', '2026-07-23', 850.00, NOW()),
('Cambio de centro de carga en Galaxy S22 Ultra', 4, 4, 1, 1, '2026-07-22', '2026-07-22', 550.00, NOW()),
('Reparación de lector Blu-Ray en Xbox Series X', 5, 5, 1, 4, '2026-07-21', '2026-07-24', 950.00, NOW()),
('Cambio de cristal digitalizador en iPad Pro 11"', 6, 6, 1, 3, '2026-07-22', '2026-07-23', 1450.00, NOW()),
('Limpieza profunda y cambio de pasta térmica en Dell XPS 15', 7, 1, 1, 5, '2026-07-18', '2026-07-19', 650.00, NOW()),
('Reparación de Joystick Joy-Con Nintendo Switch OLED', 8, 3, 1, 2, '2026-07-22', '2026-07-22', 550.00, NOW()),
('Reparación de tarjeta lógica en lavadora LG Inverter', 9, 4, 1, 3, '2026-07-21', '2026-07-23', 2200.00, NOW());

-- 6. Servicios cobrados en cada orden
INSERT INTO operation_detail (operation_id, service_id, quantity, price, discount) VALUES
(1, 2, 1, 1450.00, 0),
(2, 4, 1, 850.00, 0),
(3, 3, 1, 550.00, 0),
(4, 7, 1, 950.00, 0),
(5, 2, 1, 1450.00, 0),
(6, 1, 1, 650.00, 0),
(7, 3, 1, 550.00, 0),
(8, 5, 1, 2200.00, 0);

-- 7. Pagos y abonos de las ordenes
--    Orden 1 y 6: liquidadas. Orden 2 y 8: con abonos parciales.
--    Las demas siguen sin cobro registrado.
INSERT INTO payment (operation_id, amount, method, reference, notes, user_id, created_at) VALUES
(1, 1450.00, 'Efectivo', '', 'Pago total a la entrega', 1, NOW()),
(6, 650.00, 'Transferencia', 'SPEI-884512', 'Liquidado por transferencia', 1, NOW()),
(2, 400.00, 'Efectivo', '', 'Anticipo para material', 1, NOW()),
(8, 1000.00, 'Tarjeta', 'VISA-4421', 'Primer abono', 1, NOW()),
(8, 500.00, 'Efectivo', '', 'Segundo abono', 1, NOW());
