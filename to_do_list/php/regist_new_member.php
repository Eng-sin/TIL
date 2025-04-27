<?php
session_start();
require_once("config.php");
$errorMessage = "";
$isComplete = "";
function registNewMember()
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
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("select * from users where name = ? limit 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_assoc();

    if ($result != null) {
      $errorMessage = "そのユーザー名は既に使用されています。別のユーザー名を入力してください。";
      return $errorMessage;
    } else {
      $stmt = $mysqli->prepare("insert into users(name,password) values(?,?)");
      $stmt->bind_param("ss", $username, $password);
      $result = $stmt->execute();
      if (!$result) {
        die("クエリの実行に失敗しました: " . $mysqli->error);
      }
      $isComplete = "?isComplete=true";
      header("Location: login.php{$isComplete}");
      exit();
    }
  } finally {
    if (isset($mysqli)) {
      $mysqli->close();
    }
  }
}

if (array_key_exists("regist_new_member_button", $_POST)) {
  $errorMessage = registNewMember();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/regist_new_member.css">
</head>

<body>
  <div class="wrapper">
    <h1 class="regist__new__member__title">新規会員登録画面</h1>
    <form action="regist_new_member.php" method="post" class="regist__new__member__form">
      <?php if (!empty($errorMessage)) : ?>
      <p class="error__message"><?= htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8") ?></p>
      <?php endif; ?>
      ユーザー名
      <input type="text" name="username"
        value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      パスワード
      <input type="password" name="password">
      <button name="regist_new_member_button">登録する</button>
    </form>
    <a href="login.php">ログイン画面に戻る</a>
  </div>

</body>

</html>