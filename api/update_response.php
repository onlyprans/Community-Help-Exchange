
<?php

session_start();
require_once "../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$response_id = $data["response_id"] ?? null;
$status = $data["status"] ?? "";

if (!$response_id || !in_array($status, ["Accepted", "Rejected"], true)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid response details."
    ]);
    exit;
}

// Only the owner of the original help request can update its response
$stmt = $pdo->prepare("
    UPDATE responses
    JOIN help_requests
        ON responses.request_id = help_requests.id
    SET responses.status = ?
    WHERE responses.id = ?
      AND help_requests.user_id = ?
      AND responses.status = 'Pending'
");

$stmt->execute([$status, $response_id, $_SESSION["user_id"]]);

if ($stmt->rowCount() > 0) {
    echo json_encode([
        "success" => true,
        "message" => "Response " . strtolower($status) . " successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Response could not be updated. It may already have been handled."
    ]);
}

?>
