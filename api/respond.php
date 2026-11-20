
<?php

session_start();
header("Content-Type: application/json");

require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please log in to submit a response."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$request_id = filter_var(
    $data["request_id"] ?? null,
    FILTER_VALIDATE_INT
);

$message = trim($data["message"] ?? "");

if (!$request_id || $message === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Request ID and response message are required."
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];

try {

    // Check whether the help request exists
    $check = $pdo->prepare(
        "SELECT user_id, status FROM help_requests WHERE id = ?"
    );

    $check->execute([$request_id]);
    $request = $check->fetch();

    if (!$request) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Help request not found."
        ]);
        exit;
    }

    // Prevent users from responding to their own requests
    if ($request["user_id"] == $user_id) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "You cannot respond to your own request."
        ]);
        exit;
    }

    // Prevent responses to completed requests
    if ($request["status"] === "Completed") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "This request has already been completed."
        ]);
        exit;
    }

    // Insert the response
    $stmt = $pdo->prepare(
        "INSERT INTO responses (request_id, responder_id, message)
         VALUES (?, ?, ?)"
    );

    $stmt->execute([
        $request_id,
        $user_id,
        $message
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Your response was submitted successfully!"
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error while submitting your response."
    ]);

}

?>
