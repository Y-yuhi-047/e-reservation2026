document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');

    if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = '非表示';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = '表示';
            }
        });
    }
});


/* account_create */

const selectIconBtn = document.getElementById("selectIconBtn");
const iconInput = document.getElementById("iconInput");
const iconPlaceholder = document.getElementById("iconPlaceholder");
const iconImage = document.getElementById("iconImage");
const registerForm = document.getElementById("registerForm");

// 画像が選択されているか確認するフラグ
let isIconSelected = false;

// 「アイコンを追加」を押したら、隠れているファイル選択を開く
selectIconBtn.addEventListener("click", function () {
    iconInput.click();
});

// 画像が選択された時の処理
iconInput.addEventListener("change", function (event) {
    const file = event.target.files[0];

    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            // プレビュー表示を切替
            iconImage.src = e.target.result;
            iconImage.style.display = "block";
            iconPlaceholder.style.display = "none";

            // 画像選択フラグをtrueにする
            isIconSelected = true;
        };

        reader.readAsDataURL(file);
    }
});

// 送信時のバリデーション
registerForm.addEventListener("submit", function (event) {
    // アイコンが未選択の場合は処理を止める
    if (!isIconSelected) {
        event.preventDefault(); // フォーム送信をキャンセル
        alert("プロフィールアイコンを選択してください。");
        return;
    }

    // 作成完了処理
    event.preventDefault();
    const name = document.getElementById("name").value;
    alert(name + " さんのアカウントを作成しました。");
});