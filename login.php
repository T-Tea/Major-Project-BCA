<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page</title>
  <link rel="stylesheet" href="loginStyle.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="website icon" type="png" href="images/Hosteller/Home/airplane.png">
</head>
<body>
  <div class="login-page">
    
    <!-- Left side: full-height image -->
    <div class="login-image">
      <img src="images/Login/loginimg.jpg" alt="Login Visual">
    </div>

    <!-- Right side: compact login form -->
    <div class="login-wrapper">
      <form class="login-container" action="logic/loginLogic.php" method="POST" onsubmit="return validateForm()">
        <h2>Welcome to Hostel Portal</h2>
        <p>Select your role:</p>
        
        <div class="role-buttons">
          <button type="button" class="role-btn" data-role="hosteller">Hosteller</button>
          <button type="button" class="role-btn" data-role="warden">Warden</button>
          <button type="button" class="role-btn" data-role="admin">Admin</button>
        </div>

        <!-- Hidden input to capture selected role -->
        <input type="hidden" name="role" id="selectedRole" />

        <div class="login-fields">
          <input type="text" name="username" placeholder="Username" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit" class="submit-btn">Login</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const roleButtons = document.querySelectorAll('.role-btn');
    const selectedRoleInput = document.getElementById('selectedRole');

    roleButtons.forEach(button => {
      button.addEventListener('click', () => {
        roleButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        selectedRoleInput.value = button.getAttribute('data-role');
      });
    });

    function validateForm() {
      if (!selectedRoleInput.value) {
        alert('Please select a role before logging in.');
        return false;
      }
      return true;
    }
  </script>
</body>
</html>