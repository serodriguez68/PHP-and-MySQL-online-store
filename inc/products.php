<?php

/*
 * Returns the four most recent products, using the order of the elements in the array
 * @return   array           a list of the last four products in the array;
                             the most recent product is the last one in the array
 */
function get_products_recent() {
    
    require(ROOT_PATH . "inc/database.php");

    try {
        // Como no hay input del usuario, no es necesario usar el prepare method
            // Ordena todos los productos por orden descendente y limita a 4 filas (4 resultados)
            // El resultado son los 4 productos con mayor sku
        $results = $db->query("
                SELECT name, price, img, sku, paypal
                FROM products
                ORDER BY sku DESC
                LIMIT 4");
    } catch (Exception $e) {
        echo "Data could not be retrieved from the database.";
        exit;
    }

    // Se extraen los resultados a un array
    $recent = $results->fetchAll(PDO::FETCH_ASSOC);
    // Se revierte el órden del array para que queden en orden ascendente
    // para que el producto más reciente quede al final del array
    $recent = array_reverse($recent);

    return $recent;
}

/*
 * Looks for a search term in the product names
 * @param    string    $s    the search term
 * @return   array           a list of the products that contain the search term in their name
 */
function get_products_search($s) {

    // Se incluye la base de datos en un objeto llamado $db
    require(ROOT_PATH . "inc/database.php");

    try {
        // Se usa prepare porque se va a recibir input
        // La búsqueda se hace con WHERE name LIKE ?
        // 
        $results = $db->prepare("
                SELECT name, price, img, sku, paypal
                FROM products
                WHERE name LIKE ?
                ORDER BY sku");
        // Se utiliza en lubar de bindParam porque nos permite hacer concatenation.
            // bindValue pone single quotes al rededor (que se necisitan para el LIKE)
            // bindValue es ligeramente diferente a bindParam. bindValue tiene el valor del argumento
            // disponible durante la ejecución de esa línea (por eso se puede concatenar.
            // bindParam solo hace disponible el valor del argumento hasta que se corra el método execute() (ver documentación)
        $results->bindValue(1,"%" . $s . "%");
        $results->execute();
    } catch (Exception $e) {
        echo "Data could not be retrieved from the database.";
        exit;
    }

    // Pasa los resultados del objeto $results al arreglo $matches
    $matches = $results->fetchAll(PDO::FETCH_ASSOC);

    return $matches;
}

/*
 * Counts the total number of products
 * @return   int             the total number of products
 */
function get_products_count() {
    
    require(ROOT_PATH . "inc/database.php");

    // No es necesario usar prepare porque no hay user input
    // En vez de pedir todos los productos de la base de datos y contarlos en php,
    // se cuentan desde un query de MySQL (ver database foundations)
    try {
        $results = $db->query("
            SELECT COUNT(sku)
            FROM products");
    } catch (Exception $e) {
        echo "Data could not be retrieved from the database.";
        exit;
    }

    // fetchColumn saca una única columna como una variable simple.
        // El argumento es el número de la columna que se queire sacar (iniciando en 0)
        // fetchColum devuelve strings.  intval convierte a entero.
    return intval($results->fetchColumn(0));
}

/*
 * Returns a specified subset of products, based on the values received,
 * using the order of the elements in the array .
 * @param    int             the position of the first product in the requested subset 
 * @param    int             the position of the last product in the requested subset 
 * @return   array           the list of products that correspond to the start and end positions
 */
function get_products_subset($positionStart, $positionEnd) {

    // Se traduce positionStart y positonEnd a offset y rows
    $offset = $positionStart - 1;
    $rows = $positionEnd - $positionStart + 1;

    // Se importa da base de datos en un objeto $db
    require(ROOT_PATH . "inc/database.php");

    try {

        // Se utiliza prepare porque la función tiene input
        // LIMIT con dos argumentos ayuda a sacar un número dado de filas iniciando con un offset
            // LIMIT "offset", "rows".  Los datos devueltos inician en offset+1 (e.g si el offset es 10 se sacan a partir del 11)
        $results = $db->prepare("
                SELECT name, price, img, sku, paypal
                FROM products
                ORDER BY sku
                LIMIT ?, ?");
        // Relaciona la variable $offset con el primer placeholder ?.
            // bindParam trata las variables como strings -> PDO::PARAM_INT oblica a que se traten como enteros
        $results->bindParam(1,$offset,PDO::PARAM_INT);
        $results->bindParam(2,$rows,PDO::PARAM_INT);
        $results->execute();
    } catch (Exception $e) {
        echo "Data could not be retrieved from the database.";
        exit;
    }

    // Pasar los resultados en el objeto $results a un array.
    $subset = $results->fetchAll(PDO::FETCH_ASSOC);

    return $subset;
}

/*
 * Returns the full list of products. This function contains the full list of products,
 * and the other model functions first call this function.
 * @return   array           the full list of products
 */
function get_products_all() {

    require(ROOT_PATH . "inc/database.php");

    try {
        $results = $db->query("SELECT name, price, img, sku, paypal FROM products ORDER BY sku ASC");
    } catch (Exception $e) {
        echo "Data could not be retrieved from the database.";
        exit;
    }

    $products = $results->fetchAll(PDO::FETCH_ASSOC);    

    return $products;
}


/*
 * Returns an array of product information for the product that matches the sku;
 * returns a boolean false if no product matches the sku
 * @param    int      $sku     the sku
 * @return   mixed    array    list of product information for the one matching product
 *                    bool     false if no product matches
 */


function get_product_single($sku) {

    require(ROOT_PATH . "inc/database.php");

    try {
        $results = $db->prepare("SELECT name, price, img, sku, paypal FROM products WHERE sku = ?");
        $results->bindParam(1,$sku);
        $results->execute();
    } catch (Exception $e) {
        echo "Data could not be retrieved from the database.";
        exit;
    }

    $product = $results->fetch(PDO::FETCH_ASSOC);

    if ($product === false) return $product;

    $product["sizes"] = array();

    try {
        $results = $db->prepare("
            SELECT size
            FROM   products_sizes ps
            INNER JOIN sizes s ON ps.size_id = s.id
            WHERE product_sku = ?
            ORDER BY `order`");
        $results->bindParam(1,$sku);
        $results->execute();
    } catch (Exception $e) {
        echo "Data could not be retrieved from the database.";
        exit;
    }

    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $product["sizes"][] = $row["size"];
    }

    return $product;
}

?>