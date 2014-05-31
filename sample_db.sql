-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Czas wygenerowania: 22 Maj 2014, 15:04
-- Wersja serwera: 5.6.11
-- Wersja PHP: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `sample_db`
--
CREATE DATABASE IF NOT EXISTS `sample_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `sample_db`;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` char(60) NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned DEFAULT NULL,
  `last_login` int(10) unsigned DEFAULT NULL,
  `is_active` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Użytkownicy aplikacji' AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, 'arkadio', 'sample@email.pl', '$2y$10$eC.SI05u9NnWy4uI1N4FFunXSjhSl6nkAaR64zzvIMjOEWCIDoSxu', 1398528884, NULL, NULL, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
