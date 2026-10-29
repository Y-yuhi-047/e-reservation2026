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
            

            
            <?php require 'footer.php'?>
    </body>
</html>