CREATE TABLE IF NOT EXISTS `usuario` (
    `idUsuario` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(150) NOT NULL,
    `contrasena` varchar(255) NOT NULL,
    `claveApi` varchar(150) NOT NULL,
    `correo` varchar(255) NOT NULL UNIQUE,
    PRIMARY KEY (idUsuario)
);

CREATE TABLE IF NOT EXISTS `producto` (
    `idProducto` int(11) NOT NULL AUTO_INCREMENT,
    `nombreProducto` varchar(100) NOT NULL,
    `descripcion` text NOT NULL,
    `precio` decimal(10,2) NOT NULL,
    `stock` int(11) NOT NULL,
    `categoria` varchar(50) NOT NULL,
    `idUsuario` int(11) NOT NULL, 
    PRIMARY KEY (idProducto),
    FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);