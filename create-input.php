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

    // ゲーム一覧（アイコン表示用に game_icon も取得）
    $games = $pdo->query('SELECT * FROM game')->fetchAll();

    // メンバー選択用のユーザー一覧
    $allUsersList = $pdo->query('SELECT student_number, user_name FROM `user`')->fetchAll();
} catch (PDOException $e) {
    exit('データベースエラー: ' . $e->getMessage());
}

$firstGame = $games[0] ?? null;
?>
<!-- 新規予約モーダル本体 -->
<div class="modal js-modal">
    <div class="modal-container">
        <!-- モーダル内部のコンテンツ -->
        <div class="modal-content" id="create-reservation-content">

            <div class="modal-header">
                <span class="modal-title">新規予約</span>
                <!-- モーダルを閉じるボタン -->
                <button type="button" class="modal-close-btn js-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="create-output.php" method="post" id="create-reservation-form">

                <!-- 開始予定日時 -->
                <div class="form-group">
                    <label class="form-label">開始日時</label>
                    <div class="custom-input-box" style="position: relative; display: flex; align-items: center; justify-content: space-between; padding: 0 16px;">
                        <span class="js-datetime-label" style="font-size: 15px; color: #333; font-weight: 500;">日時を選択</span>
                        <input type="datetime-local" name="scheduled_start_time" class="form-control-hidden-date js-datetime" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; border: none;" required>
                        <i class="far fa-calendar-alt" style="font-size: 18px; color: #666; cursor: pointer;"></i>
                    </div>
                </div>

                <!-- 終了予定日時 -->
                <div class="form-group">
                    <label class="form-label">終了日時</label>
                    <div class="custom-input-box" style="position: relative; display: flex; align-items: center; justify-content: space-between; padding: 0 16px;">
                        <span class="js-datetime-label" style="font-size: 15px; color: #333; font-weight: 500;">日時を選択</span>
                        <input type="datetime-local" name="scheduled_end_time" class="form-control-hidden-date js-datetime" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; border: none;" required>
                        <i class="far fa-calendar-alt" style="font-size: 18px; color: #666; cursor: pointer;"></i>
                    </div>
                </div>

                <!-- ゲーム選択 -->
                <div class="form-group">
                    <label class="form-label">ゲーム</label>
                    <div class="custom-game-box">
                        <div style="display: flex; align-items: center; gap: 10px; width: 100%; position: relative;">
                            <div class="js-game-icon" style="width: 32px; height: 32px; background: #e6f2ed; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                <?php if ($firstGame && !empty($firstGame['game_icon'])): ?>
                                    <img src="<?= htmlspecialchars($firstGame['game_icon'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    🎮
                                <?php endif; ?>
                            </div>
                            <select name="game_id" class="custom-game-select js-game-select">
                                <?php foreach ($games as $game): ?>
                                    <option value="<?= htmlspecialchars($game['game_id'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-icon="<?= htmlspecialchars($game['game_icon'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($game['game_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 席を選択 -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">席を選択</label>
                        <span class="seat-count-text" style="font-size: 12px; color: #1b4d3e; font-weight: bold;">0席 ・ 0人</span>
                    </div>
                    <div class="seat-group-box">
                        <div class="seat-container">
                            <div class="seat-row">
                                <label class="seat-chip">4<input type="checkbox" name="seats4" value="4" class="seat-checkbox"></label>
                                <label class="seat-chip">5<input type="checkbox" name="seats5" value="5" class="seat-checkbox"></label>
                            </div>
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <label class="seat-chip">3<input type="checkbox" name="seats3" value="3" class="seat-checkbox"></label>
                                <label class="seat-chip">2<input type="checkbox" name="seats2" value="2" class="seat-checkbox"></label>
                                <label class="seat-chip">1<input type="checkbox" name="seats1" value="1" class="seat-checkbox"></label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- メンバー選択 -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">メンバー</label>
                        <span class="member-count-text" style="font-size: 12px; color: #1b4d3e; font-weight: bold;">0人を選択中</span>
                    </div>
                    <div class="member-list-box">
                        <?php foreach ($allUsersList as $usr): ?>
                            <label class="member-item">
                                <input type="checkbox" name="student_numbers[]" class="member-checkbox"
                                       value="<?= htmlspecialchars($usr['student_number'], ENT_QUOTES, 'UTF-8') ?>">
                                <span style="font-weight: 500; color: #333;"><?= htmlspecialchars($usr['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- メモ -->
                <div class="form-group">
                    <label class="form-label">メモ</label>
                    <textarea name="memo" class="form-input" rows="2" placeholder="メモ（任意）" style="resize: none; font-family: inherit;"></textarea>
                </div>

                <!-- 予約ボタン -->
                <div style="display: flex; gap: 10px; margin-top: 24px;">
                    <button type="submit" class="btn-save" style="flex: 1;">予約</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- 新規予約モーダル本体ここまで -->

<script>
(function () {
    var root = document.getElementById('create-reservation-content');
    if (!root) return;

    var weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    function pad(n) { return (n < 10 ? '0' : '') + n; }

    // datetime-local の値を「2026年11月5日（木） 15:30」の形で表示
    function formatDateTime(value) {
        if (!value) return '日時を選択';
        var d = new Date(value);
        if (isNaN(d.getTime())) return '日時を選択';
        return d.getFullYear() + '年' + (d.getMonth() + 1) + '月' + d.getDate() + '日（' +
               weekdays[d.getDay()] + '） ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    var startInput = root.querySelector('input[name="scheduled_start_time"]');
    var endInput   = root.querySelector('input[name="scheduled_end_time"]');

    root.querySelectorAll('.js-datetime').forEach(function (input) {
        var label = input.closest('.custom-input-box').querySelector('.js-datetime-label');
        // どこをクリックしてもピッカーを開く
        input.addEventListener('click', function () {
            if (typeof input.showPicker === 'function') {
                try { input.showPicker(); } catch (err) {}
            }
        });
        input.addEventListener('change', function () {
            label.textContent = formatDateTime(input.value);

            // 開始を選んだとき、終了が未入力または開始より前なら開始に合わせる
            if (input === startInput && startInput.value &&
                (!endInput.value || endInput.value < startInput.value)) {
                endInput.value = startInput.value;
                endInput.dispatchEvent(new Event('change'));
            }
        });
    });

    // ゲームを変えたらアイコンも切り替え
    var gameSelect = root.querySelector('.js-game-select');
    var gameIcon   = root.querySelector('.js-game-icon');
    if (gameSelect && gameIcon) {
        gameSelect.addEventListener('change', function () {
            var icon = gameSelect.options[gameSelect.selectedIndex].getAttribute('data-icon');
            gameIcon.textContent = '';
            if (icon) {
                var img = document.createElement('img');
                img.src = icon;
                img.alt = '';
                img.style.cssText = 'width:100%; height:100%; object-fit:cover;';
                gameIcon.appendChild(img);
            } else {
                gameIcon.textContent = '🎮';
            }
        });
    }

    // 席数・人数のリアルタイム更新
    root.addEventListener('change', function (e) {
        if (e.target.classList.contains('seat-checkbox') || e.target.classList.contains('member-checkbox')) {
            var seats   = root.querySelectorAll('.seat-checkbox:checked').length;
            var members = root.querySelectorAll('.member-checkbox:checked').length;
            root.querySelector('.seat-count-text').textContent   = seats + '席 ・ ' + members + '人';
            root.querySelector('.member-count-text').textContent = members + '人を選択中';
        }
    });

    // 送信前チェック：終了日時は開始日時より後
    document.getElementById('create-reservation-form').addEventListener('submit', function (e) {
        if (startInput.value && endInput.value && endInput.value <= startInput.value) {
            e.preventDefault();
            alert('終了日時は開始日時より後に設定してください。');
        }
    });
})();
</script>
