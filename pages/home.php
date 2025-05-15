<?php
require_once __DIR__ . '/../templates/header.php';
?>
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', sans-serif;
    background-image: url('../uploads/Background.png');
    background-size: cover;
    background-position: center;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
  }

  .container {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 20px;
    position: relative;
    top: -80px;
  }

  .logo {
    width: 350px; 
    margin-bottom: -95px;
  }

  h1 {
    font-size: 2.5rem;
    margin-bottom: 0px;
    font-weight: 600;
    color: #c2b6f3;
    text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
  }

  .buttons a {
    background-color: #7744dd;
    color: white;
    text-decoration: none;
    padding: 12px 30px;
    margin: 0 10px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    transition: 0.3s;
    display: inline-block;
  }

  .buttons a:hover {
    background-color: #5f3dc4;
  }

  footer {
    position: absolute;
    bottom: 20px;
    font-size: 0.8rem;
    color: #ccc;
    width: 100%;
    text-align: center;
  }
</style>

<div class="container">
  <img src="../uploads/Logo.png" alt="Talentum Logo" class="logo" />
  <h1>Welcome to Talentum</h1>
  <div class="buttons">
    <a href="login.php">Login</a>
    <a href="register.php">Sign Up</a>
    <a href="learnmore.php">Learn More</a>
  </div>
</div>

<footer>&copy; 2025 Talentum. All rights reserved.</footer>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
