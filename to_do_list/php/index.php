<?php
global $result, $taskCount, $managerTaskCount;
require_once('config.php');
require_once('common_utils.php');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
  die("データベースの接続に失敗しました: " . $mysqli->connect_error);
}

$completeFlag = array_key_exists("hidden_complete_flg", $_POST);
$otherPeopleFlag = array_key_exists("hidden_other_people_task_flg", $_POST);

if ($completeFlag && $otherPeopleFlag) {
  $sql = "SELECT * FROM t_todo T left outer join users U on T.manager_id = U.id where task_status in ('未着手','進行中') and T.user_id = ?
          ORDER BY 
            FIELD(task_status,'未着手','進行中'),
            deadline_date,
            FIELD(priority,'高','中','低')";
} elseif ($completeFlag && !$otherPeopleFlag) {
  $sql = "SELECT * FROM t_todo T left outer join users U on T.manager_id = U.id where task_status in ('未着手','進行中') and (user_id = ? or publication_range = '公開')
          ORDER BY 
            FIELD(task_status,'未着手','進行中','完了'),
            deadline_date,
            FIELD(priority,'高','中','低')";
} elseif (!$completeFlag && $otherPeopleFlag) {
  $sql = "SELECT * FROM t_todo T left outer join users U on T.manager_id = U.id where user_id = ?
          ORDER BY 
            FIELD(task_status,'未着手','進行中','完了'),
            deadline_date,
            FIELD(priority,'高','中','低')";
} else {
  $sql = "SELECT * FROM t_todo T left outer join users U on T.manager_id = U.id where user_id = ? or publication_range = '公開'
          ORDER BY 
            FIELD(task_status,'未着手','進行中'),
            deadline_date,
            FIELD(priority,'高','中','低')";
}
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $_SESSION["userid"]);
$stmt->execute();
$results = $stmt->get_result();
if (!$results) {
  die("クエリの実行に失敗しました: " . $mysqli->error);
}
$result = [];
while ($row = $results->fetch_assoc()) {
  $result[] = $row;
}

if ($completeFlag && $otherPeopleFlag) {
  $sql = "SELECT task_status,count(*) as count FROM t_todo where task_status in ('未着手','進行中') and user_id = ?
          group by task_status";
} elseif ($completeFlag && !$otherPeopleFlag) {
  $sql = "SELECT task_status,count(*) as count FROM t_todo where task_status in ('未着手','進行中') and (user_id = ? or publication_range = '公開')
          group by task_status";
} elseif (!$completeFlag && $otherPeopleFlag) {
  $sql = "SELECT task_status,count(*) as count FROM t_todo where user_id = ?
          group by task_status";
} else {
  $sql = "SELECT task_status,count(*) as count FROM t_todo where user_id = ? or publication_range = '公開'
          group by task_status";
}
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $_SESSION["userid"]);
$stmt->execute();
$taskCounts = $stmt->get_result();
if (!$taskCounts) {
  die("クエリの実行に失敗しました: " . $mysqli->error);
}
$taskCount = [];
while ($row = $taskCounts->fetch_assoc()) {
  $taskCount[$row["task_status"]] = $row["count"];
}

if ($completeFlag && $otherPeopleFlag) {
  $sql = "SELECT count(*) as count FROM t_todo where task_status in ('未着手','進行中') and user_id = ? and manager_id = ?";
} elseif ($completeFlag && !$otherPeopleFlag) {
  $sql = "SELECT count(*) as count FROM t_todo where task_status in ('未着手','進行中') and (user_id = ? or publication_range = '公開') and manager_id = ?";
} elseif (!$completeFlag && $otherPeopleFlag) {
  $sql = "SELECT count(*) as count FROM t_todo where user_id = ? and manager_id = ?";
} else {
  $sql = "SELECT count(*) as count FROM t_todo where (user_id = ? or publication_range = '公開') and manager_id = ?";
}
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $_SESSION["userid"], $_SESSION["userid"]);
$stmt->execute();
$managerTaskCounts = $stmt->get_result();
if (!$managerTaskCounts) {
  die("クエリの実行に失敗しました: " . $mysqli->error);
}
$managerTaskCount = 0;
while ($row = $managerTaskCounts->fetch_assoc()) {
  $managerTaskCount = $row["count"];
}

$mysqli->close();


?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>To Do List</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>

</head>

