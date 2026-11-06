-- =============================================
-- BASE DE DATOS EGOZ — Boutique Esotérica
-- v5 — Añadido nombre_de y descripcion_de para DeepL
-- =============================================

-- CREATE DATABASE IF NOT EXISTS egoz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE egoz;

-- CATEGORIAS
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PRODUCTOS
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    descripcion TEXT,
    nombre_de VARCHAR(200),
    descripcion_de TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    categoria_id INT,
    imagen VARCHAR(255),
    destacado TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- CLIENTES
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    telefono VARCHAR(50),
    direccion TEXT,
    ciudad VARCHAR(100),
    codigo_postal VARCHAR(20),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PEDIDOS
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente','pagado','enviado','entregado','cancelado') DEFAULT 'pendiente',
    stripe_payment_id VARCHAR(255),
    codigo_referencia VARCHAR(20) UNIQUE,
    tipo_entrega ENUM('recogida','envio') DEFAULT 'recogida',
    notas TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- PEDIDO ITEMS
CREATE TABLE pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    nombre_producto VARCHAR(200),
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- CONTACTO
CREATE TABLE contacto_mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    asunto VARCHAR(200),
    mensaje TEXT NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ADMIN
CREATE TABLE admin_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- NOTIFICACIONES
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('pedido','mensaje','stock','resena') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    leida TINYINT(1) DEFAULT 0,
    referencia_id INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- USUARIOS (cuentas de clientes)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    direccion TEXT,
    ciudad VARCHAR(100),
    codigo_postal VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CONFIGURACIÓN WEB
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- RESEÑAS
CREATE TABLE resenas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    cliente_nombre VARCHAR(200) NOT NULL,
    cliente_email VARCHAR(200) NOT NULL,
    puntuacion TINYINT(1) NOT NULL DEFAULT 0,
    comentario TEXT NOT NULL DEFAULT '',
    aprobada TINYINT(1) DEFAULT 0,
    token VARCHAR(64) UNIQUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
);

-- =============================================
-- DATOS INICIALES
-- =============================================

INSERT INTO categorias (nombre, slug) VALUES
('Pulseras', 'pulseras'),
('Velas', 'velas'),
('Zodíaco', 'zodiaco'),
('Arcángeles', 'arcangeles'),
('Baños Esotéricos', 'banos-esotericos'),
('Lectura de Cartas', 'lectura-cartas'),
('Cursos', 'cursos');

-- Admin por defecto (password: egoz2026)
INSERT INTO admin_usuarios (nombre, email, password) VALUES
('Edwin', 'pedidos@egoz.ch', '$2a$12$jF9Y/boA8zHRQV5RGZRUWOxkc7gLmt0FJ.4d84qXXTcjmgviM8JcC');

-- Configuración inicial
INSERT INTO configuracion (clave, valor) VALUES
('telefono', '+41 76 713 33 41'),
('email_contacto', 'info@egoz.ch'),
('instagram', 'https://www.instagram.com/egoz1984'),
('whatsapp', '41767133341'),
('sobre_mi_texto1', 'Llevo más de 10 años trabajando con energías y elementos naturales. Cada pulsera que creo lleva una intención específica, cargada con amor y propósito espiritual.'),
('sobre_mi_texto2', 'Vivo en Suiza y creo cada pieza de forma artesanal, combinando la tradición espiritual dominicana con el misticismo y el poder de los arcángeles.'),
('horario', 'Lunes a Viernes · 9:00 — 20:00 · Sábados 10:00 — 18:00'),
('recogida_info', 'Recogida en tienda · Zúrich'),
('recogida_direccion', 'Zúrich, Suiza'),
('hero_titulo', 'ENERGÍA · LUZ · TRANSFORMACIÓN'),
('hero_subtitulo', 'Pulseras artesanales y espiritualidad dominicana. Cada pieza creada con amor e intención. Recogida en Zúrich.');

-- =============================================
-- PRODUCTOS DE EJEMPLO
-- =============================================

