-- Repair Lagos using names and generated IDs; retain existing state/customer references.
INSERT INTO db_states (store_id,state_code,state,country_code,country_id,country,added_on,status)
SELECT s.id,'NG0001','Lagos','NG',n.id,'Nigeria',CURDATE(),1
FROM db_store s JOIN (SELECT MIN(id) id FROM db_country WHERE UPPER(TRIM(country))='NIGERIA') n ON n.id IS NOT NULL
WHERE NOT EXISTS (SELECT 1 FROM db_states st WHERE st.store_id=s.id AND UPPER(TRIM(st.state))='LAGOS' AND UPPER(TRIM(st.country))='NIGERIA');
UPDATE db_states st JOIN (SELECT MIN(id) id FROM db_country WHERE UPPER(TRIM(country))='NIGERIA') n ON n.id IS NOT NULL
SET st.country_id=n.id,st.country_code='NG'
WHERE UPPER(TRIM(st.country))='NIGERIA' AND st.country_id IS NULL;
INSERT INTO db_cities (city,state_id,status,store_id)
SELECT v.city,st.id,1,st.store_id FROM db_states st
JOIN (
SELECT 'Lagos' city
 UNION ALL SELECT 'Ikeja' city
 UNION ALL SELECT 'Surulere' city
 UNION ALL SELECT 'Victoria Island' city
 UNION ALL SELECT 'Ikoyi' city
 UNION ALL SELECT 'Lekki' city
 UNION ALL SELECT 'Ajah' city
 UNION ALL SELECT 'Sangotedo' city
 UNION ALL SELECT 'Ibeju-Lekki' city
 UNION ALL SELECT 'Epe' city
 UNION ALL SELECT 'Badagry' city
 UNION ALL SELECT 'Ikorodu' city
 UNION ALL SELECT 'Ebute Ikorodu' city
 UNION ALL SELECT 'Apapa' city
 UNION ALL SELECT 'Tin Can Island' city
 UNION ALL SELECT 'Festac Town' city
 UNION ALL SELECT 'Satellite Town' city
 UNION ALL SELECT 'Amuwo-Odofin' city
 UNION ALL SELECT 'Mile 2' city
 UNION ALL SELECT 'Oshodi' city
 UNION ALL SELECT 'Isolo' city
 UNION ALL SELECT 'Ejigbo' city
 UNION ALL SELECT 'Okota' city
 UNION ALL SELECT 'Ago Palace' city
 UNION ALL SELECT 'Mushin' city
 UNION ALL SELECT 'Agege' city
 UNION ALL SELECT 'Alimosho' city
 UNION ALL SELECT 'Ikotun' city
 UNION ALL SELECT 'Egbeda' city
 UNION ALL SELECT 'Ipaja' city
 UNION ALL SELECT 'Iyana-Ipaja' city
 UNION ALL SELECT 'Ogba' city
 UNION ALL SELECT 'Ojodu Berger' city
 UNION ALL SELECT 'Ojota' city
 UNION ALL SELECT 'Ketu' city
 UNION ALL SELECT 'Magodo' city
 UNION ALL SELECT 'Maryland' city
 UNION ALL SELECT 'Ilupeju' city
 UNION ALL SELECT 'Anthony' city
 UNION ALL SELECT 'Gbagada' city
 UNION ALL SELECT 'Ogudu' city
 UNION ALL SELECT 'Bariga' city
 UNION ALL SELECT 'Shomolu' city
 UNION ALL SELECT 'Yaba' city
 UNION ALL SELECT 'Ebute Metta' city
 UNION ALL SELECT 'Ijesha' city
 UNION ALL SELECT 'Ojo' city
 UNION ALL SELECT 'Ajegunle' city
 UNION ALL SELECT 'Orile' city
 UNION ALL SELECT 'Obalende' city
 UNION ALL SELECT 'Isheri' city
) v ON UPPER(TRIM(st.state))='LAGOS' AND UPPER(TRIM(st.country))='NIGERIA'
WHERE NOT EXISTS (SELECT 1 FROM db_cities c WHERE c.state_id=st.id AND UPPER(TRIM(c.city))=UPPER(v.city));
