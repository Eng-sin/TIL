<?php
require_once("config.php");
$errorMessage = "";
function login()
{
  if (!empty($_SESSION["username"])) {
    header("Location: index.php");
    exit();
  }

  $username = "";
  $password = "";

  try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
      die("データベースの接続に失敗しました: " . $mysqli->connect_error);
    }

    if (empty($_POST["username"]) || empty($_POST["password"])) {
      $errorMessage = "ユーザー名とパスワードを入力してください。";
      return $errorMessage;
    }
    $username = $_POST["username"];
    $password = $_POST["password"];

    // TODO:パスワードは暗号化して実装する
    $stmt = $mysqli->prepare("select * from users where name = ? and password = ? limit 1");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_assoc();

    if ($result == null) {
      $errorMessage = "ユーザー名またはパスワードが違います。";
      return $errorMessage;
    } else {
      session_start();
      $_SESSION["username"] = $result["name"];
      var_dump($_SESSION);
      header("Location: index.php");
      exit();
    }
  } finally {
    if (isset($mysqli)) {
      $mysqli->close();
    }
  }
}

if (array_key_exists("login_button", $_POST)) {
  $errorMessage = login();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/login.css">
</head>

<body>
  <div class="wrapper">
    <form action="login.php" method="post" class="login__form">
      <?php if (!empty($errorMessage)) : ?>
      <p class="error__message"><?= htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8") ?></p>
      <?php endif; ?>
      ユーザー名
      <input type="text" name="username"
        value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      パスワード
      <input type="password" name="password">
      <button name="login_button">ログイン</button>
    </form>
  </div>

</body>

</html>