-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 23 أكتوبر 2023 الساعة 16:11
-- إصدار الخادم: 10.4.13-MariaDB
-- PHP Version: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- Remove existing tables so this single SQL file can be imported again safely.
-- Foreign keys are disabled temporarily so dependent tables can be removed first.
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `user`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------

--
-- بنية الجدول `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `book_name` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publish_date` date NOT NULL,
  `price` float NOT NULL,
  `quantity` int(11) NOT NULL,
  `img` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `books`
--

INSERT INTO `books` (`book_id`, `book_name`, `author`, `publish_date`, `price`, `quantity`, `img`) VALUES
(2, 'Gasdjkhjzshk', 'Ana Huang', '2019-12-10', 1000, 28, 0x75706c6f6164732f332e6a7067),
(3, 'Hate', 'Ana Huang', '2019-06-12', 1200, 30, 0x75706c6f6164732f342e6a7067),
(4, 'LIES', 'Ana Huang', '2018-01-04', 1300, 30, 0x75706c6f6164732f352e6a7067),
(5, 'Love', 'Ana Huang', '2020-12-05', 1800, 30, 0x75706c6f6164732f322e6a7067),
(7, 'IT Ends With Us', 'Colleen Hoover', '2023-07-12', 1800, 30, 0x75706c6f6164732f31322e6a7067),
(8, 'You are mine novel', 'Mona Al-Marshoud ', '2022-04-10', 1500, 30, 0x75706c6f6164732f31302e6a7067),
(9, 'I loved scoundrel', 'Emad Rashad Othman', '2022-01-09', 900, 30, 0x75706c6f6164732f32382e6a7067),
(12, 'Messages from the Quran', 'Adham Sharqawi', '2023-02-02', 1000, 30, 0x75706c6f6164732f31342e6a7067),
(13, 'Concealer', 'Nermin Nahmad', '2023-01-02', 1200, 29, 0x75706c6f6164732f31372e6a7067),
(14, 'Female books', 'Shahr Zad', '2023-11-01', 1600, 30, 0x75706c6f6164732f31352e6a7067),
(15, 'A city that never sleeps', 'Fahd Al-Awda', '2023-12-12', 1300, 29, 0x75706c6f6164732f32352e6a7067),
(16, 'Rich dad and poor dad', 'Robert T Kiyosaki', '2023-03-31', 1350, 30, 0x75706c6f6164732f31382e6a7067),
(17, 'Land of sacrifices', 'Osama Al-Muslim', '2023-11-01', 1400, 30, 0x75706c6f6164732f31362e6a7067),
(18, 'My father whom I hate', 'Emad Rashad Othman', '2022-12-10', 1700, 30, 0x75706c6f6164732f31392e6a7067),
(19, 'Intergestus', 'Ahmed Khaled Mustafa', '2020-05-05', 1150, 30, 0x75706c6f6164732f32302e6a7067),
(20, 'Morning talk', 'Adham Sharqawi', '2023-02-05', 1500, 30, 0x75706c6f6164732f32322e6a7067),
(21, 'Laknaud', 'Islam Jamal', '2023-01-02', 800, 30, 0x75706c6f6164732f32332e6a7067),
(22, 'The devil is telling', 'Ahmed Khaled Mustafa', '2019-02-10', 1000, 30, 0x75706c6f6164732f32342e6a7067),
(23, 'To reassure my heart', 'Adham Sharqawi', '2018-11-12', 850, 30, 0x75706c6f6164732f32362e6a7067),
(24, 'The Epic of the Seas series', 'Osama Al-Muslim', '2023-02-02', 6000, 30, 0x75706c6f6164732f32392e6a7067),
(25, 'Orchards Arabistan series', 'Osama Al-Muslim', '2023-05-05', 7000, 30, 0x75706c6f6164732f33302e6a7067),
(26, 'Fear series', 'Osama Al-Muslim', '2022-11-11', 3500, 30, 0x75706c6f6164732f33312e6a7067),
(27, 'Rare case series', 'Abdul Wahab Al-Sayed Al-Rifai', '2021-02-03', 8000, 30, 0x75706c6f6164732f33322e6a7067);

