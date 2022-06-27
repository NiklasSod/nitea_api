<?php

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: *");

    include 'model/database.php';
    // db passwords
    $config = include 'model/dbSettings.php';
    $objDb = new DbConnect;
    $conn = $objDb->connect($config);

    $method = $_SERVER['REQUEST_METHOD'];
    switch($method) {
        case "GET":
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $sql = "SELECT p.product_id AS id, p.product_name AS name, p.product_price AS price, p.product_url, c.currency_name AS currency, GROUP_CONCAT(ca.category_name SEPARATOR ', ') AS genre, p.release_status";
            // for edit view, fetch only the id to edit
            if (isset($path[4]) && is_numeric($path[4])){
                $sql.= " FROM products as p
                JOIN currency AS c ON c.currency_id = p.currency_id
                JOIN product_categories AS pc ON p.product_id = pc.product_id
                JOIN category AS ca ON pc.category_id = ca.category_id
                WHERE p.product_id = :id
                GROUP BY p.product_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $path[4]);
                $stmt->execute();
                $products = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($products);
                break;
            }
            // else fetch all for product view
            $sql.= " FROM products as p
            JOIN currency AS c ON c.currency_id = p.currency_id
            JOIN product_categories AS pc ON p.product_id = pc.product_id
            JOIN category AS ca ON pc.category_id = ca.category_id
            GROUP BY p.product_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($products);
            break;

        case "POST":
            $product = json_decode(file_get_contents('php://input'));
            $sql = "INSERT INTO products(product_id, product_name, product_url, product_price, currency_id)
                VALUES(null, :product_name, :product_url, :product_price, :currency_id);";
            
            $sql2 = "INSERT INTO product_categories(product_id, category_id)
                VALUES(LAST_INSERT_ID(), :category_id);";
            $stmt = $conn->prepare($sql);
            $stmt2 = $conn->prepare($sql2);
            $stmt->bindParam(':product_name', $product->product_name);
            $stmt->bindParam(':product_url', $product->product_url);
            $stmt->bindParam(':product_price', $product->product_price);
            $stmt->bindParam(':currency_id', $product->currency_id);
            $stmt2->bindParam(':category_id', $product->category_id);
            if($stmt->execute()){
                $response = ['status' => 1, 'message' => 'Product created!'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to create product.'];
            }
            if($stmt2->execute()){
                $response2 = ['status' => 1, 'message' => 'Category created!'];
            } else {
                $response2 = ['status' => 0, 'message' => 'Failed to create category.'];
            }
            echo json_encode($response);
            echo json_encode($response2);
            break;

        case "PUT":
            $product = json_decode(file_get_contents('php://input'));
            
            $sql = "UPDATE products SET product_name = :product_name, product_url = :product_url, product_price = :product_price, currency_id = :currency_id WHERE product_id = :id;";
            
            $sql2 = "UPDATE product_categories SET category_id = :category_id WHERE product_id = :id;";
            $stmt = $conn->prepare($sql);
            $stmt2 = $conn->prepare($sql2);
            $stmt->bindParam(':product_name', $product->name);
            $stmt->bindParam(':product_url', $product->product_url);
            $stmt->bindParam(':product_price', $product->price);
            $stmt->bindParam(':currency_id', $product->currency);
            $stmt->bindParam(':id', $product->id);
            $stmt2->bindParam(':category_id', $product->genre);
            $stmt2->bindParam(':id', $product->id);
            if($stmt->execute()){
                $response = ['status' => 1, 'message' => 'Product updated!'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to update product.'];
            }
            if($stmt2->execute()){
                $response2 = ['status' => 1, 'message' => 'Category updated!'];
            } else {
                $response2 = ['status' => 0, 'message' => 'Failed to update category.'];
            }
            echo json_encode($response);
            echo json_encode($response2);
            break;

        case "DELETE":
            $sql = "DELETE FROM product_categories WHERE product_id = :product_id;";
            $sql2 = "DELETE FROM products WHERE product_id = :product_id;";
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $stmt = $conn->prepare($sql);
            $stmt2 = $conn->prepare($sql2);
            $stmt->bindParam(':product_id', $path[4]);
            $stmt2->bindParam(':product_id', $path[4]);
            $stmt->execute();
            $stmt2->execute();
            break;
    };