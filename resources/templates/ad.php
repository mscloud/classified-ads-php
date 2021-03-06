<?php
require_once(LIBRARY_PATH . "/databaseFunctions.php");
$link;
connect($link);

$ad_id = mysqli_real_escape_string($link, $_GET['id']);

$query = "SELECT ad.*, user.username AS user_username, user.email AS user_email, user.phone AS user_phone, user.name AS user_name, city.name AS city_name,"
  . " state.name AS state_name, subcategory.name AS subcategory_name, category.name AS category_name"
  . " FROM ad INNER JOIN user ON ad.user_id = user.id"
  . " INNER JOIN city ON ad.city_id = city.id"
  . " INNER JOIN state ON city.state_id = state.id"
  . " INNER JOIN subcategory ON ad.subcategory_id = subcategory.id"
  . " INNER JOIN category ON subcategory.category_id = category.id WHERE ad.id='$ad_id'";
$adResult = mysqli_query($link, $query);
$ad = mysqli_fetch_array($adResult, MYSQLI_ASSOC);
$count = mysqli_num_rows($adResult);

if ($count == 1) {
  $name = $ad['name'];
  $description = $ad['description'];
  $price = $ad['price'];
  $date = $ad['date'];
  $ad_user_username = $ad['user_username'];
  $ad_user_name = $ad['user_name'];
  $ad_user_phone = $ad['user_phone'];
  $ad_user_email = $ad['user_email'];
  $image = $ad['image'];
  $sold = $ad['sold'];
  $state = $ad['state_name'];
  $city = $ad['city_name'];
  $category = $ad['category_name'];
  $subcategory = $ad['subcategory_name'];
  mysqli_free_result($adResult);
  close($link);
} else {
  header("location: error.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  require_once(LIBRARY_PATH . "/databaseFunctions.php");
  $link;
  connect($link);

  $text = mysqli_real_escape_string($link, $_POST['text']);

  // calculate current date
  $date_array = getdate();
  $date = $date_array['year'] . "-" . $date_array['mon'] . "-" . $date_array['mday'];
  // user_id from session
  $user_id = $_SESSION['id'];

  if (isset($_POST['email'])) {
    // validations
    // if () {
    //    $email_error = '';
    // } else if () {
    //    $email_error = '';
    // }
    if (!isset($email_error)) {
      $message = $name . '\r\n' . $text . '\r\n' . $_SESSION['username'] . ' - ' . $date . '\r\n' . $_SESSION['email'];
      $message = wordwrap($message, 70, "\r\n");
      if (!mail($ad_user_email, 'User ' .  $_SESSION['username'] . 'offered in ' . $name, $message)) {
        $email_error = "Error while trying to send email.";
      }
    }
  } else if (isset($_POST['comment'])) {
    // validations
    // if () {
    //    $comment_error = '';
    // } else if () {
    //    $comment_error = '';
    // }
    if (!isset($comment_error)) {
      $query = "INSERT INTO comment (ad_id, user_id, text, date) VALUES ('$ad_id', '$user_id', '$text', '$date')";
      if (!mysqli_query($link, $query)) {
        $comment_error = "Error while trying to create comment.";
      }
    }
  }
  close($link);
}
?>
<h2 class="ad-title"><?php echo $name; ?></h2>
<div class="ad-container">
  <div class="ad-image">
    <img src="<?php echo "images/uploaded/" . $image; ?>" />
  </div>
  <div class="ad-details">
    <div><b>Description:</b> <?php echo $description; ?></div>
    <div><b>Price:</b> $<?php echo $price; ?></div>
    <div><b>Category:</b> <?php echo $category; ?></div>
    <div><b>Subcategory:</b> <?php echo $subcategory; ?></div>
    <div><b>City:</b> <?php echo $city; ?>, <?php echo $state; ?></div>
    <div><b>Date posted:</b> <?php echo $date; ?></div>
    <div><b>Username:</b> <?php echo $ad_user_name; ?></div>
    <div><b>Phone:</b> <?php echo $ad_user_phone; ?></div>
    <?php if (!$sold && isset($_SESSION['username']) && $_SESSION['username'] !== $ad_user_username) { ?>
      <form action="" method="post" class="form">
        <input type="hidden" name="email" />
        <label for="text">Text:</label>
        <textarea type="text" name="text" rows="5" cols="30" maxlength="200" required>Make an offer</textarea>
        <button class="button">Submit</button>
        <?php if (isset($email_error)) { ?>
          <div class="error"><?php echo $email_error; ?></div>
        <?php } ?>
      </form>
    <?php } ?>
  </div>
</div>
<h2 class="comments-title">Comments</h2>
<div class="comments-container">
  <ul class="comments-list">
    <?php
    $link;
    connect($link);
    $commentsQuery = "SELECT comment.*, user.name AS user_name"
      . " FROM comment INNER JOIN user ON comment.user_id=user.id WHERE ad_id=" . $ad_id;
    $commentsResult = mysqli_query($link, $commentsQuery);
    while ($comment = mysqli_fetch_array($commentsResult, MYSQLI_ASSOC)) {
    ?>
      <li>
        <span class="comment-user-name <?php if($ad_user_name === $comment['user_name']) { echo 'comment-ad-owner'; } ?>">
          <?php echo $comment['user_name']; ?>:
        </span>
        <span class="comment-text">"<?php echo $comment['text']; ?>"</span>
        <span class="comment-date"><?php echo $comment['date']; ?></span>
      </li>
    <?php
    }
    close($link);
    ?>
  </ul>
  <?php if (isset($_SESSION['username'])) { ?>
    <form action="" method="post" class="form">
      <input type="hidden" name="comment" />
      <label for="text">Text:</label>
      <textarea type="text" name="text" rows="5" cols="30" maxlength="200" required>Send a comment</textarea>
      <button class="button">Submit</button>
      <?php if (isset($comment_error)) { ?>
        <div class="error"><?php echo $comment_error; ?></div>
      <?php } ?>
    </form>
  <?php } ?>
</div>
