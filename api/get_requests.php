
<?php

header("Content-Type: application/json");

require_once "../config/db.php";

try {

    $stmt = $conn->prepare(
        "SELECT
            r.id,
            r.title,
            r.description,
            r.category,
            r.location,
            r.preferred_date,
            r.status,
            r.created_at,
            u.name AS user_name
        FROM help_requests r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC"
    );

    $stmt->execute();

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "requests" => $requests
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to retrieve help requests."
    ]);

}

?>