INSERT INTO productos (nombre, slug, descripcion, precio, stock, categoria_id, destacado, activo) VALUES
('Pulsera Arcángel Miguel', 'pulsera-arcangel-miguel',
 'Pulsera artesanal dedicada al Arcángel Miguel, protector y guerrero de la luz. Elaborada con piedras naturales de protección. Ideal para quienes buscan fuerza y blindaje espiritual.',
 28.00, 10, 1, 1, 1),
('Pulsera Arcángel Rafael', 'pulsera-arcangel-rafael',
 'Pulsera dedicada al Arcángel Rafael, sanador del alma y del cuerpo. Sus piedras naturales favorecen la salud, el equilibrio y la recuperación energética.',
 28.00, 8, 1, 1, 1),
('Pulsera Arcángel Gabriel', 'pulsera-arcangel-gabriel',
 'Pulsera del Arcángel Gabriel, mensajero divino. Favorece la comunicación, la intuición y la claridad mental. Perfecta para artistas y comunicadores.',
 28.00, 12, 1, 0, 1),
('Pulsera Zodiacal Aries', 'pulsera-zodiacal-aries',
 'Pulsera artesanal diseñada especialmente para el signo de Aries. Combina piedras que potencian la energía, el liderazgo y la determinación.',
 32.00, 6, 3, 1, 1),
('Pulsera Zodiacal Tauro', 'pulsera-zodiacal-tauro',
 'Pulsera artesanal para el signo de Tauro. Sus piedras favorecen la estabilidad, la abundancia y la conexión con la tierra.',
 32.00, 6, 3, 0, 1),
('Pulsera del Mes Enero', 'pulsera-mes-enero',
 'Pulsera artesanal del mes de enero. Elaborada con piedras que corresponden a la energía de inicio de año, renovación y propósitos.',
 30.00, 5, 1, 0, 1),
('Vela Ritual Abundancia', 'vela-ritual-abundancia',
 'Vela ritual preparada para atraer la prosperidad, el dinero y las oportunidades a tu vida. Incluye hierbas y aceites esenciales seleccionados.',
 15.00, 20, 2, 1, 1),
('Vela Ritual Amor', 'vela-ritual-amor',
 'Vela ritual para atraer el amor verdadero y fortalecer las relaciones de pareja. Elaborada con rosas, canela y aceite de jazmín.',
 15.00, 15, 2, 0, 1),
('Vela Ritual Protección', 'vela-ritual-proteccion',
 'Vela ritual de alta protección espiritual. Aleja las energías negativas y crea un escudo de luz a tu alrededor.',
 15.00, 18, 2, 0, 1),
('Vela Ritual Salud', 'vela-ritual-salud',
 'Vela ritual dedicada a la sanación física y mental. Preparada con hierbas medicinales y cristales de cuarzo.',
 15.00, 10, 2, 1, 1),
('Baño Esotérico Amor y Suerte', 'bano-esoterico-amor-suerte',
 'Baño esotérico preparado para atraer el amor y la buena suerte. Incluye flores, hierbas y esencias seleccionadas para el ritual.',
 40.00, 8, 5, 1, 1),
('Baño Esotérico Limpieza Energética', 'bano-esoterico-limpieza',
 'Baño de limpieza profunda para eliminar energías negativas, cortes de lazos y limpiar el aura. Ideal después de momentos difíciles.',
 40.00, 6, 5, 0, 1),
('Lectura de Cartas Online', 'lectura-cartas-online',
 'Consulta personalizada de tarot y cartas españolas vía WhatsApp o videollamada. Incluye interpretación completa y respuestas a tus preguntas.',
 45.00, 99, 6, 1, 1),
('Lectura de Numerología', 'lectura-numerologia',
 'Análisis numerológico completo basado en tu fecha de nacimiento y nombre. Descubre tu número de vida, misión y los ciclos que te rigen.',
 50.00, 99, 6, 1, 1);