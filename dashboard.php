
<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

require_once "config/db.php";

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"] ?? "Member";

// Fetch user's help requests
$requestStmt = $pdo->prepare(
    "SELECT * FROM help_requests
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$requestStmt->execute([$user_id]);
$requests = $requestStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's help offers
$offerStmt = $pdo->prepare(
    "SELECT * FROM help_offers
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$offerStmt->execute([$user_id]);
$offers = $offerStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch responses received
$responseStmt = $pdo->prepare(
    "SELECT
        responses.id,
        responses.message,
        responses.status,
        responses.created_at,
        responses.request_id,
        users.name AS responder_name,
        help_requests.title AS request_title
     FROM responses
     JOIN help_requests
        ON responses.request_id = help_requests.id
     JOIN users
        ON responses.responder_id = users.id
     WHERE help_requests.user_id = ?
     ORDER BY responses.created_at DESC"
);
$responseStmt->execute([$user_id]);
$responses = $responseStmt->fetchAll(PDO::FETCH_ASSOC);

// Dashboard statistics
$total_requests = count($requests);
$total_offers = count($offers);

$completed_requests = 0;
$pending_responses = 0;

foreach ($requests as $request) {
    if ($request["status"] === "Completed") {
        $completed_requests++;
    }
}

foreach ($responses as $response) {
    if ($response["status"] === "Pending") {
        $pending_responses++;
    }
}

// Escape HTML output
function escapeHTML($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

// Status styling
function statusClass($status) {
    if ($status === "Open") {
        return "status-open";
    }

    if ($status === "In Progress" || $status === "Accepted") {
        return "status-progress";
    }

    return "status-completed";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Dashboard | Community Help Exchange</title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background: #fbf8f1;
        }

        .dashboard-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px 70px;
        }

        .dashboard-welcome {
            background: linear-gradient(120deg, #176b67, #10534f);
            color: white;
            padding: 42px;
            border-radius: 20px;
            margin-bottom: 32px;
        }

        .dashboard-welcome h1 {
            color: white;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .dashboard-welcome p {
            color: #e1f1ed;
            margin-bottom: 22px;
        }

        .dashboard-welcome .welcome-tag {
            display: inline-block;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
            color: #f4c9b8;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 55px;
        }

        .stat-card {
            background: white;
            border: 1px solid #eee8df;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.035);
        }

        .stat-card h3 {
            font-size: 32px;
            color: #176b67;
            margin-bottom: 7px;
        }

        .stat-card p {
            color: #777;
            margin: 0;
            font-size: 14px;
        }

        .dashboard-section {
            margin-top: 55px;
        }

        .dashboard-section-heading {
            margin-bottom: 22px;
        }

        .dashboard-section-heading h2 {
            color: #194e4b;
            margin-bottom: 7px;
        }

        .dashboard-section-heading p {
            color: #777;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .dashboard-card {
            background: white;
            border: 1px solid #eee8df;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.035);
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .dashboard-card h3 {
            color: #194e4b;
            margin-bottom: 12px;
        }

        .dashboard-card p {
            color: #666;
            line-height: 1.65;
            margin-bottom: 12px;
        }

        .dashboard-card .btn {
            display: inline-block;
            margin-top: 10px;
        }

        .dashboard-card .status {
            display: inline-block;
            margin: 5px 0 12px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .dashboard-card .status-open {
            background: #e3f3e8;
            color: #287044;
        }

        .dashboard-card .status-progress {
            background: #fff1d9;
            color: #936019;
        }

        .dashboard-card .status-completed {
            background: #f0e9e7;
            color: #79574e;
        }

        .dashboard-card label {
            display: block;
            margin-top: 14px;
            color: #444;
        }

        .dashboard-card select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            box-sizing: border-box;
        }

        .dashboard-card button {
            cursor: pointer;
        }

        .dashboard-card .response-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .dashboard-card .response-actions .btn {
            margin-top: 8px;
        }

        .dashboard-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 32px;
        }

        .dashboard-empty p {
            margin-bottom: 12px;
        }

        .dashboard-divider {
            border: none;
            border-top: 1px solid #eee8df;
            margin: 50px 0;
        }

        .dashboard-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 16px 5%;
            background: white;
            border-bottom: 1px solid #eee8df;
        }

        .dashboard-navbar .logo {
            color: #176b67;
            font-weight: 700;
            text-decoration: none;
            font-size: 21px;
        }

        .dashboard-navbar .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }

        .dashboard-navbar .nav-links a {
            text-decoration: none;
            color: #444;
        }

        .dashboard-navbar .nav-links a:hover {
            color: #176b67;
        }

        .dashboard-navbar .nav-links .btn {
            color: #176b67;
        }

        @media (max-width: 900px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-navbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 600px) {
            .dashboard-main {
                padding: 25px 16px 50px;
            }

            .dashboard-welcome {
                padding: 28px 22px;
            }

            .dashboard-welcome h1 {
                font-size: 25px;
            }

            .dashboard-stats,
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-navbar .nav-links {
                gap: 14px;
            }

            .dashboard-navbar .nav-links a {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<nav class="dashboard-navbar">

    <a href="index.html" class="logo">
        Community Help Exchange
    </a>

    <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="browse.php">Browse Help</a></li>
        <li><a href="request-help.html">Request Help</a></li>
        <li><a href="offer-help.html">Offer Help</a></li>
        <li>
            <a href="#" id="logoutBtn" class="btn btn-outline">Logout</a>
        </li>
    </ul>

</nav>

<main class="dashboard-main">

    <!-- Welcome Banner -->
    <section class="dashboard-welcome">

        <span class="welcome-tag">YOUR COMMUNITY SPACE</span>

        <h1>
            Welcome, <?php echo escapeHTML($user_name); ?>!
        </h1>

        <p>
            Every little act of kindness makes a difference.
            Here's what's happening with your community activity.
        </p>

        <a href="browse.php" class="btn btn-secondary">
            Explore Community
        </a>

    </section>


    <!-- Dashboard Statistics -->
    <section>

        <div class="dashboard-section-heading">
            <h2>Your Activity</h2>
            <p>A quick overview of your contributions.</p>
        </div>

        <div class="dashboard-stats">

            <div class="stat-card">
                <h3><?php echo $total_requests; ?></h3>
                <p>Requests Posted</p>
            </div>

            <div class="stat-card">
                <h3><?php echo $total_offers; ?></h3>
                <p>Offers Shared</p>
            </div>

            <div class="stat-card">
                <h3><?php echo $completed_requests; ?></h3>
                <p>Completed Requests</p>
            </div>

            <div class="stat-card">
                <h3><?php echo $pending_responses; ?></h3>
                <p>Pending Responses</p>
            </div>

        </div>

    </section>


    <!-- Quick Actions -->
    <section class="dashboard-section">

        <div class="dashboard-section-heading">
            <h2>What Would You Like to Do?</h2>
            <p>Choose how you'd like to participate in your community.</p>
        </div>

        <div class="dashboard-grid">

            <div class="dashboard-card">
                <h3>Need Help?</h3>
                <p>
                    Have something you need assistance with?
                    Post a request and connect with people who can help.
                </p>
                <a href="request-help.html" class="btn btn-primary">
                    Post a Request
                </a>
            </div>

            <div class="dashboard-card">
                <h3>Want to Help?</h3>
                <p>
                    Share your skills, knowledge, or resources
                    and make a positive difference.
                </p>
                <a href="offer-help.html" class="btn btn-secondary">
                    Offer a Skill
                </a>
            </div>

            <div class="dashboard-card">
                <h3>Explore Community</h3>
                <p>
                    Discover help requests and offers
                    shared by other community members.
                </p>
                <a href="browse.php" class="btn btn-outline">
                    Browse Listings
                </a>
            </div>

        </div>

    </section>


    <!-- My Help Requests -->
    <section class="dashboard-section">

        <div class="dashboard-section-heading">
            <h2>My Help Requests</h2>
            <p>Requests you've posted. Manage their progress here.</p>
        </div>

        <div class="dashboard-grid">

            <?php if (count($requests) > 0): ?>

                <?php foreach ($requests as $request): ?>

                    <div class="dashboard-card">

                        <h3>
                            <?php echo escapeHTML($request["title"]); ?>
                        </h3>

                        <p>
                            <?php echo escapeHTML($request["description"]); ?>
                        </p>

                        <p>
                            <strong>Category:</strong>
                            <?php echo escapeHTML($request["category"]); ?>
                        </p>

                        <p>
                            <strong>Location:</strong>
                            <?php echo escapeHTML($request["location"] ?: "Not specified"); ?>
                        </p>

                        <p><strong>Request Status:</strong></p>

                        <span class="status <?php echo statusClass($request["status"]); ?>">
                            <?php echo escapeHTML($request["status"]); ?>
                        </span>

                        <label for="status-<?php echo (int)$request['id']; ?>">
                            <strong>Update Status</strong>
                        </label>

                        <select
                            id="status-<?php echo (int)$request['id']; ?>"
                        >
                            <option value="Open"
                                <?php echo $request["status"] === "Open" ? "selected" : ""; ?>>
                                Open
                            </option>

                            <option value="In Progress"
                                <?php echo $request["status"] === "In Progress" ? "selected" : ""; ?>>
                                In Progress
                            </option>

                            <option value="Completed"
                                <?php echo $request["status"] === "Completed" ? "selected" : ""; ?>>
                                Completed
                            </option>
                        </select>

                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="updateRequestStatus(<?php echo (int)$request['id']; ?>)"
                        >
                            Save Status
                        </button>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="dashboard-card dashboard-empty">
                    <h3>No Requests Yet</h3>
                    <p>You haven't posted any help requests yet.</p>
                    <a href="request-help.html" class="btn btn-primary">
                        Post Your First Request
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- Responses Received -->
    <section class="dashboard-section">

        <div class="dashboard-section-heading">
            <h2>Responses Received</h2>
            <p>People who have responded to your help requests.</p>
        </div>

        <div class="dashboard-grid">

            <?php if (count($responses) > 0): ?>

                <?php foreach ($responses as $response): ?>

                    <div class="dashboard-card">

                        <h3>
                            <?php echo escapeHTML($response["request_title"]); ?>
                        </h3>

                        <p>
                            <strong>Responded by:</strong>
                            <?php echo escapeHTML($response["responder_name"]); ?>
                        </p>

                        <p><strong>Message:</strong></p>

                        <p>
                            <?php echo nl2br(escapeHTML($response["message"])); ?>
                        </p>

                        <p><strong>Response Status:</strong></p>

                        <span class="status <?php echo statusClass($response["status"]); ?>">
                            <?php echo escapeHTML($response["status"]); ?>
                        </span>

                        <p>
                            <strong>Received:</strong><br>
                            <?php echo escapeHTML($response["created_at"]); ?>
                        </p>

                        <?php if ($response["status"] === "Pending"): ?>

                            <div class="response-actions">

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    onclick="updateResponse(<?php echo (int)$response['id']; ?>, 'Accepted')"
                                >
                                    Accept
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline"
                                    onclick="updateResponse(<?php echo (int)$response['id']; ?>, 'Rejected')"
                                >
                                    Reject
                                </button>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="dashboard-card dashboard-empty">
                    <h3>No Responses Yet</h3>
                    <p>
                        When someone responds to your help request,
                        their message will appear here.
                    </p>
                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- My Help Offers -->
    <section class="dashboard-section">

        <div class="dashboard-section-heading">
            <h2>My Help Offers</h2>
            <p>Skills and services you've shared with the community.</p>
        </div>

        <div class="dashboard-grid">

            <?php if (count($offers) > 0): ?>

                <?php foreach ($offers as $offer): ?>

                    <div class="dashboard-card">

                        <h3>
                            <?php echo escapeHTML($offer["title"]); ?>
                        </h3>

                        <p>
                            <?php echo escapeHTML($offer["description"]); ?>
                        </p>

                        <p>
                            <strong>Category:</strong>
                            <?php echo escapeHTML($offer["category"]); ?>
                        </p>

                        <p>
                            <strong>Availability:</strong>
                            <?php echo escapeHTML($offer["availability"] ?: "Not specified"); ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="dashboard-card dashboard-empty">
                    <h3>No Offers Yet</h3>
                    <p>You haven't shared any skills yet.</p>
                    <a href="offer-help.html" class="btn btn-secondary">
                        Share Your First Skill
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </section>

</main>


<footer class="footer">
    <p>Community Help Exchange</p>
    <p>Together, we make helping easier.</p>
</footer>


<script>

// Update help request status
async function updateRequestStatus(requestId) {

    const select = document.getElementById("status-" + requestId);
    const newStatus = select.value;

    if (!confirm("Change this request status to " + newStatus + "?")) {
        return;
    }

    try {

        const response = await fetch("api/update_status.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "same-origin",
            body: JSON.stringify({
                request_id: requestId,
                status: newStatus
            })
        });

        const result = await response.json();

        alert(result.message);

        if (result.success) {
            location.reload();
        }

    } catch (error) {
        console.error(error);
        alert("Unable to update request status.");
    }

}


// Logout functionality
document.getElementById("logoutBtn").addEventListener("click", async function(event) {

    event.preventDefault();

    try {

        const response = await fetch("api/logout.php", {
            method: "POST",
            credentials: "same-origin"
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = "index.html";
        } else {
            alert(result.message || "Unable to logout.");
        }

    } catch (error) {
        alert("Unable to connect to the server.");
    }

});


// Accept or reject a response
async function updateResponse(responseId, status) {

    const confirmation = confirm(
        "Are you sure you want to " + status.toLowerCase() + " this response?"
    );

    if (!confirmation) {
        return;
    }

    try {

        const response = await fetch("api/update_response.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "same-origin",
            body: JSON.stringify({
                response_id: responseId,
                status: status
            })
        });

        const result = await response.json();

        alert(result.message);

        if (result.success) {
            location.reload();
        }

    } catch (error) {
        console.error(error);
        alert("Unable to update response. Please try again.");
    }

}

</script>

</body>
</html>