<body>
  <div class="bg-blue-500 min-h-screen flex justify-center items-center">
    <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2
              bg-white rounded-lg shadow-lg w-[calc(100vw-4rem)] h-[calc(100vh-4rem)] p-8">
      <div
        class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-100 border border-gray-300 rounded-lg shadow-lg w-[calc(100vw-8rem)] h-[calc(100vh-8rem)] flex">
        <div class="bg-sky-500 rounded-lg shadow-lg h-full p-2  w-1/6">
          <div class="border-b border-sky-400 pb-2">
            <?php if (isset($_SESSION["username"])) : ?>
              <p class="text-sm font-medium text-white">ユーザーID</p>
              <p class="text-white bg-sky-500 font-bold">
                <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, "UTF-8") ?></p>
            <?php endif; ?>
          </div>
          <div class="border-b border-sky-400 py-2">
            <a href="new_regist.php" class="text-sm font-medium text-white transition hover:opacity-30">タスクの追加</a>
          </div>
          <div class="border-b border-sky-400 py-2">
            <form action=" output_csv.php" method="post">
              <button type="submit" class="text-sm font-medium text-white transition hover:opacity-30"
                name="output_csv">csvエクスポート
              </button>
            </form>
          </div>
          <div class="border-b border-sky-400 py-2">
            <form action="logout.php" method="post">
              <button class="text-sm font-medium text-white transition hover:opacity-30"
                name="logout_button">ログアウト</button>
            </form>
          </div>
        </div>
        <div class="p-8 flex flex-col h-full w-full">
          <p class="text-2xl font-bold mb-6 text-gray-800">タスク一覧</p>

          <form method="post">
            完了済のタスクを非表示<input type="checkbox" id="hidden_complete_flg" name="hidden_complete_flg"
              onchange="this.form.submit()" <?php echo isset($_POST["hidden_complete_flg"]) ? "checked" : ""; ?>><br>
            他の人が作成したタスクを非表示<input type="checkbox" id="hidden_other_people_task_flg" name="hidden_other_people_task_flg"
              onchange="this.form.submit()"
              <?php echo isset($_POST["hidden_other_people_task_flg"]) ? "checked" : ""; ?>>
          </form>
          <p class="main"><?php echo "未着手:" . ($taskCount["未着手"] ?? 0) . "件";
                          echo "　進行中:" . ($taskCount["進行中"] ?? 0) . "件";
                          echo "　完了:" . ($taskCount["完了"] ?? 0) . "件";
                          echo "　自分担当のタスク:" . ($managerTaskCount ?? 0) . "件"; ?></p>
          <div class="overflow-auto h-full rounded-lg">
            <?php foreach ($result as $column): ?>
              <div class="bg-white border-b border-gray-100 px-2 py-4 grid grid-cols-6 gap-4 items-center">
                <div class="flex flex-col">
                  <?php
                  $statusClass =
                    $column['task_status'] === '進行中' ? ' main__content__status__progress' : ($column['task_status'] === '完了' ? ' main__content__status__complete' : '');
                  ?>
                  <form action="show_detail.php" method="get">
                    <input type="hidden" value="<?php echo htmlspecialchars($column['task_id'], ENT_QUOTES, 'UTF-8') ?>"
                      name="task_id">
                    <button type="submit" class="text-left">
                      <p class="text-gray-700 hover:opacity-30<?php echo $statusClass ?>">
                        <?php echo htmlspecialchars($column['content'], ENT_QUOTES, 'UTF-8')  ?></p>
                    </button>
                  </form>
                  <p class="text-gray-700 text-sm<?php echo $statusClass ?>">
                    締切日<?php echo htmlspecialchars($column['deadline_date'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="flex flex-col">
                  <p class="text-gray-700 text-sm">ステータス</p>
                  <p class="text-gray-700<?php echo $statusClass ?>">
                    <?php echo htmlspecialchars($column['task_status'], ENT_QUOTES, 'UTF-8')  ?></p>
                </div>
                <div class="flex flex-col">
                  <p class="text-gray-700 text-sm">優先度</p>
                  <p class="text-gray-700<?php echo $statusClass ?>">
                    <?php echo htmlspecialchars($column['priority'], ENT_QUOTES, 'UTF-8')  ?></p>
                </div>
                <div class="flex flex-col">
                  <p class="text-gray-700 text-sm">担当者</p>
                  <p class="text-gray-700<?php echo $statusClass ?>">
                    <?php echo htmlspecialchars($column['name'] ?? '-', ENT_QUOTES, 'UTF-8')  ?></p>
                </div>

                <?php if ($column['user_id'] == $_SESSION['userid']): ?>
                  <form action="edit_task.php" method="post">
                    <div>
                      <input type="hidden" value="<?php echo htmlspecialchars($column['task_id'], ENT_QUOTES, 'UTF-8') ?>"
                        name="task_id">
                      <input type="hidden"
                        value="<?php echo htmlspecialchars($column['deadline_date'], ENT_QUOTES, 'UTF-8') ?>"
                        name="deadline_date">
                      <input type="hidden" value="<?php echo htmlspecialchars($column['content'], ENT_QUOTES, 'UTF-8') ?>"
                        name="content">
                      <input type="hidden"
                        value="<?php echo htmlspecialchars($column['task_status'], ENT_QUOTES, 'UTF-8') ?>"
                        name="task_status">
                      <input type="hidden" value="<?php echo htmlspecialchars($column['priority'], ENT_QUOTES, 'UTF-8') ?>"
                        name="priority">
                      <input type="hidden"
                        value="<?php echo htmlspecialchars($column['publication_range'], ENT_QUOTES, 'UTF-8') ?>"
                        name="publication_range">
                      <input type="hidden"
                        value="<?php echo htmlspecialchars($column['manager_id'], ENT_QUOTES, 'UTF-8') ?>"
                        name="manager_id">
                      <button type="submit" class="text-white font-bold bg-sky-500 px-4 py-2 rounded-lg shadow-lg mr-8"
                        name="edit_button">編集</button>
                  </form>
              </div>
              <div>
                <form class="main__content__row" action="delete.php" method="post">
                  <input type="hidden" value="<?php echo htmlspecialchars($column['task_id'], ENT_QUOTES, 'UTF-8') ?>"
                    name=" task_id">
                  <button type="submit" class="bg-red-500 px-4 py-2 rounded-lg shadow-lg" name="delete_button"><i
                      class="fas fa-trash" aria-hidden="true"></i></button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  </div>
</body>

</html>