
<?php

session_start();

header("Content-Type: application/json");

require_once "../config/db.php";

// Check whether user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit;
}

// Accept only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);
    exit;
}

// Receive JSON data
$data = json_decode(file_get_contents("php://input"), true);

$title = trim($data["title"] ?? "");
$description = trim($data["description"] ?? "");
$category = trim($data["category"] ?? "");
$location = trim($data["location"] ?? "");
$preferred_date = trim($data["preferred_date"] ?? "");

// Validate required fields
if ($title === "" || $description === "" || $category === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Title, description and category are required."
    ]);
    exit;
}

// Validate preferred date
if ($preferred_date !== "") {
    $date = DateTime::createFromFormat("!Y-m-d", $preferred_date);

    if (!$date || $date->format("Y-m-d") !== $preferred_date) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Enter a valid date in YYYY-MM-DD format."
        ]);
        exit;
    }
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO help_requests
        (user_id, title, description, category, location, preferred_date)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $_SESSION["user_id"],
        $title,
        $description,
        $category,
        $location !== "" ? $location : null,
        $preferred_date !== "" ? $preferred_date : null
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Help request posted successfully!",
        "request_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to post help request."
    ]);
}

?>
