<!-- Navbar Include -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-rgb fixed-top shadow-lg">
    <div class="container">
      <a class="navbar-brand nav-title" href="index.php">Green Bites</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="nav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
          </li>
          
          <!-- Menu Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['drinks.php', 'breakfast.php', 'lunch.php', 'snacks.php']) ? 'active' : ''; ?>" href="#" id="menuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Menu
            </a>
            <ul class="dropdown-menu dropdown-menu-custom" aria-labelledby="menuDropdown">
              <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'drinks.php' ? 'active' : ''; ?>" href="drinks.php"><i class="bi bi-cup-straw me-2"></i>Drinks</a></li>
              <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'breakfast.php' ? 'active' : ''; ?>" href="breakfast.php"><i class="bi bi-sunrise me-2"></i>Breakfast</a></li>
              <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'lunch.php' ? 'active' : ''; ?>" href="lunch.php"><i class="bi bi-egg-fried me-2"></i>Lunch</a></li>
              <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'snacks.php' ? 'active' : ''; ?>" href="snacks.php"><i class="bi bi-cookie me-2"></i>Snacks</a></li>
            </ul>
          </li>
          
          <li class="nav-item"><a class="nav-link" href="index.php#complaintsSection">Complaint</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#aboutusSection">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#accountSection">My Account</a></li>
        </ul>
      </div>
    </div>
</nav>
