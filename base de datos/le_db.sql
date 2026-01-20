-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-11-2025 a las 23:31:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `le_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `Actividad_id` int(11) NOT NULL,
  `Tipo_Actividad` varchar(100) NOT NULL,
  `Fecha` timestamp NULL DEFAULT current_timestamp(),
  `Detalle` text DEFAULT NULL,
  `Codigo_Paquete` varchar(50) DEFAULT NULL,
  `Usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `destino`
--

CREATE TABLE `destino` (
  `Destino_id` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Modalidad` varchar(50) DEFAULT NULL,
  `Estado` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `destino`
--

INSERT INTO `destino` (`Destino_id`, `Nombre`, `Modalidad`, `Estado`) VALUES
(3, 'Caracas', 'Tienda', 'Activo'),
(4, 'Naguanagua', 'Ruta', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `Notificacion_id` int(11) NOT NULL,
  `Usuario_id` int(11) DEFAULT NULL,
  `Mensaje` text NOT NULL,
  `Fecha_Publicacion` timestamp NULL DEFAULT current_timestamp(),
  `Fecha_Expiracion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `origen`
--

CREATE TABLE `origen` (
  `Origen_id` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `origen`
--

INSERT INTO `origen` (`Origen_id`, `Nombre`) VALUES
(9, 'Internacional'),
(6, 'Nacional');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquete`
--

CREATE TABLE `paquete` (
  `Codigo` varchar(50) NOT NULL,
  `Origen_id` int(11) NOT NULL,
  `Fecha_Registro` timestamp NULL DEFAULT current_timestamp(),
  `Tipo_Destino_ID` varchar(50) NOT NULL,
  `Destino_id` int(11) NOT NULL,
  `Usuario_id` int(11) NOT NULL,
  `Status` varchar(20) DEFAULT 'Registrado',
  `Estado` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `paquete`
--

INSERT INTO `paquete` (`Codigo`, `Origen_id`, `Fecha_Registro`, `Tipo_Destino_ID`, `Destino_id`, `Usuario_id`, `Status`, `Estado`) VALUES
('NC-1304059295', 6, '2025-11-17 12:25:09', 'Ruta', 4, 1, 'En Sede', 1),
('NC-29842897', 6, '2025-11-10 13:48:39', 'Tienda', 3, 1, 'En Sede', 1),
('NC-95830ASEFD', 9, '2025-11-02 20:54:40', 'Tienda', 3, 1, 'En Sede', 1),
('NC13859596978', 6, '2025-11-17 12:25:09', 'Ruta', 4, 1, 'En Ruta', 1),
('WR-9587584', 9, '2025-11-17 13:28:50', 'Ruta', 4, 7, 'En Sede', 0),
('WR-io40456089', 9, '2025-11-02 20:58:40', 'Ruta', 4, 1, 'Entregado', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `Nombre`) VALUES
(3, 'Administrador'),
(1, 'Almacenista'),
(2, 'Coordinador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `apellido` varchar(30) NOT NULL,
  `turno` int(11) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contraseña` varchar(100) NOT NULL,
  `estado` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `requiere_cambio` int(1) NOT NULL DEFAULT 1,
  `token_password` varchar(100) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `apellido`, `turno`, `correo`, `contraseña`, `estado`, `rol_id`, `requiere_cambio`, `token_password`, `token_expiracion`) VALUES
(1, 'Anderliz', 'Mendoza', 0, 'menzaander@gmail.com', '$2y$10$IYz2VRTvTeq91MQEkTsfnegAN7kK59wBAT/lbwxCa30Y8.7LQs5Fi', 1, 3, 0, NULL, NULL),
(5, 'Ana', 'Lopez', 1, 'Ana@gmail.com', '$2y$10$oX3iJOHtQaQv/XQA7AIp1uq6fHPWRJFaQQj/DEhLz4oJla.0.kT7.', 1, 3, 1, NULL, NULL),
(7, 'Beslith', 'Hidalgo', 0, 'b@gmail.com', '$2y$10$ccw4bHjbRiCP0bbn1yTth.eJQssCX3VQU2MdHxGOPcwVoEou5pl1.', 1, 3, 1, NULL, NULL),
(9, 'Victor', 'De Abreu', 0, 'deabreuvictorhugo@gmail.com', '$2y$10$Rks4feto9vNnmJK96KdGxO.jFvV0TgXvRGEc9JQB7NFTuB5rOl0zm', 1, 3, 0, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`Actividad_id`);

--
-- Indices de la tabla `destino`
--
ALTER TABLE `destino`
  ADD PRIMARY KEY (`Destino_id`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`Notificacion_id`);

--
-- Indices de la tabla `origen`
--
ALTER TABLE `origen`
  ADD PRIMARY KEY (`Origen_id`),
  ADD UNIQUE KEY `Nombre` (`Nombre`);

--
-- Indices de la tabla `paquete`
--
ALTER TABLE `paquete`
  ADD PRIMARY KEY (`Codigo`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Nombre` (`Nombre`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `Actividad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `destino`
--
ALTER TABLE `destino`
  MODIFY `Destino_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `Notificacion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `origen`
--
ALTER TABLE `origen`
  MODIFY `Origen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
