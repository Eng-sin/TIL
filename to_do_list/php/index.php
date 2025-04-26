<?php
global $result, $taskCount;
require_once('config.php');
require_once('common_utils.php');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
  die("データベースの接続に失敗しました: " . $mysqli->connect_error);
}

if (isset($_POST["hidden_complete_flg"])) {
  $sql = "SELECT * FROM t_todo where task_status in ('未着手','進行中')
          ORDER BY 
            FIELD(task_status,'未着手','進行中'),
            deadline_date,
            FIELD(priority,'高','中','低')";
} else {
  $sql = "SELECT * FROM t_todo 
          ORDER BY 
            FIELD(task_status,'未着手','進行中','完了'),
            deadline_date,
            FIELD(priority,'高','中','低')";
}

$results = $mysqli->query($sql);
if (!$results) {
  die("クエリの実行に失敗しました: " . $mysqli->error);
}
$result = [];
while ($row = $results->fetch_assoc()) {
  $result[] = $row;
}

$sql = "SELECT task_status,count(*) as count FROM t_todo group by task_status";
$taskCounts = $mysqli->query($sql);
if (!$taskCounts) {
  die("クエリの実行に失敗しました: " . $mysqli->error);
}
$taskCount = [];
while ($row = $taskCounts->fetch_assoc()) {
  $taskCount[$row["task_status"]] = $row["count"];
}

$mysqli->close();


?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>To Do List</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/index.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body>
  <?php if (isset($_SESSION["username"])) : ?>
  <div class="login__area">
    <p class="display__login__user">ようこそ、<?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, "UTF-8") ?>さん</p>
    <form action="logout.php" method="post">
      <button name="logout_button">ログアウト</button>
    </form>
  </div>
  <?php endif; ?>
  <header class="header">
    <p class="header__title">タスク一覧</p>
    <form action="output_csv.php" method="post">
      <button type="submit" class="header__button_output__csv" name="output_csv">csvエクスポート
      </button>
    </form>
    <form method="post">
      完了済のタスクを非表示<input type="checkbox" id="hidden_complete_flg" name="hidden_complete_flg"
        onchange="this.form.submit()" <?php echo isset($_POST["hidden_complete_flg"]) ? "checked" : ""; ?>>
    </form>
    <a href="new_regist.php" class="header__button_regist"><i class="fa fa-plus" aria-hidden="true"></i></a>
  </header>
  <p class="main"><?php echo "未着手:" . ($taskCount["未着手"] ?? 0) . "件";
                  echo "　進行中:" . ($taskCount["進行中"] ?? 0) . "件";
                  echo "　完了:" . ($taskCount["完了"] ?? 0) . "件"; ?></p>
  <div class="main">
    <table class="main__content">
      <tr class="main__content__columns">
        <th></th>
        <th class="main__content__columns__add__border">締め切り日付</th>
        <th class="main__content__columns__add__border">内容</th>
        <th class="main__content__columns__add__border">進捗状況</th>
        <th class="main__content__columns__add__border">優先度</th>
        <th></th>
        <th></th>
      </tr>
      <?php foreach ($result as $column): ?>
      <tr class=" main__content__row">
        <?php
          $statusClass =
            $column['task_status'] === '進行中' ? ' main__content__status__progress' : ($column['task_status'] === '完了' ? ' main__content__status__complete' : '');
          ?>
        <td>
          <form class="main__content__row" action="show-detail.php" method="get">
            <input type="hidden" value="<?php echo htmlspecialchars($column['task_id'], ENT_QUOTES, 'UTF-8') ?>"
              name="task_id">
            <button type="submit" class="main__content__row__button_detail">詳細</button>
          </form>
        </td>
        <td class="main__content__row__add__border<?php echo $statusClass ?>">
          <?php echo htmlspecialchars($column['deadline_date'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="main__content__row__add__border<?php echo $statusClass ?>">
          <?php echo htmlspecialchars($column['content'], ENT_QUOTES, 'UTF-8')  ?></td>
        <td class="main__content__row__add__border<?php echo $statusClass ?>">
          <?php echo htmlspecialchars($column['task_status'], ENT_QUOTES, 'UTF-8')  ?></td>
        <td class="main__content__row__add__border<?php echo $statusClass ?>">
          <?php echo htmlspecialchars($column['priority'], ENT_QUOTES, 'UTF-8')  ?></td>
        <td>
          <form class="main__content__row" action="new_regist.php" method="post">
            <input type="hidden" value="<?php echo htmlspecialchars($column['task_id'], ENT_QUOTES, 'UTF-8') ?>"
              name="task_id">
            <input type="hidden" value="<?php echo htmlspecialchars($column['deadline_date'], ENT_QUOTES, 'UTF-8') ?>"
              name="deadline_date">
            <input type="hidden" value="<?php echo htmlspecialchars($column['content'], ENT_QUOTES, 'UTF-8') ?>"
              name="content">
            <input type="hidden" value="<?php echo htmlspecialchars($column['task_status'], ENT_QUOTES, 'UTF-8') ?>"
              name="task_status">
            <input type="hidden" value="<?php echo htmlspecialchars($column['priority'], ENT_QUOTES, 'UTF-8') ?>"
              name="priority">
            <button type="submit" class="main__content__row__button_edit" name="edit_button">編集</button>
          </form>
        </td>
        <td>
          <form class="main__content__row" action="delete.php" method="post">
            <input type="hidden" value="<?php echo htmlspecialchars($column['task_id'], ENT_QUOTES, 'UTF-8') ?>"
              name=" task_id">
            <button type="submit" class="main__content__row__button_delete" name="delete_button"><i class="fas fa-trash"
                aria-hidden="true"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

</body>

</html>