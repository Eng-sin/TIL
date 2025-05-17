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

    $stmt = $mysqli->prepare("select * from users where name = ? limit 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_assoc();

    if ($result == null) {
      $errorMessage = "ユーザー名またはパスワードが違います。";
      return $errorMessage;
    }

    $passwordDb = $result["password"];
    if (!password_verify($password, $passwordDb)) {
      $errorMessage = "ユーザー名またはパスワードが違います。";
      return $errorMessage;
    }


    session_start();
    $_SESSION["userid"] = $result["id"];
    $_SESSION["username"] = $result["name"];
    var_dump($_SESSION);
    header("Location: index.php");
    exit();
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
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <div class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
      <?php if (!empty($_GET["isComplete"])) : ?>
        <p class="bg-green-100 text-green-700 p-4 rounded-md mb-4 text-sm">新規会員登録が成功しました。</p>
      <?php endif; ?>
      <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">ログイン画面</h1>
      <form action="login.php" method="post" class="mb-4">
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

        <button name="login_button"
          class="w-full bg-sky-500 text-white px-4 py-2 rounded-md shadow hover:bg-sky-600">ログイン</button>
      </form>
      <a class="block w-full text-center text-gray-800 hover:text-gray-500" href="regist_new_member.php">新規会員登録はこちら</a>
    </div>
  </div>

</body>

</html>