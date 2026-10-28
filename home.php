<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel='stylesheet' href="css/yuhi.css">
        <!-- カレンダーのデザイン -->
        <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.18.0/dist/event-calendar.min.css"
        >
        <!-- カレンダーのJavaScript本体 -->
        <script src="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.18.0/dist/event-calendar.min.js"></script>
        <script src="js/script.js" defer></script>
    </head>
    <body>
        <?php require 'header.php'?>
        <div class="wrapper">
            
            <!-- 月間カレンダーの表示場所 -->
            <div id="calendar"></div>

            <!-- 選択した日付の表示 -->
            <h3 id="selected-date">日付を選択してください</h3>

            <!-- 選択した日の予約タイムライン -->
            <div id="reservation-timeline"></div>

            <!-- モーダルを開くボタン -->
            <!-- <button class="modal-open js-modal-open">モーダルを開く</button> -->
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

            <!-- 予約詳細モーダル本体 -->
            <div class="modal js-modal">
                <div class="modal-container">
                    <!-- モーダル内部のコンテンツ -->
                    <div class="modal-content">
                        <h2>新規編集</h2>
                        <!-- モーダルを閉じるボタン -->
                        <div class="modal-close js-modal-close">×</div>
                        <div> <!-- 日時、するゲーム、使う席、人数を表示したい-->
                            
                        </div>
                        <div> <!-- 利用者を表示したい-->
                                
                        </div>
                        <div> <!-- メモを表示したい-->
                                    
                        </div>
                        <form action="" method="post"> 
                            <div>
                                <button> <!-- 活動開始ボタン-->

                                </button>
                                <button> <!-- 活動終了ボタン-->

                                </button>
                            </div>
                            <div>
                                <button> <!-- 予約編集を開くボタン-->

                                </button>
                                <button> <!-- 予約削除ボタン-->

                                </button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
        </div>
            <!-- 新規詳細モーダル本体ここまで -->
            <?php require 'footer.php'?>
    </body>
</html>