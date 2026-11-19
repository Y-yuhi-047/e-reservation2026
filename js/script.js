console.log("テスト：script.jsを実行しています");

// ここからモーダルウィンドウ
if (typeof modal === 'undefined') {
  var modal = document.querySelector('.js-modal'),
      open = document.querySelector('.js-modal-open'),
      close = document.querySelector('.js-modal-close');
}

function modalOpen() {
  if (modal) modal.classList.add('is-active');
}
if (open) {
  open.addEventListener('click', modalOpen);
}

function modalClose() {
  if (modal) modal.classList.remove('is-active');
}
if (close) {
  close.addEventListener('click', modalClose);
}

function modalOut(e) {
  if (modal && e.target == modal) {
    modal.classList.remove('is-active');
  }
}
addEventListener('click', modalOut);
// ここまでモーダルウィンドウ


// HTML要素を取得
const calendarElement = document.getElementById("calendar");
const selectedDateElement = document.getElementById("selected-date");
const timelineElement = document.getElementById("reservation-timeline");
const newReservationArea = document.getElementById("new-reservation-area");

// タイムラインの設定
const TIMELINE_START_HOUR = 12;   // 表示開始時刻
const TIMELINE_END_HOUR = 19;    // 表示終了時刻

const containerWidth = timelineElement && timelineElement.offsetWidth > 0 ? timelineElement.offsetWidth : 300;
const WIDTH = containerWidth - 60; // 席番号の部分を引いたタイムライン表の幅
const HOUR_WIDTH = WIDTH / 7;            // 1時間あたりの横幅(px)
const SEATS = [1, 2, 3, 4, 5];   // 席の一覧

// "10:30" → 630(分)に変換
function timeToMinutes(timeStr) {
    const [h, m] = timeStr.split(":").map(Number);
    return h * 60 + m;
}

function showReservations(dateStr) {
    if (!selectedDateElement || !timelineElement) return;
    selectedDateElement.textContent = `${dateStr} の予約`;

    // PHPから渡された reservations（window.appReservations）を使用
    const list = window.appReservations || [];
    const dailyReservations = list.filter(r => r.date === dateStr);

    const hourCount = TIMELINE_END_HOUR - TIMELINE_START_HOUR;
    const trackWidth = hourCount * HOUR_WIDTH;

    timelineElement.replaceChildren();

    const scroll = document.createElement("div");
    scroll.className = "timeline-scroll";

    const table = document.createElement("div");
    table.className = "timeline-table";
    table.style.setProperty("--hour-width", `${HOUR_WIDTH}px`);
    table.style.setProperty("--track-width", `${trackWidth}px`);

    // ヘッダー行(時刻)
    const headerRow = document.createElement("div");
    headerRow.className = "timeline-row timeline-header";

    const headerSeat = document.createElement("div");
    headerSeat.className = "timeline-seat";
    headerSeat.textContent = "席番号";

    const headerTrack = document.createElement("div");
    headerTrack.className = "timeline-track";
    for (let h = TIMELINE_START_HOUR; h < TIMELINE_END_HOUR; h++) {
        const label = document.createElement("div");
        label.className = "timeline-hour";
        label.textContent = `${h}:00`;
        headerTrack.appendChild(label);
    }
    headerRow.append(headerSeat, headerTrack);
    table.appendChild(headerRow);

    // 席ごとの行
    SEATS.forEach(seatNo => {
        const row = document.createElement("div");
        row.className = "timeline-row";

        const seatCell = document.createElement("div");
        seatCell.className = "timeline-seat";
        seatCell.textContent = String(seatNo).padStart(2, "0");

        const track = document.createElement("div");
        track.className = "timeline-track";

        // 1時間ごとのセルを並べて縦線にする
        for (let h = TIMELINE_START_HOUR; h < TIMELINE_END_HOUR; h++) {
            const cell = document.createElement("div");
            cell.className = "timeline-cell";
            track.appendChild(cell);
        }

        // この席の予約を帯として配置
        dailyReservations
            .filter(r => Number(r.seat) === seatNo)
            .forEach(r => {
                const baseMin = TIMELINE_START_HOUR * 60;
                const startMin = Math.max(timeToMinutes(r.start), baseMin);
                const endMin = Math.min(timeToMinutes(r.end), TIMELINE_END_HOUR * 60);
                if (endMin <= startMin) return; // 表示範囲外

                const bar = document.createElement("div");
                bar.className = "timeline-bar";
                bar.style.left = `${(startMin - baseMin) / 60 * HOUR_WIDTH}px`;
                bar.style.width = `${(endMin - startMin) / 60 * HOUR_WIDTH}px`;
                bar.textContent = r.title;
                bar.title = `${r.start} ～ ${r.end} ${r.title}`;

                track.appendChild(bar);
            });

        row.append(seatCell, track);
        table.appendChild(row);
    });

    scroll.appendChild(table);
    timelineElement.appendChild(scroll);

}

// ★ タイムライン表の下に、シフトボード風の「＋ 新規予約を追加」ボタンを配置
    if (newReservationArea) {
        newReservationArea.replaceChildren();
 
        const btn = document.createElement("button");
        btn.textContent = "+ 新規予約を追加";
        btn.style.width = "100%";
        btn.style.padding = "12px";
        btn.style.fontSize = "15px";
        btn.style.fontWeight = "bold";
        btn.style.cursor = "pointer";
        btn.style.backgroundColor = "#fff";
        btn.style.color = "#4CAF50";
        btn.style.border = "2px dashed #4CAF50"; // 点線のおしゃれな枠
        btn.style.borderRadius = "8px";
        btn.style.marginTop = "15px";
 
        // ボタンを押したときにモーダルを開く
        btn.addEventListener("click", () => {
            if (typeof modalOpen === 'function') {
                modalOpen();
            }
        });
 
        newReservationArea.appendChild(btn);
    }

