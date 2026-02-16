-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 16-02-2026 a las 13:50:02
-- Versión del servidor: 5.7.23-23
-- Versión de PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `herework_cerrodelivery`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_completo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `usuario`, `password`, `nombre_completo`) VALUES
(1, 'admin', '$2y$10$9q.OytnO1Gd47qNKu3zT3e.ajTeKYjEzN3CWy.g.ju7v3x/rNPKrm', 'Luis Josue Torres Lucas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono_categoria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre_categoria`, `icono_categoria`) VALUES
(1, 'Hamburguesas', 'bi-hamburger'),
(2, 'Pollo a la Brasa', 'bi-egg-fried'),
(3, 'Chaufas', 'bi-egg'),
(4, 'Broaster', 'bi-basket'),
(5, 'Salchipapas', 'bi-grid'),
(6, 'Mariscos', 'bi-water');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_direcciones`
--

CREATE TABLE `cliente_direcciones` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `referencia` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `latitud` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `longitud` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `cliente_direcciones`
--

INSERT INTO `cliente_direcciones` (`id`, `id_cliente`, `nombre`, `direccion`, `referencia`, `latitud`, `longitud`) VALUES
(26, 1, 'Casa', 'Angamos 15, Asoc de la XV Region Agraria', 'casa de 3 pisos', '-10.66463247675074', '-76.25044621570747');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones`
--

CREATE TABLE `cupones` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('fijo','porcentaje') COLLATE utf8_unicode_ci DEFAULT 'fijo',
  `valor` decimal(10,2) NOT NULL,
  `fecha_limite` datetime NOT NULL,
  `usos_maximos` int(11) DEFAULT '100',
  `usos_actuales` int(11) DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `cupones`
--

INSERT INTO `cupones` (`id`, `codigo`, `tipo`, `valor`, `fecha_limite`, `usos_maximos`, `usos_actuales`, `activo`) VALUES
(1, 'BIENVENIDA', 'fijo', 5.00, '2025-12-31 23:59:59', 100, 0, 1),
(3, 'CERRO2025', 'fijo', 5.00, '2026-12-31 23:59:59', 100, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_plato` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `nombre_plato` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id`, `id_pedido`, `id_plato`, `cantidad`, `precio_unitario`, `nombre_plato`) VALUES
(1, 5, 2, 1, 16.00, '1/4 de Pollo a la Brasa'),
(2, 6, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(3, 7, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(4, 8, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(5, 9, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(6, 10, 2, 1, 16.00, '1/4 de Pollo a la Brasa'),
(12, 15, 2, 1, 16.00, '1/4 de Pollo a la Brasa'),
(13, 16, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(14, 17, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(15, 18, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(16, 19, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(17, 20, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(18, 21, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(19, 22, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(20, 23, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(21, 24, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(22, 25, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(23, 26, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(24, 27, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(25, 28, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(26, 29, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(27, 30, 1, 2, 11.00, 'Arroz Chaufa de Pollo'),
(28, 31, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(29, 32, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(30, 33, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(31, 34, 4, 1, 12.00, 'Chafaukai'),
(32, 37, 5, 1, 5.50, 'Hamburguesas'),
(33, 38, 5, 1, 5.50, 'Hamburguesas'),
(34, 39, 5, 1, 5.50, 'Hamburguesas'),
(35, 40, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(36, 41, 1, 2, 11.00, 'Arroz Chaufa de Pollo'),
(37, 42, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(38, 43, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(39, 44, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(40, 50, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(41, 51, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(42, 52, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(43, 53, 4, 1, 12.00, 'Chafaukai'),
(44, 54, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(45, 55, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(46, 56, 4, 1, 12.00, 'Chafaukai'),
(47, 57, 1, 1, 11.00, 'Arroz Chaufa de Pollo'),
(48, 58, 4, 1, 12.00, 'Chafaukai');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_platos`
--

CREATE TABLE `menu_platos` (
  `id` int(11) NOT NULL,
  `id_restaurante` int(11) NOT NULL,
  `nombre_plato` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio` decimal(10,2) NOT NULL,
  `foto_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.jpg',
  `esta_visible` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `menu_platos`
--

INSERT INTO `menu_platos` (`id`, `id_restaurante`, `nombre_plato`, `descripcion`, `precio`, `foto_url`, `esta_visible`) VALUES
(1, 1, 'Arroz Chaufa de Pollo', 'Arroz Chaufa de Pollo, con buen sabor', 11.00, 'default.jpg', 1),
(2, 2, '1/4 de Pollo a la Brasa', '1/4 de Pollo a la Brasa crujiente', 16.00, 'plato_696ab9610d4d4.png', 1),
(3, 2, '1/8 de Pollo a la Brasa', '1/8 de Pollo a la Brasa doradito', 10.00, 'plato_696aaa263dc18.png', 1),
(4, 1, 'Chafaukai', 'Chaufita g', 12.00, 'plato_696922214e60b.png', 1),
(5, 4, 'Hamburguesas', 'hamburguesa de carne con papas fritas', 5.50, 'plato_696e3ea1b245a.png', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_restaurante` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_repartidor` int(11) DEFAULT NULL,
  `direccion_pedido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '-',
  `telefono_pedido` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `foto_yape` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_pedido` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `costo_envio` decimal(10,2) NOT NULL DEFAULT '5.00',
  `metodo_pago` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `comprobante_pago` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_restaurante`, `id_cliente`, `id_repartidor`, `direccion_pedido`, `referencia`, `telefono_pedido`, `latitud`, `longitud`, `monto_total`, `foto_yape`, `fecha_pedido`, `estado_pedido`, `costo_envio`, `metodo_pago`, `comprobante_pago`) VALUES
(1, 1, 1, 2, 'wdwd', '-', NULL, NULL, NULL, 11.00, NULL, '2025-10-12 21:41:40', 'Entregado', 5.00, 'efectivo', NULL),
(5, 2, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66493739, -76.25169521, 16.00, NULL, '2025-10-12 22:01:59', 'Entregado', 5.00, 'efectivo', NULL),
(6, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6650, Lon: -76.2517)', '-', NULL, -10.66498561, -76.25168926, 11.00, NULL, '2025-10-12 22:07:52', 'Entregado', 5.00, 'efectivo', NULL),
(7, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6650, Lon: -76.2517)', '-', NULL, -10.66498561, -76.25168926, 11.00, NULL, '2025-10-12 22:17:49', 'Entregado', 5.00, 'efectivo', NULL),
(8, 1, 3, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66493739, -76.25169521, 11.00, NULL, '2025-10-12 22:29:16', 'Entregado', 5.00, 'efectivo', NULL),
(9, 1, 3, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6651, Lon: -76.2518)', '-', NULL, -10.66505432, -76.25181645, 11.00, NULL, '2025-10-12 22:39:48', 'Entregado', 5.00, 'efectivo', NULL),
(10, 2, 3, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6651, Lon: -76.2518)', '-', NULL, -10.66505432, -76.25181645, 16.00, NULL, '2025-10-12 22:39:53', 'Entregado', 5.00, 'efectivo', NULL),
(15, 2, 1, NULL, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66493739, -76.25169521, 16.00, NULL, '2025-10-13 19:56:57', 'Pendiente', 5.00, 'efectivo', NULL),
(16, 1, 3, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2516)', '-', NULL, -10.66489400, -76.25157150, 11.00, NULL, '2025-10-13 20:53:33', 'Entregado', 5.00, 'efectivo', NULL),
(17, 1, 3, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66493794, -76.25172312, 11.00, NULL, '2025-10-13 21:00:05', 'Entregado', 5.00, 'efectivo', NULL),
(18, 1, 3, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6650, Lon: -76.2518)', '-', NULL, -10.66496769, -76.25178159, 11.00, NULL, '2025-10-13 21:05:21', 'Entregado', 5.00, 'efectivo', NULL),
(19, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66493072, -76.25169889, 11.00, NULL, '2025-10-15 04:32:19', 'Listo para recoger', 5.00, 'efectivo', NULL),
(20, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2515)', '-', NULL, -10.66491218, -76.25154463, 11.00, NULL, '2025-10-18 01:49:23', 'Entregado', 5.00, 'efectivo', NULL),
(21, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2518)', '-', NULL, -10.66493210, -76.25176079, 11.00, NULL, '2025-10-18 01:57:13', 'Entregado', 5.00, 'efectivo', NULL),
(22, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2518)', '-', NULL, -10.66493210, -76.25176079, 11.00, NULL, '2025-10-18 01:58:27', 'Entregado', 5.00, 'efectivo', NULL),
(23, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66491976, -76.25169365, 11.00, NULL, '2025-10-18 02:04:20', 'Entregado', 5.00, 'efectivo', NULL),
(24, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66491976, -76.25169365, 11.00, NULL, '2025-10-18 02:08:42', 'Listo para recoger', 5.00, 'efectivo', NULL),
(25, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6651, Lon: -76.2519)', '-', NULL, -10.66505382, -76.25187673, 11.00, NULL, '2025-10-18 02:11:26', 'Listo para recoger', 5.00, 'efectivo', NULL),
(26, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2517)', '-', NULL, -10.66492568, -76.25168991, 11.00, NULL, '2025-10-18 02:31:15', 'Entregado', 5.00, 'efectivo', NULL),
(27, 1, 1, NULL, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2516)', '-', NULL, -10.66491513, -76.25159417, 11.00, NULL, '2025-10-18 02:46:20', 'Entregado', 5.00, 'efectivo', NULL),
(28, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6650, Lon: -76.2519)', '-', NULL, -10.66499861, -76.25187221, 11.00, NULL, '2025-10-18 03:03:33', 'Entregado', 5.00, 'efectivo', NULL),
(29, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6650, Lon: -76.2519)', '-', NULL, NULL, NULL, 11.00, NULL, '2025-10-18 03:07:20', 'Entregado', 5.00, 'efectivo', NULL),
(30, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2527)', '-', NULL, -10.66493900, -76.25269300, 22.00, NULL, '2025-10-22 05:01:46', 'Entregado', 5.00, 'efectivo', NULL),
(31, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2527)', '-', NULL, -10.66493900, -76.25269300, 11.00, NULL, '2025-10-22 06:32:50', 'Entregado', 5.00, 'efectivo', NULL),
(32, 1, 1, 2, 'Ubicación precisa obtenida por GPS (Lat: -10.6649, Lon: -76.2527)', '-', NULL, -10.66493900, -76.25269300, 11.00, NULL, '2025-10-22 07:01:52', 'Entregado', 5.00, 'efectivo', NULL),
(33, 1, 1, 2, 'Node', '-', NULL, NULL, NULL, 11.00, NULL, '2025-10-22 07:12:35', 'Entregado', 5.00, 'efectivo', NULL),
(34, 1, 1, 2, 'Ubicación GPS (Lat: -10.6655, Lon: -76.2520) - Añade detalles (piso, color)...', '-', NULL, -10.66552348, -76.25203568, 17.00, NULL, '2026-01-15 17:40:43', 'Entregado', 5.00, 'yape', 'pago_1768498843_1.jpg'),
(35, 4, 1, NULL, 'Ubicación GPS (Lat: -10.6654, Lon: -76.2521) - Añade detalles...', '-', '', NULL, NULL, 10.50, 'pago_1768835139_1.jpg', '2026-01-19 15:05:39', 'Pendiente', 5.00, 'yape', NULL),
(36, 4, 1, NULL, 'Ubicación GPS (Lat: -10.6654, Lon: -76.2521) - Añade detalles...', '-', '', NULL, NULL, 10.50, 'pago_1768835626_1.jpg', '2026-01-19 15:13:46', 'Pendiente', 5.00, 'yape', NULL),
(37, 4, 1, 2, 'Ubicación GPS (Lat: -10.6656, Lon: -76.2521) - Añade detalles...', '-', '', NULL, NULL, 10.50, 'pago_1768835915_1.jpg', '2026-01-19 15:18:35', 'Entregado', 5.00, 'yape', NULL),
(38, 4, 1, 2, 'Ubicación GPS (Lat: -10.6657, Lon: -76.2520) - Añade detalles...', '-', '', -10.66567420, -76.25203180, 10.50, 'pago_1768837344_1.jpg', '2026-01-19 15:42:24', 'Entregado', 5.00, 'yape', NULL),
(39, 4, 1, 2, 'Ubicación GPS (Lat: -10.6648, Lon: -76.2505) - Añade detalles...', '-', '969704480', -10.66476000, -76.25050500, 10.50, 'pago_1769566503_1.jpg', '2026-01-28 02:15:03', 'Entregado', 5.00, 'yape', NULL),
(40, 1, 1, 2, 'Ubicación GPS (Lat: -10.6655, Lon: -76.2519) - Añade detalles...', '-', '969704480', -10.66545750, -76.25189600, 16.00, 'pago_1769815414_1.png', '2026-01-30 23:23:34', 'Entregado', 5.00, 'yape', NULL),
(41, 1, 1, 2, 'Ubicación GPS (Lat: -10.6655, Lon: -76.2519) - Añade detalles...', '-', '969704480', -10.66545750, -76.25189600, 27.00, 'pago_1769816655_1.png', '2026-01-30 23:44:15', 'Entregado', 5.00, 'yape', NULL),
(42, 1, 1, 2, 'Ubicación GPS (Lat: -10.6655, Lon: -76.2519) - Añade detalles...', '-', '969704480', -10.66545750, -76.25189600, 16.00, 'pago_1769816995_1.png', '2026-01-30 23:49:55', 'Entregado', 5.00, 'yape', NULL),
(43, 1, 1, 2, 'Ubicación GPS (Lat: -10.6672, Lon: -76.2538) - Añade detalles...', '-', '969704480', -10.66715260, -76.25376140, 16.00, 'pago_1769827089_1.jpg', '2026-01-31 02:38:09', 'Entregado', 5.00, 'yape', NULL),
(44, 1, 1, 2, 'Ubicación GPS (Lat: -10.6682, Lon: -76.2546) - Añade detalles...', '-', '969704480', -10.66822230, -76.25457200, 16.00, 'pago_1769830685_1.jpg', '2026-01-31 03:38:05', 'Entregado', 5.00, 'yape', NULL),
(50, 1, 1, 2, 'Lat: -10.665592, Lng: -76.252139', '', '969704480', -10.66559210, -76.25213900, 16.00, 'yape_1770068182_1.jpg', '2026-02-02 21:36:22', 'Entregado', 5.00, 'yape', NULL),
(51, 1, 1, NULL, 'Lat: -10.665630, Lng: -76.252002', '', '969704480', -10.66563020, -76.25200150, 16.00, 'yape_1770074031_1.jpg', '2026-02-02 23:13:51', 'En preparación', 5.00, 'yape', NULL),
(52, 1, 1, NULL, 'Lat: -10.665594, Lng: -76.252146', '', '969704480', -10.66559380, -76.25214560, 16.00, 'yape_1770217291_1.jpg', '2026-02-04 15:01:31', 'Pendiente', 5.00, 'yape', NULL),
(53, 1, 1, NULL, 'Lat: -10.665686, Lng: -76.252055', '', '969704480', -10.66515832, -76.25203809, 17.00, 'yape_1770227420_1.jpg', '2026-02-04 17:50:20', 'Listo para recoger', 5.00, 'yape', NULL),
(54, 1, 1, NULL, 'Lat: -10.665706, Lng: -76.252041', '', '969704480', -10.66411454, -76.25030998, 16.00, 'yape_1770227891_1.jpg', '2026-02-04 17:58:11', 'Listo para recoger', 5.00, 'yape', NULL),
(55, 1, 1, 2, 'Lat: -10.665680, Lng: -76.252055', '', '969704480', -10.66514140, -76.25207139, 16.00, 'yape_1770240292_1.jpg', '2026-02-04 21:24:52', 'Entregado', 5.00, 'yape', NULL),
(56, 1, 1, 2, 'Lat: -10.665634, Lng: -76.252088', '', '969704480', -10.66563420, -76.25208830, 17.00, 'yape_1770240437_1.jpg', '2026-02-04 21:27:17', 'En camino', 5.00, 'yape', NULL),
(57, 1, 1, 2, 'Ubicación GPS (Lat: -10.6700, Lon: -76.2500)', '-', '969704480', -10.66543691, -76.25206947, 17.20, 'pago_1770298708_1.png', '2026-02-05 13:38:28', 'En camino', 6.20, 'yape', NULL),
(58, 1, 1, 2, 'Lat: -10.665662, Lng: -76.252016', '', '969704480', -10.66566200, -76.25201560, 18.10, 'yape_1770310798_1.jpg', '2026-02-05 16:59:58', 'En camino', 5.00, 'yape', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_solicitudes_entrega`
--

CREATE TABLE `pedido_solicitudes_entrega` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_repartidor` int(11) NOT NULL,
  `estado_solicitud` enum('pendiente','aprobado','rechazado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `notificacion_vista` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_solicitud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_solicitudes_entrega`
--

INSERT INTO `pedido_solicitudes_entrega` (`id`, `id_pedido`, `id_repartidor`, `estado_solicitud`, `notificacion_vista`, `fecha_solicitud`) VALUES
(1, 21, 2, 'aprobado', 1, '2025-10-18 01:57:30'),
(2, 22, 2, 'aprobado', 1, '2025-10-18 01:58:58'),
(3, 23, 2, 'aprobado', 1, '2025-10-18 02:04:45'),
(4, 24, 2, 'aprobado', 1, '2025-10-18 02:09:21'),
(5, 25, 2, 'aprobado', 1, '2025-10-18 02:22:22'),
(6, 26, 2, 'aprobado', 1, '2025-10-18 02:31:44'),
(7, 27, 2, 'pendiente', 0, '2025-10-18 03:02:05'),
(8, 28, 2, 'aprobado', 1, '2025-10-18 03:04:00'),
(9, 29, 2, 'aprobado', 1, '2025-10-18 03:07:53'),
(10, 30, 2, 'aprobado', 1, '2025-10-22 05:13:11'),
(11, 31, 2, 'aprobado', 1, '2025-10-22 06:33:44'),
(12, 32, 2, 'aprobado', 1, '2025-10-22 07:02:49'),
(13, 33, 2, 'aprobado', 1, '2025-11-12 02:45:29'),
(14, 34, 2, 'aprobado', 1, '2026-01-15 17:43:41'),
(15, 37, 2, 'aprobado', 1, '2026-01-19 15:35:16'),
(16, 38, 2, 'aprobado', 1, '2026-01-19 15:43:03'),
(17, 40, 2, 'aprobado', 1, '2026-01-30 23:24:17'),
(18, 39, 2, 'aprobado', 1, '2026-01-30 23:32:06'),
(19, 41, 2, 'aprobado', 1, '2026-01-30 23:45:15'),
(20, 42, 2, 'aprobado', 1, '2026-01-30 23:50:49'),
(21, 43, 2, 'aprobado', 1, '2026-01-31 02:40:53'),
(22, 44, 2, 'aprobado', 1, '2026-01-31 03:47:26'),
(23, 50, 2, 'aprobado', 1, '2026-02-02 21:40:23'),
(24, 53, 2, 'pendiente', 0, '2026-02-04 17:52:01'),
(25, 55, 2, 'aprobado', 1, '2026-02-04 21:25:39'),
(26, 54, 2, 'pendiente', 0, '2026-02-04 21:26:29'),
(27, 56, 2, 'aprobado', 1, '2026-02-04 21:27:42'),
(28, 53, 5, 'pendiente', 0, '2026-02-04 21:31:14'),
(29, 54, 5, 'pendiente', 0, '2026-02-04 21:31:50'),
(30, 27, 5, 'pendiente', 0, '2026-02-04 21:31:57'),
(31, 57, 2, 'aprobado', 1, '2026-02-05 13:40:07'),
(32, 58, 2, 'aprobado', 1, '2026-02-05 17:02:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repartidores`
--

CREATE TABLE `repartidores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_aprobacion` enum('pendiente','aprobado','rechazado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `estado_disponibilidad` enum('disponible','ocupado','desconectado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'desconectado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `repartidores`
--

INSERT INTO `repartidores` (`id`, `nombre`, `email`, `password`, `telefono`, `estado_aprobacion`, `estado_disponibilidad`) VALUES
(1, 'Luis', 'jolsata24@email.com', '$2y$10$9q.OytnO1Gd47qNKu3zT3e.ajTeKYjEzN3CWy.g.ju7v3x/rNPKrm', '912345678', 'aprobado', 'desconectado'),
(2, 'Luis', 'jolsata24@gmail.com', '$2y$10$9q.OytnO1Gd47qNKu3zT3e.ajTeKYjEzN3CWy.g.ju7v3x/rNPKrm', '912345678', 'aprobado', 'desconectado'),
(3, 'Matias', 'matias@gmail.com', '$2y$10$sopzRhAtqXnLGF4UKZ.uguoiB0IDKFOXjGZmdVQQ27Jir3oORa.S2', '969704480', 'aprobado', 'desconectado'),
(4, 'repartidor', 'repartidor@gmail.com', '$2y$10$PQB5FN0DCufNBSj88MR80uhYSZSrAfcXG4LfKS7nUmjjAo4kT1nsG', '998835300', 'aprobado', 'desconectado'),
(5, 'Hazler', 'Hazler@gmail.com', '$2y$10$gp9JVni21y6NTKjUh3.XwOchnB1zw3uM8QpzDkPJKA3vohba5YGBW', '998835300', 'aprobado', 'desconectado'),
(6, 'Hazle', 'Hazle@gmail.com', '$2y$10$BTq8HUu0GLv5ANc9ChhdnufveyOmu/ruLDD924bUTO824IvZRZOam', '998835300', 'aprobado', 'desconectado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repartidor_afiliaciones`
--

CREATE TABLE `repartidor_afiliaciones` (
  `id` int(11) NOT NULL,
  `id_repartidor` int(11) NOT NULL,
  `id_restaurante` int(11) NOT NULL,
  `estado_afiliacion` enum('pendiente','aprobado','rechazado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `repartidor_afiliaciones`
--

INSERT INTO `repartidor_afiliaciones` (`id`, `id_repartidor`, `id_restaurante`, `estado_afiliacion`) VALUES
(4, 2, 1, 'aprobado'),
(5, 2, 2, 'pendiente'),
(6, 5, 1, 'aprobado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repartidor_ubicaciones`
--

CREATE TABLE `repartidor_ubicaciones` (
  `id_repartidor` int(11) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `ultima_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `repartidor_ubicaciones`
--

INSERT INTO `repartidor_ubicaciones` (`id_repartidor`, `latitud`, `longitud`, `ultima_actualizacion`) VALUES
(2, -10.67000000, -76.25000000, '2026-02-05 13:43:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurantes`
--

CREATE TABLE `restaurantes` (
  `id` int(11) NOT NULL,
  `nombre_restaurante` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen_fondo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default_restaurante.jpg',
  `puntuacion_promedio` decimal(3,2) NOT NULL DEFAULT '0.00',
  `total_puntuaciones` int(11) NOT NULL DEFAULT '0',
  `estado` enum('inactivo','activo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactivo',
  `fecha_vencimiento_suscripcion` date DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hora_apertura` time DEFAULT NULL,
  `hora_cierre` time DEFAULT NULL,
  `yape_numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `yape_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitud` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitud` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurantes`
--

INSERT INTO `restaurantes` (`id`, `nombre_restaurante`, `email`, `password`, `direccion`, `telefono`, `imagen_fondo`, `puntuacion_promedio`, `total_puntuaciones`, `estado`, `fecha_vencimiento_suscripcion`, `fecha_registro`, `hora_apertura`, `hora_cierre`, `yape_numero`, `yape_qr`, `latitud`, `longitud`) VALUES
(1, 'Bembos', 'jolsata24@gmail.com', '$2y$10$K6/mlJMfBgaUJGQL0YcmiuFHqVo5Kc6x8mL7ylhoEalJMYqUyNctW', NULL, '969704480', 'restaurante_1_1769552730.png', 4.33, 3, 'activo', '2026-02-14', '2025-10-12 20:10:31', '23:59:00', '23:58:00', '969704480', 'qr_1.png', '-10.667186', '-76.256154'),
(2, 'Kimbos', '214440322@undac.edu.pe', '$2y$10$pUBFn/k3..YFFJXm.dKUrO9.7H2dp3BY.CM6jUv1OpwHrP9abO0o.', NULL, '969704480', 'restaurante_2_1769552760.jpg', 4.00, 2, 'activo', '2026-02-14', '2025-10-12 21:25:50', '14:40:00', '20:00:00', NULL, NULL, NULL, NULL),
(3, 'Kentoky', 'kentoky@gmail.com', '$2y$10$doyXxry9AQVRT8.sZZILZ.AZwSJphFfDeYcNLHb839foIBHzxhHvK', NULL, NULL, 'default_restaurante.jpg', 0.00, 0, 'activo', '2026-03-06', '2026-01-19 13:41:34', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Doñita', 'jolsata@gmail.com', '$2y$10$S6sQwpwQB94trm0hhLpM4uU2U.ZQWcRs7GlO8aGkMwiwWpXq7X3se', NULL, '969704480', 'restaurante_4_1769552792.jpg', 5.00, 2, 'activo', '2026-02-18', '2026-01-19 14:22:07', '09:01:00', '09:00:00', '969704480', 'qr_4.png', '-10.665447115451606', '-76.25213466101452');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurante_categorias`
--

CREATE TABLE `restaurante_categorias` (
  `id_restaurante` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurante_categorias`
--

INSERT INTO `restaurante_categorias` (`id_restaurante`, `id_categoria`) VALUES
(4, 1),
(1, 2),
(2, 2),
(1, 3),
(2, 3),
(1, 4),
(2, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurante_puntuaciones`
--

CREATE TABLE `restaurante_puntuaciones` (
  `id` int(11) NOT NULL,
  `id_restaurante` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `puntuacion` tinyint(4) NOT NULL COMMENT 'Puntuación de 1 a 5',
  `fecha_puntuacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `restaurante_puntuaciones`
--

INSERT INTO `restaurante_puntuaciones` (`id`, `id_restaurante`, `id_cliente`, `id_pedido`, `puntuacion`, `fecha_puntuacion`) VALUES
(1, 1, 4, NULL, 5, '2025-10-13 18:55:07'),
(4, 2, 4, NULL, 5, '2025-10-13 19:08:04'),
(7, 2, 1, NULL, 3, '2025-10-13 19:08:50'),
(8, 1, 1, NULL, 5, '2025-10-13 19:08:59'),
(10, 1, 3, NULL, 3, '2025-10-13 19:10:14'),
(11, 4, 1, NULL, 5, '2026-01-19 17:06:00'),
(15, 4, 3, NULL, 5, '2026-01-20 15:33:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_clientes`
--

CREATE TABLE `usuarios_clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_default` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token_verificacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuenta_confirmada` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_clientes`
--

INSERT INTO `usuarios_clientes` (`id`, `nombre`, `email`, `password`, `telefono`, `direccion_default`, `fecha_registro`, `token_verificacion`, `cuenta_confirmada`) VALUES
(1, 'Luis Josue Torres Lucas', 'Jolsata24@gmail.com', '$2y$10$MNLkvVXd0mE10TJgLSlwZ.w6cfcOkFvty0URBRa8awiMRkJMOiGG6', '969704480', NULL, '2025-10-12 20:38:13', NULL, 0),
(3, 'Luis Josue', '214440322@undac.edu.pe', '$2y$10$y8b6i85NRV0g9xDlRWVysef1wN8xm0VAEYDx4vMVC1KgXWyW6opqG', '969704480', NULL, '2025-10-12 22:28:41', NULL, 0),
(4, 'Jolsata24', 'turnitinexperthelper@gmail.com', '$2y$10$LGj6Tb9tHLkfZKftYh4N1ebrHxumk70R6EEit3Juqj3vTLbHZIHdW', '969704480', NULL, '2025-10-13 18:17:53', NULL, 0),
(5, 'Matias', 'matias@gmail.com', '$2y$10$bOg3P/RyZE94mRGVJm17xeyGg6hpq/PTZlQqRA/ret.LJyO34B.KK', '969704480', NULL, '2025-10-19 09:46:02', NULL, 0),
(6, 'Kevin', 'kevin@gmail.com', '$2y$10$QFSex7U44yCbEsuGt.F5NuXWrs48vcSEHDLVuEj38VrwH1AVp6DIW', '998835300', NULL, '2026-01-19 13:38:51', '3b82cb7e7191900e8ae77a6b8c398bd323d985ef99732e19baf3121ad5511e88', 0),
(7, 'Dik', 'Dik@gmail.com', '$2y$10$WI5siTwOio8ary0Qxz4M8eAsK2wXCDtUSedIIOjHHZBEQ/QeCz8n2', '998835300', NULL, '2026-01-19 13:39:32', '270924bb934bbb65b7292b36a0827b9bfea1f0d471f955a2af5cc6c8e9a1df4b', 0),
(11, 'Dikde', 'Dikd@gmail.com', '$2y$10$h94D8Z92n1ZoCm/oBh.MneRoTHbI5HQhrk.ZBqA6FDn9aFleJqN5W', '998835300', NULL, '2026-01-20 15:21:57', 'd3af919daa27a7e1abb0f216c2fd1f92e45e614e1a3e8a5a9ae5348732e3a971', 0),
(15, 'Oño', 'ono@gmail.com', '$2y$10$jgb57L/oSeQ63bsVI0JQpex20g/N/vLlidvtum95fWnc42S2mX8Du', '969704480', NULL, '2026-01-28 02:16:54', 'a2f4d640a99d5d910db85a4436d80bc8a303af9dc91f17cb8672442d621a29f1', 0),
(16, 'Eugenio Salazar Rapri', 'Samoinquilla@gmail.com', '$2y$10$3rc9ipqW9wqVC/niu.F8U.i7H5YYCZxZGPDrFT8HJtDlE2ry0qKi6', '916706053', NULL, '2026-02-03 04:02:24', '94cd08449f38b6e202de49302594bb92d96bcb8a26fead045aaa116eb7fe2926', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_unico` (`usuario`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cliente_direcciones`
--
ALTER TABLE `cliente_direcciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Indices de la tabla `menu_platos`
--
ALTER TABLE `menu_platos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_restaurante` (`id_restaurante`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_restaurante` (`id_restaurante`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_repartidor` (`id_repartidor`);

--
-- Indices de la tabla `pedido_solicitudes_entrega`
--
ALTER TABLE `pedido_solicitudes_entrega`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `solicitud_unica` (`id_pedido`,`id_repartidor`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_repartidor` (`id_repartidor`);

--
-- Indices de la tabla `repartidores`
--
ALTER TABLE `repartidores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `repartidor_afiliaciones`
--
ALTER TABLE `repartidor_afiliaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_afiliacion` (`id_repartidor`,`id_restaurante`),
  ADD KEY `id_repartidor` (`id_repartidor`),
  ADD KEY `id_restaurante` (`id_restaurante`);

--
-- Indices de la tabla `repartidor_ubicaciones`
--
ALTER TABLE `repartidor_ubicaciones`
  ADD PRIMARY KEY (`id_repartidor`);

--
-- Indices de la tabla `restaurantes`
--
ALTER TABLE `restaurantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unico` (`email`);

--
-- Indices de la tabla `restaurante_categorias`
--
ALTER TABLE `restaurante_categorias`
  ADD PRIMARY KEY (`id_restaurante`,`id_categoria`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `restaurante_puntuaciones`
--
ALTER TABLE `restaurante_puntuaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_cliente_restaurante` (`id_cliente`,`id_restaurante`);

--
-- Indices de la tabla `usuarios_clientes`
--
ALTER TABLE `usuarios_clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cliente_direcciones`
--
ALTER TABLE `cliente_direcciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `cupones`
--
ALTER TABLE `cupones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `menu_platos`
--
ALTER TABLE `menu_platos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `pedido_solicitudes_entrega`
--
ALTER TABLE `pedido_solicitudes_entrega`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `repartidores`
--
ALTER TABLE `repartidores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `repartidor_afiliaciones`
--
ALTER TABLE `repartidor_afiliaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `restaurantes`
--
ALTER TABLE `restaurantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `restaurante_puntuaciones`
--
ALTER TABLE `restaurante_puntuaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuarios_clientes`
--
ALTER TABLE `usuarios_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `menu_platos`
--
ALTER TABLE `menu_platos`
  ADD CONSTRAINT `fk_plato_restaurante` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurantes` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios_clientes` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id`);

--
-- Filtros para la tabla `pedido_solicitudes_entrega`
--
ALTER TABLE `pedido_solicitudes_entrega`
  ADD CONSTRAINT `fk_pedido_solicitud` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_repartidor_solicitud` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `repartidor_afiliaciones`
--
ALTER TABLE `repartidor_afiliaciones`
  ADD CONSTRAINT `fk_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_restaurante_afiliado` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `repartidor_ubicaciones`
--
ALTER TABLE `repartidor_ubicaciones`
  ADD CONSTRAINT `repartidor_ubicaciones_ibfk_1` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `restaurante_categorias`
--
ALTER TABLE `restaurante_categorias`
  ADD CONSTRAINT `fk_categoria_vinculo` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_restaurante_vinculo` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurantes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
