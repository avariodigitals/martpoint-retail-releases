-- ============================================================
-- MartPoint Location Migration
-- West Africa + UK + US focused country/state/city support
-- ============================================================

-- 1. Create db_cities table
CREATE TABLE IF NOT EXISTS db_cities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(255) NOT NULL,
  state_id INT NOT NULL,
  status TINYINT(1) DEFAULT 1,
  store_id INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Seed major cities for West African states + UK + US
--    (Run this after your db_states table already has matching states)

-- NIGERIA
INSERT INTO db_cities (city, state_id) VALUES
('Lagos', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Ikeja', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Lekki', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Victoria Island', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Ibadan', (SELECT id FROM db_states WHERE state = 'Oyo' LIMIT 1)),
('Abeokuta', (SELECT id FROM db_states WHERE state = 'Ogun' LIMIT 1)),
('Abuja', (SELECT id FROM db_states WHERE state = 'FCT' LIMIT 1)),
('Wuse', (SELECT id FROM db_states WHERE state = 'FCT' LIMIT 1)),
('Garki', (SELECT id FROM db_states WHERE state = 'FCT' LIMIT 1)),
('Port Harcourt', (SELECT id FROM db_states WHERE state = 'Rivers' LIMIT 1)),
('Kano', (SELECT id FROM db_states WHERE state = 'Kano' LIMIT 1)),
('Kaduna', (SELECT id FROM db_states WHERE state = 'Kaduna' LIMIT 1)),
('Enugu', (SELECT id FROM db_states WHERE state = 'Enugu' LIMIT 1)),
('Onitsha', (SELECT id FROM db_states WHERE state = 'Anambra' LIMIT 1)),
('Awka', (SELECT id FROM db_states WHERE state = 'Anambra' LIMIT 1)),
('Owerri', (SELECT id FROM db_states WHERE state = 'Imo' LIMIT 1)),
('Uyo', (SELECT id FROM db_states WHERE state = 'Akwa Ibom' LIMIT 1)),
('Calabar', (SELECT id FROM db_states WHERE state = 'Cross River' LIMIT 1)),
('Benin City', (SELECT id FROM db_states WHERE state = 'Edo' LIMIT 1)),
('Warri', (SELECT id FROM db_states WHERE state = 'Delta' LIMIT 1)),
('Asaba', (SELECT id FROM db_states WHERE state = 'Delta' LIMIT 1)),
('Jos', (SELECT id FROM db_states WHERE state = 'Plateau' LIMIT 1)),
('Maiduguri', (SELECT id FROM db_states WHERE state = 'Borno' LIMIT 1)),
('Sokoto', (SELECT id FROM db_states WHERE state = 'Sokoto' LIMIT 1)),
('Ilorin', (SELECT id FROM db_states WHERE state = 'Kwara' LIMIT 1)),
('Akure', (SELECT id FROM db_states WHERE state = 'Ondo' LIMIT 1)),
('Ado-Ekiti', (SELECT id FROM db_states WHERE state = 'Ekiti' LIMIT 1)),
('Osogbo', (SELECT id FROM db_states WHERE state = 'Osun' LIMIT 1)),
('Bauchi', (SELECT id FROM db_states WHERE state = 'Bauchi' LIMIT 1)),
('Yola', (SELECT id FROM db_states WHERE state = 'Adamawa' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;

-- GHANA
INSERT INTO db_cities (city, state_id) VALUES
('Accra', (SELECT id FROM db_states WHERE state = 'Greater Accra' LIMIT 1)),
('Tema', (SELECT id FROM db_states WHERE state = 'Greater Accra' LIMIT 1)),
('Kumasi', (SELECT id FROM db_states WHERE state = 'Ashanti' LIMIT 1)),
('Tamale', (SELECT id FROM db_states WHERE state = 'Northern' LIMIT 1)),
('Sekondi-Takoradi', (SELECT id FROM db_states WHERE state = 'Western' LIMIT 1)),
('Cape Coast', (SELECT id FROM db_states WHERE state = 'Central' LIMIT 1)),
('Sunyani', (SELECT id FROM db_states WHERE state = 'Bono' LIMIT 1)),
('Ho', (SELECT id FROM db_states WHERE state = 'Volta' LIMIT 1)),
('Wa', (SELECT id FROM db_states WHERE state = 'Upper West' LIMIT 1)),
('Bolgatanga', (SELECT id FROM db_states WHERE state = 'Upper East' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;

-- UNITED KINGDOM
INSERT INTO db_cities (city, state_id) VALUES
('London', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Manchester', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Birmingham', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Liverpool', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Leeds', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Edinburgh', (SELECT id FROM db_states WHERE state = 'Scotland' LIMIT 1)),
('Glasgow', (SELECT id FROM db_states WHERE state = 'Scotland' LIMIT 1)),
('Cardiff', (SELECT id FROM db_states WHERE state = 'Wales' LIMIT 1)),
('Swansea', (SELECT id FROM db_states WHERE state = 'Wales' LIMIT 1)),
('Belfast', (SELECT id FROM db_states WHERE state = 'Northern Ireland' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;

-- UNITED STATES
INSERT INTO db_cities (city, state_id) VALUES
('New York', (SELECT id FROM db_states WHERE state = 'New York' LIMIT 1)),
('Los Angeles', (SELECT id FROM db_states WHERE state = 'California' LIMIT 1)),
('San Francisco', (SELECT id FROM db_states WHERE state = 'California' LIMIT 1)),
('Chicago', (SELECT id FROM db_states WHERE state = 'Illinois' LIMIT 1)),
('Houston', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Dallas', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Phoenix', (SELECT id FROM db_states WHERE state = 'Arizona' LIMIT 1)),
('Philadelphia', (SELECT id FROM db_states WHERE state = 'Pennsylvania' LIMIT 1)),
('San Antonio', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('San Diego', (SELECT id FROM db_states WHERE state = 'California' LIMIT 1)),
('Austin', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Jacksonville', (SELECT id FROM db_states WHERE state = 'Florida' LIMIT 1)),
('Miami', (SELECT id FROM db_states WHERE state = 'Florida' LIMIT 1)),
('Fort Worth', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Columbus', (SELECT id FROM db_states WHERE state = 'Ohio' LIMIT 1)),
('Charlotte', (SELECT id FROM db_states WHERE state = 'North Carolina' LIMIT 1)),
('Seattle', (SELECT id FROM db_states WHERE state = 'Washington' LIMIT 1)),
('Denver', (SELECT id FROM db_states WHERE state = 'Colorado' LIMIT 1)),
('Boston', (SELECT id FROM db_states WHERE state = 'Massachusetts' LIMIT 1)),
('Atlanta', (SELECT id FROM db_states WHERE state = 'Georgia' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;
