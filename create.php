<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title></title>
        <meta name="description" content="">
        <link rel='stylesheet' href="css/yuhi.css">
    </head>
    <body>
        <!-- 新規予約モーダル本体 -->
            <div class="modal js-modal">
                <div class="modal-container">
                    <!-- モーダル内部のコンテンツ -->
                    <div class="modal-content">
                        <h2>新規予約</h2>
                        <!-- モーダルを閉じるボタン -->
                        <div class="modal-close js-modal-close">×</div>
                            <form action="" method="post"> 
                            <div>
                                <p>開始日時</p>
                                <input type="datetime-local">
                            </div>
                            <div>
                                <p>終了日時</p>
                                <input type="datetime-local">
                            </div>
                            <div>
                                <p>ゲーム</p>
                                <select id="fruit" name="fruit">
                                    <option value=""></option>
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
                        </form>
                    </div>
                </div>
            </div>
            <!-- 新規予約モーダル本体ここまで -->
    </body>
</html>
