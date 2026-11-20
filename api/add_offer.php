
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
        "message" => "Please login first."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$title = trim($data["title"] ?? "");
$description = trim($data["description"] ?? "");
$category = trim($data["category"] ?? "");
$location = trim($data["location"] ?? "");
$availability = trim($data["availability"] ?? "");

if ($title === "" || $description === "" || $category === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Title, description and category are required."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO help_offers
        (user_id, title, description, category, location, availability)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $_SESSION["user_id"],
        $title,
        $description,
        $category,
        $location !== "" ? $location : null,
        $availability !== "" ? $availability : null
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Help offer posted successfully!",
        "offer_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log("Add offer error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to post help offer."
    ]);
}
?>
