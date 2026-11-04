<?php
//フォームが送信されたときに中の処理を実行します。
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //name が送られていなければ空文字にします。
    $name = trim($_POST['name'] ?? '');

    //$imagePath は、画像の保存先を入れる変数です。最初は空にしておきます。
    $imagePath = ''; // 画像パス用

    // アイコン画像がアップロードされている場合
    //$_FILES['icon'] が存在し、アップロードエラーがない場合だけ処理します。
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
        //$fileTmpPath は、アップロードされたファイルの一時保存場所です。
        $fileTmpPath = $_FILES['icon']['tmp_name'];
        //$fileName は、アップロード時の元のファイル名です。
        $fileName = $_FILES['icon']['name'];

        //保存先を uploads/ にします。
        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            //フォルダがまだなければ mkdir() で作成します。
            mkdir($uploadFileDir, 0755, true);
        }
        //元のファイル名をそのまま使わず、user_ とハッシュ値を使った名前を作ります
        $newFileName = 'user_' . md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        $dest_path = $uploadFileDir . $newFileName;

        //一時保存場所から uploads/ にファイルを移動します。成功したら、その保存先を $imagePath に入れます
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $imagePath = $dest_path; // 保存成功したらパスを格納
        }
    }
    //名前が空でない場合だけ登録します
    if ($name !== '') {
        // mb_substr() で名前の最初の1文字を取得します。UTF-8 を指定して、日本語なども正しく扱います。
        $initial = mb_substr($name, 0, 1, 'UTF-8');
        // 新しいユーザーIDを生成します。既存のユーザーがいれば最大IDに1を足し、いなければ1にします。
        $newId = count($_SESSION['users']) > 0 ? max(array_column($_SESSION['users'], 'id')) + 1 : 1;

        // 新しいユーザー情報をセッションに追加します。画像パスも保存します。
        $_SESSION['users'][] = [
            'id' => $newId,
            'name' => $name,
            'initial' => $initial,
            'image' => $imagePath, // ← 画像パスを保存
            'role' => '一般'
        ];
    }

    header('Location: login-input.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント作成</title>
    <link rel="stylesheet" href="css/hiroki.css">
    <script src="js/hiroki.js" defer></script>
</head>

<body>

    <div class="container login">

        <div class="header">
            <p class="logo">e-reservation</p>
            <h1>アカウント作成</h1>
        </div>

        <!-- ① formタグに action と method を追加 -->
        <form id="registerForm" action="account_create_output.php" method="POST" enctype="multipart/form-data">

            <!-- アイコン選択エリア -->
            <div class="icon-section">
                <div class="icon-preview" id="iconPreview">
                    <img id="iconImage" src="images/初期設定アイコン.png" alt="アイコン">
                </div>
                <button type="button" id="selectIconBtn" class="btn-secondary">アイコンを追加</button>
                <input type="file" id="iconInput" name="icon" accept="image/*" style="display: none;">
            </div>

            <!-- 名前 (② name="name" を追加) -->
            <div class="form-group">
                <label for="name">名前</label>
                <input type="text" id="name" name="name" placeholder="山田 太郎" required>
            </div>

            <!-- 学生番号 -->
            <div class="form-group">
                <label for="studentId">学生番号</label>
                <input type="text" id="studentId" name="student_id" placeholder="学生番号か教員番号" required>
            </div>

            <!-- パスワード -->
            <div class="form-group">
                <label for="password">パスワード</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="パスワードを入力" required>
                    <button type="button" id="togglePassword" class="btn-toggle-password">表示</button>
                </div>
            </div>

            <!-- 作成ボタン -->
            <button type="submit" class="btn">アカウントを作成</button>
        </form>

        <div class="footer">
            <p>すでにアカウントをお持ちの方</p>
            <a href="login-input.php">ログインに戻る</a>
        </div>

    </div>

    <script src="script.js"></script>
</body>

</html>