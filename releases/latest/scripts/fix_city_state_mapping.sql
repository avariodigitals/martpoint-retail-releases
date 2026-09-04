-- ============================================================
-- MartPoint: Fix City-to-State Mapping
-- Root cause: db_cities.state_id = 0 for all cities,
-- so city dropdowns in Store Settings always show "No Records Found"
-- ============================================================

-- 1. Add Nigerian States
INSERT IGNORE INTO `db_states` (`store_id`, `state`, `country`, `status`) VALUES
(1, 'Abia', 'Nigeria', 1),
(1, 'Adamawa', 'Nigeria', 1),
(1, 'Akwa Ibom', 'Nigeria', 1),
(1, 'Anambra', 'Nigeria', 1),
(1, 'Bauchi', 'Nigeria', 1),
(1, 'Bayelsa', 'Nigeria', 1),
(1, 'Benue', 'Nigeria', 1),
(1, 'Borno', 'Nigeria', 1),
(1, 'Cross River', 'Nigeria', 1),
(1, 'Delta', 'Nigeria', 1),
(1, 'Ebonyi', 'Nigeria', 1),
(1, 'Edo', 'Nigeria', 1),
(1, 'Ekiti', 'Nigeria', 1),
(1, 'Enugu', 'Nigeria', 1),
(1, 'FCT', 'Nigeria', 1),
(1, 'Gombe', 'Nigeria', 1),
(1, 'Imo', 'Nigeria', 1),
(1, 'Jigawa', 'Nigeria', 1),
(1, 'Kaduna', 'Nigeria', 1),
(1, 'Kano', 'Nigeria', 1),
(1, 'Katsina', 'Nigeria', 1),
(1, 'Kebbi', 'Nigeria', 1),
(1, 'Kogi', 'Nigeria', 1),
(1, 'Kwara', 'Nigeria', 1),
(1, 'Nasarawa', 'Nigeria', 1),
(1, 'Niger', 'Nigeria', 1),
(1, 'Ogun', 'Nigeria', 1),
(1, 'Ondo', 'Nigeria', 1),
(1, 'Osun', 'Nigeria', 1),
(1, 'Oyo', 'Nigeria', 1),
(1, 'Plateau', 'Nigeria', 1),
(1, 'Rivers', 'Nigeria', 1),
(1, 'Sokoto', 'Nigeria', 1),
(1, 'Taraba', 'Nigeria', 1),
(1, 'Yobe', 'Nigeria', 1),
(1, 'Zamfara', 'Nigeria', 1);

-- 2. Add Ghanaian Regions
INSERT IGNORE INTO `db_states` (`store_id`, `state`, `country`, `status`) VALUES
(1, 'Greater Accra', 'Ghana', 1),
(1, 'Ashanti', 'Ghana', 1),
(1, 'Northern', 'Ghana', 1),
(1, 'Western', 'Ghana', 1),
(1, 'Central', 'Ghana', 1),
(1, 'Bono', 'Ghana', 1),
(1, 'Volta', 'Ghana', 1),
(1, 'Upper West', 'Ghana', 1),
(1, 'Upper East', 'Ghana', 1);

-- 3. Add UK Regions
INSERT IGNORE INTO `db_states` (`store_id`, `state`, `country`, `status`) VALUES
(1, 'England', 'United Kingdom', 1),
(1, 'Scotland', 'United Kingdom', 1),
(1, 'Wales', 'United Kingdom', 1),
(1, 'Northern Ireland', 'United Kingdom', 1);

-- 4. Add US States (only those referenced by cities in db_cities)
INSERT IGNORE INTO `db_states` (`store_id`, `state`, `country`, `status`) VALUES
(1, 'California', 'USA', 1),
(1, 'Illinois', 'USA', 1),
(1, 'Texas', 'USA', 1),
(1, 'Arizona', 'USA', 1),
(1, 'Pennsylvania', 'USA', 1),
(1, 'Florida', 'USA', 1),
(1, 'Ohio', 'USA', 1),
(1, 'North Carolina', 'USA', 1),
(1, 'Washington', 'USA', 1),
(1, 'Colorado', 'USA', 1),
(1, 'Massachusetts', 'USA', 1),
(1, 'Georgia', 'USA', 1);

-- ============================================================
-- 5. Map cities to their correct state_id
-- ============================================================

-- Nigerian cities -> states
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Lagos' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` IN ('Lagos', 'Ikeja', 'Lekki', 'Victoria Island') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Oyo' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Ibadan' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Ogun' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Abeokuta' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'FCT' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` IN ('Abuja', 'Wuse', 'Garki') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Rivers' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Port Harcourt' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Kano' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Kano' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Kaduna' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Kaduna' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Enugu' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Enugu' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Anambra' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` IN ('Onitsha', 'Awka') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Imo' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Owerri' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Akwa Ibom' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Uyo' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Cross River' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Calabar' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Edo' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Benin City' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Delta' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` IN ('Warri', 'Asaba') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Plateau' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Jos' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Borno' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Maiduguri' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Sokoto' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Sokoto' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Kwara' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Ilorin' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Ondo' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Akure' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Ekiti' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Ado-Ekiti' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Osun' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Osogbo' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Bauchi' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Bauchi' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Adamawa' AND `country` = 'Nigeria' LIMIT 1) AS s) WHERE `city` = 'Yola' AND `state_id` = 0;

-- Ghanaian cities -> regions
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Greater Accra' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` IN ('Accra', 'Tema') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Ashanti' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Kumasi' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Northern' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Tamale' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Western' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Sekondi-Takoradi' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Central' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Cape Coast' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Bono' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Sunyani' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Volta' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Ho' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Upper West' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Wa' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Upper East' AND `country` = 'Ghana' LIMIT 1) AS s) WHERE `city` = 'Bolgatanga' AND `state_id` = 0;

-- UK cities -> regions
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'England' AND `country` = 'United Kingdom' LIMIT 1) AS s) WHERE `city` IN ('London', 'Manchester', 'Birmingham', 'Liverpool', 'Leeds') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Scotland' AND `country` = 'United Kingdom' LIMIT 1) AS s) WHERE `city` IN ('Edinburgh', 'Glasgow') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Wales' AND `country` = 'United Kingdom' LIMIT 1) AS s) WHERE `city` IN ('Cardiff', 'Swansea') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Northern Ireland' AND `country` = 'United Kingdom' LIMIT 1) AS s) WHERE `city` = 'Belfast' AND `state_id` = 0;

-- US cities -> states
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'California' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` IN ('Los Angeles', 'San Francisco', 'San Diego') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Illinois' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Chicago' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Texas' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` IN ('Houston', 'Dallas', 'San Antonio', 'Austin', 'Fort Worth') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Arizona' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Phoenix' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Pennsylvania' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Philadelphia' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Florida' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` IN ('Jacksonville', 'Miami') AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Ohio' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Columbus' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'North Carolina' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Charlotte' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Washington' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Seattle' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Colorado' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Denver' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Massachusetts' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Boston' AND `state_id` = 0;
UPDATE `db_cities` SET `state_id` = (SELECT `id` FROM (SELECT `id` FROM `db_states` WHERE `state` = 'Georgia' AND `country` = 'USA' LIMIT 1) AS s) WHERE `city` = 'Atlanta' AND `state_id` = 0;

-- ============================================================
-- Verify: should show 0 cities with state_id=0
-- ============================================================
SELECT COUNT(*) AS 'cities_still_unmapped' FROM `db_cities` WHERE `state_id` = 0;
