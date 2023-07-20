<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include 'dbinfo.php';

print_r(file_get_contents("php://input"));

$method = $_SERVER['REQUEST_METHOD'];
switch($method) {
    case "GET":
        $sql = "SELECT * FROM customers";
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if(isset($path[3]) && is_numeric($path[3])) {
            $sql .= " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(':id', $path[3]);
            $stmt->execute();
            $users = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo json_encode($users);
        break;
    case "POST":
        $user = json_decode( file_get_contents('php://input') );
        $sql = "INSERT INTO customers (name, email, mobile, created) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bind_param('ssss', $user->name, $user->email, $user->mobile, $created_at);
        // $stmt->bind_param('s', $user->email);
        // $stmt->bind_param('s', $user->mobile);
        // $stmt->bind_param('s', $created_at);

        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record created successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to create record.'];
        }
        echo json_encode($response);
        break;

    case "PUT":
        $user = json_decode( file_get_contents('php://input') );
        $sql = "UPDATE customers SET name= ?, email = ?, mobile = ?, updated_at = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bind_param('sssss', $user->name, $user->email, $user->mobile, $updated_at, $user->id);
        // $stmt->bind_param(':name', $user->name);
        // $stmt->bind_param(':email', $user->email);
        // $stmt->bind_param(':mobile', $user->mobile);
        // $stmt->bind_param(':updated_at', $updated_at);

        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record updated successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to update record.'];
        }
        echo json_encode($response);
        break;

    case "DELETE":
        $sql = "DELETE FROM customers WHERE id = ?";
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $path[3]);

        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record deleted successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to delete record.'];
        }
        echo json_encode($response);
        break;
}