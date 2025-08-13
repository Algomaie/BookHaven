-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 27 أبريل 2025 الساعة 21:44
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookhaven`
--

-- --------------------------------------------------------

--
-- بنية الجدول `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `cover_image` varchar(255) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `dimensions` varchar(50) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `rating_count` int(11) DEFAULT 0,
  `sales_count` int(11) DEFAULT 0,
  `seller_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `publisher`, `description`, `isbn`, `category`, `price`, `original_price`, `stock`, `cover_image`, `pages`, `language`, `dimensions`, `rating`, `rating_count`, `sales_count`, `seller_id`, `status`, `created_at`, `updated_at`) VALUES
(69, 'ساق البامبو', 'سعود السنعوسي', 'دار الساقي', 'رواية تتناول قضية الهوية والانتماء في الكويت.', '9786144258912', 'رواية', 45.00, 60.00, 50, 'upload/1.jpg', 396, 'العربية', '5.5 x 0.8 x 8.5 inches', 4.8, 1520, 2200, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:11:22'),
(70, 'طوق الطهارة', 'عبده خال', 'دار الساقي', 'رواية تناقش التقاليد والعادات الاجتماعية.', '9786144253078', 'رواية', 38.00, 50.00, 30, 'upload/2.jpg', 432, 'العربية', '5.5 x 0.9 x 8.6 inches', 4.5, 980, 1300, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:11:46'),
(71, 'حكايا سعودي في أوروبا', 'عبد الله المغلوث', 'دار مدارك', 'رحلة سردية لرحالة سعودي في الغرب.', '9789948200241', 'رحلات', 29.00, 45.00, 40, 'upload/3.jpg', 240, 'العربية', '5.3 x 0.7 x 8 inches', 4.6, 1120, 1500, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:12:01'),
(72, 'شقة الحرية', 'غازي القصيبي', 'المؤسسة العربية للدراسات', 'رواية سياسية واجتماعية عن الحرية والتحولات.', '9789953362208', 'رواية', 42.00, 55.00, 35, 'upload/4.jpg', 360, 'العربية', '5.2 x 0.8 x 8.3 inches', 4.7, 1345, 1900, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:12:08'),
(73, 'البيكاسو وستاربكس', 'ياسر حارب', 'دار كلمات', 'تأملات ومقالات تحفيزية للحياة اليومية.', '9789948176706', 'تنمية ذاتية', 33.00, 45.00, 45, 'upload/5.jpg', 200, 'العربية', '5.4 x 0.6 x 8.4 inches', 4.4, 890, 1100, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:12:16'),
(74, 'أنت تستطيع', 'إبراهيم الفقي', 'دار المعرفة', 'كتاب تحفيزي لتحقيق الأهداف والطموحات.', '9789953422088', 'تنمية ذاتية', 35.00, 50.00, 60, 'upload/5.jpg', 210, 'العربية', '5.6 x 0.7 x 8.5 inches', 4.5, 1220, 1750, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:08:36'),
(75, 'غربة تحت الصفر', 'منى المرشود', 'دار الفلاح', 'رواية عاطفية واجتماعية.', '9789774720532', 'رواية', 40.00, 58.00, 20, 'upload/book-placeholder.jpg', 380, 'العربية', '5.4 x 0.8 x 8.4 inches', 4.3, 750, 950, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 18:21:31'),
(76, 'بائعة الخبز', 'خليل جبران', 'دار العودة', 'رواية إنسانية كلاسيكية تناولت موضوع الفقر والمعاناة.', '9789953214157', 'رواية', 20.00, 35.00, 70, 'upload/7.jpg', 250, 'العربية', '5.0 x 0.5 x 8 inches', 4.6, 1100, 1550, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:12:59'),
(77, 'في قلبي أنثى عبرية', 'خولة حمدي', 'دار كيان', 'قصة مستوحاة من أحداث حقيقية.', '9789953876227', 'رواية', 37.00, 52.00, 48, 'upload/book-placeholder.jpg', 384, 'العربية', '5.5 x 0.9 x 8.5 inches', 4.9, 1780, 2200, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 18:21:31'),
(78, 'أن تبقى', 'خالد الخضير', 'ذات السلاسل', 'رواية تتناول قصص الحرب واللجوء.', '9789996694422', 'رواية', 32.00, 46.00, 26, 'upload/book-placeholder.jpg', 320, 'العربية', '5.2 x 0.7 x 8.4 inches', 4.7, 990, 1400, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 18:21:31'),
(79, 'أحببتك أكثر مما ينبغي', 'أثير عبد الله النشمي', 'دار الفارابي', 'رواية رومانسية سعودية مشهورة.', '9786140102059', 'fiction', 39.00, 55.00, 35, 'upload/6.jpg', 328, 'العربية', '5.4 x 0.8 x 8.6 inches', 4.8, 1430, 2100, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 19:09:54'),
(80, 'فوضى الحواس', 'أحلام مستغانمي', 'دار الآداب', 'رواية عاطفية فلسفية.', '9789953892241', 'fiction', 41.00, 58.00, 30, 'upload/book_1745780106_680e7d8a5a2f9.jpg', 312, 'العربية', '5.5 x 0.8 x 8.7 inches', 4.6, 1300, 1800, 1, 'active', '2025-04-27 18:21:31', '2025-04-27 18:55:06'),
(81, 'عندما التقيت عمر بن الخطاب', 'أدهم شرقاوي', 'دار كلمات', 'رحلة أدبية رائعة مع شخصية عمر بن الخطاب.', '9786144242232', 'biography', 40.00, 55.00, 50, 'upload/book_1745779395_680e7ac3851a4.jpg', 296, 'العربية', '5.5 x 0.8 x 8.5 inches', 4.8, 1200, 2100, 1, 'active', '2025-04-27 18:32:39', '2025-04-27 18:43:15'),
(83, 'حديث الصباح', 'أدهم شرقاوي', 'دار كلمات', 'خواطر جميلة لبدء صباحك بطاقة إيجابية.', '9786140125959', 'thriller', 35.00, 48.00, 55, 'upload/book_1745779569_680e7b711c20b.jpg', 290, 'العربية', '5.4 x 0.7 x 8.4 inches', 4.7, 980, 1650, 1, 'active', '2025-04-27 18:32:39', '2025-04-27 18:46:09'),
(84, 'نبض', 'أدهم شرقاوي', 'دار كلمات', 'رواية رومانسية فلسفية.', '9786140122545', 'رواية', 38.00, 50.00, 45, 'upload/10.jpg', 304, 'العربية', '5.5 x 0.8 x 8.5 inches', 4.6, 1000, 1600, 1, 'active', '2025-04-27 18:32:39', '2025-04-27 19:15:12'),
(87, 'مع النبي', 'أدهم شرقاوي', 'دار كلمات', 'قصص واقعية مستوحاة من حياة النبي محمد ﷺ.', '9786140127915', 'biography', 42.00, 58.00, 40, 'upload/book_1745779713_680e7c0169041.jpg', 320, 'العربية', '5.5 x 0.9 x 8.5 inches', 4.9, 1500, 2300, 1, 'active', '2025-04-27 18:32:39', '2025-04-27 18:48:33');

-- --------------------------------------------------------

--
-- بنية الجدول `bundles`
--

CREATE TABLE `bundles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `book_count` int(11) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `bundle_books`
--