-- --------------------------------------------------------

--
-- بنية الجدول `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `authorName` varchar(255) NOT NULL,
  `publicationDate` date NOT NULL,
  `price` float NOT NULL,
  `img` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `carts`
--

INSERT INTO `carts` (`id`, `userId`, `name`, `authorName`, `publicationDate`, `price`, `img`) VALUES
(28, 10, 'Hate', 'Ana Huang', '2019-06-12', 1200, 0x75706c6f6164732f342e6a7067);

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` int(11) NOT NULL,
  `bookName` varchar(255) NOT NULL,
  `authorName` varchar(255) NOT NULL,
  `orderDate` date NOT NULL,
  `price` float NOT NULL,
  `img` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `userId`, `address`, `phone`, `bookName`, `authorName`, `orderDate`, `price`, `img`) VALUES
(16, 10, 'hhh', 772878794, 'Orchards Arabistan series', 'Osama Al-Muslim', '2023-10-21', 7000, 0x75706c6f6164732f33302e6a7067),
(17, 18, 'sanaa', 773804888, 'I loved scoundrel', 'Emad Rashad Othman', '2023-10-23', 900, 0x75706c6f6164732f32382e6a7067),
(18, 18, 'sanaa', 773804888, 'Intergestus', 'Ahmed Khaled Mustafa', '2023-10-23', 1150, 0x75706c6f6164732f32302e6a7067),
(19, 18, 'sanaa', 773804888, 'Messages from the Quran', 'Adham Sharqawi', '2023-10-23', 1000, 0x75706c6f6164732f31342e6a7067),
(20, 19, 'hamdan', 774620441, 'The devil is telling', 'Ahmed Khaled Mustafa', '2023-10-23', 1000, 0x75706c6f6164732f32322e6a7067),
(22, 18, 'sanaa', 773804888, 'Fear series', 'Osama Al-Muslim', '2023-10-23', 3500, 0x75706c6f6164732f33312e6a7067),
(23, 10, 'hhh', 772878794, 'Games', 'Ana Huang', '2023-10-23', 1000, 0x75706c6f6164732f332e6a7067),
(24, 10, 'hhh', 772878794, 'LIES', 'Ana Huang', '2023-10-23', 1300, 0x75706c6f6164732f352e6a7067);

-- --------------------------------------------------------

--
-- بنية الجدول `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `userName` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `img` blob NOT NULL,
  `role` varchar(255) NOT NULL,
  `status` enum('pending','active') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `birthdate`, `userName`, `password`, `img`, `role`, `status`) VALUES
(9, 'Bashar Alabbasi', 'basharalabbasi500@gmail.com', '1999-01-12', 'b', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 0x75706c6f6164732f373231312e6a7067, 'admin', 'active'),
(10, 'Bashar Alabbasi', 'basharalabbasi500@gmail.com', '2000-01-10', 'a', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 0x75706c6f6164732f7465616d2d312e706e67, 'User', 'active'),
(15, 'cgjhghnh', 'basharalabbasi1@gmail.com', '2018-01-01', 'cc', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 0x75706c6f6164732f7465616d2d342e706e67, 'User', 'active'),
(17, 'd', 'basharalabbasi33@gmail.com', '2023-10-23', 'd', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 0x75706c6f6164732f312e6a7067, 'User', 'active'),
(18, 'ahmed', 'a@g.com', '2001-03-09', 'ariqe', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 0x75706c6f6164732f7465616d2d332e706e67, 'User', 'active'),
(19, 'emad', 'emad@gmail.com', '2001-06-01', 'emad', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 0x75706c6f6164732f7465616d2d342e706e67, 'User', 'active');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`);

ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- القيود للجدول `carts`
--

ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);

--
-- القيود للجدول `orders`
--

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
