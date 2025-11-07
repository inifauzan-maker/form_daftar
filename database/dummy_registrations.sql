-- Dummy data for registrants from Jawa Barat & DKI Jakarta
-- Adjust the program codes below so they match your table `programs`.
-- Run: mysql -u root -p si.vmi_pendaftaran < database/dummy_registrations.sql

INSERT INTO registrations (
    full_name,
    school_id,
    school_name,
    class_level,
    phone_number,
    province,
    city,
    district,
    subdistrict,
    postal_code,
    address_detail,
    program_id,
    student_status,
    payment_status,
    payment_notes,
    program_fee,
    registration_fee,
    discount_amount,
    total_due,
    amount_paid,
    balance_due,
    last_payment_at,
    study_location,
    registration_number,
    invoice_number
)
SELECT
    'Nadira Salsabila',
    NULL,
    'SMA Negeri 3 Bandung',
    '12',
    '6281234500010',
    'Jawa Barat',
    'Bandung',
    'Coblong',
    'Dago',
    '40135',
    'Jl. Ir. H. Djuanda No. 100',
    p.id,
    'active',
    'paid',
    'Lunas via transfer',
    9000000,
    500000,
    500000,
    9000000,
    9000000,
    0,
    '2024-09-10',
    'Bandung',
    '2425-11B001',
    '2425-11B001'
FROM programs p
WHERE p.code = 'REG-SMA';

INSERT INTO registrations (
    full_name, school_id, school_name, class_level, phone_number,
    province, city, district, subdistrict, postal_code, address_detail,
    program_id, student_status, payment_status, payment_notes,
    program_fee, registration_fee, discount_amount, total_due,
    amount_paid, balance_due, last_payment_at, study_location,
    registration_number, invoice_number
)
SELECT
    'Rafli Dharmawan',
    NULL,
    'SMP Negeri 5 Cimahi',
    '9',
    '6281234500011',
    'Jawa Barat',
    'Cimahi',
    'Cimahi Tengah',
    'Baros',
    '40521',
    'Gg. Cempaka 12',
    p.id,
    'active',
    'partial',
    'Cicilan ke-2 (3x)',
    6500000,
    400000,
    0,
    6900000,
    3500000,
    3400000,
    '2024-10-05',
    'Bandung',
    '2425-11B002',
    '2425-11B002'
FROM programs p
WHERE p.code = 'REG-SMP';

INSERT INTO registrations (
    full_name, school_id, school_name, class_level, phone_number,
    province, city, district, subdistrict, postal_code, address_detail,
    program_id, student_status, payment_status, payment_notes,
    program_fee, registration_fee, discount_amount, total_due,
    amount_paid, balance_due, last_payment_at, study_location,
    registration_number, invoice_number
)
SELECT
    'Yunita Pramesti',
    NULL,
    'SMK Negeri 6 Bandung',
    '12',
    '6281234500012',
    'Jawa Barat',
    'Bandung',
    'Lengkong',
    'Cijagra',
    '40265',
    'Jl. Buah Batu No. 240',
    p.id,
    'active',
    'paid',
    'Program UTBK kelas akhir',
    11000000,
    500000,
    1000000,
    10500000,
    10500000,
    0,
    '2024-08-25',
    'Bandung',
    '2425-11B003',
    '2425-11B003'
FROM programs p
WHERE p.code = 'UTBK-INT';

-- DKI Jakarta (paid & partial mix)
INSERT INTO registrations (
    full_name, school_id, school_name, class_level, phone_number,
    province, city, district, subdistrict, postal_code, address_detail,
    program_id, student_status, payment_status, payment_notes,
    program_fee, registration_fee, discount_amount, total_due,
    amount_paid, balance_due, last_payment_at, study_location,
    registration_number, invoice_number
)
SELECT
    'Hafidz Ramadhan',
    NULL,
    'SMA Negeri 6 Jakarta',
    '11',
    '6281234500013',
    'DKI Jakarta',
    'Jakarta Selatan',
    'Kebayoran Baru',
    'Senayan',
    '12190',
    'Jl. Asia Afrika No. 8',
    p.id,
    'active',
    'partial',
    'Cicilan ke-1 (4x)',
    9500000,
    500000,
    0,
    10000000,
    2500000,
    7500000,
    '2024-09-30',
    'Jaksel',
    '2425-21J001',
    '2425-21J001'
FROM programs p
WHERE p.code = 'SOSHUM-XI';

INSERT INTO registrations (
    full_name, school_id, school_name, class_level, phone_number,
    province, city, district, subdistrict, postal_code, address_detail,
    program_id, student_status, payment_status, payment_notes,
    program_fee, registration_fee, discount_amount, total_due,
    amount_paid, balance_due, last_payment_at, study_location,
    registration_number, invoice_number
)
SELECT
    'Samuel Halomoan',
    NULL,
    'SMP Negeri 115 Jakarta',
    '9',
    '6281234500014',
    'DKI Jakarta',
    'Jakarta Timur',
    'Cakung',
    'Penggilingan',
    '13940',
    'Jl. Penggilingan Raya No. 45',
    p.id,
    'active',
    'paid',
    'Lunas via auto debit',
    7200000,
    350000,
    0,
    7550000,
    7550000,
    0,
    '2024-07-18',
    'Jaktim',
    '2425-31J001',
    '2425-31J001'
FROM programs p
WHERE p.code = 'REG-SMP';

INSERT INTO registrations (
    full_name, school_id, school_name, class_level, phone_number,
    province, city, district, subdistrict, postal_code, address_detail,
    program_id, student_status, payment_status, payment_notes,
    program_fee, registration_fee, discount_amount, total_due,
    amount_paid, balance_due, last_payment_at, study_location,
    registration_number, invoice_number
)
SELECT
    'Karina Aprilia',
    NULL,
    'SMA Labschool Kebayoran',
    '12',
    '6281234500015',
    'DKI Jakarta',
    'Jakarta Selatan',
    'Kebayoran Lama',
    'Grogol Utara',
    '12210',
    'Jl. Ciputat Raya No. 60',
    p.id,
    'active',
    'partial',
    'Potongan beasiswa 15%',
    13000000,
    750000,
    2000000,
    11750000,
    8000000,
    3750000,
    '2024-10-01',
    'Jaksel',
    '2425-21J002',
    '2425-21J002'
FROM programs p
WHERE p.code = 'SAINTEK-XII';

-- Feel free to duplicate the patterns above for additional dummy rows.
