<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Featured Arts</title>
    <meta name="description" content="Visual Learning Tools and Games.">
    <meta name="author" content="Christian Bonin">
    <link rel="stylesheet" media="screen" href="defstylsh.css">
    <link rel="shortcut icon" type="image/jpg" href="painty.jpg">
  </head>
  <body>
    <div class="nav">
      <a href="index.php">Home</a>
      <a href="cards.php">Guessing Game</a>
      <a href="GUI-tar.php">GUI-tar</a>
      <a href="polyform.php">PolyForm</a>
      <a class="active" href="featured.php">Featured Arts</a>
      
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
        <h1 id="title">Featured Arts</h1>
        <p class="sub-title">Send your art to christianbonin11@gmail.com to be featured here</p>
      </section>
      
      <br>
      <br>
      
    
      <p style="font-size: 24px; background-color: #82add9; width: 360px; border-style: solid; border-color: black; padding: 8px;">
      Titled: Bullet With Butterfly Wings <br> Artist: Christian Bonin
      </p>
      
      <img src="bullet_with_butterfly_wings.PNG" alt="bullet_with_butterfly_wings" style="transform: scale(0.6,0.6) translate(-120px, -200px); border-style: solid; border-color: #744; border-width: 9px">
     
      <br>
      <br>
      <br>
      <br>
      
    </main>
    <footer>
       <div class="center">
         Copyright 2022 Christian Bonin
       </div>
    </footer>
  </body>
  <script>
    if('' + '<?=$_SESSION["user"] ?>'){
      document.getElementById('profile').innerHTML = '<form action="php/logout.php" method="post"> Logged in as ' + '<?=$_SESSION["user"]?>' + ' <input type="submit" value="Logout"> </form>';
      console.log('<?=$_SESSION["user"] ?>');
    }
  </script>
</html>
