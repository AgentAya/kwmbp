 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jimstar Waste Management</title>

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f8f9fa;
    }
    .navbar-brand .logo {
    width: 50px; /* Adjust size of the logo */
    height: auto;
    margin-right: 10px; /* Add spacing between logo and text */
    }
    .navbar-brand {
    display: flex; /* Display elements side by side */
    align-items: center; /* Center align the logo and text */
    text-decoration: none;
    color: #333; /* Adjust text color */
    }
     
    .navbar-brand span {
    font-family: 'Playfair Display', serif; /* Choose a beautiful font */
    font-size: 24px; /* Adjust font size */
    font-weight: bold; /* Make the text stand out */
    color: white; /* Adjust color to match branding */
    }
    .hero {
      background: url("/waste_management_system/public/assets/images/hero.jpg") no-repeat center center;
      background-size: cover;
      height: 100vh;
      position: relative;
      color: #fff;
    }
    .hero-overlay {
      background-color: rgba(46, 29, 29, 0.6);
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
    }
    .hero-content {
      position: relative;
      z-index: 2;
      text-align: center;
      padding-top: 200px;
    }
    .service-box {
      text-align: center;
      padding: 30px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease-in-out;
      margin-bottom: 30px;
    }
    .service-box:hover {
      transform: translateY(-5px);
    }
    .service-box img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      border-radius: 10px;
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
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">
      <img src="/waste_management_system/public/assets/images/jslogo.png" alt="Image" class="logo" width="50">
        <span>Jimstar Waste Management</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
    <li class="nav-item"><a class="nav-link" href="guest_login.php">Make Payment</a></li>
     <li class="nav-item"><a class="nav-link" href="#" onclick="return false;">Contact Us</a></li>
    <li class="nav-item"><a class="nav-link" href="#" onclick="return false;">FAQ</a></li>
    <li class="nav-item"><a class="nav-link" href="guest_login.php">House  Login</a></li>
 </ul>

      </div>
    </div>
  </nav>

  <div class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="display-4 fw-bold">Cleaner Communities, Greener Future</h1>
      <p class="lead">Efficient Waste Collection, Recycling & Management</p>
      <a href="login.php" class="btn btn-primary btn-lg">Staff Login</a>
      <a href="register.php" class="btn btn-outline-light btn-lg">Register</a>
    </div>
  </div>
<!-- About Section -->
<div class="container content-section text-center">
    <h2 class="fw-bold">About Jimstar Waste Management</h2>
    <p class="lead text-muted">
      Our system is designed to optimize waste collection, recycling, and management processes.
      We serve both residential and industrial sectors, ensuring a cleaner environment and promoting sustainable recycling practices.
    </p>
  </div>

  <!-- Services Section -->
  <div class="container content-section">
    <h2 class="text-center fw-bold">Our Services</h2>
    <div class="row mt-4">
      <!-- Residential Waste Collection -->
      <div class="col-md-4">
        <div class="service-box">
        <img src="/waste_management_system/public/assets/images/residentialwaste.jpg" alt="Residential Waste Collection">
        <h3 class="mt-3">Residential Waste Collection</h3>
          <p>
            We provide efficient and timely waste collection services for households, ensuring clean neighborhoods with proper waste disposal.
          </p>
        </div>
      </div>
      <!-- Industrial Waste Collection -->
      <div class="col-md-4">
        <div class="service-box">
        <img src= "/waste_management_system/public/assets/images/indutrial.jpg" alt="Industrial Waste Collection">
        <h3 class="mt-3">Industrial Waste Collection</h3>
          <p>
            Our industrial waste management service ensures safe and compliant disposal of hazardous and non-hazardous waste materials.
          </p>
        </div>
      </div>
      <!-- Waste Recycling Services -->
      <div class="col-md-4">
        <div class="service-box">
        <img src= "/waste_management_system/public/assets/images/recycle.webp" alt="Waste Recycling Services">
        <h3 class="mt-3">Waste Recycling Services</h3>
          <p>
            We promote recycling initiatives to convert waste into useful materials, reducing environmental impact and supporting sustainability.
          </p>
        </div>
      </div>
    </div>
  </div>


  <div class="footer">&copy; <?php echo date('Y'); ?> Jimstar Waste Management. All Rights Reserved.</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
