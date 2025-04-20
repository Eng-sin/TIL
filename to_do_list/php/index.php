<?php
require_once('config.php');
$deadlineDate = "";
$content = "";
$taskStatus = "";
$priority = "";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_error()) {
  die("データベースの接続に失敗しました");
}
$sql = "select * from t_todo order by field(task_status,'未着手','進行中','完了'),deadline_date,field(priority,'高','中','低')";
$result = $mysqli->query($sql);
if (!$result) {
  die("クエリの実行に失敗しました: " . $mysqli->error);
}
mysqli_close($mysqli);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>To Do List</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/index.css">
</head>

<body>
  <header class="header">
    <p class="header__title">タスク一覧</p>
    <form action="output_csv.php" method="post">
      <button type="submit" class="header__button_output__csv" name="output_csv">csvエクスポート
      </button>
    </form>

    <a href="new_regist.php" class="header__button_regist">登録</a>
  </header>
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
      <tr class="main__content__row">
        <?php
          $statusClass =
            $column['task_status'] === '進行中' ? ' main__content__status__progress' : ($column['task_status'] === '完了' ? ' main__content__status__complete' : '');
          ?>
        <td>
          <form class="main__content__row" action="show_detail.php" method="post">
            <input type="hidden" value="<?php echo htmlspecialchars($column['task_id'], ENT_QUOTES, 'UTF-8') ?>"
              name=" task_id">
            <button type="submit" class="main__content__row__button_detail" name="detail_button">詳細</button>
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
            <button type="submit" class="main__content__row__button_delete" name="delete_button">削除</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>

</html>



<?php if ($column['task_status'] == '進行中') {
  echo '<td class="main__content__status__progress main__content__row__add__border"';
} else if ($column['task_status'] == '完了') {
  echo '<td class="main__content__status__complete main__content__row__add__border"';
} else {
  echo '<td class="main__content__row__add__border"';
} ?>