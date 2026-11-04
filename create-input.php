<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=e-reservation;charset=utf8mb4',
        'root',
        'root',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->query('SELECT game_name FROM game');
    $games = $stmt->fetchAll();
} catch (PDOException $e) {
    exit('データベースエラー: ' . $e->getMessage());
}
// print_r($games);
?>
<!-- 新規予約モーダル本体 -->
    <div class="modal js-modal">
        <div class="modal-container">
            <!-- モーダル内部のコンテンツ -->
            <div class="modal-content">
                <h2>新規予約</h2>
                <!-- モーダルを閉じるボタン -->
                <div class="modal-close js-modal-close">×</div>
                <form action="create-output.php" method="post"> 
                    <div>
                        <p>開始日時</p>
                        <input type="datetime-local" name="scheduled_start_time">
                    </div>
                    <div>
                        <p>終了日時</p>
                        <input type="datetime-local" name="scheduled_end_time">
                    </div>
                    <div>
                        <p>ゲーム</p>
                        <select name="game_name">
                            <?php foreach ($games as $game): ?>
                            <option value="<?= htmlspecialchars($game['game_name'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($game['game_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <p>席を選択</p>
                    </div>
                    <div>
                        <p>メンバー</p>
                    </div>
                    <div>
                        <p>メモ</p>
                        <input type="text">
                    </div>
                    <input type="submit" value="予約">
                </form>
            </div>
        </div>
    </div>
<!-- 新規予約モーダル本体ここまで -->

