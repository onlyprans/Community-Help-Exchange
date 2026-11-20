
<?php
session_start();
require_once "config/db.php";

$isLoggedIn = isset($_SESSION["user_id"]);
$currentUserId = $_SESSION["user_id"] ?? 0;

$requestQuery = $pdo->query("
    SELECT help_requests.*, users.name AS user_name
    FROM help_requests
    JOIN users ON help_requests.user_id = users.id
    ORDER BY help_requests.created_at DESC
");
$requests = $requestQuery->fetchAll(PDO::FETCH_ASSOC);

$offerQuery = $pdo->query("
    SELECT help_offers.*, users.name AS user_name
    FROM help_offers
    JOIN users ON help_offers.user_id = users.id
    ORDER BY help_offers.created_at DESC
");
$offers = $offerQuery->fetchAll(PDO::FETCH_ASSOC);

// Collect categories from both requests and offers
$categories = [];

foreach ($requests as $request) {
    if (!empty($request["category"])) {
        $categories[] = $request["category"];
    }
}

foreach ($offers as $offer) {
    if (!empty($offer["category"])) {
        $categories[] = $offer["category"];
    }
}

$categories = array_unique($categories);
sort($categories);

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Help | Community Help Exchange</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .browse-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }

        .browse-controls input,
        .browse-controls select {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }

        .filter-btn.active {
            background-color: #333;
            color: white;
        }

        .no-results {
            display: none;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="container nav-container">
        <a href="index.html" class="logo">Community Help Exchange</a>

        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="browse.php">Browse Help</a>
            <a href="offer-help.html">Offer Help</a>

            <?php if ($isLoggedIn): ?>
                <a href="dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="login.html">Login</a>
                <a href="register.html">Join Us</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container">

    <section class="page-header">
        <h1>Explore Your Community</h1>
        <p>Find someone who needs help or discover skills shared by others.</p>
    </section>

    <div class="browse-controls">

        <input
            type="text"
            id="searchInput"
            placeholder="Search by title, description, location or keyword..."
        >

        <select id="categoryFilter">
            <option value="all">All Categories</option>

            <?php foreach ($categories as $category): ?>
                <option value="<?= e(strtolower($category)) ?>">
                    <?= e($category) ?>
                </option>
            <?php endforeach; ?>

        </select>

    </div>

    <div class="filter-buttons">
        <button class="btn filter-btn active" data-filter="all">All</button>
        <button class="btn filter-btn" data-filter="requests">Help Requests</button>
        <button class="btn filter-btn" data-filter="offers">Help Offers</button>
        <button class="btn" id="clearFilters">Clear Filters</button>
    </div>

    <section id="requestsSection">

        <h2>People Looking for Help</h2>

        <div class="cards-grid">

            <?php if (count($requests) === 0): ?>
                <p>No help requests have been posted yet.</p>
            <?php endif; ?>

            <?php foreach ($requests as $request): ?>

                <article class="card listing-card"
                    data-type="requests"
                    data-category="<?= e(strtolower($request["category"])) ?>"
                    data-search="<?= e($request["title"] . " " . $request["description"] . " " . $request["category"] . " " . $request["location"]) ?>">

                    <span class="status status-<?= strtolower(str_replace(" ", "-", e($request["status"]))) ?>">
                        <?= e($request["status"]) ?>
                    </span>

                    <h3><?= e($request["title"]) ?></h3>

                    <p><?= e($request["description"]) ?></p>

                    <p><strong>Category:</strong> <?= e($request["category"]) ?></p>
                    <p><strong>Location:</strong> <?= e($request["location"] ?: "Not specified") ?></p>
                    <p><strong>Needed by:</strong> <?= e($request["preferred_date"] ?: "Flexible") ?></p>
                    <p><strong>Posted by:</strong> <?= e($request["user_name"]) ?></p>

                    <?php if (
                        $isLoggedIn &&
                        (int)$request["user_id"] !== (int)$currentUserId &&
                        $request["status"] !== "Completed"
                    ): ?>

                        <form class="response-form">
                            <input type="hidden" name="request_id" value="<?= (int)$request["id"] ?>">

                            <textarea
                                name="message"
                                placeholder="Write a message offering your help..."
                                required
                            ></textarea>

                            <button type="submit" class="btn btn-primary">
                                Respond to Request
                            </button>
                        </form>

                    <?php elseif (!$isLoggedIn): ?>

                        <a href="login.html" class="btn btn-primary">
                            Login to Respond
                        </a>

                    <?php endif; ?>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

    <section id="offersSection">

        <h2>Skills People Are Offering</h2>

        <div class="cards-grid">

            <?php if (count($offers) === 0): ?>
                <p>No help offers have been posted yet.</p>
            <?php endif; ?>

            <?php foreach ($offers as $offer): ?>

                <article class="card listing-card"
                    data-type="offers"
                    data-category="<?= e(strtolower($offer["category"])) ?>"
                    data-search="<?= e($offer["title"] . " " . $offer["description"] . " " . $offer["category"] . " " . $offer["location"]) ?>">

                    <h3><?= e($offer["title"]) ?></h3>

                    <p><?= e($offer["description"]) ?></p>

                    <p><strong>Category:</strong> <?= e($offer["category"]) ?></p>
                    <p><strong>Location:</strong> <?= e($offer["location"] ?: "Not specified") ?></p>
                    <p><strong>Availability:</strong> <?= e($offer["availability"] ?: "Flexible") ?></p>
                    <p><strong>Offered by:</strong> <?= e($offer["user_name"]) ?></p>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

    <p class="no-results" id="noResults">
        No matching listings found. Try changing your search or filters.
    </p>

</main>

<footer class="footer">
    <p>Community Help Exchange | Building stronger communities together.</p>
</footer>

<script>

const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const filterButtons = document.querySelectorAll(".filter-btn");
const listingCards = document.querySelectorAll(".listing-card");

const requestsSection = document.getElementById("requestsSection");
const offersSection = document.getElementById("offersSection");
const noResults = document.getElementById("noResults");

let currentFilter = "all";

function filterListings() {

    const searchTerm = searchInput.value.toLowerCase().trim();
    const selectedCategory = categoryFilter.value;

    let visibleCount = 0;

    listingCards.forEach(card => {

        const matchesSearch =
            card.dataset.search.toLowerCase().includes(searchTerm);

        const matchesCategory =
            selectedCategory === "all" ||
            card.dataset.category === selectedCategory;

        const matchesType =
            currentFilter === "all" ||
            card.dataset.type === currentFilter;

        const isVisible =
            matchesSearch && matchesCategory && matchesType;

        card.style.display = isVisible ? "" : "none";

        if (isVisible) {
            visibleCount++;
        }

    });

    requestsSection.style.display =
        currentFilter === "offers" ? "none" : "";

    offersSection.style.display =
        currentFilter === "requests" ? "none" : "";

    noResults.style.display =
        visibleCount === 0 ? "block" : "none";
}

searchInput.addEventListener("input", filterListings);
categoryFilter.addEventListener("change", filterListings);

filterButtons.forEach(button => {

    button.addEventListener("click", () => {

        currentFilter = button.dataset.filter;

        filterButtons.forEach(btn => btn.classList.remove("active"));

        button.classList.add("active");

        filterListings();

    });

});

document.getElementById("clearFilters").addEventListener("click", () => {

    searchInput.value = "";
    categoryFilter.value = "all";
    currentFilter = "all";

    filterButtons.forEach(btn => btn.classList.remove("active"));

    document.querySelector('[data-filter="all"]').classList.add("active");

    filterListings();

});

// Submit responses to help requests
document.querySelectorAll(".response-form").forEach(form => {

    form.addEventListener("submit", async event => {

        event.preventDefault();

        const requestId =
            form.querySelector('[name="request_id"]').value;

        const message =
            form.querySelector('[name="message"]').value;

        const button = form.querySelector("button");

        button.disabled = true;
        button.textContent = "Sending...";

        try {

            const response = await fetch("api/respond.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "same-origin",
                body: JSON.stringify({
                    request_id: requestId,
                    message: message
                })
            });

            const result = await response.json();

            if (result.success) {

                alert("Your response has been sent successfully!");
                form.reset();

            } else {

                alert(result.message || "Could not send your response.");

            }

        } catch (error) {

            alert("Something went wrong. Please try again.");

        } finally {

            button.disabled = false;
            button.textContent = "Respond to Request";

        }

    });

});

</script>

</body>
</html>
