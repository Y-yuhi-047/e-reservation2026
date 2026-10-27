console.error("テスト：script.jsを実行しています");
// ここからモーダルウィンドウ
//要素を取得
const modal = document.querySelector('.js-modal'),
      open = document.querySelector('.js-modal-open'),
      close = document.querySelector('.js-modal-close');

//「開くボタン」をクリックしてモーダルを開く
function modalOpen() {
  modal.classList.add('is-active');
}
open.addEventListener('click', modalOpen);

//「閉じるボタン」をクリックしてモーダルを閉じる
function modalClose() {
  modal.classList.remove('is-active');
}
close.addEventListener('click', modalClose);

//「モーダルの外側」をクリックしてモーダルを閉じる
function modalOut(e) {
  if (e.target == modal) {
    modal.classList.remove('is-active');
  }
}
addEventListener('click', modalOut);
// ここまでモーダルウィンドウ


// 仮の予約データ(あとで消す)
const reservations = [
    { date: "2026-07-15", start: "10:00", end: "12:00", title: "ゲームA", seat: 1 },
    { date: "2026-07-15", start: "13:00", end: "15:00", title: "ゲームB", seat: 2 },
    { date: "2026-07-16", start: "11:00", end: "14:00", title: "ゲームC", seat: 3 }
];
// ここまで仮の予約(あとで消す)

// ここからカレンダーと予約タイムライン
// HTML要素を取得
const calendarElement = document.getElementById("calendar");
const selectedDateElement = document.getElementById("selected-date");
const timelineElement = document.getElementById("reservation-timeline");

// 予約タイムラインはじまり
// 選択した日の予約を表示する
// function showReservations(dateStr) {
//     selectedDateElement.textContent = `${dateStr} の予約`;

//     // 選択した日付の予約だけを抽出
//     const dailyReservations = reservations
//         .filter(reservation => reservation.date === dateStr)
//         .sort((a, b) => a.start.localeCompare(b.start));

//     // 表示内容を初期化
//     timelineElement.replaceChildren();

//     if (dailyReservations.length === 0) {
//         timelineElement.textContent = "この日の予約はありません。";
//         return;
//     }

//     // 予約を1件ずつ表示
//     dailyReservations.forEach(reservation => {
//         const item = document.createElement("div");
//         item.className = "reservation-item";

//         const time = document.createElement("strong");
//         time.textContent =
//             `${reservation.start} ～ ${reservation.end}`;

//         const details = document.createElement("p");
//         details.textContent =
//             `${reservation.title} / ${reservation.seat}`;

//         item.append(time, details);
//         timelineElement.appendChild(item);
//     });
// }

// タイムラインの設定
const TIMELINE_START_HOUR = 12;   // 表示開始時刻
const TIMELINE_END_HOUR = 19;    // 表示終了時刻
const HOUR_WIDTH = 45;           // 1時間あたりの横幅(px)
const SEATS = [1, 2, 3, 4, 5];   // 席の一覧

// "10:30" → 630(分)に変換
function timeToMinutes(timeStr) {
    const [h, m] = timeStr.split(":").map(Number);
    return h * 60 + m;
}

function showReservations(dateStr) {
    selectedDateElement.textContent = `${dateStr} の予約`;

    const dailyReservations = reservations.filter(r => r.date === dateStr);

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
        track.className = "timeline-track";   // timeline-grid は外す

        // 1時間ごとのセルを並べて縦線にする
        for (let h = TIMELINE_START_HOUR; h < TIMELINE_END_HOUR; h++) {
            const cell = document.createElement("div");
            cell.className = "timeline-cell";
            track.appendChild(cell);
        }

        // この席の予約を帯として配置
        dailyReservations
            .filter(r => r.seat === seatNo)
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
// 予約タイムラインおわり

// カレンダーを作成
const calendar = EventCalendar.create(calendarElement, {
    view: "dayGridMonth",
    date: "2026-07-15",
    height :"100%",

    headerToolbar: {
        start: "prev",
        center: "title",
        end: "next"
    },

    // 日付クリック時にタイムラインを更新
    dateClick: function(info) {
        const dateStr = info.dateStr.slice(0, 10);
        // console.log("クリックした日付:", dateStr);
        // console.log("予約データ:", reservations);

        console.log("日付が一致する予約:",reservations.filter(r => r.date === info.dateStr));
        showReservations(dateStr);
    }
});