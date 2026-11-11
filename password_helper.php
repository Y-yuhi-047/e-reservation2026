<?php
date_default_timezone_set('Asia/Tokyo');

// 仮パスワードの先頭につける目印。「これは仮パスワードだ」と見分けるために使う
const TEMP_PW_PREFIX = 'TMP-';
const TEMP_PW_HOURS = 12; // 一時パスワードの有効期限（時間）
// 仮パスワードを作る。[平文, 期限のUNIX時間] を返す
function generateTempPassword(): array
{
    // 0, O, I, 1 を除外
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    // ランダムな文字を入れていく空の文字列を用意する
    $rand = "";
    // 8回くり返す（= 8文字のランダム文字列を作る）
    for ($i = 0; $i < 8; $i++) {
        // 0 〜 (文字数-1) のランダムな位置を決めて、その位置の1文字を取り出し、$rand の後ろに足す
        $rand .= $chars[random_int(0, strlen($chars) - 1)];
    }
    // 期限 = 今のUNIX時間 + 12時間分の秒数（12 × 3600 = 43200秒）
    //time() は現在のUNIX時間を返す関数
    $expires = time() + TEMP_PW_HOURS * 3600;

    // 最終的な形は「TMP-ランダム8文字-期限」
    //UNIX時間 を36進数に変換して、文字数を短くする
    $plain = TEMP_PW_PREFIX . $rand . '-' . base_convert((string)$expires, 10, 36);

    // パスワード本体と、期限のUNIX時間をセットで返す
    return [$plain, $expires];
}

// 入力されたパスワードが「期限切れの仮パスワード」なら true を返す関数
function isTempPasswordExpired(string $input): bool
{
    // 先頭が "TMP-" でなければ仮パスワードではないので、期限切れではない（false）
    if (!str_starts_with($input, TEMP_PW_PREFIX)) {
        return false;
    }

    // パスワードを '-' で分割し３つの要素にする
    $parts = explode('-', $input);

    //3つに分割できなければ、仮パスワードではない
    if (count($parts) !== 3) {
        return false;
    }

    // 期限のUNIX時間を取得
    //36進数から10進数に変換して整数にする
    $expires = (int)base_convert($parts[2], 36, 10);
    // 現在時刻と比較
    return time() > $expires;
}
