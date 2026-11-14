-- MartPoint Physio — approved five-page assessment form as a reusable template.
-- Platform scope (store_id=0) makes it available to every physiotherapy store.
-- Existing assessments keep their captured template version; new assessments
-- resolve to this v2 template through the normal versioned template registry.
CREATE TABLE IF NOT EXISTS `db_assessment_template_settings` (
  `store_id` INT(11) NOT NULL,
  `template_key` VARCHAR(60) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_by` INT(11) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`store_id`,`template_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `db_assessment_templates`
  (`store_id`,`template_key`,`name`,`version`,`status`,`provisional`,`sections_json`,`created_by`,`created_at`)
SELECT 0,'physio_initial','Physiotherapy Assessment',2,'active',0,
'[{"key":"administration","title":"Assessment details","fields":[
  {"key":"physiotherapist_name","label":"Name of physiotherapist","type":"text","required":true},
  {"key":"position","label":"Position","type":"text","required":false},
  {"key":"assessment_date","label":"Assessment date","type":"text","required":true},
  {"key":"signature_note","label":"Signature / professional note","type":"textarea","required":false}
]},
{"key":"subjective","title":"Subjective evaluation","fields":[
  {"key":"onset","label":"Details of onset","type":"textarea","required":true},
  {"key":"symptoms_location","label":"Symptoms and location","type":"textarea","required":true},
  {"key":"lifestyle_effect","label":"Effect on lifestyle","type":"textarea","required":false},
  {"key":"treatment_so_far","label":"Treatment so far","type":"textarea","required":false},
  {"key":"treatment_effect","label":"Effect of treatment","type":"textarea","required":false},
  {"key":"occupational_demands","label":"Occupational demands","type":"textarea","required":false},
  {"key":"family_social_history","label":"Family and social history","type":"textarea","required":false},
  {"key":"accommodation_social_habits","label":"Accommodation, living condition and social habits","type":"textarea","required":false},
  {"key":"serious_illness_admission","label":"Serious illness and admission","type":"textarea","required":false},
  {"key":"operations_conditions","label":"Surgical operations and prevailing conditions","type":"textarea","required":false},
  {"key":"medications","label":"Current medications and treatment","type":"textarea","required":false},
  {"key":"previous_physiotherapy","label":"Previous physiotherapy","type":"textarea","required":false}
]},
{"key":"objective","title":"Objective evaluation","fields":[
  {"key":"general_observation","label":"General observation: appearance, mental state, communication, ambulation and posture","type":"textarea","required":false},
  {"key":"local_observation","label":"Local observation: skin, contour, alignment, deformity, swelling and respiratory movement","type":"textarea","required":false},
  {"key":"active_rom_upper_limb","label":"Active range of movement / upper limb assessment","type":"textarea","required":false},
  {"key":"resting_position","label":"Resting position","type":"textarea","required":false},
  {"key":"palpation","label":"Palpation: muscle tone, tenderness, swelling and skin texture","type":"textarea","required":false}
]},
{"key":"measurements","title":"Important medical assessments, tests and measurements","fields":[
  {"key":"medical_assessment_information","label":"Important information from medical assessments","type":"textarea","required":false},
  {"key":"blood_pressure","label":"Blood pressure","type":"text","required":false},
  {"key":"height","label":"Height (cm)","type":"text","required":false},
  {"key":"weight","label":"Weight (kg)","type":"text","required":false},
  {"key":"bmi","label":"BMI result and category","type":"text","required":false},
  {"key":"ideal_weight_range","label":"Ideal weight range (kg)","type":"text","required":false},
  {"key":"imaging_reports","label":"X-ray, MRI, CT scan and other reports","type":"textarea","required":false},
  {"key":"laboratory_results","label":"Laboratory results","type":"textarea","required":false},
  {"key":"rom_measurements","label":"Range of joint movement measurements","type":"textarea","required":false},
  {"key":"muscle_power","label":"Assessment of muscle power","type":"textarea","required":false},
  {"key":"other_tests","label":"Other tests","type":"textarea","required":false}
]},
{"key":"function","title":"Functional assessment","fields":[
  {"key":"adl_with_assistance","label":"ADL with assistance: sitting, standing, walking, dressing, stairs and feeding","type":"textarea","required":false},
  {"key":"adl_without_assistance","label":"ADL without assistance: sitting, standing, walking, dressing, stairs and feeding","type":"textarea","required":false},
  {"key":"occupational_postures","label":"Occupational postures","type":"textarea","required":false},
  {"key":"sensory_tests","label":"Sensory tests","type":"textarea","required":false},
  {"key":"respiratory_circumference","label":"Respiratory circumference","type":"textarea","required":false},
  {"key":"limb_length_circumference","label":"Length and circumference of limb","type":"textarea","required":false},
  {"key":"deformity_other","label":"Deformity and other findings","type":"textarea","required":false}
]},
{"key":"plan","title":"Interpretation of findings and treatment plan","fields":[
  {"key":"clinical_problems","label":"Clinical problems or physiotherapy diagnosis in priority order","type":"textarea","required":true},
  {"key":"impression","label":"Impression","type":"textarea","required":true},
  {"key":"principles_treatment","label":"Principles of treatment","type":"textarea","required":false},
  {"key":"short_term_goals","label":"Short-term goals","type":"textarea","required":false},
  {"key":"long_term_goals","label":"Long-term goals","type":"textarea","required":false},
  {"key":"means_treatment","label":"Means of treatment, facilities, pathology stage and precautions","type":"textarea","required":false},
  {"key":"treatment_plan_reassessment","label":"Treatment plan including reassessments","type":"textarea","required":true},
  {"key":"recommendations","label":"Recommendations","type":"textarea","required":false}
]}]',
'system',NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `db_assessment_templates`
  WHERE `store_id`=0 AND `template_key`='physio_initial' AND `version`=2
);
