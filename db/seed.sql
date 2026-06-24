USE lis_gateway;

-- Labs
INSERT INTO labs (name, ahfoz) VALUES
    ('Downtown Medical Center',      'AH 24/098/B'),
    ('St. Jude Clinical Laboratory', 'JH 25/714/S'),
    ('Apex Diagnostic Partners',     'AP 23/110/A'),
    ('Valley Health LIS',            'VH 26/030/V');

-- Users
-- Passwords: admin123 / password123 — hashed with PASSWORD_BCRYPT (cost 12)
INSERT INTO users (id, name, role, lab_name, password_hash) VALUES
    ('admin-01',     'Dr. Andrew Chen', 'Administrator', NULL,
     '$2y$12$yPGFCuOfAFUjfBLJ8GFXeei6OpT.OPWninwXfDmyA4Ggg8FawkFJi'),
    ('scientist-01', 'S. Sibanda',      'Lab Scientist', 'Downtown Medical Center',
     '$2y$12$bNLnvQQdkNBx4wabqHDgpOjuLJawbCO20G0bGsarYEo.u2iYa/bY2'),
    ('scientist-02', 'M. Moyo',         'Lab Scientist', 'St. Jude Clinical Laboratory',
     '$2y$12$bNLnvQQdkNBx4wabqHDgpOjuLJawbCO20G0bGsarYEo.u2iYa/bY2');

-- Pending records
INSERT INTO records
    (accession_id, patient_name, dob, test_type, lab_name, status, date_time, ordering_physician, scientist_notes)
VALUES
    ('ACC-2026-0041', 'Oliver Mwanga',  '1991-04-12', 'Full Blood Count',  'Downtown Medical Center',      'Urgent',   '2026-06-23 08:14:00', 'Dr. E. Thompson', 'Slightly elevated WBC counts observed. Red blood platelet distribution indices normal.'),
    ('ACC-2026-0042', 'Sarah Ndlovu',   '1984-11-03', 'Lipid Profile',     'St. Jude Clinical Laboratory', 'Critical', '2026-06-23 08:30:00', 'Dr. T. Sibanda',  'Critical total cholesterol concentration logged.'),
    ('ACC-2026-0043', 'Tinashe Mariga', '1976-07-22', 'Renal Function',    'Apex Diagnostic Partners',     'Routine',  '2026-06-23 09:15:00', 'Dr. A. Mutasa',   'Urea and serum creatinine parameters are within standard baseline thresholds.');

-- Completed records
INSERT INTO records
    (accession_id, patient_name, dob, test_type, lab_name, status, date_time, scientist_notes, authorized_scientist, authorized_time)
VALUES
    ('ACC-2026-0038', 'Farai Gumbo',    '1998-09-17', 'Glycated Haemoglobin (HbA1c)', 'Apex Diagnostic Partners', 'Approved', '2026-06-22 15:40:00',
     'Glycated hemoglobin indicates stable glycemic management profile (Pre-diabetic threshold). No panic alerts requested.',
     'Dr. Andrew Chen', '4:10 PM'),
    ('ACC-2026-0039', 'Kudzanai Zhou',  '1965-02-28', 'Liver Panel',                  'Valley Health LIS',        'Rejected', '2026-06-22 14:15:00',
     'Sample Hemolyzed. Requested recollect action sequence from nursing workstation.',
     'Dr. S. Moyo', '2:45 PM');

-- Record parameters
INSERT INTO record_parameters (record_id, name, result, reference_range, flag) VALUES
    -- ACC-2026-0041 (Full Blood Count)
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0041'), 'White Blood Cell (WBC)',  '11.8 x10^9/L',  '4.5 - 11.0',  'High'),
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0041'), 'Red Blood Cell (RBC)',    '4.85 x10^12/L', '4.30 - 5.90', 'Normal'),
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0041'), 'Haemoglobin',             '14.2 g/dL',     '13.5 - 17.5', 'Normal'),

    -- ACC-2026-0042 (Lipid Profile)
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0042'), 'Total Cholesterol', '6.8 mmol/L', '< 5.2', 'Critically High'),
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0042'), 'HDL Cholesterol',   '0.9 mmol/L', '> 1.0', 'Critically Low'),

    -- ACC-2026-0043 (Renal Function)
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0043'), 'Serum Creatinine',    '82 umol/L',  '60 - 110', 'Normal'),
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0043'), 'Blood Urea Nitrogen', '4.1 mmol/L', '2.5 - 7.1','Normal'),

    -- ACC-2026-0038 (HbA1c)
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0038'), 'HbA1c Concentration', '5.9 %', '4.0 - 5.6', 'High'),

    -- ACC-2026-0039 (Liver Panel)
    ((SELECT id FROM records WHERE accession_id = 'ACC-2026-0039'), 'Alanine Aminotransferase (ALT)', '280 U/L', '7 - 56', 'Critically High');