// 今日の日付を "YYYY-MM-DD" 形式で取得
function getTodayString() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const dd = String(today.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
}

const todayStr = getTodayString();

// 自動で予約日時に今日の15:30〜17:00を入れる
const startInput = document.querySelector('input[name="scheduled_start_time"]');
const endInput = document.querySelector('input[name="scheduled_end_time"]');
startInput.value = `${todayStr}T15:30`;
endInput.value = `${todayStr}T17:00`;
startInput.dispatchEvent(new Event('change'));
endInput.dispatchEvent(new Event('change'));

// Setオブジェジェクトは重複を許さないオブジェジェクト（配列みたいなやつだけどインデックス番号で指定はできない）
// home.phpから受け取った予約データ（window.appReservations）の日付だけを一つずつ取り出してreservedDates(Setオブジェジェクト)に入れていく
// .mapは配列の中身を取り出して何かしらの処理をして新しい配列に入れるメソッド
// <-- 予約データの日付だけを重なりなしで取得-->
const reservedDates = new Set ((window.appReservations || []).map(r=>r.date)); //A || B　Aが無理ならB

// 日付を"YYYY-MM-DD"にするための関数
function toDateString(date) {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
}



// カレンダーを作成
if (calendarElement && !calendarElement.hasChildNodes()) {
    var calendar = EventCalendar.create(calendarElement, {
        view: "dayGridMonth",
        date: todayStr,
        height: "100%",

        dayCellFormat: function(date) {
        // .hasはmapインスタンスのメソッド。引数で指定されたキーがあればtrueを返すなければfalse
        let dot = '';
        if(reservedDates.has(toDateString(date))){
            dot = '<span class="reserve-dot"></span>';
        }
        return { html: `${date.getDate()}${dot}` };
        // 予約があればその日付のところに印をつける用のspanタグを追加する
        },

        headerToolbar: {
            start: "prev",
            center: "title",
            end: "next"
        },

        // 日付をクリックしたときの処理
        dateClick: function(info) {
            const dateStr = info.dateStr.slice(0, 10);

            // 1. カレンダー内のすべての日の枠線を一旦リセット（消す）
            document.querySelectorAll('.ec-day').forEach(el => {
                el.style.border = "";
                el.style.backgroundColor = "";
            });

            // 2. クリックされた日（info.dayEl）に直接枠線と背景色のスタイルを設定する
            if (info.dayEl) {
                info.dayEl.style.border = "2px solid #4CAF50";
                info.dayEl.style.backgroundColor = "rgba(76, 175, 80, 0.1)";
                info.dayEl.style.boxSizing = "border-box";
            }

            // モーダルの日時を、クリックした日の15:30〜17:00にする
            const startInput = document.querySelector('input[name="scheduled_start_time"]');
            const endInput = document.querySelector('input[name="scheduled_end_time"]');
            if (startInput && endInput) {
                startInput.value = `${dateStr}T15:30`;
                endInput.value = `${dateStr}T17:00`;
                startInput.dispatchEvent(new Event('change'));
                endInput.dispatchEvent(new Event('change'));
            }
           

            // 3. 予約タイムラインを更新
            showReservations(dateStr);
        }
    });

    // 画面読み込み時に今日のタイムラインを自動で表示
    showReservations(todayStr);

    // ▼ 初期表示（今日）の日付を最初から枠で囲む場合
    setTimeout(() => {
        const todayCell = document.querySelector(`.ec-day[data-date="${todayStr}"]`);
        if (todayCell) {
            todayCell.style.border = "2px solid #4CAF50";
            todayCell.style.backgroundColor = "rgba(76, 175, 80, 0.1)";
            todayCell.style.boxSizing = "border-box";
        }
    }, 100);

    const resizeObserver = new ResizeObserver(() => {
        if (calendar) calendar.setOption("height", "100%");
    });
    resizeObserver.observe(calendarElement);
}

// 12:00~19:00以内ではない予約をはじく
const input_start = document.getElementById('event-start');
const input_end = document.getElementById('event-end');

function validateStartTime() {
  // 値は "2026-11-18T12:30" の形式
  const time = input_start.value.split('T')[1];
  if (!time) return;

  const [h, m] = time.split(':').map(Number);
  const minutes = h * 60 + m;

  const valid = minutes >= 12 * 60 && minutes <= 19 * 60 && minutes % 30 === 0;

  input_start.setCustomValidity(valid ? '' : '12:00〜19:00の30分刻みで入力してください');
}

input_start.addEventListener('input', validateStartTime);
input_start.addEventListener('change', validateStartTime);

function validateEndTime() {
  const time = input_end.value.split('T')[1];
  if (!time) return;

  const [h, m] = time.split(':').map(Number);
  const minutes = h * 60 + m;

  const valid = minutes >= 12 * 60 && minutes <= 19 * 60 && minutes % 30 === 0;

  input_end.setCustomValidity(valid ? '' : '12:00〜19:00の30分刻みで入力してください');
}

input_end.addEventListener('input', validateEndTime);
input_end.addEventListener('change', validateEndTime);