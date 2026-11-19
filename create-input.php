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
    $allUsersList = $pdo->query('SELECT student_number, user_name, profile_picture FROM `user`')->fetchAll();
} catch (PDOException $e) {
    exit('データベースエラー: ' . $e->getMessage());
}

$firstGame = $games[0] ?? null;
?>
<script src="js/script.js?v=2" defer></script>
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
                        <input type="datetime-local" name="scheduled_start_time" step="1800" class="form-control-hidden-date js-datetime" id="event-start" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; border: none;" required>
                        <i class="far fa-calendar-alt" style="font-size: 18px; color: #666; cursor: pointer;"></i>
                    </div>
                </div>

                <!-- 終了予定日時 -->
                <div class="form-group">
                    <label class="form-label">終了日時</label>
                    <div class="custom-input-box" style="position: relative; display: flex; align-items: center; justify-content: space-between; padding: 0 16px;">
                        <span class="js-datetime-label" style="font-size: 15px; color: #333; font-weight: 500;">日時を選択</span>
                        <input type="datetime-local" name="scheduled_end_time" step="1800" class="form-control-hidden-date js-datetime" id="event-end" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; border: none;" required>
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

                <!-- メンバー選択セクション -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">メンバー</label>
                        <span class="member-count-text" style="font-size: 12px; color: #1b4d3e; font-weight: bold;">0人を選択中</span>
                    </div>

                    <div style="background: #f7f9f8; border: 1px solid #dce3e0; border-radius: 12px; padding: 12px; display: flex; align-items: center; gap: 8px; overflow-x: auto;">
                        <!-- 選択中のメンバーアバター横並びエリア -->
                        <div class="sub-avatar-preview-list" style="display: flex; gap: 8px; align-items: center; flex-shrink: 0;">
                            <!-- JSで動的にアバターが並びます -->
                        </div>

                        <!-- ＋ボタン（クリックで利用者一覧モーダルを開く） -->
                        <div class="js-member-open" style="width: 38px; height: 38px; background: #e6f2ed; color: #1b4d3e; border: 1.5px dashed #1b4d3e; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; margin-left: auto;" title="メンバーを追加・変更">
                            <i class="fas fa-plus" style="font-size: 14px;"></i>
                        </div>
                    </div>
                </div>

                <!-- ▼ 新規予約モーダルの中に内包する「利用者一覧サブモーダル」 -->
                <div id="sub-member-modal-create" class="modal-overlay" style="display: none; z-index: 1000;">
                    <div class="modal-content" style="max-width: 380px; max-height: 85vh; display: flex; flex-direction: column;">

                        <!-- サブモーダルヘッダー -->
                        <div style="display: flex; align-items: center; margin-bottom: 14px;">
                            <button type="button" class="js-member-close" style="background: none; border: none; font-size: 20px; cursor: pointer; margin-right: 12px; color: #333;">‹</button>
                            <h2 style="font-size: 17px; margin: 0; font-weight: bold; color: #1a1a1a;">利用者一覧</h2>
                        </div>

                        <!-- 検索バー -->
                        <div style="display: flex; align-items: center; background: #f4f6f5; border-radius: 10px; padding: 8px 12px; margin-bottom: 14px; position: relative;">
                            <i class="fas fa-search" style="margin-right: 8px; color: #1b4d3e; font-size: 14px;"></i>
                            <input type="text" class="sub-modal-search js-member-search" placeholder="利用者名で検索" style="border: none; background: transparent; width: 100%; outline: none; font-size: 14px;" />
                        </div>

                        <!-- 選択中のメンバーアイコンプレビューエリア -->
                        <div style="margin-bottom: 14px; background: #fafbfc; border: 1px solid #edf1ef; border-radius: 12px; padding: 10px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #666; margin-bottom: 8px;">
                                <span>メンバー</span>
                                <span class="sub-selected-count-label" style="color: #1b4d3e; font-weight: bold;">0人を選択中</span>
                            </div>
                            <div class="sub-avatar-preview-list" style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 2px;">
                                <!-- JSで動的にアバターが並びます -->
                            </div>
                        </div>

                        <!-- 利用者リスト（スクロール） -->
                        <div style="overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 8px; padding-right: 2px; max-height: 240px;">
                            <?php foreach ($allUsersList as $usr): ?>
                                <div class="sub-member-row" data-name="<?= htmlspecialchars($usr['user_name'], ENT_QUOTES, 'UTF-8') ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border: 1px solid #eee; background: #fff; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 38px; height: 38px; background: #f0f2f1; color: #888; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; border: 1px solid #e0e4e2;">
                                            <?php if (!empty($usr['profile_picture'])): ?>
                                                <img src="<?= htmlspecialchars($usr['profile_picture'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                            <?php else: ?>
                                                <i class="fas fa-user" style="font-size: 18px; color: #aaa;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <span style="font-size: 14px; font-weight: 500; color: #333;"><?= htmlspecialchars($usr['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>

                                    <!-- 実送信用の隠しチェックボックス -->
                                    <input type="checkbox" name="student_numbers[]" class="member-checkbox sub-member-chk"
                                           value="<?= htmlspecialchars($usr['student_number'], ENT_QUOTES, 'UTF-8') ?>"
                                           data-name="<?= htmlspecialchars($usr['user_name'], ENT_QUOTES, 'UTF-8') ?>"
                                           data-picture="<?= htmlspecialchars($usr['profile_picture'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                           style="display: none;">

                                    <!-- チェックアイコンの見た目 -->
                                    <div class="sub-select-icon" style="width: 26px; height: 26px; border-radius: 50%; border: 1px solid #d1d5db; background: transparent; color: transparent; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                        ✓
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- サブモーダルを閉じるボタン -->
                        <div style="margin-top: 14px;">
                            <button type="button" class="save-btn js-member-close" style="background-color: #1b4d3e; width: 100%; padding: 10px; font-size: 14px;">
                                決定する
                            </button>
                        </div>

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
    (function() {
        var root = document.getElementById('create-reservation-content');
        if (!root) return;

        var weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        function pad(n) {
            return (n < 10 ? '0' : '') + n;
        }

        // datetime-local の値を「2026年11月5日（木） 15:30」の形で表示
        function formatDateTime(value) {
            if (!value) return '日時を選択';
            var d = new Date(value);
            if (isNaN(d.getTime())) return '日時を選択';
            return d.getFullYear() + '年' + (d.getMonth() + 1) + '月' + d.getDate() + '日（' +
                weekdays[d.getDay()] + '） ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        }

        var startInput = root.querySelector('input[name="scheduled_start_time"]');
        var endInput = root.querySelector('input[name="scheduled_end_time"]');

        root.querySelectorAll('.js-datetime').forEach(function(input) {
            var label = input.closest('.custom-input-box').querySelector('.js-datetime-label');
            // どこをクリックしてもピッカーを開く
            input.addEventListener('click', function() {
                if (typeof input.showPicker === 'function') {
                    try {
                        input.showPicker();
                    } catch (err) {}
                }
            });
            input.addEventListener('change', function() {
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
        var gameIcon = root.querySelector('.js-game-icon');
        if (gameSelect && gameIcon) {
            gameSelect.addEventListener('change', function() {
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
        root.addEventListener('change', function(e) {
            if (e.target.classList.contains('seat-checkbox') || e.target.classList.contains('member-checkbox')) {
                var seats = root.querySelectorAll('.seat-checkbox:checked').length;
                var members = root.querySelectorAll('.member-checkbox:checked').length;
                root.querySelector('.seat-count-text').textContent = seats + '席 ・ ' + members + '人';
                root.querySelector('.member-count-text').textContent = members + '人を選択中';
            }
        });

        // 送信前チェック：終了日時は開始日時より後
        document.getElementById('create-reservation-form').addEventListener('submit', function(e) {
            if (startInput.value && endInput.value && endInput.value <= startInput.value) {
                e.preventDefault();
                alert('終了日時は開始日時より後に設定してください。');
            }
        });
    }

    // ---- 利用者一覧サブモーダル（list.php の編集画面と同じ挙動） ----
    var subModal = document.getElementById('sub-member-modal-create');

    // 選択中メンバーのプレビューとカウントを更新
    function updateMemberPreview() {
        var checked = root.querySelectorAll('.sub-member-chk:checked');
        var count = checked.length;

        root.querySelectorAll('.member-count-text, .sub-selected-count-label').forEach(function (lbl) {
            lbl.textContent = count + '人を選択中';
        });

        root.querySelectorAll('.sub-avatar-preview-list').forEach(function (container) {
            container.innerHTML = '';
            checked.forEach(function (chk) {
                var name = chk.getAttribute('data-name') || '';
                var picture = chk.getAttribute('data-picture') || '';

                var item = document.createElement('div');
                item.style.cssText = 'display: flex; flex-direction: column; align-items: center; gap: 2px; flex-shrink: 0;';
                item.title = name;

                var avatar = document.createElement('div');
                avatar.style.cssText = 'width: 38px; height: 38px; background: #f0f2f1; color: #1b4d3e; border: 1.5px solid #1b4d3e; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden;';
                if (picture) {
                    var img = document.createElement('img');
                    img.src = picture;
                    img.alt = '';
                    img.style.cssText = 'width:100%; height:100%; object-fit:cover;';
                    avatar.appendChild(img);
                } else {
                    var icon = document.createElement('i');
                    icon.className = 'fas fa-user';
                    icon.style.cssText = 'font-size: 14px; color: #aaa;';
                    avatar.appendChild(icon);
                }

                var label = document.createElement('span');
                label.style.cssText = 'font-size: 10px; color: #333; max-width: 42px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;';
                label.textContent = name;

                item.appendChild(avatar);
                item.appendChild(label);
                container.appendChild(item);
            });
        });
    }

    function openSubModal()  { if (subModal) { subModal.style.display = 'flex'; updateMemberPreview(); } }
    function closeSubModal() { if (subModal) subModal.style.display = 'none'; }

    root.querySelectorAll('.js-member-open').forEach(function (btn) {
        btn.addEventListener('click', openSubModal);
    });
    root.querySelectorAll('.js-member-close').forEach(function (btn) {
        btn.addEventListener('click', closeSubModal);
    });
    // 背景（オーバーレイ）クリックで閉じる
    if (subModal) {
        subModal.addEventListener('click', function (e) {
            if (e.target === subModal) closeSubModal();
        });
    }

    // 行クリックでチェック切り替え
    root.querySelectorAll('.sub-member-row').forEach(function (row) {
        row.addEventListener('click', function () {
            var checkbox = row.querySelector('.sub-member-chk');
            var icon = row.querySelector('.sub-select-icon');
            if (!checkbox) return;

            checkbox.checked = !checkbox.checked;

            if (checkbox.checked) {
                row.style.borderColor = '#1b4d3e';
                row.style.background = '#f2f8f5';
                icon.style.borderColor = '#1b4d3e';
                icon.style.background = '#1b4d3e';
                icon.style.color = '#fff';
            } else {
                row.style.borderColor = '#eee';
                row.style.background = '#fff';
                icon.style.borderColor = '#d1d5db';
                icon.style.background = 'transparent';
                icon.style.color = 'transparent';
            }

            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    // 検索窓フィルター（Enterでフォーム送信されないようにする）
    var searchInput = root.querySelector('.js-member-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var keyword = searchInput.value.toLowerCase().trim();
            root.querySelectorAll('.sub-member-row').forEach(function (row) {
                var name = (row.getAttribute('data-name') || '').toLowerCase();
                row.style.display = (keyword === '' || name.indexOf(keyword) !== -1) ? 'flex' : 'none';
            });
        });
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    }

    // 席数・人数のリアルタイム更新
    root.addEventListener('change', function (e) {
        if (e.target.classList.contains('seat-checkbox') || e.target.classList.contains('member-checkbox')) {
            var seats   = root.querySelectorAll('.seat-checkbox:checked').length;
            var members = root.querySelectorAll('.member-checkbox:checked').length;
            root.querySelector('.seat-count-text').textContent = seats + '席 ・ ' + members + '人';
            updateMemberPreview();
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