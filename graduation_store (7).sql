-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 01, 2025 at 09:43 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `graduation_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cartitem`
--

CREATE TABLE `cartitem` (
  `CartItemID` varchar(50) DEFAULT NULL,
  `CartID` varchar(32) DEFAULT NULL,
  `productId` varchar(20) NOT NULL,
  `Quantity` int DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL,
  `TotalPrice` decimal(10,2) DEFAULT NULL,
  `SalesTax` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cartitem`
--

INSERT INTO `cartitem` (`CartItemID`, `CartID`, `productId`, `Quantity`, `Price`, `TotalPrice`, `SalesTax`) VALUES
('CI150', 'C68113c0555505', 'P007', 1, NULL, NULL, NULL),
('CI156', 'C68113d95cc5cf', 'P002', 11, NULL, NULL, NULL),
('CI160', 'C681138211cc78', 'P003', 1, NULL, NULL, NULL),
('CI191', 'C681138211cc78', 'P002', 1, NULL, NULL, NULL),
('CI207', 'C68113c0555505', 'P003', 1, NULL, NULL, NULL),
('CI290', 'C6811375e6966a', 'P004', 1, NULL, NULL, NULL),
('CI319', 'C6811350676e97', 'P004', 1, NULL, NULL, NULL),
('CI637', 'C68113d95cc5cf', 'P003', 2, NULL, NULL, NULL),
('CI691', 'C681138211cc78', 'P004', 1, NULL, NULL, NULL),
('CI772', 'C68112e7766626', 'P003', 16, NULL, NULL, NULL),
('CI859', 'C68113c0555505', 'P002', 1, NULL, NULL, NULL),
('CI1569235668120de3a951e2.79275812', 'C6811bde0bb3b0', 'P031', 1, NULL, NULL, NULL),
('CI1765340560681222f6275568.10480519', 'C6811c72531b9d', 'P002', 2, NULL, NULL, NULL),
('CI125520633868122376e8d9a1.06766877', 'C6811c72531b9d', 'P001', 1, NULL, NULL, NULL),
('CI157996749368122e341d1bb1.23450653', 'C68122e341be57', 'P002', 1, NULL, NULL, NULL),
('CI67351336812382a34da00.66542640', 'C681237e2c81a5', 'P025', 3, NULL, NULL, NULL),
('CI163840491681259dacb56b2.34438263', 'C68125755580f4', 'P026', 1, NULL, NULL, NULL),
('CI1646213709681259ed1bf398.18439927', 'C68125755580f4', 'P023', 2, NULL, NULL, NULL),
('CI1661917166681259f33060e6.42139413', 'C68125755580f4', 'P001', 2, NULL, NULL, NULL),
('CI15800611576813406646c8b4.52942170', 'C68122c79c3135', 'P002', 5, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryId` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryId`, `name`) VALUES
('C001', 'Floral Gifts'),
('C002', 'Personalized Gifts'),
('C003', 'Souvenirs & Memorabilia'),
('C004', 'Photography Frames'),
('C005', 'Party & Supplies');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customerid` varchar(5) NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phonenum` varchar(12) NOT NULL,
  `dateofbirth` date NOT NULL,
  `address` varchar(50) NOT NULL,
  `gender` char(1) NOT NULL,
  `password` varchar(8) NOT NULL,
  `registrationtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customerid`, `name`, `email`, `phonenum`, `dateofbirth`, `address`, `gender`, `password`, `registrationtime`) VALUES
('C0001', 'wei', 'wei@gmail.com', '010-1234567', '2025-04-08', 'TARUMT,', 'M', '11111111', '2025-04-19 05:08:31'),
('C0002', 'hao', 'hao777@gmail.com', '010-9999999', '2025-04-08', 'TARUMT', 'M', '22222222', '2025-04-19 05:11:52'),
('C0003', 'wei hu', 'weihu777@gmail.com', '010-1234567', '2025-04-08', 'TARUMT,', 'M', '33333333', '2025-04-19 05:13:16'),
('C0004', 'hu wei', 'hushi777@gmail.com', '010-1234567', '2025-04-08', 'TARUMT,', 'M', '13141314', '2025-04-23 06:07:34'),
('C0005', 'Koh Ke Xing', 'kohkexing0@gmail.com', '017-5816201', '2005-06-22', 'Jalan Seri Pinang 1', 'F', '12345678', '2025-04-25 12:29:49'),
('C0006', 's', 's@gmail.com', '011-74859632', '2001-06-11', 'jalan tepi pantai', 'M', '0000', '2025-05-01 09:34:58');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `log_id` int NOT NULL,
  `productId` varchar(20) NOT NULL,
  `change_type` varchar(50) NOT NULL,
  `change_amount` int NOT NULL,
  `notes` text,
  `manager_id` int DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`log_id`, `productId`, `change_type`, `change_amount`, `notes`, `manager_id`, `timestamp`) VALUES
(1, 'P001', 'Stock Added', 4, '', 1, '2025-04-25 08:48:12'),
(2, 'P027', 'Stock Added', 5, '', 1, '2025-04-25 08:51:04'),
(3, 'P001', 'Stock Added', 1, '', 1, '2025-05-01 09:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `manager`
--

CREATE TABLE `manager` (
  `managerID` varchar(5) NOT NULL,
  `managername` varchar(30) NOT NULL,
  `mgnTelephone` varchar(12) NOT NULL,
  `mgnemail` varchar(30) NOT NULL,
  `mgnpassword` varchar(8) NOT NULL,
  `department` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `manager`
--

INSERT INTO `manager` (`managerID`, `managername`, `mgnTelephone`, `mgnemail`, `mgnpassword`, `department`) VALUES
('A1001', 'Ng Soon Siang', '011-22229888', 'ss333@gmail.com', '12345678', 'IT employee');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderID` varchar(5) NOT NULL,
  `customerid` varchar(5) DEFAULT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `DiscountAmount` decimal(10,2) DEFAULT NULL,
  `FinalAmount` decimal(10,2) DEFAULT NULL,
  `OrderStatus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`OrderID`, `customerid`, `OrderDate`, `TotalAmount`, `DiscountAmount`, `FinalAmount`, `OrderStatus`) VALUES
('O0001', 'C0001', '2025-04-30 04:21:50', 2880.00, 0.00, 3052.80, 'Completed'),
('O0002', 'C0001', '2025-04-30 04:31:49', 220.00, 0.00, 233.20, 'Pending'),
('O0003', 'C0001', '2025-04-30 04:35:04', 220.00, 0.00, 233.20, 'Pending'),
('O0004', 'C0001', '2025-04-30 04:45:40', 650.00, 0.00, 689.00, 'Pending'),
('O0005', 'C0001', '2025-04-30 04:55:37', 570.00, 0.00, 604.20, 'Pending'),
('O0006', 'C0006', '2025-04-30 21:55:16', 620.00, 0.00, 657.20, 'Pending'),
('O0007', 'C0005', '2025-04-30 22:03:01', 360.00, 0.00, 381.60, 'Pending'),
('O0008', 'C0005', '2025-04-30 22:06:43', 250.00, 0.00, 265.00, 'Processing'),
('O0009', 'C0005', '2025-05-01 00:53:54', 60.00, 0.00, 63.60, 'Pending'),
('O0010', 'C0005', '2025-05-01 01:12:40', 378.00, 0.00, 400.68, 'Processing'),
('O0011', 'C0006', '2025-05-01 17:36:16', 1250.00, 0.00, 1325.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetail`
--

CREATE TABLE `orderdetail` (
  `OrderDetailID` varchar(5) NOT NULL,
  `OrderID` varchar(5) DEFAULT NULL,
  `productId` varchar(20) DEFAULT NULL,
  `Quantity` int DEFAULT NULL,
  `UnitPrice` decimal(10,2) DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `DiscountAmount` decimal(10,2) DEFAULT NULL,
  `FinalAmount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orderdetail`
--

INSERT INTO `orderdetail` (`OrderDetailID`, `OrderID`, `productId`, `Quantity`, `UnitPrice`, `TotalAmount`, `DiscountAmount`, `FinalAmount`) VALUES
('OD001', 'O0003', 'P004', 1, 220.00, 220.00, 0.00, 220.00),
('OD002', 'O0004', 'P003', 1, 180.00, 180.00, 0.00, 180.00),
('OD003', 'O0004', 'P002', 1, 250.00, 250.00, 0.00, 250.00),
('OD004', 'O0004', 'P004', 1, 220.00, 220.00, 0.00, 220.00),
('OD005', 'O0005', 'P007', 1, 140.00, 140.00, 0.00, 140.00),
('OD006', 'O0005', 'P003', 1, 180.00, 180.00, 0.00, 180.00),
('OD007', 'O0005', 'P002', 1, 250.00, 250.00, 0.00, 250.00),
('OD008', 'O0006', 'P002', 2, 250.00, 500.00, 0.00, 500.00),
('OD009', 'O0006', 'P001', 1, 120.00, 120.00, 0.00, 120.00),
('OD010', 'O0007', 'P031', 1, 360.00, 360.00, 0.00, 360.00),
('OD011', 'O0008', 'P002', 1, 250.00, 250.00, 0.00, 250.00),
('OD012', 'O0009', 'P025', 3, 20.00, 60.00, 0.00, 60.00),
('OD013', 'O0010', 'P026', 1, 28.00, 28.00, 0.00, 28.00),
('OD014', 'O0010', 'P023', 2, 55.00, 110.00, 0.00, 110.00),
('OD015', 'O0010', 'P001', 2, 120.00, 240.00, 0.00, 240.00),
('OD016', 'O0011', 'P002', 5, 250.00, 1250.00, 0.00, 1250.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` varchar(5) NOT NULL,
  `OrderID` varchar(5) DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `PaymentDate` datetime DEFAULT NULL,
  `PaymentAmount` decimal(10,2) DEFAULT NULL,
  `PaymentStatus` varchar(50) DEFAULT NULL,
  `TransactionID` varchar(255) DEFAULT NULL,
  `PaymentToken` varchar(255) DEFAULT NULL,
  `PaymentConfirmation` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `OrderID`, `PaymentMethod`, `PaymentDate`, `PaymentAmount`, `PaymentStatus`, `TransactionID`, `PaymentToken`, `PaymentConfirmation`, `name`) VALUES
('P0001', 'O0001', 'Credit/Debit Card', '2025-04-30 04:21:50', 3052.80, 'Completed', 'TXNA31C244F', NULL, NULL, NULL),
('P0002', 'O0002', 'Credit/Debit Card', '2025-04-30 04:31:49', 233.20, 'Completed', 'TXN2F6068B1', NULL, NULL, NULL),
('P0003', 'O0003', 'Credit/Debit Card', '2025-04-30 04:35:04', 233.20, 'Completed', 'TXNEB8F9668', NULL, NULL, NULL),
('P0004', 'O0004', 'Credit/Debit Card', '2025-04-30 04:45:40', 689.00, 'Completed', 'TXN37D3ED62', NULL, NULL, NULL),
('P0005', 'O0005', 'Credit/Debit Card', '2025-04-30 04:55:37', 604.20, 'Completed', 'TXNAE88A3D7', NULL, NULL, NULL),
('P0006', 'O0006', 'E-Wallet', '2025-04-30 21:55:16', 657.20, 'Completed', 'TXNE7A447A7', NULL, NULL, NULL),
('P0007', 'O0007', 'E-Wallet', '2025-04-30 22:03:01', 381.60, 'Completed', 'TXN0570AF67', NULL, NULL, NULL),
('P0008', 'O0008', 'E-Wallet', '2025-04-30 22:06:43', 265.00, 'Completed', 'TXNFC119BF1', NULL, NULL, 'KL'),
('P0009', 'O0009', 'Credit/Debit Card', '2025-05-01 00:53:54', 63.60, 'Completed', 'TXNF8C162BC', NULL, NULL, 'Lim Chun Chuan'),
('P0010', 'O0010', 'E-Wallet', '2025-05-01 01:12:40', 400.68, 'Completed', 'TXN32F5717C', NULL, NULL, 'Koh Ke XIng'),
('P0011', 'O0011', 'E-Wallet', '2025-05-01 17:36:16', 1325.00, 'Completed', 'TXNEB25EFE8', NULL, NULL, 's');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productId` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `categoryId` varchar(20) DEFAULT NULL,
  `subcategoryId` varchar(20) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productId`, `name`, `description`, `price`, `categoryId`, `subcategoryId`, `stock`, `image_url`, `created_at`) VALUES
('P001', 'Rad Grad Graduation Bouquet', 'Cheers to the coolest graduate with our Rad Grad bouquet! Bursting with vibrant energy and cheerful vibes, this bouquet is the ultimate symbol of your pride and excitement', 250.00, 'C001', 'S001', 48, 'img\\FloralGifts\\flower\\img.jpg', '2025-04-18 11:10:13'),
('P002', 'Rosy Felicitations Graduation Set', 'Wish the wonderful graduate a hearty congratulations, with a heartfelt gift for their big day! Our Rosy Felicitations Graduation Set brings you an adorable graduate teddy bear, along with our stunning La Vie En Rose bouquet', 250.00, 'C001', 'S001', 42, 'img\\FloralGifts\\flower\\img5.jpg', '2025-04-18 11:10:13'),
('P003', 'Bright Future Graduation Bouquet', 'Step into a Bright Future with our bouquet, a delightful ensemble that captures the essence of hope and the excitement of what lies ahead. With each bloom carefully chosen, it celebrates achievements and beckons a world of opportunities.', 180.00, 'C001', 'S001', 20, 'img\\FloralGifts\\flower\\img3.jpg', '2025-04-18 11:10:13'),
('P004', 'Big Cheers Graduation Set', 'A milestone this momentous deserves a Big Cheers - with the perfect graduation gift set. Bringing you our lovely Just For You rose bouquet, along with an adorable lil\' graduate teddy', 220.00, 'C001', 'S001', 20, 'img\\FloralGifts\\flower\\img6.jpg', '2025-04-18 11:10:13'),
('P005', 'Flying Colours Graduation Set', 'A gift to celebrate their hard work, long hours, and Flying Colours! Featuring our vibrant Gerbera Galore bouquet, along with an adorable little graduate teddy - this gift set is sure to put the spotlight on them', 180.00, 'C001', 'S001', 20, 'img\\FloralGifts\\flower\\img2.jpg', '2025-04-18 11:10:13'),
('P006', 'Chocolate Joy', 'Spoil someone\'s day with a bouquet of chocolates! Not your typical flower bouquet but it will surely hit the spot for those with a sweet-tooth. Nothing can get any sweeter than this.', 159.00, 'C001', 'S002', 30, 'img\\FloralGifts\\Chocolate\\img1.jpg', '2025-04-18 11:10:13'),
('P007', '30 Red Roses and Ferrero', 'Express your faithfulness to your significant other with our 30 Red Roses and Fererro bouquet; decked with 30 majestic red roses and delicious 10 Ferrero rocher chocolates.', 140.00, 'C001', 'S002', 30, 'img\\FloralGifts\\Chocolate\\img5.jpg', '2025-04-18 11:11:07'),
('P008', 'Sweeter Blooms', 'Love is not always bold, sometimes it is also soft and sweet.', 130.00, 'C001', 'S002', 30, 'img\\FloralGifts\\Chocolate\\img3.jpg', '2025-04-18 11:11:07'),
('P009', 'Graceful Gift Box', 'For the beautiful, charming, and enchanting woman in your life - the perfect gift of love, made for her!', 140.00, 'C001', 'S003', 20, 'img\\FloralGifts\\gift\\img1.jpg', '2025-04-18 11:11:07'),
('P010', 'The Juggernaut Gift Set', 'This is a thoughtful gift that captures the warmth and sweetness of young love.', 150.00, 'C001', 'S003', 20, 'img\\FloralGifts\\gift\\img2.jpg', '2025-04-18 11:11:07'),
('P011', 'Silver Celebrations Gift Box', 'Give them the gift of a blast-filled birthday, with our Silver Celebrations Gift Box', 110.00, 'C001', 'S003', 20, 'img\\FloralGifts\\gift\\img3.jpg', '2025-04-18 11:11:07'),
('P012', 'Sparkle Balloon Bunch', 'Make a bold statement with our Sparkle Balloon Bunch.', 89.00, 'C001', 'S004', 20, 'img\\FloralGifts\\ballon\\img2.jpg', '2025-04-18 11:11:07'),
('P013', 'Linda Balloon Flower Box', 'Make your next celebration more glorious with our Linda Hot Air Balloon Bouquet!', 129.00, 'C001', 'S004', 20, 'img\\FloralGifts\\ballon\\img1.jpg', '2025-04-18 11:11:07'),
('P014', 'Imelight', 'Wish them all the luck and glory on their graduation day, with the perfect flower bouquet!', 145.00, 'C001', 'S004', 20, 'img\\FloralGifts\\ballon\\img3.jpg', '2025-04-18 11:11:07'),
('P015', 'GradGlow Keychain', 'Celebrate your achievement with the GradGlow Keychain — a sleek and timeless keepsake crafted to honor your graduation milestone.', 32.00, 'C002', 'S011', 15, 'img\\PersonalizedGifts\\keychain\\img2.jpg', '2025-04-18 11:11:07'),
('P016', 'Milestone Memory Keychain', 'Mark your special moment with the Milestone Memory Keychain.', 20.00, 'C002', 'S011', 20, 'img\\PersonalizedGifts\\keychain\\img5.jpg', '2025-04-18 11:11:07'),
('P017', 'Grad Sip Memories Mug', 'Start each day with a warm reminder of your hard-earned success.', 56.00, 'C002', 'S012', 22, 'img\\PersonalizedGifts\\mug\\img2.jpg', '2025-04-18 11:11:07'),
('P018', 'The Graduate Mug', 'The Graduate Mug features an elegant black-and-white design with a minimalist cap-and-scroll graphic and your graduation year.', 56.00, 'C002', 'S012', 22, 'img\\PersonalizedGifts\\mug\\img3.jpg', '2025-04-18 11:11:07'),
('P019', 'Forever a Graduate Tee', 'Celebrate a milestone that lasts a lifetime. The Forever a Graduate Tee features a clean design.', 88.00, 'C002', 'S013', 25, 'img\\PersonalizedGifts\\shirt\\img2.jpg', '2025-04-18 11:11:07'),
('P020', 'Done & Dusted Shirt', 'The Done & Dusted Shirt is for grads who survived the chaos with humor.', 92.00, 'C002', 'S013', 25, 'img\\PersonalizedGifts\\shirt\\img3.jpg', '2025-04-18 11:11:07'),
('P021', 'GradVibe Frame', 'Make your graduation wall-worthy. The GradVibe Frame blends modern design with personal touches.', 66.00, 'C002', 'S014', 30, 'img\\PersonalizedGifts\\frame\\img2.jpg', '2025-04-18 11:11:55'),
('P022', 'Frame of Achievement', 'Honor your journey with the Frame of Achievement — a beautifully crafted custom frame designed to showcase your proudest moment.', 76.00, 'C002', 'S014', 30, 'img\\PersonalizedGifts\\frame\\img6.jpg', '2025-04-18 11:11:55'),
('P023', 'Congrats Bear Hug', 'Soft, cuddly, and full of pride — Congrats Bear Hug is here to celebrate your big day!', 55.00, 'C003', 'S021', 53, 'img\\SouvenirsMemorabilia\\Toys\\img1.jpg', '2025-04-18 11:11:55'),
('P024', 'Forever Proud Grad Bear', 'Celebrate your journey with the Forever Proud Grad Bear, a heartwarming keepsake wearing a custom cap and gown.', 68.00, 'C003', 'S021', 55, 'img\\SouvenirsMemorabilia\\Toys\\img2.jpg', '2025-04-18 11:11:55'),
('P025', 'GradMetal Badge', 'Celebrate your academic milestone with the GradMetal Badge.', 20.00, 'C003', 'S022', 97, 'img\\SouvenirsMemorabilia\\Badges\\img1.jpg', '2025-04-18 11:11:55'),
('P026', 'Shine & Grad Badge', 'Let your success shine! The Shine & Grad Badge is crafted from high-quality aluminum.', 28.00, 'C003', 'S022', 99, 'img\\SouvenirsMemorabilia\\Badges\\img3.jpg', '2025-04-18 11:11:55'),
('P027', 'HonorFrame Certificate', 'Celebrate your academic achievement with the HonorFrame Certificate — a professionally printed and beautifully framed certificate customized.', 58.00, 'C004', 'S031', 50, 'img\\PhotographyFrames\\FramedCertificates\\img2.jpg', '2025-04-18 11:11:55'),
('P028', 'Graduate’s Pride Frame', 'Display your milestone in style. The Graduate’s Pride Frame features a high-quality certificate printed on premium paper, set in a sleek frame.', 88.00, 'C004', 'S031', 50, 'img\\PhotographyFrames\\FramedCertificates\\img4.jpg', '2025-04-18 11:11:55'),
('P029', 'The Graduation Chronicle', 'The Graduation Chronicle is a beautifully bound yearbook filled with unforgettable memories, class photos, quotes, and heartfelt messages.', 99.00, 'C004', 'S032', 30, 'img\\PhotographyFrames\\YearbooksScrapbooks\\img2.jpg', '2025-04-18 11:11:55'),
('P030', 'My Grad Story Scrapbook', 'Tell your story, your way. My Grad Story Scrapbook is a personalized space to keep photos, polaroids, notes, ticket stubs.', 99.00, 'C004', 'S032', 30, 'img\\PhotographyFrames\\YearbooksScrapbooks\\img3.jpg', '2025-04-18 11:11:55'),
('P031', 'Float to the Future Balloons', 'Celebrate in full color with Float to the Future Balloons! These graduation-themed balloons come in classic black, gold, and white.', 360.00, 'C005', 'S041', 19, 'img\\PartySupplies\\BannersBalloons\\img1.jpg', '2025-04-18 11:11:55'),
('P032', 'Congrats Grad Custom Banner', 'Shout it out loud with a Congrats Grad Custom Banner! Printed on premium material, this banner features customizable text, school logo, and your photo if you like.', 480.00, 'C005', 'S041', 20, 'img\\PartySupplies\\BannersBalloons\\img6.jpg', '2025-04-18 11:11:55'),
('P033', 'Celebrate Spark Confetti', 'Toss the joy in the air with Celebrate Spark Confetti!', 15.00, 'C005', 'S042', 100, 'img\\PartySupplies\\ConfettiStreamers\\img2.png', '2025-04-18 11:11:55'),
('P034', 'Gun Confetti', 'Make your graduation celebration explode with excitement! The GradPop Confetti Cannon launches a burst of colorful confetti high into the air.', 10.00, 'C005', 'S042', 100, 'img\\PartySupplies\\ConfettiStreamers\\img5.png', '2025-04-18 11:11:55'),
('P035', 'Graduation Star Centerpiece', 'Celebrate your journey with the Graduation Star Centerpiece!', 89.00, 'C005', 'S043', 25, 'img\\PartySupplies\\TableCenterpieces\\img4.jpg', '2025-04-18 11:11:55'),
('P036', 'Balloon Blossom Graduation', 'Add a burst of color and elegance with the Balloon Blossom Graduation Centerpiece.', 120.00, 'C005', 'S043', 25, 'img\\PartySupplies\\TableCenterpieces\\img1.jpg', '2025-04-18 11:11:55'),
('P037', 'Best Cleaning Balm', 'asdfg', 300.00, 'C003', 'S022', 200, 'images/products/1746092018_WhatsApp 图像2025-04-27于15.38.33_4b282e10.jpg', '2025-05-01 09:33:38');

-- --------------------------------------------------------

--
-- Table structure for table `receipt`
--

CREATE TABLE `receipt` (
  `ReceiptID` varchar(5) NOT NULL,
  `PaymentID` varchar(5) DEFAULT NULL,
  `OrderID` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shoppingcart`
--

CREATE TABLE `shoppingcart` (
  `CartID` varchar(32) NOT NULL,
  `customerid` varchar(5) NOT NULL,
  `CreatedDate` datetime NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shoppingcart`
--

INSERT INTO `shoppingcart` (`CartID`, `customerid`, `CreatedDate`, `TotalAmount`, `Status`) VALUES
('C68112e7766626', 'C0001', '2025-04-29 21:54:31', 0.00, 'Completed'),
('C6811350676e97', 'C0001', '2025-04-29 22:22:30', 0.00, 'Completed'),
('C6811375e6966a', 'C0001', '2025-04-29 22:32:30', 0.00, 'Completed'),
('C681138211cc78', 'C0001', '2025-04-29 22:35:45', 0.00, 'Completed'),
('C68113c0555505', 'C0001', '2025-04-29 22:52:21', 0.00, 'Completed'),
('C68113d95cc5cf', 'C0001', '2025-04-29 22:59:01', 0.00, 'Active'),
('C6811bde0bb3b0', 'C0005', '2025-04-30 06:06:24', 0.00, 'Completed'),
('C6811c72531b9d', 'C0006', '2025-04-30 06:45:57', 0.00, 'Completed'),
('C68122c79c3135', 'C0006', '2025-04-30 13:58:17', 0.00, 'Completed'),
('C68122e341be57', 'C0005', '2025-04-30 14:05:40', 0.00, 'Completed'),
('C681237e2c81a5', 'C0005', '2025-04-30 14:46:58', 0.00, 'Completed'),
('C68125755580f4', 'C0005', '2025-04-30 17:01:09', 0.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategoryId` varchar(20) NOT NULL,
  `categoryId` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`subcategoryId`, `categoryId`, `name`) VALUES
('S001', 'C001', 'Flower Bouquets'),
('S002', 'C001', 'Chocolate Bouquets'),
('S003', 'C001', 'Gift Hampers'),
('S004', 'C001', 'Message Balloons'),
('S011', 'C002', 'Engraved Keychains'),
('S012', 'C002', 'Custom Graduation'),
('S013', 'C002', 'Personalized T-Shirts & Hoodies'),
('S014', 'C002', 'Custom Photo Frames'),
('S021', 'C003', 'Graduation Bears'),
('S022', 'C003', 'Aluminum Badges'),
('S031', 'C004', 'Framed Certificates'),
('S032', 'C004', 'Yearbook or Memory Book'),
('S041', 'C005', 'Banner & Balloons'),
('S042', 'C005', 'Confetti & Streamers'),
('S043', 'C005', 'Table Centerpieces');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryId`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customerid`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `productId` (`productId`);

--
-- Indexes for table `manager`
--
ALTER TABLE `manager`
  ADD PRIMARY KEY (`managerID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productId`),
  ADD KEY `categoryId` (`categoryId`),
  ADD KEY `subcategoryId` (`subcategoryId`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategoryId`),
  ADD KEY `categoryId` (`categoryId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategoryId`) REFERENCES `subcategories` (`subcategoryId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
