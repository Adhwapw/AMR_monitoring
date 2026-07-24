-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 24, 2026 at 04:01 AM
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
-- Database: `robot_amr_logging`
--

-- --------------------------------------------------------

--
-- Table structure for table `connection_logs`
--

CREATE TABLE `connection_logs` (
  `id` bigint(20) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `event_type` varchar(30) NOT NULL,
  `message` text DEFAULT NULL,
  `occurred_at` datetime(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `connection_logs`
--

INSERT INTO `connection_logs` (`id`, `robot_id`, `event_type`, `message`, `occurred_at`) VALUES
(3272, 1, 'connected', NULL, '2026-07-23 13:34:41.537'),
(3273, 1, 'connected', NULL, '2026-07-23 13:34:43.611'),
(3274, 1, 'connected', NULL, '2026-07-23 13:34:45.686'),
(3275, 1, 'connected', NULL, '2026-07-23 13:34:47.751'),
(3276, 1, 'connected', NULL, '2026-07-23 13:34:49.834'),
(3277, 1, 'connected', NULL, '2026-07-23 13:34:51.898'),
(3278, 1, 'connected', NULL, '2026-07-23 13:34:53.975'),
(3279, 1, 'connected', NULL, '2026-07-23 13:34:56.029');

-- --------------------------------------------------------

--
-- Table structure for table `data_sessions`
--

CREATE TABLE `data_sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(150) NOT NULL,
  `robot_id` int(11) DEFAULT NULL,
  `floor_condition` varchar(100) DEFAULT NULL,
  `load_note` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `started_at` datetime(3) NOT NULL,
  `ended_at` datetime(3) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `data_sessions`
--

INSERT INTO `data_sessions` (`id`, `session_name`, `robot_id`, `floor_condition`, `load_note`, `notes`, `started_at`, `ended_at`, `created_at`) VALUES
(1, 'dari titik a ke b', 1, 'normal', 'kosong', NULL, '2026-07-22 10:50:31.000', '2026-07-22 11:34:10.000', '2026-07-22 15:50:31'),
(2, 'parking to hall', 1, 'normal', NULL, NULL, '2026-07-22 11:35:23.000', '2026-07-23 03:19:54.000', '2026-07-22 16:35:23');

-- --------------------------------------------------------

--
-- Table structure for table `robots`
--

CREATE TABLE `robots` (
  `id` int(11) NOT NULL,
  `robot_id_str` varchar(100) NOT NULL,
  `vehicle_id` varchar(100) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `version` varchar(30) DEFAULT NULL,
  `dsp_version` varchar(30) DEFAULT NULL,
  `map_version` varchar(30) DEFAULT NULL,
  `current_map` varchar(100) DEFAULT NULL,
  `current_ip` varchar(45) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `extra_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra_info`)),
  `last_info_synced_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `robots`
--

INSERT INTO `robots` (`id`, `robot_id_str`, `vehicle_id`, `model`, `version`, `dsp_version`, `map_version`, `current_map`, `current_ip`, `port`, `is_active`, `extra_info`, `last_info_synced_at`, `created_at`) VALUES
(1, 'robot-1', 'AMR-TEST-01', 'S1', 'v1.1.0', 'v1.2.2', 'v1.0.0', 'map_gudang_a', '127.0.0.1', 19205, 1, NULL, '2026-07-23 13:34:56', '2026-07-21 08:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `robot_control_lock_logs`
--

CREATE TABLE `robot_control_lock_logs` (
  `id` bigint(20) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `logged_at` datetime(3) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `control_ip` varchar(45) DEFAULT NULL,
  `control_port` int(11) DEFAULT NULL,
  `control_type` tinyint(4) DEFAULT NULL,
  `nick_name` varchar(100) DEFAULT NULL,
  `locked_time_t` bigint(20) DEFAULT NULL,
  `desc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `robot_control_lock_logs`
--

INSERT INTO `robot_control_lock_logs` (`id`, `robot_id`, `logged_at`, `locked`, `control_ip`, `control_port`, `control_type`, `nick_name`, `locked_time_t`, `desc`) VALUES
(121, 1, '2026-07-23 13:34:41.543', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(122, 1, '2026-07-23 13:34:43.618', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(123, 1, '2026-07-23 13:34:45.699', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 1, '2026-07-23 13:34:47.764', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 1, '2026-07-23 13:34:49.840', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 1, '2026-07-23 13:34:51.905', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(127, 1, '2026-07-23 13:34:53.982', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 1, '2026-07-23 13:34:56.037', 0, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `robot_imu_logs`
--

CREATE TABLE `robot_imu_logs` (
  `id` bigint(20) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `logged_at` datetime(3) NOT NULL,
  `data_nsec` bigint(20) DEFAULT NULL,
  `pub_nsec` bigint(20) DEFAULT NULL,
  `seq` bigint(20) DEFAULT NULL,
  `yaw` double DEFAULT NULL,
  `roll` double DEFAULT NULL,
  `pitch` double DEFAULT NULL,
  `acc_x` double DEFAULT NULL,
  `acc_y` double DEFAULT NULL,
  `acc_z` double DEFAULT NULL,
  `rot_x` double DEFAULT NULL,
  `rot_y` double DEFAULT NULL,
  `rot_z` double DEFAULT NULL,
  `qx` double DEFAULT NULL,
  `qy` double DEFAULT NULL,
  `qz` double DEFAULT NULL,
  `qw` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `robot_imu_logs`
--

INSERT INTO `robot_imu_logs` (`id`, `robot_id`, `logged_at`, `data_nsec`, `pub_nsec`, `seq`, `yaw`, `roll`, `pitch`, `acc_x`, `acc_y`, `acc_z`, `rot_x`, `rot_y`, `rot_z`, `qx`, `qy`, `qz`, `qw`) VALUES
(121, 1, '2026-07-23 13:34:41.543', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(122, 1, '2026-07-23 13:34:43.618', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(123, 1, '2026-07-23 13:34:45.699', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(124, 1, '2026-07-23 13:34:47.764', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(125, 1, '2026-07-23 13:34:49.840', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(126, 1, '2026-07-23 13:34:51.905', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(127, 1, '2026-07-23 13:34:53.982', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(128, 1, '2026-07-23 13:34:56.037', 16704707855595, 16704707855637, 0, -3.128697633743291, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `robot_motor_logs`
--

CREATE TABLE `robot_motor_logs` (
  `id` bigint(20) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `logged_at` datetime(3) NOT NULL,
  `motor_name` varchar(50) NOT NULL,
  `motor_type` tinyint(4) DEFAULT NULL,
  `can_router` int(11) DEFAULT NULL,
  `can_id` int(11) DEFAULT NULL,
  `position` double DEFAULT NULL,
  `speed` double DEFAULT NULL,
  `current` double DEFAULT NULL,
  `voltage` double DEFAULT NULL,
  `stop` tinyint(1) DEFAULT NULL,
  `error_code` int(11) DEFAULT NULL,
  `err` tinyint(1) DEFAULT NULL,
  `emc` tinyint(1) DEFAULT NULL,
  `temperature` double DEFAULT NULL,
  `encoder` double DEFAULT NULL,
  `passive` tinyint(1) DEFAULT NULL,
  `calib` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `robot_motor_logs`
--

INSERT INTO `robot_motor_logs` (`id`, `robot_id`, `logged_at`, `motor_name`, `motor_type`, `can_router`, `can_id`, `position`, `speed`, `current`, `voltage`, `stop`, `error_code`, `err`, `emc`, `temperature`, `encoder`, `passive`, `calib`) VALUES
(241, 1, '2026-07-23 13:34:41.543', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(242, 1, '2026-07-23 13:34:41.543', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1),
(243, 1, '2026-07-23 13:34:43.618', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(244, 1, '2026-07-23 13:34:43.618', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1),
(245, 1, '2026-07-23 13:34:45.699', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(246, 1, '2026-07-23 13:34:45.699', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1),
(247, 1, '2026-07-23 13:34:47.764', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(248, 1, '2026-07-23 13:34:47.764', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1),
(249, 1, '2026-07-23 13:34:49.840', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(250, 1, '2026-07-23 13:34:49.840', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1),
(251, 1, '2026-07-23 13:34:51.905', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(252, 1, '2026-07-23 13:34:51.905', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1),
(253, 1, '2026-07-23 13:34:53.982', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(254, 1, '2026-07-23 13:34:53.982', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1),
(255, 1, '2026-07-23 13:34:56.037', 'motor_left', 1, 1, 1, 0, 0.42, 1.1, 24, 0, 0, 0, 0, 30, 12345, 0, 1),
(256, 1, '2026-07-23 13:34:56.037', 'motor_right', 1, 1, 2, 0, 0.4, 1, 24, 0, 0, 0, 0, 30, 12300, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `robot_stations`
--

CREATE TABLE `robot_stations` (
  `id` int(11) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `station_id` varchar(100) NOT NULL,
  `station_type` varchar(50) DEFAULT NULL,
  `x` double DEFAULT NULL,
  `y` double DEFAULT NULL,
  `r` double DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `map_name` varchar(100) DEFAULT NULL,
  `synced_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `robot_stations`
--

INSERT INTO `robot_stations` (`id`, `robot_id`, `station_id`, `station_type`, `x`, `y`, `r`, `description`, `map_name`, `synced_at`) VALUES
(1, 1, 'LM1', 'LocationMark', 0, 0, 0, 'titik awal', NULL, '2026-07-23 13:34:41'),
(2, 1, 'LM2', 'LocationMark', 3.5, 0.07, -0.0064, '', NULL, '2026-07-23 13:34:41'),
(3, 1, 'LM3', 'LocationMark', 6, 2.5, 1.57, '', NULL, '2026-07-23 13:34:41'),
(4, 1, 'LM4', 'LocationMark', 8, 5, 1.57, '', NULL, '2026-07-23 13:34:41'),
(5, 1, 'CP1', 'ChargePoint', -1.5, -2, 3.14, 'titik charging', NULL, '2026-07-23 13:34:41');

-- --------------------------------------------------------

--
-- Table structure for table `robot_status_logs`
--

CREATE TABLE `robot_status_logs` (
  `id` bigint(20) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `logged_at` datetime(3) NOT NULL,
  `battery_level` double DEFAULT NULL,
  `battery_temp` double DEFAULT NULL,
  `charging` tinyint(1) DEFAULT NULL,
  `voltage` double DEFAULT NULL,
  `current` double DEFAULT NULL,
  `max_charge_voltage` double DEFAULT NULL,
  `max_charge_current` double DEFAULT NULL,
  `manual_charge` tinyint(1) DEFAULT NULL,
  `auto_charge` tinyint(1) DEFAULT NULL,
  `battery_cycle` int(11) DEFAULT NULL,
  `pos_x` double DEFAULT NULL,
  `pos_y` double DEFAULT NULL,
  `angle` double DEFAULT NULL,
  `loc_confidence` double DEFAULT NULL,
  `current_station` varchar(50) DEFAULT NULL,
  `last_station` varchar(50) DEFAULT NULL,
  `loc_method` tinyint(4) DEFAULT NULL,
  `vx` double DEFAULT NULL,
  `vy` double DEFAULT NULL,
  `w` double DEFAULT NULL,
  `is_stop` tinyint(1) DEFAULT NULL,
  `raw_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `robot_status_logs`
--

INSERT INTO `robot_status_logs` (`id`, `robot_id`, `logged_at`, `battery_level`, `battery_temp`, `charging`, `voltage`, `current`, `max_charge_voltage`, `max_charge_current`, `manual_charge`, `auto_charge`, `battery_cycle`, `pos_x`, `pos_y`, `angle`, `loc_confidence`, `current_station`, `last_station`, `loc_method`, `vx`, `vy`, `w`, `is_stop`, `raw_json`) VALUES
(121, 1, '2026-07-23 13:34:41.543', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}'),
(122, 1, '2026-07-23 13:34:43.618', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}'),
(123, 1, '2026-07-23 13:34:45.699', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}'),
(124, 1, '2026-07-23 13:34:47.764', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}'),
(125, 1, '2026-07-23 13:34:49.840', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}'),
(126, 1, '2026-07-23 13:34:51.905', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}'),
(127, 1, '2026-07-23 13:34:53.982', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}'),
(128, 1, '2026-07-23 13:34:56.037', 0.87, 35, 0, 24.5, 2, 48, 5, 0, 0, 9, 3.5069, 0.0687, -0.0064, 0.637, 'LM1', 'LM2', 0, 0.41, 0, 0.02, 0, '{\"battery\": {\"auto_charge\": false, \"battery_cycle\": 9, \"battery_level\": 0.87, \"battery_temp\": 35, \"battery_user_data\": \"\", \"charging\": false, \"current\": 2, \"manual_charge\": false, \"max_charge_current\": 5, \"max_charge_voltage\": 48, \"ret_code\": 0, \"voltage\": 24.5}, \"location\": {\"angle\": -0.0064, \"confidence\": 0.637, \"x\": 3.5069, \"y\": 0.0687, \"current_station\": \"LM1\", \"last_station\": \"LM2\", \"loc_method\": 0}, \"speed\": {\"vx\": 0.41, \"vy\": 0.0, \"w\": 0.02, \"steer\": 0, \"spin\": 0, \"is_stop\": false, \"ret_code\": 0}}');

-- --------------------------------------------------------

--
-- Table structure for table `robot_task_logs`
--

CREATE TABLE `robot_task_logs` (
  `id` bigint(20) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `logged_at` datetime(3) NOT NULL,
  `task_status` tinyint(4) DEFAULT NULL,
  `task_type` tinyint(4) DEFAULT NULL,
  `target_id` varchar(100) DEFAULT NULL,
  `target_point` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_point`)),
  `finished_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`finished_path`)),
  `unfinished_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`unfinished_path`)),
  `move_status_info` varchar(255) DEFAULT NULL,
  `containers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`containers`)),
  `raw_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `robot_task_logs`
--

INSERT INTO `robot_task_logs` (`id`, `robot_id`, `logged_at`, `task_status`, `task_type`, `target_id`, `target_point`, `finished_path`, `unfinished_path`, `move_status_info`, `containers`, `raw_json`) VALUES
(1, 1, '2026-07-22 13:56:58.852', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(2, 1, '2026-07-22 13:57:01.044', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(3, 1, '2026-07-22 13:57:03.106', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(4, 1, '2026-07-22 13:57:05.169', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(5, 1, '2026-07-22 13:57:07.226', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(6, 1, '2026-07-22 13:57:09.331', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(7, 1, '2026-07-22 13:57:11.409', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(8, 1, '2026-07-22 13:57:13.469', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(9, 1, '2026-07-22 13:57:15.524', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(10, 1, '2026-07-22 13:57:17.590', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(11, 1, '2026-07-22 13:57:19.648', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(12, 1, '2026-07-22 13:57:21.725', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(13, 1, '2026-07-22 13:57:23.797', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(14, 1, '2026-07-22 13:57:26.053', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(15, 1, '2026-07-22 13:57:28.115', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(16, 1, '2026-07-22 13:57:30.190', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(17, 1, '2026-07-22 13:57:32.252', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(18, 1, '2026-07-22 13:57:34.318', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(19, 1, '2026-07-22 13:57:36.387', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(20, 1, '2026-07-22 13:57:38.451', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(21, 1, '2026-07-22 13:57:40.525', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(22, 1, '2026-07-22 13:57:42.593', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(23, 1, '2026-07-22 13:57:44.671', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(24, 1, '2026-07-22 13:57:46.742', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(25, 1, '2026-07-22 13:57:48.800', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(26, 1, '2026-07-22 13:57:50.884', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(27, 1, '2026-07-22 13:57:52.949', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(28, 1, '2026-07-23 08:46:17.867', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(29, 1, '2026-07-23 08:46:19.942', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(30, 1, '2026-07-23 08:46:21.998', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(31, 1, '2026-07-23 08:46:24.074', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(32, 1, '2026-07-23 08:46:26.215', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(33, 1, '2026-07-23 08:46:28.384', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(34, 1, '2026-07-23 08:46:30.551', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(35, 1, '2026-07-23 08:46:33.210', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(36, 1, '2026-07-23 08:46:35.567', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(37, 1, '2026-07-23 08:46:37.776', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(38, 1, '2026-07-23 08:46:39.898', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(39, 1, '2026-07-23 08:46:42.914', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(40, 1, '2026-07-23 08:46:44.977', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(41, 1, '2026-07-23 08:46:47.179', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(42, 1, '2026-07-23 08:46:49.284', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(43, 1, '2026-07-23 08:46:51.342', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(44, 1, '2026-07-23 08:46:53.417', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(45, 1, '2026-07-23 08:46:55.513', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(46, 1, '2026-07-23 08:46:57.679', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(47, 1, '2026-07-23 08:46:59.787', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(48, 1, '2026-07-23 08:47:01.869', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(49, 1, '2026-07-23 08:47:03.925', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(50, 1, '2026-07-23 08:47:05.991', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(51, 1, '2026-07-23 13:34:41.543', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(52, 1, '2026-07-23 13:34:43.618', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(53, 1, '2026-07-23 13:34:45.699', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(54, 1, '2026-07-23 13:34:47.764', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(55, 1, '2026-07-23 13:34:49.840', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(56, 1, '2026-07-23 13:34:51.905', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(57, 1, '2026-07-23 13:34:53.982', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}'),
(58, 1, '2026-07-23 13:34:56.037', 2, 3, 'LM3', NULL, '[\"LM1\", \"LM2\"]', '[\"LM3\", \"LM4\"]', '', NULL, '{\"task_status\": 2, \"task_type\": 3, \"target_id\": \"LM3\", \"finished_path\": [\"LM1\", \"LM2\"], \"unfinished_path\": [\"LM3\", \"LM4\"], \"move_status_info\": \"\", \"ret_code\": 0}');

-- --------------------------------------------------------

--
-- Table structure for table `robot_trips`
--

CREATE TABLE `robot_trips` (
  `id` bigint(20) NOT NULL,
  `robot_id` int(11) NOT NULL,
  `from_station` varchar(100) DEFAULT NULL,
  `to_station` varchar(100) NOT NULL,
  `started_at` datetime(3) NOT NULL,
  `completed_at` datetime(3) DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `distance_m` double DEFAULT NULL,
  `battery_start` double DEFAULT NULL,
  `battery_end` double DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `synced_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `connection_logs`
--
ALTER TABLE `connection_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_connection_robot_time` (`robot_id`,`occurred_at`);

--
-- Indexes for table `data_sessions`
--
ALTER TABLE `data_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_session_robot` (`robot_id`),
  ADD KEY `idx_session_time` (`started_at`,`ended_at`);

--
-- Indexes for table `robots`
--
ALTER TABLE `robots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_robot_id_str` (`robot_id_str`);

--
-- Indexes for table `robot_control_lock_logs`
--
ALTER TABLE `robot_control_lock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_control_lock_robot_time` (`robot_id`,`logged_at`);

--
-- Indexes for table `robot_imu_logs`
--
ALTER TABLE `robot_imu_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_imu_robot_time` (`robot_id`,`logged_at`);

--
-- Indexes for table `robot_motor_logs`
--
ALTER TABLE `robot_motor_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_motor_robot_time_name` (`robot_id`,`logged_at`,`motor_name`);

--
-- Indexes for table `robot_stations`
--
ALTER TABLE `robot_stations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_robot_station` (`robot_id`,`station_id`);

--
-- Indexes for table `robot_status_logs`
--
ALTER TABLE `robot_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_robot_time` (`robot_id`,`logged_at`);

--
-- Indexes for table `robot_task_logs`
--
ALTER TABLE `robot_task_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_robot_time` (`robot_id`,`logged_at`);

--
-- Indexes for table `robot_trips`
--
ALTER TABLE `robot_trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trips_robot_time` (`robot_id`,`started_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `connection_logs`
--
ALTER TABLE `connection_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3280;

--
-- AUTO_INCREMENT for table `data_sessions`
--
ALTER TABLE `data_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `robots`
--
ALTER TABLE `robots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `robot_control_lock_logs`
--
ALTER TABLE `robot_control_lock_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `robot_imu_logs`
--
ALTER TABLE `robot_imu_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `robot_motor_logs`
--
ALTER TABLE `robot_motor_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=257;

--
-- AUTO_INCREMENT for table `robot_stations`
--
ALTER TABLE `robot_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `robot_status_logs`
--
ALTER TABLE `robot_status_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `robot_task_logs`
--
ALTER TABLE `robot_task_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `robot_trips`
--
ALTER TABLE `robot_trips`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `connection_logs`
--
ALTER TABLE `connection_logs`
  ADD CONSTRAINT `fk_connection_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `data_sessions`
--
ALTER TABLE `data_sessions`
  ADD CONSTRAINT `fk_session_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `robot_control_lock_logs`
--
ALTER TABLE `robot_control_lock_logs`
  ADD CONSTRAINT `fk_control_lock_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `robot_imu_logs`
--
ALTER TABLE `robot_imu_logs`
  ADD CONSTRAINT `fk_imu_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `robot_motor_logs`
--
ALTER TABLE `robot_motor_logs`
  ADD CONSTRAINT `fk_motor_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `robot_stations`
--
ALTER TABLE `robot_stations`
  ADD CONSTRAINT `fk_stations_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `robot_status_logs`
--
ALTER TABLE `robot_status_logs`
  ADD CONSTRAINT `fk_status_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `robot_task_logs`
--
ALTER TABLE `robot_task_logs`
  ADD CONSTRAINT `fk_task_logs_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `robot_trips`
--
ALTER TABLE `robot_trips`
  ADD CONSTRAINT `fk_trips_robot` FOREIGN KEY (`robot_id`) REFERENCES `robots` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
