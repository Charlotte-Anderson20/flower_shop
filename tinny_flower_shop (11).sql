-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2025 at 05:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tinny_flower_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_us`
--

CREATE TABLE `about_us` (
  `about_id` int(11) NOT NULL,
  `about_heading` text NOT NULL,
  `about_short_desc` varchar(255) NOT NULL,
  `about_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accessories`
--

CREATE TABLE `accessories` (
  `aid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accessories`
--

INSERT INTO `accessories` (`aid`, `name`, `description`, `price`, `image`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Ribbon Bow', 'Decorative ribbon for bouquets', 8000.00, '68a407143f494.jpg', 'Ribbons', '2025-07-23 02:25:16', '2025-08-19 05:09:40'),
(2, 'Gift Wrap Paper', 'Floral gift wrapping paper', 9500.00, '68a406b852090.jpg', 'Gift Boxes', '2025-07-23 17:46:55', '2025-08-19 05:08:08'),
(3, 'Greeting Card', 'Small card for personal messages', 3000.00, '689885a89e7ff.jpg', 'Greeting Cards', '2025-07-23 17:48:33', '2025-08-16 19:58:21'),
(4, 'Scented Candle', 'Rose-scented mini candle', 15000.00, '68988363a39f7.jpg', 'Candles', '2025-07-23 17:51:57', '2025-08-16 19:57:59'),
(5, 'Decorative boxes', 'Elegant keepsake boxes designed to hold flower arrangements in style and sophistication.', 15000.00, '68a40682a8563.jpg', 'Boxes', '2025-08-17 13:09:51', '2025-08-19 05:07:14'),
(6, 'Flower Baskets', 'Hand-woven baskets filled with fresh blooms, perfect for gifting with a rustic charm.', 10000.00, '68a40652be0f0.jpg', 'Baskets', '2025-08-17 13:10:46', '2025-08-19 05:06:26');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_pass` varchar(255) NOT NULL,
  `admin_image` varchar(255) NOT NULL,
  `admin_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_email`, `admin_pass`, `admin_image`, `admin_created_at`) VALUES
(1, 'Tinny', 'tinny@gmail.com', '$2y$10$f6j1Le/oxJNomMlOq5KI0.2igxOKCuqEUOnTAzoURrUFqr2z45drC', 'uploads/admin/Tinny_A.png', '2025-07-05 07:55:55'),
(2, 'Rey', 'rey@gmail.com', '$2y$10$YCdQabCXg7A/w27wrQprnO/tIF4eQd29HZDXj1Hg31gkolNxGJEZi', 'uploads/admin/reyy.jpg', '2025-07-06 02:27:57');

-- --------------------------------------------------------

--
-- Table structure for table `admin_promos`
--

