<?php

class productos
{
    const NOMBRE_TABLA = "producto";
    const ID_PRODUCTO = "idProducto";
    const NOMBRE_PRODUCTO = "nombreProducto";
    const DESCRIPCION = "descripcion";
    const PRECIO = "precio";
    const STOCK = "stock";
    const CATEGORIA = "categoria";
    const ID_USUARIO = "idUsuario";

    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;

    public static function get($peticion)
    {
        $idUsuario = usuarios::autorizar();

        if (empty($peticion[0]))
            return (new productos)->obtenerProductos($idUsuario);
        else
            return (new productos)->obtenerProductos($idUsuario, $peticion[0]);
    }

    public static function post($peticion)
    {
        $idUsuario = usuarios::autorizar();

        $body = file_get_contents('php://input');
        $producto = json_decode($body);

        $idProducto = (new productos)->crear($idUsuario, $producto);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "Producto creado",
            "id" => $idProducto
        ];
    }

    public static function put($peticion)
    {
        $idUsuario = usuarios::autorizar();

        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $producto = json_decode($body);

            if ((new productos)->actualizar($idUsuario, $producto, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro actualizado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El producto al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }

    public static function delete($peticion)
    {
        $idUsuario = usuarios::autorizar();

        if (!empty($peticion[0])) {
            if ((new productos)->eliminar($idUsuario, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro eliminado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El producto al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }

    /**
     * Obtiene la colección de productos o un solo producto indicado por el identificador
     * @param int $idUsuario identificador del usuario
     * @param null $idProducto identificador del producto (Opcional)
     * @return array registros de la tabla producto
     * @throws Exception
     */
    private function obtenerProductos($idUsuario, $idProducto = NULL)
    {
        try {
            if (!$idProducto) {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::ID_USUARIO . "=?";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idUsuario
                $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);

            } else {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::ID_PRODUCTO . "=? AND " .
                    self::ID_USUARIO . "=?";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idProducto e idUsuario
                $sentencia->bindParam(1, $idProducto, PDO::PARAM_INT);
                $sentencia->bindParam(2, $idUsuario, PDO::PARAM_INT);
            }

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return [
                    "estado" => self::ESTADO_EXITO,
                    "datos" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Añade un nuevo producto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $producto datos del producto
     * @return string identificador del producto
     * @throws ExcepcionApi
     */
    private function crear($idUsuario, $producto)
    {
        if ($producto) {
            try {
                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::NOMBRE_PRODUCTO . "," .
                    self::DESCRIPCION . "," .
                    self::PRECIO . "," .
                    self::STOCK . "," .
                    self::CATEGORIA . "," .
                    self::ID_USUARIO . ")" .
                    " VALUES(?,?,?,?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $nombreProducto);
                $sentencia->bindParam(2, $descripcion);
                $sentencia->bindParam(3, $precio);
                $sentencia->bindParam(4, $stock);
                $sentencia->bindParam(5, $categoria);
                $sentencia->bindParam(6, $idUsuario);

                $nombreProducto = $producto->nombreProducto;
                $descripcion = $producto->descripcion;
                $precio = $producto->precio;
                $stock = $producto->stock;
                $categoria = $producto->categoria;

                $sentencia->execute();

                // Retornar en el último id insertado
                return $pdo->lastInsertId();

            } catch (PDOException $e) {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
            }
        } else {
            throw new ExcepcionApi(
                self::ESTADO_ERROR_PARAMETROS,
                utf8_encode("Error en existencia o sintaxis de parámetros"));
        }
    }

    /**
     * Actualiza el producto especificado por idUsuario
     * @param int $idUsuario
     * @param object $producto objeto con los valores nuevos del producto
     * @param int $idProducto
     * @return PDOStatement
     * @throws Exception
     */
    private function actualizar($idUsuario, $producto, $idProducto)
    {
        try {
            // Creando consulta UPDATE
            $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self::NOMBRE_PRODUCTO . "=?," .
                self::DESCRIPCION . "=?," .
                self::PRECIO . "=?," .
                self::STOCK . "=?," .
                self::CATEGORIA . "=? " .
                " WHERE " . self::ID_PRODUCTO . "=? AND " . self::ID_USUARIO . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);

            $sentencia->bindParam(1, $nombreProducto);
            $sentencia->bindParam(2, $descripcion);
            $sentencia->bindParam(3, $precio);
            $sentencia->bindParam(4, $stock);
            $sentencia->bindParam(5, $categoria);
            $sentencia->bindParam(6, $idProducto);
            $sentencia->bindParam(7, $idUsuario);

            $nombreProducto = $producto->nombreProducto;
            $descripcion = $producto->descripcion;
            $precio = $producto->precio;
            $stock = $producto->stock;
            $categoria = $producto->categoria;

            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Elimina un producto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param int $idProducto identificador del producto
     * @return bool true si la eliminación se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */
    private function eliminar($idUsuario, $idProducto)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::ID_PRODUCTO . "=? AND " .
                self::ID_USUARIO . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $idProducto);
            $sentencia->bindParam(2, $idUsuario);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
}