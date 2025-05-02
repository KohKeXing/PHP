<?php
session_start();

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the cart items from the request body
    $cartItems = json_decode(file_get_contents('php://input'), true);

    // Validate the input to ensure it's an array
    if (is_array($cartItems)) {
        // Store cart items in session as JSON
        $_SESSION['cartItems'] = json_encode($cartItems);
        // Log to check if items are stored correctly
        error_log(print_r($cartItems, true)); // Log the cart items
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cart item format.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}