CREATE TABLE `bundle_books` (
  `id` int(11) NOT NULL,
  `bundle_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `payment_method`, `tracking_number`, `created_at`, `updated_at`) VALUES
(1, 4, 150.00, 'delivered', 'qqw adada\nasda\nwew, wewe 232\nUnited States\nPhone: 0554736374', 'paypal', NULL, '2025-04-26 23:53:55', '2025-04-26 23:56:25'),
(2, 4, 200.00, 'delivered', 'qqw adada\nasda\nwew, wewe 232\nUnited States\nPhone: 0554736374', 'credit_card', NULL, '2025-04-27 00:11:55', '2025-04-27 00:13:56'),
(3, 4, 29.00, 'cancelled', 'qqw adada\nasda\nwew, wewe 232\nUnited States\nPhone: 0554736374', 'credit_card', NULL, '2025-04-27 00:24:44', '2025-04-27 00:24:53');

-- --------------------------------------------------------

--
-- بنية الجدول `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `verified_purchase` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `stores`
--

INSERT INTO `stores` (`id`, `seller_id`, `name`, `description`, `logo`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'FanBook', 'Choose a unique, memorable name for your bookstore.\r\nChoose a unique, memorable name for your bookstore.\r\nChoose a unique, memorable name for your bookstore.\r\nChoose a unique, memorable name for your bookstore.', 'upload/store_logos/store_1_1745418303.jpg', 'approved', '2025-04-23 14:25:03', '2025-04-23 14:31:11');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','seller','admin') DEFAULT 'customer',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','suspended','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `first_name`, `last_name`, `phone`, `address`, `city`, `state`, `zip_code`, `country`, `created_at`, `updated_at`, `status`) VALUES
(1, 'Eissa', 'admin@gmail.com', '$2y$10$743KQcWuh3l1qWprdS7qCuWcA/ZGk7nkyD5g4bbPobRbcMROEwNtO', 'seller', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-23 14:01:41', '2025-04-23 14:11:09', 'active'),
(3, 'Eissa', 'adminbook@gmail.com', '$2y$10$743KQcWuh3l1qWprdS7qCuWcA/ZGk7nkyD5g4bbPobRbcMROEwNtO', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-23 14:01:41', '2025-04-23 14:11:09', 'active'),
(4, 'sad', 'admin@bookhub.com', '$2y$10$6yt4n3AgBJ.YtCKpaL42zucVp9DgarbLtGV73TeMpYfW1A.Ba4XbW', '', 'qqw', 'adada', '0554736374', 'asda', 'wew', 'wewe', '232', 'United States', '2025-04-24 20:00:28', '2025-04-26 23:53:55', 'active');

-- --------------------------------------------------------

--
-- بنية الجدول `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `bundles`
--
ALTER TABLE `bundles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `bundle_books`
--
ALTER TABLE `bundle_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bundle_id` (`bundle_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seller_id` (`seller_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `bundles`
--
ALTER TABLE `bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bundle_books`
--
ALTER TABLE `bundle_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `bundles`
--
ALTER TABLE `bundles`
  ADD CONSTRAINT `bundles_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `bundle_books`
--
ALTER TABLE `bundle_books`
  ADD CONSTRAINT `bundle_books_ibfk_1` FOREIGN KEY (`bundle_id`) REFERENCES `bundles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bundle_books_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