CREATE TABLE `admin_promos` (
  `promo_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `gift_description` varchar(255) DEFAULT NULL,
  `min_order_amount` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_promos`
--

INSERT INTO `admin_promos` (`promo_id`, `title`, `discount_percent`, `gift_description`, `min_order_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Cute Teddy Bear', 0.00, 'ddddd', 25000.00, 'active', '2025-07-24 03:49:17', '2025-08-16 20:01:16'),
(2, 'Labu Labu', 0.00, 'Labu Labu gift way', 25000.00, 'active', '2025-08-10 20:01:22', '2025-08-16 20:01:03'),
(3, 'Summer Deal', 0.00, 'Complimentary Keychain Souvenir', 15000.00, 'active', '2025-08-16 20:01:38', '2025-08-16 20:01:38'),
(4, 'Weekend Offer', 0.00, 'Free Chocolate Bar', 20000.00, 'active', '2025-08-16 20:02:03', '2025-08-16 20:02:03'),
(5, 'Fragrance Fun', 0.00, 'Perfume Gift', 50000.00, 'active', '2025-08-16 20:02:46', '2025-08-16 20:02:46'),
(6, 'Sweet Treats', 0.00, 'Box of Cookies', 40000.00, 'active', '2025-08-16 20:03:18', '2025-08-16 20:03:18');

-- --------------------------------------------------------

--
-- Table structure for table `arrangement_type`
--

CREATE TABLE `arrangement_type` (
  `arrangement_id` int(11) NOT NULL,
  `arrangement_name` varchar(255) NOT NULL,
  `icon_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `arrangement_type`
--

INSERT INTO `arrangement_type` (`arrangement_id`, `arrangement_name`, `icon_image`) VALUES
(3, 'Bouquet', 'uploads/arrangement_icons/icon_689885639e72d.png'),
(4, 'Basket', 'uploads/arrangement_icons/icon_68988554e9813.png'),
(5, 'Box', 'uploads/arrangement_icons/icon_68988544ef8ac.png');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
(1, 'Charlotte Anderson', 'shairudiaz@gmail.com', '09954925419', 'Support', 'Lalaa', '2025-08-10 19:52:29');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `customer_password` varchar(255) NOT NULL,
  `customer_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_image` varchar(255) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_login_attempt` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `remember_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `customer_password`, `customer_created_at`, `customer_image`, `login_attempts`, `last_login_attempt`, `remember_token`, `remember_expiry`) VALUES
(1, 'Tinny', 'tinny15@gmail.com', '+959777555333', '69 109', '$2y$10$HTBCzw3sWh2cR0CAJOmbRe4ujHM4mvVWWUl5ngZWyReWgy9Q9s3wO', '2025-07-06 01:58:12', 'uploads/customers/customer_686a177c7ea93.jpg', 0, NULL, NULL, NULL),
(2, 'Jimin', 'jimin30@gmail.com', '+959954925419', '109X110 69B', '$2y$10$CDvMZI3KZwqbNYt66tNI1uiVhvowX5XS.zdGiyUstPYFYXc2ykEaK', '2025-07-07 09:05:48', 'uploads/customers/customer_689e1b338f982.jpg', 0, NULL, 'e4ed110102333c8418d17d781a0b520a8edd21ae3a733b1303fd30e0c7fcf24c', '2025-09-02 14:57:19'),
(3, 'Charlotte', 'charlotte2005@gmail.com', '+959954925419', 'New York', '$2y$10$qeIC8eZEu.wJe9CjZXCa2u8oaph0mfI9tQp4dl5DlnJxbc9wO9V/S', '2025-07-13 21:51:18', 'uploads/customers/customer_6874699ecf006.jpg', 0, NULL, '4ecb1ad7143e5141e162b67d452c4aa0fea13aa412cb3a60e2cf4ee307392f99', '2025-07-21 04:30:04'),
(4, 'Juno Park', 'junopark@gmail.com', '+95925902324', 'USA', '$2y$10$dGl7D6idG8snEyCoVP5sc.QhpORFKB.1hPvFHpr.X6jrAqwAQWAyu', '2025-07-22 23:14:14', 'uploads/customers/customer_68805a8e4326f.jpg', 2, '2025-07-24 11:08:22', NULL, NULL),
(5, 'Haru Liam', 'haruliam@gmail.com', '+95925902325', 'USA', '$2y$10$RjEctWs02fFcF2biZUQkUu5hTWZxe2TMR3VqV/Pnrj8dWrYUi3Zdy', '2025-07-24 00:09:18', 'uploads/customers/customer_6881b8f6c1288.png', 3, '2025-08-17 15:46:28', '4582ce40ad5b942c46d169705851cc7075f5b50e18ef83001f8e4caca2034f50', '2025-07-31 06:39:35'),
(6, 'Charlotte', 'charlotte@gmail.com', '+95925902327', 'New York', '$2y$10$grNM4Ze0CPmux0nWlTTcoeX/L56aw4C0oEpLJ8Q/9xyFXWOIkqSHW', '2025-08-17 05:10:13', 'uploads/customers/customer_68a1a37d9b1e3.jpg', 0, NULL, NULL, NULL),
(7, 'Tin Tin', 'tinny24@gmail.com', '+95925902335', 'USA', '$2y$10$mh8udxiXscaF1sK85U8dY.CZ3WEodbm5OGcXVB1M1/D0cBbExuc96', '2025-08-17 09:35:50', 'uploads/customers/customer_68a1e6fb590a5.jpg', 0, NULL, '27bf30212d57cb40b3bfd4a416f9e3b1a33741873ccda4a237be69bb6e670c8e', '2025-08-24 16:30:24'),
(8, 'Tin', 'tin@gmail.com', '+959954925419', '109X110 69B', '$2y$10$XbsV4PvLiCoUKrfUvnN9suYNrGR4f6NFeRtGQutGH7at28w/j/S9.', '2025-08-18 01:27:44', 'uploads/customers/customer_68a54706d179b.jpg', 0, NULL, 'ab944650578ed1678e4147e0acf40b09326d8575823bd377d2ff10a377156dbc', '2025-08-29 06:44:19');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `feedback_text` text DEFAULT NULL,
  `feedback_rating` int(11) DEFAULT NULL CHECK (`feedback_rating` between 1 and 5),
  `feedback_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `customer_id`, `order_id`, `product_id`, `feedback_text`, `feedback_rating`, `feedback_date`) VALUES
(1, 4, 0, 15, 'Absolutely loved this basket! The daisies were fresh, vibrant, and arranged so beautifully. It brought a huge smile to my mom’s face on her birthday. Will definitely order again!', 5, '2025-07-23 18:35:36'),
(2, 4, 0, 14, 'The bouquet arrived in great condition and the flowers looked just like the picture. I would love the option to add a few more accent flowers for variety, but overall it was lovely.', 4, '2025-07-23 18:36:16'),
(3, 2, 0, 9, 'Love this creation', 5, '2025-08-10 19:40:47'),
(4, 2, 0, 15, '', 5, '2025-08-14 16:53:11'),
(5, 2, 0, 8, 'Love it', 4, '2025-08-14 16:53:27'),
(6, 6, 0, 16, 'Really love this.', 5, '2025-08-17 12:44:27'),
(7, 7, 0, 17, 'Love it.', 5, '2025-08-17 14:18:53'),
(8, 7, 0, 19, 'Love it..', 5, '2025-08-17 14:19:43'),
(9, 8, 0, 19, '', 2, '2025-08-19 09:23:17'),
(10, 8, 0, 16, 'love this', 5, '2025-08-19 16:42:17'),
(11, 8, 0, 17, 'love it, it so beautiful', 5, '2025-08-20 03:51:10'),
(12, 8, 0, 11, 'Love this', 5, '2025-08-22 05:01:18'),
(13, 2, 0, 19, 'Love it this creation.', 4, '2025-08-26 13:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `flower_type`
--

CREATE TABLE `flower_type` (
  `flower_type_id` int(11) NOT NULL,
  `flower_name` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flower_type`
--

INSERT INTO `flower_type` (`flower_type_id`, `flower_name`, `image_url`) VALUES
(13, 'Tulip', '686bbb1f5e41d.jpg'),
(14, 'Rose', '686be54aa52b1.jpg'),
(15, 'Lily', '6871518a6b369.jpg'),
(16, 'Gardenia', '68a1d590d39e8.jpg'),
(17, 'Sunflower', '68a1d560eb505.jpg'),
(18, 'Daisy', '68a1d57d003ab.jpg'),
(19, 'Lavender', '689e1ce301920.jpg'),
(20, 'Orchid', '68a1d22d9eaeb.png'),
(21, 'Peony', '68a1d3427282c.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `occasions`
--

CREATE TABLE `occasions` (
  `occasion_id` int(11) NOT NULL,
  `occasion_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `occasions`
--

INSERT INTO `occasions` (`occasion_id`, `occasion_name`) VALUES
(1, 'Birthday'),
(2, 'Wedding'),
(3, 'Farewell'),
(4, 'Graduation'),
(5, 'Anniversary'),
(6, 'Congratulations'),
(7, 'Get Well Soon'),
(8, 'Valentine Day'),
(9, 'Mother Day'),
(10, 'Father Day'),
(11, 'Housewarming');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL,
  `customer_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `customer_id`, `order_date`, `order_status`, `total_amount`, `customer_note`) VALUES
(3, 2, '2025-07-07 17:46:04', 'Accepted', 45.00, NULL),
(4, 2, '2025-07-07 19:36:51', 'Accepted', 80.50, NULL),
(9, 4, '2025-07-23 18:13:02', 'Accepted', 65.00, ''),
(10, 4, '2025-07-24 03:50:52', 'Accepted', 200.00, ''),
(24, 2, '2025-08-10 18:08:19', 'Accepted', 4.25, ''),
(25, 2, '2025-08-10 19:39:07', 'Accepted', 24018.00, 'pick up at 5pm'),
(26, 6, '2025-08-17 09:43:22', 'Accepted', 63000.00, 'Deliver at 12 pm'),
(27, 7, '2025-08-17 14:13:49', 'Accepted', 125000.00, 'Deliver at 1 pm'),
(28, 8, '2025-08-19 09:04:52', 'Accepted', 500000.00, 'for my sweet'),
(29, 8, '2025-08-19 13:36:28', 'Accepted', 73000.00, ''),
(30, 8, '2025-08-19 16:12:25', 'Accepted', 15000.00, 'dd'),
(31, 8, '2025-08-19 16:14:40', 'Accepted', 45000.00, 'lala'),
(32, 8, '2025-08-20 03:34:02', 'Rejected', 348000.00, ''),
(33, 8, '2025-08-20 03:42:54', 'Accepted', 45000.00, 'For Ruby'),
(34, 8, '2025-08-20 03:49:56', 'Accepted', 95000.00, 'for juno'),
(35, 8, '2025-08-20 03:53:13', 'Accepted', 45000.00, ''),
(36, 8, '2025-08-20 04:11:50', 'Accepted', 45000.00, ''),
(37, 8, '2025-08-22 05:02:41', 'Accepted', 39000.00, ''),
(38, 2, '2025-08-26 12:58:16', 'Accepted', 70000.00, ''),
(39, 2, '2025-08-26 12:59:59', 'Accepted', 115000.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `orders_item`
--

CREATE TABLE `orders_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `aid` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `sub_price` decimal(10,2) NOT NULL,
  `item_type` enum('product','accessory') NOT NULL DEFAULT 'product'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_item`
--

INSERT INTO `orders_item` (`order_item_id`, `order_id`, `product_id`, `aid`, `quantity`, `sub_price`, `item_type`) VALUES
(1, 3, 9, NULL, 1, 45.00, 'product'),
(2, 4, 9, NULL, 1, 45.00, 'product'),
(3, 4, 8, NULL, 1, 35.50, 'product'),
(13, 9, 15, NULL, 1, 40.00, 'product'),
(14, 9, 14, NULL, 1, 25.00, 'product'),
(15, 10, 13, NULL, 5, 175.00, 'product'),
(16, 10, 14, NULL, 1, 25.00, 'product'),
(26, 24, NULL, 3, 1, 0.75, 'accessory'),
(27, 24, NULL, 4, 1, 3.50, 'accessory'),
(28, 25, 15, NULL, 2, 24000.00, 'product'),
(29, 25, NULL, 1, 3, 7.50, 'accessory'),
(30, 25, NULL, 4, 3, 10.50, 'accessory'),
(31, 26, 16, NULL, 1, 45000.00, 'product'),
(32, 26, NULL, 4, 1, 15000.00, 'accessory'),
(33, 26, NULL, 3, 1, 3000.00, 'accessory'),
(34, 27, 19, NULL, 1, 70000.00, 'product'),
(35, 27, 17, NULL, 1, 45000.00, 'product'),
(36, 27, NULL, 6, 1, 10000.00, 'accessory'),
(37, 28, NULL, 4, 4, 60000.00, 'accessory'),
(38, 28, 19, NULL, 5, 350000.00, 'product'),
(39, 28, 17, NULL, 2, 90000.00, 'product'),
(40, 29, 19, NULL, 1, 70000.00, 'product'),
(41, 29, NULL, 3, 1, 3000.00, 'accessory'),
(42, 30, NULL, 4, 1, 15000.00, 'accessory'),
(43, 31, 16, NULL, 1, 45000.00, 'product'),
(44, 32, 14, NULL, 3, 300000.00, 'product'),
(45, 32, 10, NULL, 2, 48000.00, 'product'),
(46, 33, NULL, 3, 5, 15000.00, 'accessory'),
(47, 33, NULL, 5, 2, 30000.00, 'accessory'),
(48, 34, 11, NULL, 1, 25000.00, 'product'),
(49, 34, 19, NULL, 1, 70000.00, 'product'),
(50, 35, 17, NULL, 1, 45000.00, 'product'),
(51, 36, 16, NULL, 1, 45000.00, 'product'),
(52, 37, 10, NULL, 1, 24000.00, 'product'),
(53, 37, 12, NULL, 1, 15000.00, 'product'),
(54, 38, 19, NULL, 1, 70000.00, 'product'),
(55, 39, 19, NULL, 1, 70000.00, 'product'),
(56, 39, 17, NULL, 1, 45000.00, 'product');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_method_id`, `payment_date`, `payment_img`) VALUES
(1, 3, 1, '2025-07-07 17:46:04', 'pay_686c07dcbad56.jpg'),
(2, 4, 1, '2025-07-07 19:36:51', 'pay_686c21d3a09da.jpg'),
(3, 9, 1, '2025-07-23 18:13:02', 'pay_6881262e168a6.jpg'),
(4, 10, 1, '2025-07-24 03:50:52', 'pay_6881ad9c98844.jpg'),
(7, 24, 1, '2025-08-10 18:08:19', 'proof_6898e013a9a127.12813940.png'),
(8, 25, 1, '2025-08-10 19:39:07', 'proof_6898f55be93654.27123194.png'),
(9, 26, 1, '2025-08-17 09:43:22', 'proof_68a1a43aa7baf2.08655131.jpg'),
(10, 27, 1, '2025-08-17 14:13:49', 'proof_68a1e39d206773.08143358.jpg'),
(11, 28, 2, '2025-08-19 09:04:52', 'proof_68a43e343d27a3.15653144.svg'),
(12, 29, 1, '2025-08-19 13:36:28', 'proof_68a47ddc940728.03184427.jpg'),
(13, 30, 2, '2025-08-19 16:12:25', 'proof_68a4a26953bcb2.26728333.jpg'),
(14, 31, 1, '2025-08-19 16:14:40', 'proof_68a4a2f000e959.42521061.jpg'),
(15, 32, 1, '2025-08-20 03:34:02', 'proof_68a5422acd55c0.02480989.png'),
(16, 33, 1, '2025-08-20 03:42:54', 'proof_68a5443e4614d9.10723584.jpg'),
(17, 34, 1, '2025-08-20 03:49:56', 'proof_68a545e4a95fa5.54963865.jpg'),
(18, 35, 1, '2025-08-20 03:53:13', 'proof_68a546a9751f80.33928365.jpg'),
(19, 36, 1, '2025-08-20 04:11:50', 'proof_68a54b0614b835.67103920.jpg'),
(20, 37, 1, '2025-08-22 05:02:41', 'proof_68a7f9f1966c37.91835682.jpg'),
(21, 38, 1, '2025-08-26 12:58:16', 'proof_68adaf68d6f904.02737384.jpg'),
(22, 39, 1, '2025-08-26 12:59:59', 'proof_68adafcfc33572.67227035.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `payment_method`
--

CREATE TABLE `payment_method` (
  `payment_method_id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `holder_name` varchar(255) NOT NULL,
  `ph_no` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_method`
--

INSERT INTO `payment_method` (`payment_method_id`, `method_name`, `holder_name`, `ph_no`) VALUES
(1, 'KBZPay', 'Aung Aung', '09987654321'),
(2, 'WavePay', 'Hla Hla', '09765432100');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `arrangement_id` int(11) NOT NULL,
  `size` enum('Small','Medium','Large') NOT NULL DEFAULT 'Small',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `product_description`, `product_price`, `arrangement_id`, `size`, `date`, `is_active`) VALUES
(8, 'Tulip Dream Arrangement', '🌷 Tulip Dream Bouquet 🌷\r\nStep into a world of color and charm with our Tulip Dream Arrangement Bouquet — a graceful blend of fresh tulips that captures the essence of springtime magic. Each tulip, with its sleek silhouette and delicate bloom, is hand-arranged to create a bouquet that feels light, romantic, and refreshingly modern. Perfect for birthdays, new beginnings, or simply brightening someone’s day, this bouquet is a dreamy gift for every moment.\r\n\r\n✨ Features:\r\n\r\nA vibrant mix of handpicked tulips in soft or bold tones\r\n\r\nElegantly wrapped with ribbon and luxe floral paper\r\n\r\nIdeal for celebrations, thank-yous, or romantic surprises\r\n\r\nA symbol of hope, happiness, and love in bloom', 35000.00, 3, 'Small', '2025-07-07 13:40:05', 1),
(9, 'Elegant Rose Box', '🌹 Elegant Rose Box 🌹\r\nExperience the art of gifting with our Elegant Rose Box — a stunning fusion of modern luxury and classic romance. Each rose is carefully handpicked for its flawless beauty and arranged in a sleek, designer box that speaks volumes without saying a word. Perfect for anniversaries, proposals, or elegant surprises, this rose box is more than a gift — it’s a statement of love and sophistication.\r\n\r\n✨ Features:\r\n\r\nPremium fresh roses arranged in a chic, stylish box\r\n\r\nAvailable in classic red, soft pink, ivory white, or custom colors\r\n\r\nIdeal for romantic gestures, birthdays, or luxury gifting\r\n\r\nA graceful expression of elegance, love, and class', 45000.00, 5, 'Medium', '2025-07-07 15:21:01', 1),
(10, 'Elegant White Lilies', '🤍 Elegant White Lilies 🤍\r\nPure, graceful, and effortlessly stunning — our Elegant White Lilies arrangement is a symbol of serenity and refined beauty. With their soft petals and subtle fragrance, these lilies bring a touch of peace and sophistication to any space. Whether you’re expressing sympathy, celebrating a new beginning, or simply sending love, this timeless floral piece speaks gently and meaningfully.\r\n\r\n✨ Features:\r\n\r\nFresh, hand-selected white lilies\r\n\r\nArranged with lush greens for a natural, elegant look\r\n\r\nIdeal for sympathy, weddings, or minimalist floral gifts\r\n\r\nA symbol of purity, renewal, and grace', 24000.00, 3, 'Medium', '2025-07-11 18:02:39', 1),
(11, 'Elegant Rose Basket', '🌹 Elegant Rose Basket 🌹\r\nTimeless beauty meets graceful presentation in our Elegant Rose Basket. Overflowing with hand-selected, velvety roses in full bloom, this exquisite arrangement is the epitome of love, romance, and sophistication. Artfully arranged in a classic basket, each rose speaks a language of passion and elegance — perfect for anniversaries, grand gestures, or unforgettable moments.\r\n\r\n✨ Features:\r\n\r\nPremium fresh roses in rich, romantic hues\r\n\r\nArranged in a chic woven basket with elegant accents\r\n\r\nA luxurious gift for weddings, anniversaries, or celebrations\r\n\r\nCaptures the essence of love, beauty, and refinement', 25000.00, 4, 'Medium', '2025-07-11 18:03:38', 1),
(12, 'Elegant Rose Bouquet', '🌹 Elegant Rose Bouquet 🌹\r\nA timeless expression of love and sophistication, our Elegant Rose Bouquet is designed to leave a lasting impression. Featuring perfectly bloomed, hand-selected roses wrapped in luxurious packaging, this bouquet embodies grace, passion, and elegance in every stem. Whether it\'s for a romantic gesture, a heartfelt celebration, or simply to say \"I care,\" this classic arrangement is always the right choice.\r\n\r\n✨ Features:\r\n\r\nPremium, fresh-cut roses in your choice of color\r\n\r\nWrapped in stylish, high-quality paper with ribbon detail\r\n\r\nIdeal for romantic occasions, anniversaries, or heartfelt gifts\r\n\r\nA true symbol of beauty, love, and refined emotion', 15000.00, 3, 'Medium', '2025-07-11 18:04:18', 1),
(13, '🌸 Elegant Gardenia Bloom Box 🌸', 'Indulge in timeless elegance with our Gardenia Box Arrangement — a delicate harmony of pure white blooms and lush greenery, beautifully nestled in a luxurious gift box. Each gardenia flower is carefully selected for its rich fragrance and graceful charm, creating a serene and romantic centerpiece perfect for any occasion. Whether you\'re celebrating love, sending a thoughtful gift, or brightening up your space, this enchanting arrangement brings sophistication and beauty in every petal.\r\n\r\n✨ Features:\r\n\r\nFresh, fragrant gardenias\r\n\r\nHand-arranged in a premium box\r\n\r\nIdeal for weddings, anniversaries, or heartfelt gestures\r\n\r\nReady to impress with timeless beauty and grace', 500000.00, 5, 'Small', '2025-07-23 17:39:57', 1),
(14, '🌻 Radiant Sunflower Bouquet 🌻', 'Bring a burst of sunshine to any moment with our Sunflower Bouquet — a vibrant arrangement of golden blooms that radiate happiness, warmth, and positivity. Each sunflower is handpicked for its bold beauty and lively energy, creating a stunning bouquet that’s impossible to ignore. Perfect for birthdays, congratulations, or simply to brighten someone\'s day, this joyful bouquet is a symbol of optimism and light.\r\n\r\n✨ Features:\r\n\r\nFresh, vibrant sunflowers with lush greenery\r\n\r\nWrapped in elegant, eco-friendly packaging\r\n\r\nA cheerful gift for any occasion\r\n\r\nBrightens spaces and hearts alike', 100000.00, 3, 'Medium', '2025-07-23 17:42:23', 1),
(15, '🌼 Delightful Daisy Basket 🌼', 'Fresh, cheerful, and full of charm — our Daisy Basket Arrangement is a sweet celebration of nature’s simple beauty. Hand-arranged with love, this basket overflows with bright daisies, bringing warmth and whimsy to any space. Whether you\'re sending well wishes, saying thank you, or just spreading a little sunshine, this rustic floral basket delivers smiles every time.\r\n\r\n✨ Features:\r\n\r\nA generous mix of fresh, blooming daisies\r\n\r\nArranged in a natural woven basket for a rustic touch\r\n\r\nIdeal for home décor, casual gifting, or thoughtful surprises\r\n\r\nA symbol of innocence, purity, and joy', 12000.00, 4, 'Small', '2025-07-23 17:44:15', 1),
(16, 'Lavender Bouquet', 'A timeless arrangement of fresh lavender stems, hand-tied to bring natural beauty and a soothing aroma to any space. Its delicate purple blooms and calming fragrance make it perfect for gifting, home décor, or creating a relaxing atmosphere. Ideal for birthdays, anniversaries, or simply to brighten someone’s day.\r\n\r\nFragrance: Soft, calming, and floral\r\n\r\nStyle: Rustic yet elegant\r\n\r\nPerfect for: Relaxation, home décor, and thoughtful gifts', 45000.00, 3, 'Medium', '2025-08-14 17:31:03', 1),
(17, 'Velour Orchid Bouquet', 'A masterpiece of elegance, the Velour Orchid Bouquet blends the soft, velvety charm of orchids with timeless sophistication. Each bloom is handpicked for its graceful beauty and arranged to create a bouquet that feels both luxurious and heartfelt. Perfect for celebrating love, milestones, or simply adding a touch of refined beauty to any space.\r\n\r\n✨ Best for: Anniversaries, weddings, or as a romantic gift\r\n🌸 Features: Fresh-cut premium orchids, soft velour-like petals, carefully hand-tied presentation', 45000.00, 3, 'Medium', '2025-08-17 13:01:54', 1),
(19, 'Peony Bouquet 🌸', 'Soft, lush, and undeniably romantic — the Peony Bouquet is a symbol of love, prosperity, and beauty. With its full, delicate petals and gentle fragrance, each bloom feels like a whisper of elegance. Handcrafted with care, this bouquet makes the perfect gift for weddings, anniversaries, or simply to brighten someone’s day with timeless charm.\r\n\r\n✨ Best for: Romantic gestures, bridal gifts, or heartfelt celebrations\r\n🌸 Features: Fresh, seasonal peonies in full bloom, hand-tied with graceful detailing', 70000.00, 3, 'Medium', '2025-08-17 13:05:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_flower_type`
--

CREATE TABLE `product_flower_type` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `flower_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_flower_type`
--

INSERT INTO `product_flower_type` (`id`, `product_id`, `flower_type_id`) VALUES
(22, 12, 14),
(23, 9, 14),
(24, 10, 15),
(25, 8, 13),
(26, 15, 18),
(27, 13, 16),
(28, 14, 17),
(30, 11, 14),
(31, 17, 20),
(33, 19, 21),
(35, 16, 19);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `date_uploaded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `date_uploaded`) VALUES
(5, 9, 'img_6871514069088.jpg', '2025-07-11 18:00:32'),
(6, 8, 'img_68715151e2f98.jpg', '2025-07-11 18:00:49'),
(7, 10, '687151bfe5322.jpg', '2025-07-11 18:02:39'),
(8, 10, '687151bfe5a30.jpg', '2025-07-11 18:02:39'),
(9, 11, '687151fac17c4.jpg', '2025-07-11 18:03:38'),
(10, 12, '68715222cc848.png', '2025-07-11 18:04:18'),
(14, 15, 'img_6892f6c80e422.jpg', '2025-08-06 06:31:36'),
(16, 13, 'img_6892f7850d15e.jpg', '2025-08-06 06:34:45'),
(17, 14, 'img_6892f86e454e3.jpg', '2025-08-06 06:38:38'),
(18, 16, '689e1d57707a0.jpg', '2025-08-14 17:31:03'),
(19, 17, '68a1d2c23900c.jpg', '2025-08-17 13:01:54'),
(21, 19, '68a1d39f1e2b8.jpg', '2025-08-17 13:05:35');

-- --------------------------------------------------------

--
-- Table structure for table `product_occasions`
--

CREATE TABLE `product_occasions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `occasion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_occasions`
--

INSERT INTO `product_occasions` (`id`, `product_id`, `occasion_id`) VALUES
(75, 12, 5),
(76, 12, 1),
(77, 12, 8),
(78, 9, 5),
(79, 9, 2),
(80, 10, 6),
(81, 10, 10),
(82, 10, 7),
(83, 10, 11),
(84, 10, 9),
(85, 8, 1),
(86, 8, 7),
(87, 8, 9),
(88, 15, 6),
(89, 15, 7),
(90, 15, 9),
(91, 13, 1),
(92, 13, 3),
(93, 13, 8),
(94, 13, 2),
(95, 14, 10),
(96, 14, 7),
(97, 14, 9),
(101, 11, 6),
(102, 11, 7),
(103, 11, 11),
(104, 11, 9),
(105, 17, 1),
(106, 17, 2),
(107, 17, 3),
(108, 17, 4),
(109, 17, 6),
(110, 17, 7),
(111, 17, 8),
(112, 17, 9),
(113, 17, 10),
(123, 19, 1),
(124, 19, 2),
(125, 19, 3),
(126, 19, 4),
(127, 19, 5),
(128, 19, 6),
(129, 19, 7),
(130, 19, 8),
(131, 19, 9),
(132, 19, 10),
(133, 19, 11),
(137, 16, 6),
(138, 16, 7),
(139, 16, 11);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `product_id`, `customer_id`) VALUES
(1, 14, 5),
(2, 14, 2),
(4, 16, 6),
(5, 15, 6),
(9, 16, 7),
(10, 10, 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`about_id`);

--
-- Indexes for table `accessories`
--
ALTER TABLE `accessories`
  ADD PRIMARY KEY (`aid`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `admin_promos`
--
ALTER TABLE `admin_promos`
  ADD PRIMARY KEY (`promo_id`);

--
-- Indexes for table `arrangement_type`
--
ALTER TABLE `arrangement_type`
  ADD PRIMARY KEY (`arrangement_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD UNIQUE KEY `unique_feedback` (`customer_id`,`order_id`,`product_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `flower_type`
--
ALTER TABLE `flower_type`
  ADD PRIMARY KEY (`flower_type_id`);

--
-- Indexes for table `occasions`
--
ALTER TABLE `occasions`
  ADD PRIMARY KEY (`occasion_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `orders_item`
--
ALTER TABLE `orders_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`payment_method_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `arrangement_id` (`arrangement_id`);

--
-- Indexes for table `product_flower_type`
--
ALTER TABLE `product_flower_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `flower_type_id` (`flower_type_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_occasions`
--
ALTER TABLE `product_occasions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `occasion_id` (`occasion_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_us`
--
ALTER TABLE `about_us`
  MODIFY `about_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accessories`
--
ALTER TABLE `accessories`
  MODIFY `aid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_promos`
--
ALTER TABLE `admin_promos`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `arrangement_type`
--
ALTER TABLE `arrangement_type`
  MODIFY `arrangement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `flower_type`
--
ALTER TABLE `flower_type`
  MODIFY `flower_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `occasions`
--
ALTER TABLE `occasions`
  MODIFY `occasion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `orders_item`
--
ALTER TABLE `orders_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payment_method`
--
ALTER TABLE `payment_method`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `product_flower_type`
--
ALTER TABLE `product_flower_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_occasions`
--
ALTER TABLE `product_occasions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `orders_item`
--
ALTER TABLE `orders_item`
  ADD CONSTRAINT `orders_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`payment_method_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`arrangement_id`) REFERENCES `arrangement_type` (`arrangement_id`);

--
-- Constraints for table `product_flower_type`
--
ALTER TABLE `product_flower_type`
  ADD CONSTRAINT `product_flower_type_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `product_flower_type_ibfk_2` FOREIGN KEY (`flower_type_id`) REFERENCES `flower_type` (`flower_type_id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_occasions`
--
ALTER TABLE `product_occasions`
  ADD CONSTRAINT `product_occasions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `product_occasions_ibfk_2` FOREIGN KEY (`occasion_id`) REFERENCES `occasions` (`occasion_id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
