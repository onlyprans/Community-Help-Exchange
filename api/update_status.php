
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
        "message" => "Please log in again."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$request_id = filter_var(
    $data["request_id"] ?? null,
    FILTER_VALIDATE_INT
);

$status = trim($data["status"] ?? "");

$allowed_statuses = ["Open", "In Progress", "Completed"];

if (!$request_id || !in_array($status, $allowed_statuses, true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid request ID or status."
    ]);
    exit;
}

try {

    // Update only the logged-in user's own request
    $stmt = $pdo->prepare(
        "UPDATE help_requests
         SET status = ?
         WHERE id = ? AND user_id = ?"
    );

    $stmt->execute([
        $status,
        $request_id,
        $_SESSION["user_id"]
    ]);

    if ($stmt->rowCount() === 0) {

        // Check whether the request exists and belongs to this user
        $check = $pdo->prepare(
            "SELECT id, status
             FROM help_requests
             WHERE id = ? AND user_id = ?"
        );

        $check->execute([
            $request_id,
            $_SESSION["user_id"]
        ]);

        $existing = $check->fetch();

        if (!$existing) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Request not found or you do not own it."
            ]);
            exit;
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Request status updated to " . $status . "!"
    ]);

} catch (PDOException $e) {

    error_log("Status update error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error while updating status."
    ]);
}

?>
