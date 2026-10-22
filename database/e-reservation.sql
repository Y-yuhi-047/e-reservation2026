-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost:3306
-- 生成日時: 2026-10-22 03:15:14
-- サーバのバージョン： 5.7.24
-- PHP のバージョン: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `e-reservation`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `game`
--

CREATE TABLE `game` (
  `game_id` int(11) NOT NULL,
  `game_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `scheduled_start_time` datetime NOT NULL,
  `scheduled_end_time` datetime NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `game_id` int(11) NOT NULL,
  `seat_1` tinyint(1) NOT NULL,
  `seat_2` tinyint(1) NOT NULL,
  `seat_3` tinyint(1) NOT NULL,
  `seat_4` tinyint(1) NOT NULL,
  `seat_5` tinyint(1) NOT NULL,
  `creator` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creation_date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `memo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `user`
--

CREATE TABLE `user` (
  `student_number` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `user_reservation`
--

CREATE TABLE `user_reservation` (
  `reservation_id` int(11) NOT NULL,
  `student_number` char(8) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`game_id`);

--
-- テーブルのインデックス `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `game_id` (`game_id`);

--
-- テーブルのインデックス `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`student_number`);

--
-- テーブルのインデックス `user_reservation`
--
ALTER TABLE `user_reservation`
  ADD KEY `student_number` (`student_number`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `game`
--
ALTER TABLE `game`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `game` (`game_id`);

--
-- テーブルの制約 `user_reservation`
--
ALTER TABLE `user_reservation`
  ADD CONSTRAINT `user_reservation_ibfk_1` FOREIGN KEY (`student_number`) REFERENCES `user` (`student_number`),
  ADD CONSTRAINT `user_reservation_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
