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
        <h1 id="title">Visual Learning Hub</h1>
        <p class="sub-title">Check out our tools and games!</p>
        <a href="https://www.patreon.com/ChristianBonin">Support me on Patreon!</a>
      </section>
      
      <br>
      <br>
      
      <img src="poly_stairs.jpg" alt="poly_stairs" class="center">
      
      <br>
      <br>
      <br>
      <br>
      
    </main>
    <footer>
      <div class="center">
        <hr>
        <a href="https://bitnami.com/" aria-label="Go to the Bitnami site" target="_blank"><svg width="42" height="47" viewBox="0 0 126 141" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>Bitnami logo</title><defs><path d="M7.187 75.559C2.698 72.968.301 70.402.301 65.32V7.476c0-2.643.705-5.022 1.845-7.034l24.345 13.99v72.273L7.187 75.56z" id="ba"/><linearGradient x1="18.01%" y1="80.098%" x2="77.804%" y2="28.779%" id="c"><stop stop-color="#00437C" offset="0%"/><stop stop-color="#093266" offset="100%"/></linearGradient><path d="M8.673 47.68L.45 42.932V12.691l15.23 8.792L51.908.566V.432L76.35 14.478c-1.178 2.14-2.937 3.913-5.25 5.248L22.7 47.671a14.004 14.004 0 0 1-7.02 1.902 14 14 0 0 1-7.006-1.893" id="d"/><linearGradient x1="0%" y1="50%" y2="50%" id="f"><stop stop-color="#00437C" offset="0%"/><stop stop-color="#3984B5" offset="100%"/></linearGradient></defs><g fill="none" fill-rule="evenodd"><path d="M78.432 61.949l-15.206-8.78-15.206 8.78 15.205 8.865z" fill="#00577B"/><path d="M63.226 88.292l15.206-8.782V61.948L63.226 70.73z" fill="#1E384B"/><path d="M63.225 88.292L48.02 79.51V61.948l15.205 8.782z" fill="#15211F"/><path d="M99.455 50.024v41.704l-36.23 20.917-36.233-20.917v-.063l-24.345 13.99c1.276 2.251 3.097 4.043 5.113 5.207 16.067 9.276 32.132 18.553 48.2 27.828 4.715 2.724 9.79 2.737 14.48.03 16.104-9.3 32.212-18.599 48.316-27.897 4.51-2.604 6.889-6.736 6.889-11.99V43.242c0-2.736-.597-5.175-1.748-7.264L99.455 50.024z" fill="#00437C" fill-rule="nonzero"/><g><g transform="matrix(1 0 0 -1 .502 106.097)"><mask id="bb" fill="#fff"><use xlink:href="#ba"/></mask><g mask="url(#bb)" fill="url(#c)" fill-rule="nonzero"><path d="M7.187 75.559C2.698 72.968.301 70.402.301 65.32V7.476c0-2.643.705-5.022 1.845-7.034l24.345 13.99v72.273L7.187 75.56z"/></g></g></g><g><g transform="matrix(1 0 0 -1 47.547 50.457)"><mask id="e" fill="#fff"><use xlink:href="#d"/></mask><g mask="url(#e)" fill="url(#f)" fill-rule="nonzero"><path d="M8.673 47.68L.45 42.932V12.691l15.23 8.792L51.908.566V.432L76.35 14.478c-1.178 2.14-2.937 3.913-5.25 5.248L22.7 47.671a14.004 14.004 0 0 1-7.02 1.902 14 14 0 0 1-7.006-1.893"/></g></g></g></g></svg></a>
        <p>Proudly built with Bitnami</p>
      </div>
      <br>
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
