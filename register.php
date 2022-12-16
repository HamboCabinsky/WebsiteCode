<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Visual Learning Hub</title>
    <meta name="description" content="Visual Learning Tools and Games.">
    <meta name="author" content="Christian Bonin">
    <link rel="stylesheet" media="screen" href="defstylsh.css">
    <link rel="shortcut icon" type="image/jpg" href="painty.jpg">
  </head>
  <body>
    <div class="nav">
      <a class="active" href="index.php">Home</a>
      <a href="cards.php">Guessing Game</a>
      <a href="GUI-tar.php">GUI-tar</a>
      <a href="polyform.php">PolyForm</a>
      <a href="featured.php">Featured Arts</a>
      
      <div id='profile' style="float: right; text-align: right; padding: 10px;">
        <a href='register.php' style="position: absolute; right: 400px; padding: 0px;">Register</a>
        <form action = "php/authenticate.php" method="post">
          <input type="text" name="username" placeholder="Username" id="username" required>
          <input type="password" name="password" placeholder="Password" id="password" required>
          <input type="submit" value="Login">
        </form>
      </div>
      
    </div>
    <br>
    <br>
    <main class="margin-t-huge">
      <section class="title-container">
        <h1 id="title">Register</h1>
        <p class="sub-title">Enter the requested information to create an account.</p>
      </section>
      
      <br>
      <br>
      
      <div id='register' class='center' style='background-color: #82add9; border-style: solid; border-color: black; padding: 20px;'>
        <form action = "php/register.php" method="post">
          <input type="text" name="username" placeholder="Username" id="username" required> &nbsp &nbsp &nbsp &nbsp No vulgar usernames please.
          <br>
          <br>
          <input type="text" name="email" placeholder="Email" id="email" required> &nbsp &nbsp &nbsp &nbsp If you're a Patreon supporter, please use the same email as your Patreon account.
          <br>
          <br>
          <input type="password" name="password" placeholder="Password" id="password" required>  &nbsp &nbsp &nbsp &nbsp Must be at least 7 characters.
          <br>
          <br>
          <input type="password" name="confirm_password" placeholder="Confirm Password" id="confirm_password" required> &nbsp &nbsp &nbsp &nbsp Confirm you've entered password correctly.
          <br>
          <br>
          <input type="submit" value="Register">
        </form>
      </div>
      
      <br>
      <br>
      <br>
      <br>
      
    </main>
  </body>
  <script>
    if('' + '<?=$_SESSION["user"] ?>'){
      document.getElementById('profile').innerHTML = '<form action="php/logout.php" method="post"> Logged in as ' + '<?=$_SESSION["user"]?>' + ' <input type="submit" value="Logout"> </form>';
      console.log('<?=$_SESSION["user"] ?>');
    }
  </script>
</html>
