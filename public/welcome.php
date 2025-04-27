<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jimstar Waste Management</title>

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<!-- AOS (Animate On Scroll) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

  <style>
    body {
  font-family: 'Poppins', sans-serif;
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

  <!-- Hero Section -->
  <div class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="display-4 fw-bold" data-aos="fade-down">Cleaner Communities, Greener Future</h1>
      <p class="lead" data-aos="fade-up">Efficient Waste Collection, Recycling & Management</p>
      <a href="login.php" class="btn btn-primary btn-lg" data-aos="fade-right">Staff Login</a>
      <a href="register.php" class="btn btn-outline-light btn-lg" data-aos="fade-left">Register</a>
    </div>
  </div>

<!-- About Section -->
<section class="py-5 bg-light">
  <div class="container text-center" data-aos="fade-up">
    <h2 class="fw-bold mb-4">About Jimstar Waste Management</h2>
    <p class="lead text-muted mx-auto" style="max-width: 800px;">
      At Jimstar, we're committed to sustainable waste management. Our platform streamlines waste collection, promotes recycling, and ensures compliance in residential, commercial, and industrial sectors.
    </p>
    <div class="row mt-4">
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
        <div class="p-4 bg-white rounded shadow-sm">
          <img src="https://cdn-icons-png.flaticon.com/512/1904/1904425.png" alt="Eco-Friendly" width="60" class="mb-3">
          <h5>Eco-Friendly Mission</h5>
          <p>We reduce landfill waste by encouraging recycling and responsible waste practices.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
        <div class="p-4 bg-white rounded shadow-sm">
          <img src="https://cdn-icons-png.flaticon.com/512/2942/2942920.png" alt="Smart System" width="60" class="mb-3">
          <h5>Smart Collection System</h5>
          <p>Agents and operators collaborate with our platform to ensure real-time waste tracking.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
        <div class="p-4 bg-white rounded shadow-sm">
          <img src="https://cdn-icons-png.flaticon.com/512/2620/2620503.png" alt="Transparency" width="60" class="mb-3">
          <h5>Transparent Billing</h5>
          <p>House owners and agents can track payment history, receipts, and service status easily.</p>
        </div>
      </div>
    </div>
  </div>
</section>
  <!-- Services Section -->
  <div class="container content-section py-5">
    <h2 class="text-center fw-bold mb-5" data-aos="fade-up">Our Services</h2>
    <div class="row g-4">
      <!-- Residential Waste Collection -->
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card h-100 shadow-sm border-0">
          <img src="/waste_management_system/public/assets/images/residentialwaste.jpg" class="card-img-top" alt="Residential Waste">
          <div class="card-body text-center">
            <h5 class="card-title">Residential Waste Collection</h5>
            <p class="card-text">Timely waste collection for homes, keeping neighborhoods clean and healthy.</p>
          </div>
        </div>
      </div>
     <!-- Industrial Waste Collection -->
     <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card h-100 shadow-sm border-0">
          <img src="/waste_management_system/public/assets/images/indutrial.jpg" class="card-img-top" alt="Industrial Waste">
          <div class="card-body text-center">
            <h5 class="card-title">Industrial Waste Collection</h5>
            <p class="card-text">Safe disposal of hazardous and non-hazardous industrial waste materials.</p>
          </div>
        </div>
      </div>
      <!-- Recycling Services -->
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card h-100 shadow-sm border-0">
          <img src="/waste_management_system/public/assets/images/recycle.webp" class="card-img-top" alt="Recycling">
          <div class="card-body text-center">
            <h5 class="card-title">Recycling Services</h5>
            <p class="card-text">Promoting sustainability by transforming waste into reusable resources.</p>
          </div>
        </div>
      </div>

      <!-- Why Choose Us / Stats Section -->
<section class="py-5">
  <div class="container text-center">
    <h2 class="fw-bold mb-4" data-aos="fade-up">Why Choose Jimstar?</h2>
    <div class="row mt-4">
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
        <h1 class="display-4 fw-bold text-success">98%</h1>
        <p class="text-muted">Collection Success Rate</p>
      </div>
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
        <h1 class="display-4 fw-bold text-success">200+</h1>
        <p class="text-muted">Communities Served</p>
      </div>
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
        <h1 class="display-4 fw-bold text-success">5000+</h1>
        <p class="text-muted">Homes Registered</p>
      </div>
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
        <h1 class="display-4 fw-bold text-success">100%</h1>
        <p class="text-muted">Secure Payments</p>
      </div>
    </div>
  </div>
</section>
<!-- Testimonials Carousel Section -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="fw-bold text-center mb-5" data-aos="fade-up">What Our Clients Say</h2>

    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner text-center">
        <!-- Testimonial 1 -->
        <div class="carousel-item active">
          <div class="p-4">
            <img src="https://i.pravatar.cc/100?img=1" class="rounded-circle mb-3" alt="User 1">
            <blockquote class="blockquote">
              <p class="mb-0">"Jimstar has completely transformed our neighborhood. Waste is collected on time, and their support is excellent!"</p>
            </blockquote>
            <footer class="blockquote-footer mt-2">Wakili O., Osogbo</footer>
          </div>
        </div>
        <!-- Testimonial 2 -->
        <div class="carousel-item">
          <div class="p-4">
            <img src="https://i.pravatar.cc/100?img=5" class="rounded-circle mb-3" alt="User 2">
            <blockquote class="blockquote">
              <p class="mb-0">"The online payment and receipt system is super easy to use. Highly recommended for every home."</p>
            </blockquote>
            <footer class="blockquote-footer mt-2">Grace T., Ede</footer>
          </div>
        </div>
        <!-- Testimonial 3 -->
        <div class="carousel-item">
          <div class="p-4">
            <img src="https://i.pravatar.cc/100?img=9" class="rounded-circle mb-3" alt="User 3">
            <blockquote class="blockquote">
              <p class="mb-0">"As an agent, the Jimstar platform saves me hours of paperwork and allows real-time updates."</p>
            </blockquote>
            <footer class="blockquote-footer mt-2">Emeka I., Ife</footer>
          </div>
        </div>
      </div>

      <!-- Carousel Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
</section>
<!-- How It Works -->
<section class="py-5 text-center bg-white">
  <div class="container">
  <h2 class="fw-bold mb-4" data-aos="fade-up">How It Works</h2>
     <div class="row">
    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
    <div class="p-4 bg-white rounded shadow-sm">
    <img src="/waste_management_system/public/assets/images/register.svg" alt="Register" class="mb-3" width="80">
           <h4>1. Register Your House</h4>
          <p class="text-muted">Agents register properties into the system using accurate building info.</p>
        </div>
      </div>    
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
      <div class="p-4 bg-white rounded shadow-sm">
      <img src="/waste_management_system/public/assets/images/truck.svg" alt="Collection" class="mb-3" width="80">
      <h4>2. Waste is Collected</h4>
          <p class="text-muted">Waste collection is carried out regularly and tracked using digital receipts & QR verification.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
      <div class="p-4 bg-white rounded shadow-sm">
      <img src="/waste_management_system/public/assets/images/payment.svg" alt="Payment" class="mb-3" width="80">
          <h4>3. Make Payment</h4>
          <p class="text-muted">House owners or agents make payments securely online via different payment channels.</p>
        </div>
      </div>  
    </div>
  </div>
</section>
<!-- FAQ Section -->
<section class="py-5">
  <div class="container">
    <h2 class="fw-bold text-center mb-4" data-aos="fade-up">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
        <!-- FAQ Item 1 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="faqHeadingOne">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="true" aria-controls="faqCollapseOne">
          How do I register for waste collection services?
        </button>
      </h2>
      <div id="faqCollapseOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadingOne" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          You can onlybregister on our online portal through our agents. Only registered agents can register houses through their dashboard.
        </div>
      </div>
    </div>

    <!-- FAQ Item 2 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="faqHeadingTwo">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
          What types of waste are collected?
        </button>
      </h2>
      <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          We collect residential, industrial, recyclable, and hazardous waste following proper guidelines.
        </div>
      </div>
    </div>
    <!-- FAQ Item 3 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="faqHeadingThree">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
          Can I make payments online?
        </button>
      </h2>
      <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Yes, you can make secure online payments via our platform using multiple payment options available.
        </div>
      </div>
    </div>
     <!-- FAQ Item 4 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="faqHeadingFour">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
        How often is waste collected in my area?
        </button>
      </h2>
      <div id="faqCollapseFour" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
        Our Agents come around every week to collect waste.
         </div>
      </div>
    </div>
     <!-- FAQ Item 5 -->
     <div class="accordion-item">
      <h2 class="accordion-header" id="faqHeadingFive">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
        What Should i do if my waste was not collected?
    </button>
      </h2>
      <div id="faqCollapseFive" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
        Kindly contact us using the numbers on the ContactUs page. we will investigate and despatch the agent to your location.
          </div>
      </div>
    </div>
    
    <!-- Add more FAQ items similarly -->
    
  </div>
</div>
</section>

</div>
</div>
<div class="footer">&copy; <?php echo date('Y'); ?> Jimstar Waste Management. All Rights Reserved.</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
 AOS.init(); // Initialize animations
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
