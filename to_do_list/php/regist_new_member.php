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
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <div class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
      <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">新規会員登録画面</h1>
      <form action="regist_new_member.php" method="post" class="mb-4">
        <?php if (!empty($errorMessage)) : ?>
          <p class="bg-red-100 text-red-700 p-4 rounded-md mb-4 text-sm">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <div class="mb-4">
          <label for="username" class="block text-sm font-medium text-gray-700">ユーザー名</label>
          <input class="mt-1 w-full px-4 py-2 border border-red-500 rounded-md shadow-sm" type="text" name="username"
            value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-gray-700">パスワード</label>
          <input class="mt-1 w-full px-4 py-2 border border-red-500 rounded-md shadow-sm" type="password"
            name="password">
        </div>
        <button name="regist_new_member_button"
          class="w-full px-4 py-2 text-white bg-sky-500 rounded-md shadow hover:bg-sky-600">登録する</button>
      </form>
      <a class="block w-full text-center text-gray-800 hover:text-gray-500" href="login.php">ログイン画面に戻る</a>
    </div>
  </div>

</body>

</html>