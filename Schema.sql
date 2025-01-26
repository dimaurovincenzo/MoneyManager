-- Versione PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION;
SET collation_connection = utf8mb4_unicode_ci;

-- Struttura della tabella `accounts`
--

CREATE TABLE `accounts` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `icon_id` int DEFAULT NULL,
  `initial_balance` decimal(10,2) DEFAULT '0.00',
  `current_balance` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


--
-- Struttura della tabella `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Struttura della tabella `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon_id` int DEFAULT NULL,
  `type` enum('income','expense') NOT NULL,
  `color` varchar(7) DEFAULT '#6c757d',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Struttura della tabella `icons`
--

CREATE TABLE `icons` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `category` enum('account','category') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dati per la tabella `icons`
--

INSERT INTO `icons` (`id`, `name`, `icon_class`, `category`, `created_at`) VALUES
(1, 'Portafoglio', 'bi-wallet2', 'account', '2024-12-12 18:15:39'),
(2, 'Banca', 'bi-bank', 'account', '2024-12-12 18:15:39'),
(3, 'Carta di Credito', 'bi-credit-card-2-front', 'account', '2024-12-12 18:15:39'),
(4, 'Salvadanaio', 'bi-piggy-bank', 'account', '2024-12-12 18:15:39'),
(5, 'Cassaforte', 'bi-safe', 'account', '2024-12-12 18:15:39'),
(6, 'PayPal', 'bi-paypal', 'account', '2024-12-12 18:15:39'),
(7, 'Investimenti', 'bi-graph-up-arrow', 'account', '2024-12-12 18:15:39'),
(8, 'Contanti', 'bi-cash-stack', 'account', '2024-12-12 18:15:39'),
(9, 'Carta Prepagata', 'bi-credit-card', 'account', '2024-12-12 18:15:39'),
(10, 'Bitcoin', 'bi-currency-bitcoin', 'account', '2024-12-12 18:15:39'),
(11, 'Casa', 'bi-house-fill', 'category', '2024-12-12 18:15:39'),
(12, 'Affitto', 'bi-house-door-fill', 'category', '2024-12-12 18:15:39'),
(13, 'Mutuo', 'bi-bank2', 'category', '2024-12-12 18:15:39'),
(14, 'Bollette', 'bi-receipt', 'category', '2024-12-12 18:15:39'),
(15, 'Acqua', 'bi-droplet-fill', 'category', '2024-12-12 18:15:39'),
(16, 'Luce', 'bi-lightning-fill', 'category', '2024-12-12 18:15:39'),
(17, 'Gas', 'bi-fire', 'category', '2024-12-12 18:15:39'),
(18, 'Internet', 'bi-wifi', 'category', '2024-12-12 18:15:39'),
(19, 'Telefono', 'bi-phone-fill', 'category', '2024-12-12 18:15:39'),
(20, 'TV', 'bi-tv-fill', 'category', '2024-12-12 18:15:39'),
(21, 'Condominio', 'bi-buildings-fill', 'category', '2024-12-12 18:15:39'),
(22, 'Manutenzione Casa', 'bi-tools', 'category', '2024-12-12 18:15:39'),
(23, 'Assicurazione Casa', 'bi-shield-fill', 'category', '2024-12-12 18:15:39'),
(24, 'Pulizie', 'bi-droplet-fill', 'category', '2024-12-12 18:15:39'),
(25, 'Detersivi', 'bi-droplet', 'category', '2024-12-12 18:15:39'),
(26, 'Lavanderia', 'bi-water', 'category', '2024-12-12 18:15:39'),
(27, 'Auto', 'bi-car-front-fill', 'category', '2024-12-12 18:15:39'),
(28, 'Carburante', 'bi-fuel-pump-fill', 'category', '2024-12-12 18:15:39'),
(29, 'Assicurazione Auto', 'bi-shield-check-fill', 'category', '2024-12-12 18:15:39'),
(30, 'Manutenzione Auto', 'bi-wrench-adjustable', 'category', '2024-12-12 18:15:39'),
(31, 'Parcheggio', 'bi-p-square-fill', 'category', '2024-12-12 18:15:39'),
(32, 'Trasporto Pubblico', 'bi-bus-front-fill', 'category', '2024-12-12 18:15:39'),
(33, 'Taxi', 'bi-taxi-front-fill', 'category', '2024-12-12 18:15:39'),
(34, 'Treno', 'bi-train-front', 'category', '2024-12-12 18:15:39'),
(35, 'Farmacia', 'bi-bandaid-fill', 'category', '2024-12-12 18:15:39'),
(36, 'Medico', 'bi-heart-pulse-fill', 'category', '2024-12-12 18:15:39'),
(37, 'Dentista', 'bi-emoji-smile-fill', 'category', '2024-12-12 18:15:39'),
(38, 'Palestra', 'bi-bicycle', 'category', '2024-12-12 18:15:39'),
(39, 'Parrucchiere', 'bi-scissors', 'category', '2024-12-12 18:15:39'),
(40, 'Estetista', 'bi-stars', 'category', '2024-12-12 18:15:39'),
(41, 'Cosmetici', 'bi-stars', 'category', '2024-12-12 18:15:39'),
(42, 'Spa', 'bi-water', 'category', '2024-12-12 18:15:39'),
(43, 'Spesa', 'bi-cart-fill', 'category', '2024-12-12 18:15:39'),
(44, 'Supermercato', 'bi-shop', 'category', '2024-12-12 18:15:39'),
(45, 'Bar', 'bi-cup-hot-fill', 'category', '2024-12-12 18:15:39'),
(46, 'Caffè', 'bi-cup-fill', 'category', '2024-12-12 18:15:39'),
(47, 'Ristorante', 'bi-shop-window', 'category', '2024-12-12 18:15:39'),
(48, 'Cibo da Asporto', 'bi-bag-fill', 'category', '2024-12-12 18:15:39'),
(49, 'Sigarette', 'bi-wind', 'category', '2024-12-12 18:15:39'),
(50, 'Cinema', 'bi-film', 'category', '2024-12-12 18:15:39'),
(51, 'Teatro', 'bi-mask', 'category', '2024-12-12 18:15:39'),
(52, 'Concerti', 'bi-music-note-beamed', 'category', '2024-12-12 18:15:39'),
(53, 'Sport', 'bi-trophy-fill', 'category', '2024-12-12 18:15:39'),
(54, 'Hobby', 'bi-controller', 'category', '2024-12-12 18:15:39'),
(55, 'Libri', 'bi-book-fill', 'category', '2024-12-12 18:15:39'),
(56, 'Giochi', 'bi-joystick', 'category', '2024-12-12 18:15:39'),
(57, 'Viaggi', 'bi-airplane-fill', 'category', '2024-12-12 18:15:39'),
(58, 'Hotel', 'bi-building', 'category', '2024-12-12 18:15:39'),
(59, 'Abbigliamento', 'bi-bag-heart-fill', 'category', '2024-12-12 18:15:39'),
(60, 'Scarpe', 'bi-boot', 'category', '2024-12-12 18:15:39'),
(61, 'Accessori', 'bi-watch', 'category', '2024-12-12 18:15:39'),
(62, 'Elettronica', 'bi-laptop', 'category', '2024-12-12 18:15:39'),
(63, 'Regali', 'bi-gift-fill', 'category', '2024-12-12 18:15:39'),
(64, 'Abbonamenti', 'bi-calendar-check-fill', 'category', '2024-12-12 18:15:39'),
(65, 'Netflix', 'bi-play-btn-fill', 'category', '2024-12-12 18:15:39'),
(66, 'Spotify', 'bi-spotify', 'category', '2024-12-12 18:15:39'),
(67, 'Amazon Prime', 'bi-box-fill', 'category', '2024-12-12 18:15:39'),
(68, 'Palestra', 'bi-bicycle', 'category', '2024-12-12 18:15:39'),
(69, 'Assicurazioni', 'bi-shield-fill', 'category', '2024-12-12 18:15:39'),
(70, 'Lavoro', 'bi-briefcase-fill', 'category', '2024-12-12 18:15:39'),
(71, 'Formazione', 'bi-mortarboard-fill', 'category', '2024-12-12 18:15:39'),
(72, 'Cancelleria', 'bi-pencil-fill', 'category', '2024-12-12 18:15:39'),
(73, 'Attrezzatura', 'bi-tools', 'category', '2024-12-12 18:15:39'),
(74, 'Software', 'bi-window', 'category', '2024-12-12 18:15:39'),
(75, 'Bambini', 'bi-people-fill', 'category', '2024-12-12 18:15:39'),
(76, 'Scuola', 'bi-pencil-square', 'category', '2024-12-12 18:15:39'),
(77, 'Giocattoli', 'bi-puzzle-fill', 'category', '2024-12-12 18:15:39'),
(78, 'Animali', 'bi-heart-fill', 'category', '2024-12-12 18:15:39'),
(79, 'Veterinario', 'bi-heart-pulse-fill', 'category', '2024-12-12 18:15:39'),
(80, 'Cibo Animali', 'bi-basket2-fill', 'category', '2024-12-12 18:15:39'),
(81, 'Stipendio', 'bi-cash-stack', 'category', '2024-12-12 18:15:39'),
(82, 'Bonus', 'bi-award-fill', 'category', '2024-12-12 18:15:39'),
(83, 'Tredicesima', 'bi-calendar-check-fill', 'category', '2024-12-12 18:15:39'),
(84, 'Quattordicesima', 'bi-calendar-heart-fill', 'category', '2024-12-12 18:15:39'),
(85, 'Rimborsi', 'bi-arrow-return-left', 'category', '2024-12-12 18:15:39'),
(86, 'Vendite', 'bi-tag-fill', 'category', '2024-12-12 18:15:39'),
(87, 'Investimenti', 'bi-graph-up', 'category', '2024-12-12 18:15:39'),
(88, 'Regalo', 'bi-gift', 'category', '2024-12-12 18:15:39'),
(89, 'Affitto Attivo', 'bi-house-door', 'category', '2024-12-12 18:15:39'),
(90, 'Dividendi', 'bi-pie-chart-fill', 'category', '2024-12-12 18:15:39'),
(91, 'Freelance', 'bi-laptop', 'category', '2024-12-12 18:15:39'),
(92, 'Pensione', 'bi-calendar-check', 'category', '2024-12-12 18:15:39'),
(93, 'Interessi', 'bi-percent', 'category', '2024-12-12 18:15:39'),
(94, 'Vincite', 'bi-trophy-fill', 'category', '2024-12-12 18:15:39');

-- --------------------------------------------------------

--
-- Struttura della tabella `recurring_payments`
--

CREATE TABLE `recurring_payments` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` int NOT NULL,
  `category_id` int NOT NULL,
  `day_of_month` int NOT NULL,
  `next_execution` date NOT NULL,
  `last_execution` date DEFAULT NULL,
  `secret_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cronjob_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Struttura della tabella `shopping_lists`
--

CREATE TABLE `shopping_lists` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_archived` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Struttura della tabella `shopping_list_items`
--

CREATE TABLE `shopping_list_items` (
  `id` int NOT NULL,
  `list_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_checked` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `position` int DEFAULT '0',
  `quantity` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Struttura della tabella `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `account_id` int NOT NULL,
  `category_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indici per le tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `icon_id` (`icon_id`);

--
-- Indici per le tabelle `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `icon_id` (`icon_id`);

--
-- Indici per le tabelle `icons`
--
ALTER TABLE `icons`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `recurring_payments`
--
ALTER TABLE `recurring_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indici per le tabelle `shopping_lists`
--
ALTER TABLE `shopping_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `shopping_list_items`
--
ALTER TABLE `shopping_list_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_id` (`list_id`);

--
-- Indici per le tabelle `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transactions_date` (`date`),
  ADD KEY `idx_transactions_account` (`account_id`),
  ADD KEY `idx_transactions_category` (`category_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT per la tabella `icons`
--
ALTER TABLE `icons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT per la tabella `recurring_payments`
--
ALTER TABLE `recurring_payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `shopping_lists`
--
ALTER TABLE `shopping_lists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `shopping_list_items`
--
ALTER TABLE `shopping_list_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT per la tabella `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

SET collation_connection = @OLD_COLLATION_CONNECTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
