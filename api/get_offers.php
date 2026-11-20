
<?php

header("Content-Type: application/json");

require_once "../config/db.php";

try {

    $stmt = $pdo->prepare(
        "SELECT
            o.id,
            o.title,
            o.description,
            o.category,
            o.location,
            o.availability,
            o.created_at,
            u.name AS user_name
        FROM help_offers o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC"
    );

    $stmt->execute();

    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "offers" => $offers
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to retrieve help offers."
    ]);

}

?>
