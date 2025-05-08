</main>
<?php
$current_page = basename($_SERVER['PHP_SELF']);
if (!in_array($current_page, ['home.php', 'login.php', 'register.php', 'learnmore.php'])):
?>
  <footer style="text-align: center; padding: 1rem; color: #ccc; font-size: 0.85rem;">
    <p>&copy; 2025 Talentum. All rights reserved.</p>
  </footer>
<?php endif; ?>
</body>
</html>
