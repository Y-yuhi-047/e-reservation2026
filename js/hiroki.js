// 予約詳細のモーダル


function openDetailModal(id) {
    var modal = document.getElementById('detail-modal-' + id);
    if (modal) modal.style.display = 'flex';
}

function closeDetailModal(id) {
    var modal = document.getElementById('detail-modal-' + id);
    if (modal) modal.style.display = 'none';
}

function openEditModal(id) {
    closeDetailModal(id);
    var modal = document.getElementById('edit-modal-' + id);
    if (modal) {
        modal.style.display = 'flex';
        updateSubModalPreview(id);
    }
}

function closeEditModal(id) {
    var modal = document.getElementById('edit-modal-' + id);
    if (modal) modal.style.display = 'none';
}

function openMemberSubModal(reservationId) {
    var modal = document.getElementById('sub-member-modal-' + reservationId);
    if (modal) {
        modal.style.display = 'flex';
        updateSubModalPreview(reservationId);
    }
}

function closeMemberSubModal(reservationId) {
    var modal = document.getElementById('sub-member-modal-' + reservationId);
    if (modal) modal.style.display = 'none';
}

function toggleSubMemberItem(rowElement, reservationId) {
    var checkbox = rowElement.querySelector('.sub-member-chk');
    if (!checkbox) return;

    checkbox.checked = !checkbox.checked;

    if (checkbox.checked) {
        rowElement.classList.add('checked');
    } else {
        rowElement.classList.remove('checked');
    }

    updateSubModalPreview(reservationId);

    var event = new Event('change', {
        bubbles: true
    });
    checkbox.dispatchEvent(event);
}

function updateSubModalPreview(reservationId) {
    var editModal = document.getElementById('edit-modal-' + reservationId);
    if (!editModal) return;

    var checkboxes = editModal.querySelectorAll('.sub-member-chk:checked');
    var count = checkboxes.length;

    var countLabels = editModal.querySelectorAll('.member-count-text, .sub-selected-count-label');
    countLabels.forEach(function (lbl) {
        lbl.textContent = count + '人を選択中';
    });

    var previewContainers = editModal.querySelectorAll('.sub-avatar-preview-list');
    previewContainers.forEach(function (previewContainer) {
        previewContainer.innerHTML = '';
        checkboxes.forEach(function (chk) {
            var name = chk.getAttribute('data-name');
            var picture = chk.getAttribute('data-picture');

            var div = document.createElement('div');
            div.className = 'js-preview-avatar-wrap';

            var avatarInner = '';
            if (picture && picture !== '') {
                avatarInner = '<img src="' + picture + '" alt="" class="detail-img-fit">';
            } else {
                avatarInner = '<i class="fas fa-user js-preview-user-icon"></i>';
            }

            div.innerHTML = '<div class="js-preview-circle">' + avatarInner + '</div>' +
                '<span class="js-preview-name">' + name + '</span>';
            previewContainer.appendChild(div);
        });
    });
}

function filterSubModalMembers(inputElement, reservationId) {
    var keyword = inputElement.value.toLowerCase().trim();
    var modal = document.getElementById('sub-member-modal-' + reservationId);
    if (!modal) return;

    var rows = modal.querySelectorAll('.sub-member-row');
    rows.forEach(function (row) {
        var name = row.getAttribute('data-name').toLowerCase();
        if (keyword === '' || name.includes(keyword)) {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    });
}

function toggleCustomGameDropdown(event, reservationId) {
    event.stopPropagation();
    document.querySelectorAll('.custom-game-dropdown-list').forEach(function (el) {
        if (el.id !== 'game-dropdown-list-' + reservationId) {
            el.style.display = 'none';
        }
    });
    var list = document.getElementById('game-dropdown-list-' + reservationId);
    if (list) {
        list.style.display = (list.style.display === 'block') ? 'none' : 'block';
    }
}

function selectCustomGame(event, reservationId, gameId, gameName, gameIcon) {
    event.stopPropagation();
    document.getElementById('selected-game-id-' + reservationId).value = gameId;

    var namePreview = document.getElementById('game-name-preview-' + reservationId);
    if (namePreview) namePreview.textContent = gameName;

    var iconPreview = document.getElementById('game-icon-preview-' + reservationId);
    if (iconPreview) {
        if (gameIcon && gameIcon !== '') {
            iconPreview.innerHTML = '<img src="' + gameIcon + '" alt="" class="detail-img-fit">';
        } else {
            iconPreview.innerHTML = '<i class="fas fa-gamepad edit-gamepad-size"></i>';
        }
    }

    var list = document.getElementById('game-dropdown-list-' + reservationId);
    if (list) list.style.display = 'none';
}

window.addEventListener('click', function (e) {
    if (!e.target.closest('.custom-game-dropdown-wrapper')) {
        document.querySelectorAll('.custom-game-dropdown-list').forEach(function (el) {
            el.style.display = 'none';
        });
    }
});

window.onclick = function (event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('seat-checkbox') || e.target.classList.contains('member-checkbox')) {
        var modal = e.target.closest('.modal-content');
        if (modal) {
            var checkedSeats = modal.querySelectorAll('.seat-checkbox:checked').length;
            var checkedMembers = modal.querySelectorAll('.sub-member-chk:checked').length;

            var seatCountText = modal.querySelector('.seat-count-text');
            if (seatCountText) {
                seatCountText.textContent = checkedSeats + '席 ・ ' + checkedMembers + '人';
            }
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[name="keyword"]');

    if (searchInput) {

        // Enterや虫眼鏡ボタンを押しても、ページを再読み込みしない
        // （再読み込みすると、メンバー名だけのPHP検索に切り替わってしまうため）
        searchInput.form.addEventListener('submit', function (e) {
            e.preventDefault();
        });

        searchInput.addEventListener('input', function () {
            const keyword = searchInput.value.toLowerCase().trim();
            const reservationCards = document.querySelectorAll('.res-card');

            reservationCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (keyword === '' || cardText.includes(keyword)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            document.querySelectorAll('.past-group').forEach(function (group) {
                const hasHit = Array.from(group.querySelectorAll('.res-card')).some(function (card) {
                    return card.style.display !== 'none';
                });
                group.open.toggle('', hasHit);
                group.style.display = (keyword !== '' && !hasHit) ? 'none' : '';
            });
        });
    }
});





document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.querySelectorAll('#current_password, #new_password, #confirm_password, #password');
    const toggleButton = document.getElementById('togglePassword');

    if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', () => {
            const show = passwordInput[0].type === 'password';

            passwordInput.forEach((input) => {
                input.type = show ? 'text' : 'password';
            });

            toggleButton.textContent = show ? '非表示' : 'パスワードを表示';
        });
    }
});


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