# Conversion to utf8mb4

--
-- Step 1.1: Enlarge columns to avoid data loss on later conversion to utf8mb4
--

ALTER TABLE `#__weblinks` MODIFY `alias` varchar(400) NOT NULL DEFAULT '';

--
-- Step 1.2: Convert table to utf8mb4 chracter set with utf8mb4_unicode_ci collation
--

ALTER TABLE `#__weblinks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Step 1.3: Set collation to utf8mb4_bin for formerly utf8_bin collated columns
--

ALTER TABLE `#__weblinks` MODIFY `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';

--
-- Step 1.4: Set default character set and collation for all tables
--

ALTER TABLE `#__weblinks` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
