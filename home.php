<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel='stylesheet' href="css/yuhi.css">
        <link rel="script" href="js/script.js">
        <link rel="stylesheet" href="css/yuhi.css">
        <script src="js/script.js" defer></script>
    </head>
    <body>
        <h1>aaa</h1>
    <!-- モーダルを開くボタン -->
    <button class="modal-open js-modal-open">モーダルを開く</button>
    <!-- モーダル本体 -->
    <div class="modal js-modal">
        <div class="modal-container">
            <!-- モーダル内部のコンテンツ -->
            <div class="modal-content">
                <h2>新規予約</h1>
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
    </body>
</html>