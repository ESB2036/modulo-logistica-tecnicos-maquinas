CREATE DATABASE bd_recrea_sys;
USE bd_recrea_sys;
-- DROP DATABASE bd_recrea_sys;

-- Tabla: usuario
CREATE TABLE usuario (
    ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
    ci CHAR(10) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL,
    usuario_asignado VARCHAR(25) NOT NULL DEFAULT 'Aun no tiene',
    contrasena VARCHAR(255) NOT NULL DEFAULT 'Aun no tiene',
    estado VARCHAR(40) NOT NULL DEFAULT 'Pendiente de asignacion'
);

CREATE TABLE inicio_sesion (
    ID_Inicio_Sesion INT AUTO_INCREMENT PRIMARY KEY,
    ID_Usuario INT NOT NULL,
    usuario_asignado VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_sesion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- Tabla para técnicos (extiende de Usuario):
CREATE TABLE Tecnico(
    ID_Tecnico INT PRIMARY KEY,
    Especialidad ENUM('Ensamblador', 'Comprobador', 'Mantenimiento') NOT NULL,
    Cantidad_Actividades INT DEFAULT 0 NOT NULL,
    FOREIGN KEY (ID_Tecnico) REFERENCES usuario(ID_Usuario)
);

-- Tabla para logística (extiende de Usuario):
CREATE TABLE Logistica(
    ID_Logistica INT PRIMARY KEY,
    FOREIGN KEY (ID_Logistica) REFERENCES usuario(ID_Usuario)
);

--  HISTORIAL DE ACTIVIDADES
CREATE TABLE historial_actividades (
    ID_Historial_Actividades INT AUTO_INCREMENT PRIMARY KEY,
    ID_Usuario INT NOT NULL,
    descripcion TEXT DEFAULT 'Estuvo en su main',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- Tabla de para comercios:
CREATE TABLE Comercio(
    ID_Comercio INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL UNIQUE,
    Tipo ENUM('Minorista', 'Mayorista') NOT NULL,
    Direccion TEXT NOT NULL,
    Telefono VARCHAR(15) NOT NULL UNIQUE,
    Cantidad_Maquinas INT DEFAULT 0 NOT NULL,
    Fecha_Registro DATE NOT NULL
);

-- Tabla para máquinas recreativas:
CREATE TABLE MaquinaRecreativa(
    ID_Maquina INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Maquina VARCHAR(100) NOT NULL,
    Tipo VARCHAR(50) NOT NULL,
    Etapa ENUM('Montaje', 'Distribucion', 'Recaudacion') DEFAULT 'Montaje' NOT NULL,
    Estado ENUM('Ensamblandose', 'Comprobandose', 'Reensamblandose', 'Distribuyendose', 'Operativa', 'No operativa', 'Retirada') DEFAULT 'Ensamblándose' NOT NULL,
    Fecha_Registro DATE NOT NULL,
    ID_Tecnico_Ensamblador INT NOT NULL,
    ID_Tecnico_Comprobador INT NOT NULL,
    ID_Comercio INT NOT NULL,
    ID_Tecnico_Mantenimiento INT,
    FOREIGN KEY (ID_Tecnico_Ensamblador) REFERENCES Tecnico(ID_Tecnico),
    FOREIGN KEY (ID_Tecnico_Comprobador) REFERENCES Tecnico(ID_Tecnico),
    FOREIGN KEY (ID_Comercio) REFERENCES Comercio(ID_Comercio),
    FOREIGN KEY (ID_Tecnico_Mantenimiento) REFERENCES Tecnico(ID_Tecnico)
);

-- Tabla para notificaciones que siguen el ciclo de vida de las máquinas recreativas:
CREATE TABLE NotificacionMaquinaRecreativa(
    ID_Notificacion INT AUTO_INCREMENT PRIMARY KEY,
    ID_Remitente INT NOT NULL,
    ID_Destinatario INT NOT NULL,
    ID_Maquina INT NOT NULL,
    Tipo ENUM( -- Solo existen estas 7 notificaciones para el flujo de máquinas recreativas. 
        'Nuevo montaje',
        'Comprobar maquina recreativa',
        'Reensamblar maquina recreativa',
        'Distribuir maquina recreativa',
        'Dar mantenimiento a maquina recreativa',
        'Maquina recreativa retirada',
        'Maquina recreativa reparada'
    ) NOT NULL,
    Mensaje TEXT,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Estado ENUM('Leido', 'No leido') DEFAULT 'No leido' NOT NULL,
    FOREIGN KEY (ID_Remitente) REFERENCES Usuario(ID_Usuario),
    FOREIGN KEY (ID_Destinatario) REFERENCES Usuario(ID_Usuario),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

CREATE INDEX idx_notificacion_maquina_estado ON NotificacionMaquinaRecreativa(Estado);

-- Tabla: proveedor -- Fecha de contratación es igual a fecha de creacion
CREATE TABLE proveedor (
    ID_Proveedor INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
	ci_usuario CHAR(10) NOT NULL,
    celular VARCHAR(10) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    producto VARCHAR(255) NOT NULL,
    usuario_asignado VARCHAR(25) NOT NULL DEFAULT 'Aun no tiene',
    contrasena VARCHAR(255) NOT NULL DEFAULT 'Aun no tiene',
    estado VARCHAR(40) NOT NULL DEFAULT 'Pendiente de asignacion',
    fecha_contratacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
);

-- Tabla: componente
CREATE TABLE componente (
    ID_Componente INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ID_Proveedor INT NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    estado VARCHAR(15) NOT NULL,
    FOREIGN KEY (ID_Proveedor) REFERENCES proveedor(ID_Proveedor)
);

-- Tabla: informe
CREATE TABLE informe (
    ID_Informe INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    CI_Usuario CHAR(10) NOT NULL,
    ID_Maquina INT NOT NULL,
    fecha_hora_inicio DATETIME NOT NULL,
    fecha_hora_fin DATETIME NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    observaciones TEXT NOT NULL,
    tipo VARCHAR(15) NOT NULL,
--    FOREIGN KEY (CI_Usuario) REFERENCES usuario(ci),
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

-- Tabla: informe_detalle
CREATE TABLE informe_detalle (
    ID_Informe_Detalle INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ID_Informe INT NOT NULL,
    ID_Componente_Repuesto INT NOT NULL,
    porcentaje_empresa FLOAT NOT NULL,
    porcentaje_comercio FLOAT NOT NULL,
    monto_total_recaudado DECIMAL(10, 2) NOT NULL,
    mensualidad_comercio DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (ID_Informe) REFERENCES informe(ID_Informe),
    FOREIGN KEY (ID_Componente_Repuesto) REFERENCES componente(ID_Componente)
);

CREATE TABLE informe_distribucion (
  ID_Informe_distribucion INT AUTO_INCREMENT PRIMARY KEY,
  CI_Usuario VARCHAR(20),
  ID_Maquina INT,
  fecha_hora_inicio DATETIME,
  fecha_hora_fin DATETIME,
  descripcion TEXT,
  observaciones TEXT,
  tipo VARCHAR(50), -- "Distribución"
  ubicacion_comercio VARCHAR(255)
);

CREATE TABLE informe_detalle_distribucion (
  ID_Informe_Detalle_Distribucion INT AUTO_INCREMENT PRIMARY KEY,
  ID_Informe_distribucion INT,
  estado_montaje VARCHAR(100),
  estado_operativo VARCHAR(100),
  comentarios TEXT,
  FOREIGN KEY (ID_Informe_distribucion) REFERENCES informe_distribucion(ID_Informe_distribucion)
);

CREATE TABLE reporte (
    ID_Reporte INT AUTO_INCREMENT PRIMARY KEY,
    ID_Usuario_Emisor INT NOT NULL,
    ID_Usuario_Destinatario INT,
    fecha_hora DATETIME NOT NULL,
    descripcion TEXT NOT NULL,
    estado VARCHAR(15) NOT NULL,
    FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (ID_Usuario_Destinatario) REFERENCES usuario(ID_Usuario)
);

CREATE TABLE notificaciones (
    ID_Notificaciones INT AUTO_INCREMENT PRIMARY KEY,
    ID_Reporte INT NOT NULL,
    ID_Usuario INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    mensaje TEXT NOT NULL,
    FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

CREATE TABLE comentario (
    ID_Comentario INT AUTO_INCREMENT PRIMARY KEY,
    ID_Reporte INT NOT NULL,
    ID_Usuario_Emisor INT NOT NULL, 
    fecha_hora DATETIME NOT NULL,
    comentario TEXT NOT NULL,
    FOREIGN KEY (ID_Reporte) REFERENCES reporte(ID_Reporte),
    FOREIGN KEY (ID_Usuario_Emisor) REFERENCES usuario(ID_Usuario)
);

-- Tabla: recaudaciones 
CREATE TABLE recaudaciones (
    ID_Recaudacion INT AUTO_INCREMENT PRIMARY KEY,
    ID_Usuario INT NOT NULL,
--    ci_usuario CHAR(10) NOT NULL,
    fecha DATETIME NOT NULL,
    detalle TEXT NOT NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);

-- Tabla: distribuciones
-- DROP TABLE distribuciones;
CREATE TABLE distribucion (
    id_informe INT AUTO_INCREMENT PRIMARY KEY,
    id_maquina INT NOT NULL,
    id_usuario_logistica INT NOT NULL,
    id_comercio INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    estado VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_maquina) REFERENCES MaquinaRecreativa(ID_Maquina),
    FOREIGN KEY (id_usuario_logistica) REFERENCES usuario(ID_Usuario),
    FOREIGN KEY (id_comercio) REFERENCES Comercio(ID_Comercio)
);

-- Tabla: montajes
CREATE TABLE montajes (
    ID_Montaje INT AUTO_INCREMENT PRIMARY KEY,
    ID_Usuario INT NOT NULL,
 --   ci_usuario CHAR(10) NOT NULL,
    fecha DATETIME NOT NULL,
    detalle TEXT NOT NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario)
);
CREATE TABLE reparaciones (
    ID_Reparaciones INT AUTO_INCREMENT PRIMARY KEY,
    ID_Maquina INT NOT NULL,
    fecha DATE NOT NULL,
    descripcion TEXT NOT NULL,
    tecnico VARCHAR(100), -- opcional
    FOREIGN KEY (ID_Maquina) REFERENCES MaquinaRecreativa(ID_Maquina)
);

-- USE recrea_sys;
-- Consultas
SELECT * FROM usuario;
SELECT * FROM inicio_sesion;
-- USE recrea_sys;
-- Validaciones
SELECT ci, usuario_asignado 
FROM usuario
WHERE usuario_asignado = @usuario_asignado AND contrasena = @contrasena;
-- Obtener todos los datos del usuario por ID
SELECT * FROM usuario WHERE ID_Usuario = @id_usuario;

-- Verificar existencia por CI o Email (opcional)
SELECT ci, email FROM usuario WHERE ci = @ci OR email = @correo;

-- Obtener información básica para login por usuario_asignado
SELECT ID_Usuario, ci, nombre, apellido, contrasena, tipo, usuario_asignado
FROM usuario
WHERE usuario_asignado = @usuario_asignado;

-- Actualizar datos completos del usuario
UPDATE usuario 
SET nombre = @nuevo_nombre,
    apellido = @nuevo_apellido,
    email = @nuevo_email,
    tipo = @nueva_profesion,
    usuario_asignado = @nuevo_usuario_asignado,
    estado = @nuevo_estado
WHERE ID_Usuario = @id_usuario;

-- Actualizar estado solamente
UPDATE usuario 
SET estado = @nuevo_estado
WHERE ID_Usuario = @id_usuario;

-- Actualizar contraseña
UPDATE usuario 
SET contrasena = @nueva_contrasena
WHERE ID_Usuario = @id_usuario;

-- Obtener datos de perfil del usuario
SELECT nombre, apellido, email, tipo, estado, usuario_asignado
FROM usuario 
WHERE ID_Usuario = @id_usuario;
USE recrea_sys;
SELECT ID_Usuario, ci, nombre, apellido, email, tipo, estado, usuario_asignado
FROM usuario
WHERE ID_Usuario = @id_usuario;

SELECT nombre, apellido, email, tipo, estado, usuario_asignado
FROM usuario 
WHERE ci = @ci;
-- Consultar historial de recaudaciones por ID
SELECT fecha, detalle 
FROM recaudaciones 
WHERE ID_Usuario = @id_usuario;

-- Consultar historial de montajes por ID
SELECT fecha, detalle 
FROM montajes 
WHERE ID_Usuario = @id_usuario;

-- Consultar historial de distribuciones por ID
SELECT fecha, detalle 
FROM distribuciones 
WHERE ID_Usuario = @id_usuario;
-- USE recrea_sys;
SELECT 
  i.ID_Informe, 
  u.ci AS CI_Usuario,
  i.ID_Maquina, 
  i.fecha_hora_inicio, 
  i.fecha_hora_fin, 
  i.descripcion, 
  i.observaciones,
  d.porcentaje_empresa, 
  d.porcentaje_comercio, 
  d.monto_total_recaudado, 
  d.mensualidad_comercio
FROM informe i
LEFT JOIN informe_detalle d 
  ON i.ID_Informe = d.ID_Informe
INNER JOIN usuario u 
  ON i.CI_Usuario = u.ci
WHERE i.tipo = 'Recaudacion'
ORDER BY i.fecha_hora_fin DESC;
-- USE recrea_sys;
SELECT ci, nombre, apellido FROM usuario WHERE ID_Usuario = @id_usuario;

SELECT tipo, estado FROM usuario WHERE ID_Usuario = @id_usuario;
SELECT ci, nombre, apellido, email, tipo, estado, usuario_asignado FROM usuario WHERE ID_Usuario = @id_usuario;

SELECT i.ID_Informe, u.nombre, u.apellido, i.ID_Maquina, i.fecha_hora_fin
FROM informe i
INNER JOIN usuario u ON i.CI_Usuario = u.ci
WHERE u.ID_Usuario = @id_usuario
AND i.tipo = 'Recaudacion';

SELECT COUNT(*) AS total_recaudaciones
FROM informe i
INNER JOIN usuario u ON i.CI_Usuario = u.ci
WHERE u.ID_Usuario = @id_usuario
AND i.tipo = 'Recaudacion';

SELECT id_maquina, id_comercio, fecha_inicio, fecha_fin, estado
        FROM distribucion
        WHERE id_usuario_logistica = @id_usuario
        ORDER BY fecha_inicio DESC
        LIMIT 1;
SELECT r.ID_Reporte, r.descripcion, r.fecha_hora, r.estado,
       ue.nombre AS emisor_nombre, ue.apellido AS emisor_apellido,
       ud.nombre AS destinatario_nombre, ud.apellido AS destinatario_apellido, ud.tipo, ud.email
FROM reporte r
JOIN usuario ue ON ue.ID_Usuario = r.ID_Usuario_Emisor
JOIN usuario ud ON ud.ID_Usuario = r.ID_Usuario_Destinatario
WHERE r.ID_Usuario_Emisor = @ID_Usuario OR r.ID_Usuario_Destinatario = @ID_Usuario
ORDER BY r.fecha_hora DESC;
        
-- Insertar usuarios
INSERT INTO usuario (nombre, apellido, ci, email, contrasena, tipo, usuario_asignado, estado)
VALUES ('Admin Principal', 'a', '1122334455', 'admin@admin.com', 'jean123', 'Administrador del sistema', 'admin1', 'Activo');
INSERT INTO usuario (nombre, apellido, ci, email, contrasena, tipo, usuario_asignado, estado)
VALUES     ('Usuario Operador', 'b', '0122334455', '123@gmail.com', '123', 'Técnico', 'operador1', 'Activo');
SELECT ID_Usuario, nombre, apellido, email 
FROM usuario 
WHERE tipo = 'Administrador del sistema' AND estado = 'Activo'
ORDER BY nombre ASC;
