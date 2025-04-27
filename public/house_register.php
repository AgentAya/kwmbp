<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'agent') {
    header("Location: login.php");
    exit();
}
// Fetch the agent ID from the session
$agent_id = $_SESSION['user']['id'];  // Use the 'id' of the logged-in user
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/csrf.php';
$csrf_token = generateCsrfToken();

 
// Fetch the operator assigned to the agent
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = (SELECT operator_id FROM users WHERE id = ? AND role = 'agent')");
$stmt->execute([$agent_id]);
$operators = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $house_number = trim($_POST['house_number']);
    $street = trim($_POST['street']);
    $area = trim($_POST['area']);
    $city = trim($_POST['city']);

    if (!ctype_digit($house_number)) {
        $_SESSION['error'] = "House number must be numeric.";
        header("Location: house_register.php");
        exit();
    }

    
    function normalizeAddress($house_number, $street, $area, $city) {
        $normalized = strtolower(trim("$house_number $street $area $city"));
        return preg_replace('/\s+/', ' ', $normalized); // Collapse multiple spaces
    }
    
    // Normalized fields
    $normalized_house_number = normalizeInput($_POST['house_number']);
    $normalized_street = normalizeInput($_POST['street']);
    $normalized_area = normalizeInput($_POST['area']);
    $normalized_city = normalizeInput($_POST['city']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM houses WHERE house_number = ? AND street = ? AND area = ? AND city = ?");
    $stmt->execute([$house_number, $street, $area, $city]);

    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "This house already exists in the system.";
        header("Location: house_register.php");
        exit();
    }
    
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register House</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            text-align: center;
            padding: 15px;
            background: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .navbar-brand img {
            width: 50px;
            margin-right: 10px;
        }
        .navbar-brand .logo {
           width: 50px; /* Adjust size of the logo */
           height: auto;
           margin-right: 10px; /* Add spacing between logo and text */
        }
        .navbar-brand span {
          font-family: 'Playfair Display', serif; /* Choose a beautiful font */
          font-size: 24px; /* Adjust font size */
          font-weight: bold; /* Make the text stand out */
          color: white; /* Adjust color to match branding */
    }
        .dashboard-header {
            background: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
        }
        .card {
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .footer {
      background-color: #343a40;
      color: #fff;
      text-align: center;
      padding: 20px;
    }
    </style>
</head>
<body>
    <!-- Navbar -->
 <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="/waste_management_system/public/assets/images/jslogo.png" alt="Logo">
                Jimstar Waste Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="form-container">
        <div class="form-header">
            <h2>🏠 Register a House</h2>
        </div>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</p>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<p class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</p>";
            unset($_SESSION['success']);
        }
        ?>

        <form action="house_register_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="mb-3">
                <label><strong>House Number:</strong></label>
                <input type="number" name="house_number" class="form-control" required min="1" step="1" placeholder="Enter house number">
                <p id="duplicate-warning" class="alert alert-warning mt-2" style="display:none;"></p>
             </div>

            <div class="mb-3">
                <label><strong>Street:</strong></label>
                <input type="text" name="street" class="form-control" required>
            </div>
            <div class="mb-3">
             <label><strong>Area:</strong></label>
             <input type="text" name="area" class="form-control" required placeholder="Enter area">
            </div>
            <div class="mb-3">
                <label><strong>City:</strong></label>
                <input type="text" name="city" class="form-control" required>
            </div>

            <div class="mb-3">
                <label><strong>State:</strong></label>
                <input type="text" name="state" class="form-control" required>
            </div>
            <div class="mb-3">
    <label><strong>Operator:</strong></label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($operators[0]['name']); ?>" readonly>
    <input type="hidden" name="operator_id" value="<?= htmlspecialchars($operators[0]['id']); ?>">
</div>
     <div class="mb-3">
        <label><strong>Owner:</strong></label>
        <input type="text" name="owner" class="form-control" required>
        </div>
     <div class="mb-3">
    <label><strong>Owner Phone:</strong></label>
    <input type="text" name="owner_phone" class="form-control" required placeholder="Enter phone number">
   </div>
   <div class="mb-3">
    <label><strong>Select Building Type:</strong></label>
    <select name="building_type" class="form-select" required>
        <option value="">Choose Building Type</option>
        <?php
        // Fetch building types from the database
        $stmt = $pdo->query("SELECT DISTINCT building_type FROM building_price");
        $building_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($building_types as $type): ?>
            <option value="<?= htmlspecialchars($type['building_type']); ?>">
                <?= htmlspecialchars($type['building_type']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

   <div class="mb-3">
    <label><strong>Number Of Unit(s):</strong></label>
    <input type="number" name="number_of_units" class="form-control" required min="1" step="1" placeholder="Enter  number of units">
  </div>
           <div id="duplicate-warning" class="text-danger mb-3"></div>
            <button type="submit" class="btn btn-primary w-100">Register House</button>
        </form>

        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
        </div>
    </div>
    <div class="footer">&copy; <?php echo date('Y'); ?> Jimstar Waste Management. All Rights Reserved.</div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
let debounceTimer;

function checkDuplicateHouse() {
    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        const houseNumber = document.querySelector("input[name='house_number']").value.trim();
        const street = document.querySelector("input[name='street']").value.trim();
        const city = document.querySelector("input[name='city']").value.trim();
        const area = document.querySelector("input[name='area']").value.trim();

        const submitBtn = document.querySelector("button[type='submit']");
        const warningDiv = document.getElementById("duplicate-warning");

        // Disable button initially
        submitBtn.disabled = true;
        warningDiv.style.display = "none";
        warningDiv.textContent = '';

        // Validate fields before making the request
        if (houseNumber && street && city && area) {
            const formData = new FormData();
            formData.append('house_number', houseNumber);
            formData.append('street', street);
            formData.append('city', city);
            formData.append('area', area);

            fetch('check_duplicate_house.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    // Display warning and disable button
                    warningDiv.textContent = "⚠️ This house already exists in the system!";
                    warningDiv.style.display = "block";
                    submitBtn.disabled = true;
                } else {
                    // Hide warning and enable button
                    warningDiv.textContent = '';
                    warningDiv.style.display = "none";
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error("Error checking for duplicate house:", error);
                warningDiv.textContent = "⚠️ Unable to verify house details. Please try again.";
                warningDiv.style.display = "block";
                submitBtn.disabled = false;
            });
        } else {
            // Show warning if fields are not completely filled
            warningDiv.textContent = "⚠️ Please fill in all required fields.";
            warningDiv.style.display = "block";
            submitBtn.disabled = true;
        }
    }, 300); // Debounce delay of 300ms
}

// Attach input event listeners
["house_number", "street", "city", "area"].forEach(name => {
    document.querySelector(`input[name='${name}']`).addEventListener('input', checkDuplicateHouse);
});
</script>
</body>
</html>
