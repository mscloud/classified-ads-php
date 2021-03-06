<?php
if(isset($_SESSION['username'])) {
  header("location: profile.php");
}
unset($error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  require_once(LIBRARY_PATH . "/databaseFunctions.php");
  $link;
  connect($link);

  $username = mysqli_real_escape_string($link, $_POST['username']);
  $password = mysqli_real_escape_string($link, $_POST['password']);

  $query = "SELECT * FROM user WHERE username='$username'";
  $userResult = mysqli_query($link, $query);
  $user = mysqli_fetch_array($userResult, MYSQLI_ASSOC);
  $count = mysqli_num_rows($userResult);

  if ($count == 1 && password_verify($password, $user["password"])) {
    $_SESSION['username'] = $username;
    $_SESSION['id'] = $user["id"];
    $_SESSION['email'] = $user["email"];
    mysqli_free_result($userResult);
    close($link);
    header("location: profile.php");
  } else {
    $error = "Username or password is invalid";
  }
}
?>
<h2 class="form-title">Login</h2>
<form action="" method="post" class="form">
  <label for="username">Username:</label>
  <input type="text" name="username" maxlength="15" required/>
  <label for="password">Password:</label>
  <input type="password" name="password" maxlength="30" required/>
  <button class="button">Submit</button>
  <?php if (isset($error)) { ?>
    <div class="error"><?php echo $error; ?></div>
  <?php } ?>
</form>
