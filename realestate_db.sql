-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2026 at 09:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `realestate_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `loggable_type` varchar(255) NOT NULL,
  `loggable_id` bigint(20) UNSIGNED NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `client_id`, `action`, `loggable_type`, `loggable_id`, `old_values`, `new_values`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'create', 'App\\Models\\Client', 1, NULL, '{\"client_id\":\"CL-2026-0001\",\"salutation\":\"Mr.\",\"full_name\":\"Asif iqbal\",\"father_husband_salutation\":\"S\\/O\",\"father_husband_name\":\"Muhammad Iqbal\",\"cnic\":\"42401-7562774-5\",\"phone\":\"0345-6900705\",\"residential_address\":\"kohinoor apartement 1234 gulshan e iqbal\",\"vendor_type\":\"default\",\"vendor_name\":null,\"vendor_cnic\":null,\"google_drive_folder_id\":\"mock-drive-folder-6a0e25620024e\",\"status\":\"active\",\"created_by\":1,\"updated_at\":\"2026-05-20T21:19:30.000000Z\",\"created_at\":\"2026-05-20T21:19:30.000000Z\",\"id\":1}', '2026-05-20 16:19:30', '2026-05-20 16:19:30'),
(2, 1, 1, 'create', 'App\\Models\\Payment', 1, NULL, '{\"client_id\":1,\"property_id\":1,\"payment_number\":1,\"amount\":10,\"payment_method\":\"CASH\",\"particulars\":\"Token \\/ Advance Payment\",\"bank_name\":null,\"cheque_number\":null,\"payment_date\":\"2026-05-20\",\"created_by\":1,\"updated_at\":\"2026-05-20T21:19:30.000000Z\",\"created_at\":\"2026-05-20T21:19:30.000000Z\",\"id\":1}', '2026-05-20 16:19:30', '2026-05-20 16:19:30'),
(3, 1, 1, 'delete', 'App\\Models\\Payment', 1, '{\"id\":1,\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":1,\"amount\":10,\"payment_method\":\"CASH\",\"particulars\":\"Token \\/ Advance Payment\",\"bank_name\":null,\"cheque_number\":null,\"payment_date\":\"2026-05-20\",\"receipt_id\":1,\"synced_to_sheet\":0,\"created_by\":1,\"created_at\":\"2026-05-20T21:19:30.000000Z\",\"updated_at\":\"2026-05-20T21:19:30.000000Z\",\"delete_reason\":\"Payment deleted\\/reversed. Reason: by mistake\"}', NULL, '2026-06-02 14:56:18', '2026-06-02 14:56:18'),
(4, 1, 1, 'create', 'App\\Models\\Payment', 2, NULL, '{\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":1,\"amount\":\"20\",\"payment_method\":\"CASH\",\"particulars\":\"Through Cash\",\"bank_name\":null,\"cheque_number\":null,\"payment_date\":\"2026-06-02\",\"created_by\":1,\"updated_at\":\"2026-06-02T20:02:22.000000Z\",\"created_at\":\"2026-06-02T20:02:22.000000Z\",\"id\":2}', '2026-06-02 15:02:22', '2026-06-02 15:02:22'),
(5, 1, 1, 'create', 'App\\Models\\Payment', 3, NULL, '{\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":2,\"amount\":\"9\",\"payment_method\":\"CHEQUE\",\"particulars\":\"Paid through Cheque No: A-3456788765\",\"bank_name\":\"Meezan Bank Limited (MBL)\",\"cheque_number\":\"A-3456788765\",\"payment_date\":\"2026-06-02\",\"created_by\":1,\"updated_at\":\"2026-06-02T20:13:31.000000Z\",\"created_at\":\"2026-06-02T20:13:31.000000Z\",\"id\":3}', '2026-06-02 15:13:31', '2026-06-02 15:13:31'),
(6, 1, 1, 'create', 'App\\Models\\Payment', 4, NULL, '{\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":3,\"amount\":\"10\",\"payment_method\":\"BANK_TRANSFER\",\"particulars\":\"Online Banking \\/ Bank Transfer\",\"bank_name\":\"Meezan Bank Limited (MBL)\",\"cheque_number\":\"A-3456788765\",\"payment_date\":\"2026-06-02\",\"created_by\":1,\"updated_at\":\"2026-06-02T20:25:18.000000Z\",\"created_at\":\"2026-06-02T20:25:18.000000Z\",\"id\":4}', '2026-06-02 15:25:18', '2026-06-02 15:25:18'),
(7, 1, 1, 'create', 'App\\Models\\Payment', 5, NULL, '{\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":4,\"amount\":\"10\",\"payment_method\":\"BANK_TRANSFER\",\"particulars\":\"Online Banking \\/ Bank Transfer\",\"bank_name\":\"Habib Bank Limited (HBL)\",\"cheque_number\":null,\"payment_date\":\"2026-06-02\",\"created_by\":1,\"updated_at\":\"2026-06-02T20:43:55.000000Z\",\"created_at\":\"2026-06-02T20:43:55.000000Z\",\"id\":5}', '2026-06-02 15:43:55', '2026-06-02 15:43:55'),
(8, 1, 1, 'create', 'App\\Models\\Payment', 6, NULL, '{\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":5,\"amount\":\"51\",\"payment_method\":\"CASH\",\"particulars\":\"Through Cash\",\"bank_name\":null,\"cheque_number\":null,\"payment_date\":\"2026-06-02\",\"created_by\":1,\"updated_at\":\"2026-06-02T20:45:17.000000Z\",\"created_at\":\"2026-06-02T20:45:17.000000Z\",\"id\":6}', '2026-06-02 15:45:17', '2026-06-02 15:45:17'),
(9, 1, 1, 'delete', 'App\\Models\\Payment', 6, '{\"id\":6,\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":5,\"amount\":51,\"payment_method\":\"CASH\",\"particulars\":\"Through Cash\",\"bank_name\":null,\"cheque_number\":null,\"payment_date\":\"2026-06-02\",\"receipt_id\":6,\"synced_to_sheet\":0,\"created_by\":1,\"created_at\":\"2026-06-02T20:45:17.000000Z\",\"updated_at\":\"2026-06-02T20:45:17.000000Z\",\"delete_reason\":\"Payment deleted\\/reversed. Reason: test\"}', NULL, '2026-06-02 15:51:08', '2026-06-02 15:51:08'),
(10, 1, 1, 'create', 'App\\Models\\Payment', 7, NULL, '{\"client_id\":1,\"property_id\":1,\"installment_id\":null,\"payment_number\":5,\"amount\":\"11\",\"payment_method\":\"CASH\",\"particulars\":\"Through Cash\",\"bank_name\":null,\"cheque_number\":null,\"payment_date\":\"2026-06-02\",\"created_by\":1,\"updated_at\":\"2026-06-02T21:04:09.000000Z\",\"created_at\":\"2026-06-02T21:04:09.000000Z\",\"id\":7}', '2026-06-02 16:04:09', '2026-06-02 16:04:09'),
(11, 1, 2, 'create', 'App\\Models\\Client', 2, NULL, '{\"client_id\":\"CL-2026-0002\",\"salutation\":\"Mr.\",\"full_name\":\"yasir\",\"father_husband_salutation\":\"S\\/O\",\"father_husband_name\":\"Muhammad Iqbal\",\"cnic\":\"42401-7562774-6\",\"phone\":\"0345-6900706\",\"residential_address\":\"Powerhouse north karachi\",\"vendor_type\":\"default\",\"vendor_name\":null,\"vendor_cnic\":null,\"google_drive_folder_id\":\"mock-drive-folder-6a1f4aba283b3\",\"status\":\"active\",\"created_by\":1,\"updated_at\":\"2026-06-02T21:27:22.000000Z\",\"created_at\":\"2026-06-02T21:27:22.000000Z\",\"id\":2}', '2026-06-02 16:27:22', '2026-06-02 16:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` varchar(20) NOT NULL,
  `salutation` enum('Mr.','Mrs.','Ms.','Dr.','Eng.') NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `father_husband_salutation` enum('S/O','D/O','W/O') NOT NULL,
  `father_husband_name` varchar(150) NOT NULL,
  `cnic` varchar(15) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `residential_address` text NOT NULL,
  `google_drive_folder_id` varchar(100) DEFAULT NULL,
  `google_sheet_row` int(11) DEFAULT NULL,
  `status` enum('active','inactive','completed') NOT NULL DEFAULT 'active',
  `vendor_type` enum('default','custom') NOT NULL DEFAULT 'default',
  `vendor_name` varchar(150) DEFAULT NULL,
  `vendor_cnic` varchar(20) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_id`, `salutation`, `full_name`, `father_husband_salutation`, `father_husband_name`, `cnic`, `phone`, `residential_address`, `google_drive_folder_id`, `google_sheet_row`, `status`, `vendor_type`, `vendor_name`, `vendor_cnic`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CL-2026-0001', 'Mr.', 'Asif iqbal', 'S/O', 'Muhammad Iqbal', '42401-7562774-5', '0345-6900705', 'kohinoor apartement 1234 gulshan e iqbal', 'mock-drive-folder-6a0e25620024e', NULL, 'active', 'default', NULL, NULL, 1, '2026-05-20 16:19:30', '2026-05-20 16:19:30', NULL),
(2, 'CL-2026-0002', 'Mr.', 'yasir', 'S/O', 'Muhammad Iqbal', '42401-7562774-6', '0345-6900706', 'Powerhouse north karachi', 'mock-drive-folder-6a1f4aba283b3', NULL, 'active', 'default', NULL, NULL, 1, '2026-06-02 16:27:22', '2026-06-02 16:27:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` enum('agreement','cnic','other') NOT NULL,
  `original_filename` varchar(200) NOT NULL,
  `google_drive_file_id` varchar(100) DEFAULT NULL,
  `google_drive_file_url` text DEFAULT NULL,
  `google_drive_folder_id` varchar(100) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installments`
--

CREATE TABLE `installments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `original_amount` decimal(15,2) DEFAULT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `installments`
--

