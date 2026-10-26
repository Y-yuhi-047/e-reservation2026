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
    {
        date: "2026-07-15",
        start: "10:00",
        end: "12:00",
        title: "ゲームA",
        seat: "席1"
    },
    {
        date: "2026-07-15",
        start: "13:00",
        end: "15:00",
        title: "ゲームB",
        seat: "席2"
    },
    {
        date: "2026-07-16",
        start: "11:00",
        end: "14:00",
        title: "ゲームC",
        seat: "席3"
    }
];
// ここまで仮の予約(あとで消す)

// ここからカレンダーと予約タイムライン
// HTML要素を取得
const calendarElement = document.getElementById("calendar");
const selectedDateElement = document.getElementById("selected-date");
const timelineElement = document.getElementById("reservation-timeline");

// 予約タイムラインはじまり
// 選択した日の予約を表示する
function showReservations(dateStr) {
    selectedDateElement.textContent = `${dateStr} の予約`;
    console.log('dateStr:' + dateStr)
    console.log('reservations:' + reservations)

    // 選択した日付の予約だけを抽出
    const dailyReservations = reservations
    console.log('dailyReservations:' + dailyReservations)
        .filter(reservation => reservation.date === dateStr)
        .sort((a, b) => a.start.localeCompare(b.start));

    // 表示内容を初期化
    timelineElement.replaceChildren();

    if (dailyReservations.length === 0) {
        timelineElement.textContent = "この日の予約はありません。";
        return;
    }

    // 予約を1件ずつ表示
    dailyReservations.forEach(reservation => {
        const item = document.createElement("div");
        item.className = "reservation-item";

        const time = document.createElement("strong");
        time.textContent =
            `${reservation.start} ～ ${reservation.end}`;

        const details = document.createElement("p");
        details.textContent =
            `${reservation.title} / ${reservation.seat}`;

        item.append(time, details);
        timelineElement.appendChild(item);
    });
}
// 予約タイムラインおわり

// カレンダーを作成
const calendar = EventCalendar.create(calendarElement, {
    view: "dayGridMonth",
    date: "2026-07-15",

    headerToolbar: {
        start: "prev",
        center: "title",
        end: "next"
    },

    // 日付クリック時にタイムラインを更新
    dateClick: function(info) {
        const dateStr = info.dateStr.slice(0, 10);
        console.log("クリックした日付:", dateStr);
        console.log("予約データ:", reservations);

        console.log(
          "日付が一致する予約:",
        reservations.filter(r => r.date === info.dateStr)
    );
        showReservations(info.dateStr);
    }
});