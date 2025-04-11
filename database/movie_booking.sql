-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 11, 2025 at 04:08 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `movie_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int NOT NULL,
  `userId` int NOT NULL,
  `showTimeId` int NOT NULL,
  `seats` varchar(255) NOT NULL,
  `totalAmount` decimal(10,2) NOT NULL,
  `paymentStatus` enum('pending','completed','cancelled') DEFAULT 'pending',
  `bookingDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `userId`, `showTimeId`, `seats`, `totalAmount`, `paymentStatus`, `bookingDate`, `createdAt`) VALUES
(28, 9, 22, 'E5,E6', '260000.00', 'completed', '2025-04-11 21:20:16', '2025-04-11 14:20:16'),
(29, 9, 22, 'E3,E4', '260000.00', 'completed', '2025-04-11 21:23:56', '2025-04-11 14:23:56'),
(30, 8, 22, 'D3,D4', '260000.00', 'completed', '2025-04-11 21:25:08', '2025-04-11 14:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `createdAt`) VALUES
(1, 'Hành động', 'hanh-dong', '2025-03-27 20:45:32'),
(2, 'Phiêu lưu', 'phieu-luu', '2025-03-27 20:45:32'),
(3, 'Hoạt hình', 'hoat-hinh', '2025-03-27 20:45:32'),
(4, 'Hài hước', 'hai-huoc', '2025-03-27 20:45:32'),
(5, 'Tình cảm', 'tinh-cam', '2025-03-27 20:45:32'),
(6, 'Kinh dị', 'kinh-di', '2025-03-27 20:45:32'),
(7, 'Khoa học viễn tưởng', 'khoa-hoc-vien-tuong', '2025-03-27 20:45:32'),
(8, 'Tâm lý', 'tam-ly', '2025-03-27 20:45:32'),
(11, 'Lịch Sử', 'lich-su', '2025-04-09 11:44:37'),
(12, 'Chính Kịch', 'chinh-kich', '2025-04-09 11:44:43'),
(13, 'Bí Ẩn', 'bi-an', '2025-04-09 11:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `movie`
--

CREATE TABLE `movie` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `trailer` varchar(255) DEFAULT NULL,
  `duration` int NOT NULL,
  `imageUrl` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `movie`
--

INSERT INTO `movie` (`id`, `title`, `description`, `trailer`, `duration`, `imageUrl`, `createdAt`) VALUES
(1, 'Oppenheimer', 'Phim tiểu sử do Christopher Nolan đạo diễn, kể về cuộc đời của J. Robert Oppenheimer – cha đẻ của bom nguyên tử. Tác phẩm khắc họa hành trình khoa học, đạo đức và chính trị xoay quanh Dự án Manhattan trong Thế chiến II.', 'https://www.youtube.com/watch?v=uYPbbksJxIg', 180, 'public/uploads/movies/thumb-1920-1326785.jpeg', '2025-03-27 20:41:00'),
(7, 'Huyết Án Truy Hành', 'Sau cái chết bí ẩn của con gái Gia Kỳ, cảnh sát Lạc Nhất Ngôn từ chức và tự mình điều tra khi phát hiện nhiều vụ án mạng tương tự. Trong quá trình này, ông tìm thấy một nhân chứng quan trọng, nhưng đó lại là một cô bé thiểu năng trí tuệ.', 'https://www.youtube.com/watch?v=gCWdumFKFeU', 83, 'public/uploads/movies/referenceSchemeHeadOfficeallowPlaceHoldertrueheight700ldapp-38.jpg', '2025-04-09 11:49:04'),
(8, 'Oán Linh Nhập Xác', 'Bộ phim kinh dị Indonesia kể về hành trình bí ẩn của hai chị em Hanif và Isti khi trở về ngôi nhà thời thơ ấu sau cái chết của người chú. Tại đây, họ đối mặt với những hiện tượng siêu nhiên và dần khám phá ra bí mật đen tối về gia đình mình.', 'https://www.youtube.com/watch?v=5vXzMvh4sMU', 95, 'public/uploads/movies/asd.jpg', '2025-04-09 11:52:57'),
(9, 'A Minecraft Movie', 'Bốn người bạn bất ngờ bị cuốn vào thế giới kỳ lạ của Minecraft thông qua một cánh cổng bí ẩn. Tại đây, họ phải học cách sinh tồn và hợp tác với Steve, một thợ xây dựng lão luyện, để chống lại các mối đe dọa như Piglins và Zombies, đồng thời tìm đường trở về nhà. ', 'https://www.youtube.com/watch?v=8B1EtVPBSMw', 101, 'public/uploads/movies/asdx.jpg', '2025-04-09 11:55:53'),
(10, 'Mật Vụ Phụ Hồ', 'Levon Cade (Jason Statham), cựu biệt kích tinh nhuệ thuộc lực lượng Thủy quân Lục chiến Hoàng gia Anh, quyết định rời xa cuộc sống bạo lực để làm công nhân xây dựng tại Chicago, Mỹ. Anh có mối quan hệ thân thiết với ông chủ Joe Garcia (Michael Peña). Khi con gái của Joe, Jenny (Arianna Rivas), bị bắt cóc, Levon buộc phải sử dụng lại kỹ năng chiến đấu để giải cứu cô bé.', NULL, 116, 'public/uploads/movies/asdzzx.jpg', '2025-04-09 12:06:34'),
(11, 'Ninja Rantaro: Giải Cứu Quân Sư', 'Sau khi thầy Doi chấp nhận lời thách đấu từ Moroizumi Sonemon, ông đột ngột mất tích. Trước tình hình đó, các thầy cô và học sinh lớp 6 của Học viện Ninjutsu quyết định tổ chức cuộc tìm kiếm toàn diện. Trong quá trình này, Kirimaru tình cờ phát hiện manh mối về tung tích của thầy Doi. Đồng thời, quân sư lạnh lùng Tenki từ đội Ninja Dokutake bất ngờ xuất hiện, tạo thêm nhiều tình huống bất ngờ cho cuộc hành trình.', NULL, 90, 'public/uploads/movies/1.jpg', '2025-04-09 12:13:39'),
(12, '​Đại Chiến Người Khổng Lồ: Lần Tấn Công Cuối Cùng', 'Trong cuộc chiến cuối cùng quyết định số phận thế giới, Eren Yeager giải phóng sức mạnh tối thượng của các Titan, dẫn đầu đội quân Titan Đại hình khổng lồ với mục tiêu hủy diệt mọi kẻ thù đe dọa đến quê hương Eldia. Tuy nhiên, cuộc chiến này không chỉ xoay quanh sự sống còn, mà còn là đối đầu giữa lòng thù hận và hy vọng.', '', 144, 'public/uploads/movies/titan.jpg', '2025-04-09 12:15:28'),
(13, 'Captain America: Trật Tự Thế Giới Mới', 'Sam Wilson (Anthony Mackie) chính thức đảm nhận vai trò Captain America. Sau cuộc gặp với Tổng thống Hoa Kỳ mới đắc cử Thaddeus Ross (Harrison Ford), Sam bị cuốn vào một sự cố quốc tế và phải khám phá động cơ đằng sau một âm mưu toàn cầu hiểm ác. ​', '', 118, 'public/uploads/movies/cap.jpg', '2025-04-09 12:17:38');

-- --------------------------------------------------------

--
-- Table structure for table `movie_categories`
--

CREATE TABLE `movie_categories` (
  `movie_id` int NOT NULL,
  `category_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movie_categories`
--

INSERT INTO `movie_categories` (`movie_id`, `category_id`) VALUES
(7, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(9, 2),
(10, 2),
(13, 2),
(11, 3),
(12, 3),
(9, 4),
(11, 4),
(8, 6),
(7, 8),
(8, 8),
(12, 8),
(1, 11),
(1, 12),
(7, 13);

-- --------------------------------------------------------

--
-- Table structure for table `showtime`
--

CREATE TABLE `showtime` (
  `id` int NOT NULL,
  `movieId` int NOT NULL,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `room` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `showtime`
--

INSERT INTO `showtime` (`id`, `movieId`, `startTime`, `endTime`, `room`, `price`) VALUES
(18, 13, '2025-04-10 15:20:00', '2025-04-10 17:18:00', 'Phòng 1', '100000.00'),
(22, 13, '2025-04-14 07:16:00', '2025-04-14 09:14:00', 'Phòng 2', '130000.00');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `phone`, `email`, `password`, `role`, `createdAt`) VALUES
(1, 'Admin', NULL, 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-03-10 15:16:03'),
(2, 'BanhDepTraiShop', NULL, 'truongnga252003@gmail.com', '$2y$10$bDQ9D6a/mu2FqADTPwt74.yHlRjcM6GZVXHYejiDTEeRSkCcUi5wm', 'user', '2025-03-10 15:26:24'),
(4, 'BAnhcute', NULL, 'nguoisudungthu21@gmail.com', '$2y$10$pKWzntdimKZRspHMKLElwONS/TxkPf/2jy6sLmjU7pTp7wZKkKFpG', 'user', '2025-03-10 21:03:56'),
(5, 'Luc', NULL, 'lucdinh1210@gmail.com', '$2y$10$0EW1cW9DGGOdx4lXZR2syeCsBzAhbEsz2vk27id627toKuxErGhz2', 'user', '2025-03-13 09:17:12'),
(6, 'bao', NULL, 'huynhngocbao2806@gmail.com', '$2y$10$d.ndG0.7sgVcJSbpoNYl9O6L8KEsPeWPQqDzw0XrRl12glQRge2Ka', 'user', '2025-03-13 09:22:18'),
(7, 'banh', '9028445456', 'nguoisudungthu3@gmail.com', '$2y$10$3VZqS3nN3FEWCZ2MO0b.NODT8f4EyMtgAKTMw9/aWlCG8oo4PG5g.', 'user', '2025-03-18 10:20:17'),
(8, 'BanhDepTraiShop', NULL, 'nguoisudungthu13@gmail.com', '$2y$10$IUT6aXbfJwEdqfpctH/z0uW9G3s.eIMuOiJV103Aas4k0IwReoPDK', 'user', '2025-04-10 14:20:46'),
(9, 'Bảo Anh Trương', '9028445456', 'testa252003@gmail.com', '$2y$10$6AJOsQphcDsuWOGOKMhncuiNI1fWATBYO4yMZ7ZYzSN5ZDzV/RWJy', 'user', '2025-04-11 20:24:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `showTimeId` (`showTimeId`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movie`
--
ALTER TABLE `movie`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movie_categories`
--
ALTER TABLE `movie_categories`
  ADD PRIMARY KEY (`movie_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `showtime`
--
ALTER TABLE `showtime`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movieId` (`movieId`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `movie`
--
ALTER TABLE `movie`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `showtime`
--
ALTER TABLE `showtime`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`showTimeId`) REFERENCES `showtime` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_categories`
--
ALTER TABLE `movie_categories`
  ADD CONSTRAINT `movie_categories_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`id`),
  ADD CONSTRAINT `movie_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `showtime`
--
ALTER TABLE `showtime`
  ADD CONSTRAINT `showtime_ibfk_1` FOREIGN KEY (`movieId`) REFERENCES `movie` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