INSERT INTO `installments` (`id`, `client_id`, `property_id`, `installment_number`, `amount`, `original_amount`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 0, 15.00, '2026-06-20', 'paid', '2026-05-20 16:19:32', '2026-06-02 15:57:57'),
(2, 1, 1, 2, 0, 15.00, '2026-07-20', 'paid', '2026-05-20 16:19:32', '2026-06-02 15:57:57'),
(3, 1, 1, 3, 0, 15.00, '2026-08-20', 'paid', '2026-05-20 16:19:32', '2026-06-02 16:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"edf9b328-262e-49ed-88fb-8b1b97e340fe\",\"displayName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\UploadToDriveJob\\\":1:{s:10:\\\"\\u0000*\\u0000receipt\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Receipt\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:2:{i:0;s:6:\\\"client\\\";i:1;s:8:\\\"property\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1779311972, 1779311972),
(2, 'default', '{\"uuid\":\"1a866f9b-5936-473a-968a-6fa6db625efa\",\"displayName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\UploadToDriveJob\\\":1:{s:10:\\\"\\u0000*\\u0000receipt\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Receipt\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:2:{i:0;s:6:\\\"client\\\";i:1;s:8:\\\"property\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1780430548, 1780430548),
(3, 'default', '{\"uuid\":\"1a6d60e2-2de5-487c-915a-bf9be5eca0f3\",\"displayName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\UploadToDriveJob\\\":1:{s:10:\\\"\\u0000*\\u0000receipt\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Receipt\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:2:{i:0;s:6:\\\"client\\\";i:1;s:8:\\\"property\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1780431211, 1780431211),
(4, 'default', '{\"uuid\":\"75626bac-f3ba-49e7-85fb-5bb251586aa6\",\"displayName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\UploadToDriveJob\\\":1:{s:10:\\\"\\u0000*\\u0000receipt\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Receipt\\\";s:2:\\\"id\\\";i:4;s:9:\\\"relations\\\";a:2:{i:0;s:6:\\\"client\\\";i:1;s:8:\\\"property\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1780431918, 1780431918),
(5, 'default', '{\"uuid\":\"025fbb9f-18e5-4cd3-b034-50613a4650d3\",\"displayName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\UploadToDriveJob\\\":1:{s:10:\\\"\\u0000*\\u0000receipt\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Receipt\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:2:{i:0;s:6:\\\"client\\\";i:1;s:8:\\\"property\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1780433036, 1780433036),
(6, 'default', '{\"uuid\":\"8ceac1e3-80ce-4006-a992-cd397993a1e3\",\"displayName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\UploadToDriveJob\\\":1:{s:10:\\\"\\u0000*\\u0000receipt\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Receipt\\\";s:2:\\\"id\\\";i:6;s:9:\\\"relations\\\";a:2:{i:0;s:6:\\\"client\\\";i:1;s:8:\\\"property\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1780433117, 1780433117),
(7, 'default', '{\"uuid\":\"bf7b2768-5d05-4d86-9772-e2105db940ca\",\"displayName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\UploadToDriveJob\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\UploadToDriveJob\\\":1:{s:10:\\\"\\u0000*\\u0000receipt\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Receipt\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:2:{i:0;s:6:\\\"client\\\";i:1;s:8:\\\"property\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1780434249, 1780434249),
(8, 'default', '{\"uuid\":\"a85758a7-a2ea-49c7-aec6-2976d7e40a55\",\"displayName\":\"App\\\\Jobs\\\\SyncToGoogleSheetJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":3,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SyncToGoogleSheetJob\",\"command\":\"O:29:\\\"App\\\\Jobs\\\\SyncToGoogleSheetJob\\\":1:{s:9:\\\"\\u0000*\\u0000client\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:17:\\\"App\\\\Models\\\\Client\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\"}}', 0, NULL, 1780435642, 1780435642);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_05_18_071440_create_clients_table', 1),
(6, '2026_05_18_071447_create_properties_table', 1),
(7, '2026_05_18_071448_create_payments_table', 1),
(8, '2026_05_18_071449_create_documents_table', 1),
(9, '2026_05_18_071449_create_receipts_table', 1),
(10, '2026_05_18_071450_create_settings_table', 1),
(11, '2026_05_18_072216_create_permission_tables', 1),
(12, '2026_05_18_072554_create_jobs_table', 1),
(13, '2026_05_18_080500_create_activity_logs_and_add_soft_deletes', 1),
(14, '2026_05_20_000000_add_vendor_fields_to_clients_table', 1),
(15, '2026_05_20_110000_create_installments_table', 1),
(16, '2026_05_21_000000_remove_unique_constraint_from_cnic_on_clients_table', 1),
(17, '2026_05_21_010000_change_payment_method_column_in_payments_table', 1),
(18, '2026_06_02_205302_add_original_amount_to_installments_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `installment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_number` int(11) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `particulars` varchar(200) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `cheque_number` varchar(50) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `receipt_id` bigint(20) UNSIGNED DEFAULT NULL,
  `synced_to_sheet` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `client_id`, `property_id`, `installment_id`, `payment_number`, `amount`, `payment_method`, `particulars`, `bank_name`, `cheque_number`, `payment_date`, `receipt_id`, `synced_to_sheet`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 1, 1, NULL, 1, 20, 'CASH', 'Through Cash', NULL, NULL, '2026-06-02', 2, 0, 1, '2026-06-02 15:02:22', '2026-06-02 15:02:22'),
(3, 1, 1, NULL, 2, 9, 'CHEQUE', 'Paid through Cheque No: A-3456788765', 'Meezan Bank Limited (MBL)', 'A-3456788765', '2026-06-02', 3, 0, 1, '2026-06-02 15:13:31', '2026-06-02 15:13:31'),
(4, 1, 1, NULL, 3, 10, 'BANK_TRANSFER', 'Online Banking / Bank Transfer', 'Meezan Bank Limited (MBL)', 'A-3456788765', '2026-06-02', 4, 0, 1, '2026-06-02 15:25:18', '2026-06-02 15:25:18'),
(5, 1, 1, NULL, 4, 10, 'BANK_TRANSFER', 'Online Banking / Bank Transfer', 'Habib Bank Limited (HBL)', NULL, '2026-06-02', 5, 0, 1, '2026-06-02 15:43:55', '2026-06-02 15:43:55'),
(7, 1, 1, NULL, 5, 11, 'CASH', 'Through Cash', NULL, NULL, '2026-06-02', 7, 0, 1, '2026-06-02 16:04:09', '2026-06-02 16:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage clients', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(2, 'delete clients', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(3, 'manage payments', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(4, 'delete payments', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(5, 'manage installments', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(6, 'delete installments', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(7, 'manage settings', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(8, 'manage users', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41'),
(9, 'view dashboard', 'web', '2026-06-02 16:15:41', '2026-06-02 16:15:41');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `property_type` enum('Residential Plot','Commercial Plot','House','Flat','Shop') NOT NULL,
  `plot_number` varchar(50) NOT NULL,
  `block_name` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `size_sqyards` decimal(10,2) NOT NULL,
  `total_deal_value` bigint(20) NOT NULL,
  `agreement_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `client_id`, `property_type`, `plot_number`, `block_name`, `location`, `size_sqyards`, `total_deal_value`, `agreement_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Residential Plot', 'A-12', 'Block H', 'House 3041 sector 5 north karachi', 120.00, 100, '2026-05-20', NULL, '2026-05-20 16:19:30', '2026-05-20 16:19:30'),
(2, 2, 'Commercial Plot', 'A-12', '5', 'House # L 5055', 120.00, 200, '2026-06-03', NULL, '2026-06-02 16:27:22', '2026-06-02 16:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(30) NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount_this_receipt` bigint(20) NOT NULL,
  `total_received_to_date` bigint(20) NOT NULL,
  `remaining_balance` bigint(20) NOT NULL,
  `receipt_date` date NOT NULL,
  `docx_filename` varchar(200) NOT NULL,
  `google_drive_file_id` varchar(100) DEFAULT NULL,
  `google_drive_file_url` text DEFAULT NULL,
  `generated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `receipt_number`, `client_id`, `property_id`, `total_amount_this_receipt`, `total_received_to_date`, `remaining_balance`, `receipt_date`, `docx_filename`, `google_drive_file_id`, `google_drive_file_url`, `generated_by`, `created_at`, `updated_at`) VALUES
(2, 'RCP-CL20260001-002', 1, 1, 20, 20, 80, '2026-06-02', 'RCP-CL20260001-002_20260602.docx', NULL, NULL, 1, '2026-06-02 15:02:22', '2026-06-02 15:02:22'),
(3, 'RCP-CL20260001-003', 1, 1, 9, 29, 71, '2026-06-02', 'RCP-CL20260001-003_20260602.docx', NULL, NULL, 1, '2026-06-02 15:13:31', '2026-06-02 15:13:31'),
(4, 'RCP-CL20260001-004', 1, 1, 10, 39, 61, '2026-06-02', 'RCP-CL20260001-004_20260602.docx', NULL, NULL, 1, '2026-06-02 15:25:18', '2026-06-02 15:25:18'),
(5, 'RCP-CL20260001-005', 1, 1, 10, 49, 51, '2026-06-02', 'RCP-CL20260001-005_20260602.docx', NULL, NULL, 1, '2026-06-02 15:43:55', '2026-06-02 15:43:55'),
(7, 'RCP-CL20260001-006', 1, 1, 11, 60, 40, '2026-06-02', 'RCP-CL20260001-006_20260602.docx', NULL, NULL, 1, '2026-06-02 16:04:09', '2026-06-02 16:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-05-20 16:16:44', '2026-05-20 16:16:44'),
(2, 'staff', 'web', '2026-05-20 16:16:44', '2026-05-20 16:16:44');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 1),
(3, 2),
(4, 1),
(5, 1),
(5, 2),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(9, 2);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Real Estate Co.', NULL, NULL),
(2, 'company_address', '123 Main St.', NULL, NULL),
(3, 'vendor_name', 'John Doe', NULL, NULL),
(4, 'vendor_cnic', '00000-0000000-0', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@admin.com', NULL, '$2y$12$miO4POMW1riwXZwo2bC1HuDnEP.p94hNc6WQLh1p3C7r2z.IYSGpe', 'super_admin', 1, NULL, '2026-05-20 16:16:45', '2026-05-20 16:17:22'),
(2, 'Asif iqbal', 'admin@asif.com', NULL, '$2y$12$SjBo.iPuwm2Znss2cXAbiOD.xlO/IFW76ZWK9S2ILofIb4IA80JSi', 'staff', 1, NULL, '2026-06-02 16:24:32', '2026-06-02 16:24:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_client_id_unique` (`client_id`),
  ADD KEY `clients_created_by_foreign` (`created_by`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_client_id_foreign` (`client_id`),
  ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `installments`
--
ALTER TABLE `installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `installments_client_id_foreign` (`client_id`),
  ADD KEY `installments_property_id_foreign` (`property_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_client_id_foreign` (`client_id`),
  ADD KEY `payments_property_id_foreign` (`property_id`),
  ADD KEY `payments_created_by_foreign` (`created_by`),
  ADD KEY `payments_installment_id_foreign` (`installment_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `properties_client_id_foreign` (`client_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipts_receipt_number_unique` (`receipt_number`),
  ADD KEY `receipts_client_id_foreign` (`client_id`),
  ADD KEY `receipts_property_id_foreign` (`property_id`),
  ADD KEY `receipts_generated_by_foreign` (`generated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installments`
--
ALTER TABLE `installments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `installments`
--
ALTER TABLE `installments`
  ADD CONSTRAINT `installments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_installment_id_foreign` FOREIGN KEY (`installment_id`) REFERENCES `installments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receipts_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `receipts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
