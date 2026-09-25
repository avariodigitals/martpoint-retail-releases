-- ============================================================
-- Nigerian cities seed — major cities/towns for all 36 states + FCT.
-- Idempotent: NOT EXISTS guards skip anything already present.
-- ============================================================

CREATE TABLE IF NOT EXISTS db_cities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(255) NOT NULL,
  state_id INT NOT NULL,
  status TINYINT(1) DEFAULT 1,
  store_id INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure all Nigerian states exist (older installs may lack some).
INSERT IGNORE INTO `db_states` (`id`, `store_id`, `state_code`, `state`, `country_code`, `country_id`, `country`, `added_on`, `company_id`, `status`) VALUES
(52, 2, 'NG0001', 'Lagos', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(53, 2, 'NG0002', 'Oyo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(54, 2, 'NG0003', 'Ogun', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(55, 2, 'NG0004', 'FCT', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(56, 2, 'NG0005', 'Rivers', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(57, 2, 'NG0006', 'Kano', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(58, 2, 'NG0007', 'Kaduna', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(59, 2, 'NG0008', 'Enugu', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(60, 2, 'NG0009', 'Anambra', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(61, 2, 'NG0010', 'Imo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(62, 2, 'NG0011', 'Akwa Ibom', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(63, 2, 'NG0012', 'Cross River', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(64, 2, 'NG0013', 'Edo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(65, 2, 'NG0014', 'Delta', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(66, 2, 'NG0015', 'Plateau', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(67, 2, 'NG0016', 'Borno', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(68, 2, 'NG0017', 'Sokoto', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(69, 2, 'NG0018', 'Kwara', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(70, 2, 'NG0019', 'Ondo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(71, 2, 'NG0020', 'Ekiti', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(72, 2, 'NG0021', 'Osun', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(73, 2, 'NG0022', 'Bauchi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(74, 2, 'NG0023', 'Adamawa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(75, 2, 'NG0024', 'Abia', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(76, 2, 'NG0025', 'Bayelsa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(77, 2, 'NG0026', 'Benue', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(78, 2, 'NG0027', 'Ebonyi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(79, 2, 'NG0028', 'Gombe', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(80, 2, 'NG0029', 'Jigawa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(81, 2, 'NG0030', 'Katsina', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(82, 2, 'NG0031', 'Kebbi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(83, 2, 'NG0032', 'Kogi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(84, 2, 'NG0033', 'Nasarawa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(85, 2, 'NG0034', 'Niger', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(86, 2, 'NG0035', 'Taraba', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(87, 2, 'NG0036', 'Yobe', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(88, 2, 'NG0037', 'Zamfara', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1);

INSERT INTO db_cities (city, state_id, status)
SELECT v.c, st.id, 1
FROM db_states st
JOIN (
  SELECT 'Lagos' AS s, 'Lagos' AS c
  UNION ALL SELECT 'Lagos','Ikeja' UNION ALL SELECT 'Lagos','Surulere'
  UNION ALL SELECT 'Lagos','Victoria Island' UNION ALL SELECT 'Lagos','Ikoyi'
  UNION ALL SELECT 'Lagos','Lekki' UNION ALL SELECT 'Lagos','Ajah'
  UNION ALL SELECT 'Lagos','Sangotedo' UNION ALL SELECT 'Lagos','Ibeju-Lekki'
  UNION ALL SELECT 'Lagos','Epe' UNION ALL SELECT 'Lagos','Badagry'
  UNION ALL SELECT 'Lagos','Ikorodu' UNION ALL SELECT 'Lagos','Ebute Ikorodu'
  UNION ALL SELECT 'Lagos','Apapa' UNION ALL SELECT 'Lagos','Tin Can Island'
  UNION ALL SELECT 'Lagos','Festac Town' UNION ALL SELECT 'Lagos','Satellite Town'
  UNION ALL SELECT 'Lagos','Amuwo-Odofin' UNION ALL SELECT 'Lagos','Mile 2'
  UNION ALL SELECT 'Lagos','Oshodi' UNION ALL SELECT 'Lagos','Isolo'
  UNION ALL SELECT 'Lagos','Ejigbo' UNION ALL SELECT 'Lagos','Okota'
  UNION ALL SELECT 'Lagos','Ago Palace' UNION ALL SELECT 'Lagos','Mushin'
  UNION ALL SELECT 'Lagos','Agege' UNION ALL SELECT 'Lagos','Alimosho'
  UNION ALL SELECT 'Lagos','Ikotun' UNION ALL SELECT 'Lagos','Egbeda'
  UNION ALL SELECT 'Lagos','Ipaja' UNION ALL SELECT 'Lagos','Iyana-Ipaja'
  UNION ALL SELECT 'Lagos','Ogba' UNION ALL SELECT 'Lagos','Ojodu Berger'
  UNION ALL SELECT 'Lagos','Ojota' UNION ALL SELECT 'Lagos','Ketu'
  UNION ALL SELECT 'Lagos','Magodo' UNION ALL SELECT 'Lagos','Maryland'
  UNION ALL SELECT 'Lagos','Ilupeju' UNION ALL SELECT 'Lagos','Anthony'
  UNION ALL SELECT 'Lagos','Gbagada' UNION ALL SELECT 'Lagos','Ogudu'
  UNION ALL SELECT 'Lagos','Bariga' UNION ALL SELECT 'Lagos','Shomolu'
  UNION ALL SELECT 'Lagos','Yaba' UNION ALL SELECT 'Lagos','Ebute Metta'
  UNION ALL SELECT 'Lagos','Ijesha' UNION ALL SELECT 'Lagos','Ojo'
  UNION ALL SELECT 'Lagos','Ajegunle' UNION ALL SELECT 'Lagos','Orile'
  UNION ALL SELECT 'Lagos','Obalende' UNION ALL SELECT 'Lagos','Isheri'
  UNION ALL SELECT 'FCT','Abuja' UNION ALL SELECT 'FCT','Wuse'
  UNION ALL SELECT 'FCT','Garki' UNION ALL SELECT 'FCT','Maitama'
  UNION ALL SELECT 'FCT','Asokoro' UNION ALL SELECT 'FCT','Gwarinpa'
  UNION ALL SELECT 'FCT','Kubwa' UNION ALL SELECT 'FCT','Nyanya'
  UNION ALL SELECT 'FCT','Karu' UNION ALL SELECT 'FCT','Gwagwalada'
  UNION ALL SELECT 'FCT','Kuje' UNION ALL SELECT 'FCT','Lugbe'
  UNION ALL SELECT 'FCT','Jabi' UNION ALL SELECT 'FCT','Utako'
  UNION ALL SELECT 'FCT','Bwari' UNION ALL SELECT 'FCT','Zuba'
  UNION ALL SELECT 'FCT','Mpape' UNION ALL SELECT 'FCT','Dei-Dei'
  UNION ALL SELECT 'Oyo','Ibadan' UNION ALL SELECT 'Oyo','Ogbomosho'
  UNION ALL SELECT 'Oyo','Oyo' UNION ALL SELECT 'Oyo','Iseyin'
  UNION ALL SELECT 'Oyo','Saki' UNION ALL SELECT 'Oyo','Igboho'
  UNION ALL SELECT 'Oyo','Eruwa' UNION ALL SELECT 'Oyo','Okeho'
  UNION ALL SELECT 'Oyo','Kisi' UNION ALL SELECT 'Oyo','Igbo-Ora'
  UNION ALL SELECT 'Oyo','Lalupon' UNION ALL SELECT 'Oyo','Moniya'
  UNION ALL SELECT 'Ogun','Abeokuta' UNION ALL SELECT 'Ogun','Ijebu-Ode'
  UNION ALL SELECT 'Ogun','Sagamu' UNION ALL SELECT 'Ogun','Sango-Ota'
  UNION ALL SELECT 'Ogun','Ifo' UNION ALL SELECT 'Ogun','Ilaro'
  UNION ALL SELECT 'Ogun','Mowe' UNION ALL SELECT 'Ogun','Ibafo'
  UNION ALL SELECT 'Ogun','Arepo' UNION ALL SELECT 'Ogun','Agbara'
  UNION ALL SELECT 'Ogun','Iperu-Remo' UNION ALL SELECT 'Ogun','Ago-Iwoye'
  UNION ALL SELECT 'Rivers','Port Harcourt' UNION ALL SELECT 'Rivers','Bonny'
  UNION ALL SELECT 'Rivers','Okrika' UNION ALL SELECT 'Rivers','Ahoada'
  UNION ALL SELECT 'Rivers','Omoku' UNION ALL SELECT 'Rivers','Eleme'
  UNION ALL SELECT 'Rivers','Oyigbo' UNION ALL SELECT 'Rivers','Obio-Akpor'
  UNION ALL SELECT 'Rivers','Bori' UNION ALL SELECT 'Rivers','Buguma'
  UNION ALL SELECT 'Rivers','Degema' UNION ALL SELECT 'Rivers','Opobo'
  UNION ALL SELECT 'Kano','Kano' UNION ALL SELECT 'Kano','Wudil'
  UNION ALL SELECT 'Kano','Gwarzo' UNION ALL SELECT 'Kano','Rano'
  UNION ALL SELECT 'Kano','Bichi' UNION ALL SELECT 'Kano','Tudun Wada'
  UNION ALL SELECT 'Kano','Sumaila' UNION ALL SELECT 'Kano','Karaye'
  UNION ALL SELECT 'Kano','Dambatta' UNION ALL SELECT 'Kano','Gezawa'
  UNION ALL SELECT 'Kano','Bagwai' UNION ALL SELECT 'Kano','Bunkure'
  UNION ALL SELECT 'Kaduna','Kaduna' UNION ALL SELECT 'Kaduna','Zaria'
  UNION ALL SELECT 'Kaduna','Kafanchan' UNION ALL SELECT 'Kaduna','Kagoro'
  UNION ALL SELECT 'Kaduna','Kachia' UNION ALL SELECT 'Kaduna','Birnin Gwari'
  UNION ALL SELECT 'Kaduna','Soba' UNION ALL SELECT 'Kaduna','Makarfi'
  UNION ALL SELECT 'Kaduna','Ikara' UNION ALL SELECT 'Kaduna','Zonkwa'
  UNION ALL SELECT 'Kaduna','Sabon Gari' UNION ALL SELECT 'Kaduna','Giwa'
  UNION ALL SELECT 'Enugu','Enugu' UNION ALL SELECT 'Enugu','Nsukka'
  UNION ALL SELECT 'Enugu','Agbani' UNION ALL SELECT 'Enugu','Awgu'
  UNION ALL SELECT 'Enugu','Udi' UNION ALL SELECT 'Enugu','Ninth Mile'
  UNION ALL SELECT 'Enugu','Oji River' UNION ALL SELECT 'Enugu','Emene'
  UNION ALL SELECT 'Enugu','Abakpa Nike' UNION ALL SELECT 'Enugu','Trans-Ekulu'
  UNION ALL SELECT 'Anambra','Onitsha' UNION ALL SELECT 'Anambra','Awka'
  UNION ALL SELECT 'Anambra','Nnewi' UNION ALL SELECT 'Anambra','Ekwulobia'
  UNION ALL SELECT 'Anambra','Ogidi' UNION ALL SELECT 'Anambra','Ihiala'
  UNION ALL SELECT 'Anambra','Abagana' UNION ALL SELECT 'Anambra','Neni'
  UNION ALL SELECT 'Anambra','Atani' UNION ALL SELECT 'Anambra','Otuocha'
  UNION ALL SELECT 'Imo','Owerri' UNION ALL SELECT 'Imo','Okigwe'
  UNION ALL SELECT 'Imo','Orlu' UNION ALL SELECT 'Imo','Mbaise'
  UNION ALL SELECT 'Imo','Ngor-Okpala' UNION ALL SELECT 'Imo','Oguta'
  UNION ALL SELECT 'Imo','Mbaitoli' UNION ALL SELECT 'Imo','Iho'
  UNION ALL SELECT 'Akwa Ibom','Uyo' UNION ALL SELECT 'Akwa Ibom','Eket'
  UNION ALL SELECT 'Akwa Ibom','Ikot Ekpene' UNION ALL SELECT 'Akwa Ibom','Oron'
  UNION ALL SELECT 'Akwa Ibom','Abak' UNION ALL SELECT 'Akwa Ibom','Etinan'
  UNION ALL SELECT 'Akwa Ibom','Ikot Abasi' UNION ALL SELECT 'Akwa Ibom','Oruk Anam'
  UNION ALL SELECT 'Cross River','Calabar' UNION ALL SELECT 'Cross River','Ikom'
  UNION ALL SELECT 'Cross River','Ogoja' UNION ALL SELECT 'Cross River','Obudu'
  UNION ALL SELECT 'Cross River','Ugep' UNION ALL SELECT 'Cross River','Odukpani'
  UNION ALL SELECT 'Cross River','Akamkpa' UNION ALL SELECT 'Cross River','Itigidi'
  UNION ALL SELECT 'Edo','Benin City' UNION ALL SELECT 'Edo','Auchi'
  UNION ALL SELECT 'Edo','Ekpoma' UNION ALL SELECT 'Edo','Uromi'
  UNION ALL SELECT 'Edo','Ubiaja' UNION ALL SELECT 'Edo','Igarra'
  UNION ALL SELECT 'Edo','Agenebode' UNION ALL SELECT 'Edo','Okada'
  UNION ALL SELECT 'Edo','Sabongida-Ora' UNION ALL SELECT 'Edo','Ewu'
  UNION ALL SELECT 'Delta','Warri' UNION ALL SELECT 'Delta','Asaba'
  UNION ALL SELECT 'Delta','Sapele' UNION ALL SELECT 'Delta','Ughelli'
  UNION ALL SELECT 'Delta','Agbor' UNION ALL SELECT 'Delta','Effurun'
  UNION ALL SELECT 'Delta','Oghara' UNION ALL SELECT 'Delta','Ozoro'
  UNION ALL SELECT 'Delta','Oleh' UNION ALL SELECT 'Delta','Kwale'
  UNION ALL SELECT 'Delta','Abraka' UNION ALL SELECT 'Delta','Burutu'
  UNION ALL SELECT 'Plateau','Jos' UNION ALL SELECT 'Plateau','Bukuru'
  UNION ALL SELECT 'Plateau','Barkin Ladi' UNION ALL SELECT 'Plateau','Pankshin'
  UNION ALL SELECT 'Plateau','Shendam' UNION ALL SELECT 'Plateau','Langtang'
  UNION ALL SELECT 'Plateau','Mangu' UNION ALL SELECT 'Plateau','Riyom'
  UNION ALL SELECT 'Plateau','Bokkos' UNION ALL SELECT 'Plateau','Vom'
  UNION ALL SELECT 'Borno','Maiduguri' UNION ALL SELECT 'Borno','Bama'
  UNION ALL SELECT 'Borno','Biu' UNION ALL SELECT 'Borno','Monguno'
  UNION ALL SELECT 'Borno','Gwoza' UNION ALL SELECT 'Borno','Dikwa'
  UNION ALL SELECT 'Borno','Konduga' UNION ALL SELECT 'Borno','Mafa'
  UNION ALL SELECT 'Borno','Damboa' UNION ALL SELECT 'Borno','Chibok'
  UNION ALL SELECT 'Sokoto','Sokoto' UNION ALL SELECT 'Sokoto','Wurno'
  UNION ALL SELECT 'Sokoto','Tambuwal' UNION ALL SELECT 'Sokoto','Illela'
  UNION ALL SELECT 'Sokoto','Gwadabawa' UNION ALL SELECT 'Sokoto','Binji'
  UNION ALL SELECT 'Sokoto','Gada' UNION ALL SELECT 'Sokoto','Yabo'
  UNION ALL SELECT 'Kwara','Ilorin' UNION ALL SELECT 'Kwara','Offa'
  UNION ALL SELECT 'Kwara','Omu-Aran' UNION ALL SELECT 'Kwara','Jebba'
  UNION ALL SELECT 'Kwara','Patigi' UNION ALL SELECT 'Kwara','Lafiagi'
  UNION ALL SELECT 'Kwara','Kaiama' UNION ALL SELECT 'Kwara','Share'
  UNION ALL SELECT 'Ondo','Akure' UNION ALL SELECT 'Ondo','Ondo'
  UNION ALL SELECT 'Ondo','Owo' UNION ALL SELECT 'Ondo','Ikare'
  UNION ALL SELECT 'Ondo','Okitipupa' UNION ALL SELECT 'Ondo','Ore'
  UNION ALL SELECT 'Ondo','Ile-Oluji' UNION ALL SELECT 'Ondo','Idanre'
  UNION ALL SELECT 'Ondo','Oka' UNION ALL SELECT 'Ondo','Igbara-Oke'
  UNION ALL SELECT 'Ekiti','Ado-Ekiti' UNION ALL SELECT 'Ekiti','Ikere-Ekiti'
  UNION ALL SELECT 'Ekiti','Ikole-Ekiti' UNION ALL SELECT 'Ekiti','Oye-Ekiti'
  UNION ALL SELECT 'Ekiti','Iyin-Ekiti' UNION ALL SELECT 'Ekiti','Ise-Ekiti'
  UNION ALL SELECT 'Ekiti','Efon-Alaaye' UNION ALL SELECT 'Ekiti','Emure-Ekiti'
  UNION ALL SELECT 'Osun','Osogbo' UNION ALL SELECT 'Osun','Ilesa'
  UNION ALL SELECT 'Osun','Ile-Ife' UNION ALL SELECT 'Osun','Iwo'
  UNION ALL SELECT 'Osun','Ede' UNION ALL SELECT 'Osun','Ikire'
  UNION ALL SELECT 'Osun','Ila Orangun' UNION ALL SELECT 'Osun','Ejigbo'
  UNION ALL SELECT 'Osun','Ifon' UNION ALL SELECT 'Osun','Modakeke'
  UNION ALL SELECT 'Bauchi','Bauchi' UNION ALL SELECT 'Bauchi','Azare'
  UNION ALL SELECT 'Bauchi','Misau' UNION ALL SELECT 'Bauchi','Jama''are'
  UNION ALL SELECT 'Bauchi','Dass' UNION ALL SELECT 'Bauchi','Ningi'
  UNION ALL SELECT 'Bauchi','Toro' UNION ALL SELECT 'Bauchi','Bogoro'
  UNION ALL SELECT 'Bauchi','Darazo' UNION ALL SELECT 'Bauchi','Gamawa'
  UNION ALL SELECT 'Adamawa','Yola' UNION ALL SELECT 'Adamawa','Jimeta'
  UNION ALL SELECT 'Adamawa','Numan' UNION ALL SELECT 'Adamawa','Mubi'
  UNION ALL SELECT 'Adamawa','Ganye' UNION ALL SELECT 'Adamawa','Michika'
  UNION ALL SELECT 'Adamawa','Gombi' UNION ALL SELECT 'Adamawa','Hong'
  UNION ALL SELECT 'Adamawa','Song' UNION ALL SELECT 'Adamawa','Fufore'
  UNION ALL SELECT 'Abia','Aba' UNION ALL SELECT 'Abia','Umuahia'
  UNION ALL SELECT 'Abia','Ohafia' UNION ALL SELECT 'Abia','Arochukwu'
  UNION ALL SELECT 'Abia','Bende' UNION ALL SELECT 'Abia','Isiala Ngwa'
  UNION ALL SELECT 'Abia','Osisioma' UNION ALL SELECT 'Abia','Mbawsi'
  UNION ALL SELECT 'Bayelsa','Yenagoa' UNION ALL SELECT 'Bayelsa','Brass'
  UNION ALL SELECT 'Bayelsa','Ogbia' UNION ALL SELECT 'Bayelsa','Sagbama'
  UNION ALL SELECT 'Bayelsa','Ekeremor' UNION ALL SELECT 'Bayelsa','Nembe'
  UNION ALL SELECT 'Bayelsa','Amassoma' UNION ALL SELECT 'Bayelsa','Odi'
  UNION ALL SELECT 'Benue','Makurdi' UNION ALL SELECT 'Benue','Gboko'
  UNION ALL SELECT 'Benue','Otukpo' UNION ALL SELECT 'Benue','Katsina-Ala'
  UNION ALL SELECT 'Benue','Vandeikya' UNION ALL SELECT 'Benue','Adikpo'
  UNION ALL SELECT 'Benue','Naka' UNION ALL SELECT 'Benue','Aliade'
  UNION ALL SELECT 'Ebonyi','Abakaliki' UNION ALL SELECT 'Ebonyi','Afikpo'
  UNION ALL SELECT 'Ebonyi','Onueke' UNION ALL SELECT 'Ebonyi','Ishiagu'
  UNION ALL SELECT 'Ebonyi','Uburu' UNION ALL SELECT 'Ebonyi','Ikwo'
  UNION ALL SELECT 'Ebonyi','Ezza' UNION ALL SELECT 'Ebonyi','Unwana'
  UNION ALL SELECT 'Gombe','Gombe' UNION ALL SELECT 'Gombe','Kumo'
  UNION ALL SELECT 'Gombe','Dukku' UNION ALL SELECT 'Gombe','Billiri'
  UNION ALL SELECT 'Gombe','Kaltungo' UNION ALL SELECT 'Gombe','Bajoga'
  UNION ALL SELECT 'Gombe','Deba' UNION ALL SELECT 'Gombe','Nafada'
  UNION ALL SELECT 'Jigawa','Dutse' UNION ALL SELECT 'Jigawa','Hadejia'
  UNION ALL SELECT 'Jigawa','Gumel' UNION ALL SELECT 'Jigawa','Kazaure'
  UNION ALL SELECT 'Jigawa','Birnin Kudu' UNION ALL SELECT 'Jigawa','Babura'
  UNION ALL SELECT 'Jigawa','Ringim' UNION ALL SELECT 'Jigawa','Kafin Hausa'
  UNION ALL SELECT 'Katsina','Katsina' UNION ALL SELECT 'Katsina','Daura'
  UNION ALL SELECT 'Katsina','Funtua' UNION ALL SELECT 'Katsina','Malumfashi'
  UNION ALL SELECT 'Katsina','Dutsin-Ma' UNION ALL SELECT 'Katsina','Kankia'
  UNION ALL SELECT 'Katsina','Jibia' UNION ALL SELECT 'Katsina','Bakori'
  UNION ALL SELECT 'Katsina','Danja' UNION ALL SELECT 'Katsina','Kankara'
  UNION ALL SELECT 'Kebbi','Birnin Kebbi' UNION ALL SELECT 'Kebbi','Argungu'
  UNION ALL SELECT 'Kebbi','Yauri' UNION ALL SELECT 'Kebbi','Zuru'
  UNION ALL SELECT 'Kebbi','Jega' UNION ALL SELECT 'Kebbi','Kamba'
  UNION ALL SELECT 'Kebbi','Bunza' UNION ALL SELECT 'Kebbi','Bagudo'
  UNION ALL SELECT 'Kogi','Lokoja' UNION ALL SELECT 'Kogi','Okene'
  UNION ALL SELECT 'Kogi','Kabba' UNION ALL SELECT 'Kogi','Idah'
  UNION ALL SELECT 'Kogi','Ankpa' UNION ALL SELECT 'Kogi','Anyigba'
  UNION ALL SELECT 'Kogi','Dekina' UNION ALL SELECT 'Kogi','Ajaokuta'
  UNION ALL SELECT 'Nasarawa','Lafia' UNION ALL SELECT 'Nasarawa','Keffi'
  UNION ALL SELECT 'Nasarawa','Akwanga' UNION ALL SELECT 'Nasarawa','Nasarawa'
  UNION ALL SELECT 'Nasarawa','Karu' UNION ALL SELECT 'Nasarawa','Doma'
  UNION ALL SELECT 'Nasarawa','Toto' UNION ALL SELECT 'Nasarawa','Eggon'
  UNION ALL SELECT 'Niger','Minna' UNION ALL SELECT 'Niger','Bida'
  UNION ALL SELECT 'Niger','Suleja' UNION ALL SELECT 'Niger','Kontagora'
  UNION ALL SELECT 'Niger','New Bussa' UNION ALL SELECT 'Niger','Mokwa'
  UNION ALL SELECT 'Niger','Lapai' UNION ALL SELECT 'Niger','Agaie'
  UNION ALL SELECT 'Niger','Zungeru' UNION ALL SELECT 'Niger','Tegina'
  UNION ALL SELECT 'Taraba','Jalingo' UNION ALL SELECT 'Taraba','Wukari'
  UNION ALL SELECT 'Taraba','Bali' UNION ALL SELECT 'Taraba','Takum'
  UNION ALL SELECT 'Taraba','Gembu' UNION ALL SELECT 'Taraba','Zing'
  UNION ALL SELECT 'Taraba','Ibi' UNION ALL SELECT 'Taraba','Mutum Biyu'
  UNION ALL SELECT 'Yobe','Damaturu' UNION ALL SELECT 'Yobe','Potiskum'
  UNION ALL SELECT 'Yobe','Gashua' UNION ALL SELECT 'Yobe','Nguru'
  UNION ALL SELECT 'Yobe','Geidam' UNION ALL SELECT 'Yobe','Buni Yadi'
  UNION ALL SELECT 'Yobe','Damagum' UNION ALL SELECT 'Yobe','Fika'
  UNION ALL SELECT 'Zamfara','Gusau' UNION ALL SELECT 'Zamfara','Kaura Namoda'
  UNION ALL SELECT 'Zamfara','Talata Mafara' UNION ALL SELECT 'Zamfara','Anka'
  UNION ALL SELECT 'Zamfara','Bungudu' UNION ALL SELECT 'Zamfara','Tsafe'
  UNION ALL SELECT 'Zamfara','Shinkafi' UNION ALL SELECT 'Zamfara','Maru'
) v ON v.s = st.state AND st.country = 'Nigeria'
WHERE NOT EXISTS (
  SELECT 1 FROM db_cities c WHERE c.state_id = st.id AND c.city = v.c
);
