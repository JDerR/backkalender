-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 17, 2020 at 05:46 PM
-- Server version: 5.7.29-0ubuntu0.16.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `backhausverein`
--

-- --------------------------------------------------------

--
-- Table structure for table `backgruppen`
--

CREATE TABLE `backgruppen` (
  `id` int(11) NOT NULL,
  `backgruppeName` text CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  `passwort` text NOT NULL,
  `mail` text NOT NULL,
  `aktiv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `backtermine`
--

CREATE TABLE `backtermine` (
  `id` int(11) NOT NULL,
  `backgruppeName` text CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  `backtermin` text CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  `storniert` text,
  `zeitstempel` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `slot` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Indexes for table `backgruppen`
--
ALTER TABLE `backgruppen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `backtermine`
--
ALTER TABLE `backtermine`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `backgruppen`
--
ALTER TABLE `backgruppen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `backtermine`
--
ALTER TABLE `backtermine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
