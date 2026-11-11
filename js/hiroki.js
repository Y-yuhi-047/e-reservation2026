document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.querySelectorAll('#current_password, #new_password, #confirm_password, #password');
    const toggleButton = document.getElementById('togglePassword');

    if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', () => {
            const show = passwordInput[0].type === 'password';

            passwordInput.forEach((input) => {
                input.type = show ? 'text' : 'password';
            });

            toggleButton.textContent = show ? 'パスワードを非表示' : 'パスワードを表示';
        });
    }
});


/* account_create */
/* account_create */

const selectIconBtn = document.getElementById("selectIconBtn");
const iconInput = document.getElementById("iconInput");
const iconImage = document.getElementById("iconImage");
const registerForm = document.getElementById("registerForm");

// アカウント作成画面にしかない要素なので、ない画面では何もしない
// (ログイン画面などでも、このファイルを読み込むため)
if (registerForm) {

    // 「アイコンを追加」を押したら、隠れているファイル選択を開く
    selectIconBtn.addEventListener("click", function () {
        iconInput.click();
    });

    // 画像が選ばれたら、丸い枠にプレビューを出す
    iconInput.addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                iconImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // 送信するときの確認
    registerForm.addEventListener("submit", function (event) {
        // ファイルが1つも選ばれていなければ、送信をやめる
        if (iconInput.files.length === 0) {
            event.preventDefault();
            alert("プロフィールアイコンを選択してください。");
        }
        // 選ばれていれば、何もしない → そのままPHPに送信される
    });
}