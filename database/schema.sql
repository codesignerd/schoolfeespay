CREATE DATABASE IF NOT EXISTS schoolfeespay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE schoolfeespay;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS exam_results;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS fee_payments;
DROP TABLE IF EXISTS fee_invoices;
DROP TABLE IF EXISTS attendance_records;
DROP TABLE IF EXISTS teacher_subjects;
DROP TABLE IF EXISTS student_parent;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS parents;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS class_streams;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS terms;
DROP TABLE IF EXISTS academic_years;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    module VARCHAR(80) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(40) NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    two_factor_secret VARCHAR(255) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role_status (role_id, status),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE academic_years (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL UNIQUE,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE terms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(40) NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_terms_year_name (academic_year_id, name),
    CONSTRAINT fk_terms_year FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB;

CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    code VARCHAR(30) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE class_streams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    stream VARCHAR(40) NOT NULL,
    class_teacher_id BIGINT UNSIGNED NULL,
    capacity INT UNSIGNED NOT NULL DEFAULT 45,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_class_stream (name, stream)
) ENGINE=InnoDB;

CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id BIGINT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(40) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_subjects_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id BIGINT UNSIGNED NULL,
    staff_no VARCHAR(60) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(40) NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    qualification VARCHAR(190) NULL,
    hire_date DATE NULL,
    status ENUM('active', 'leave', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_teachers_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE class_streams
    ADD CONSTRAINT fk_class_streams_teacher FOREIGN KEY (class_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL;

CREATE TABLE parents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    relationship VARCHAR(60) NOT NULL DEFAULT 'Guardian',
    email VARCHAR(190) NULL,
    phone VARCHAR(40) NOT NULL,
    address VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_parents_phone (phone)
) ENGINE=InnoDB;

CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL UNIQUE,
    class_stream_id BIGINT UNSIGNED NULL,
    department_id BIGINT UNSIGNED NULL,
    admission_no VARCHAR(60) NULL UNIQUE,
    matriculation_no VARCHAR(60) NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    other_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(40) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    nationality VARCHAR(80) NULL,
    religion VARCHAR(80) NULL,
    programme VARCHAR(120) NULL,
    level VARCHAR(50) NULL,
    academic_session VARCHAR(20) NULL,
    medical_notes TEXT NULL,
    emergency_contact VARCHAR(120) NULL,
    previous_school VARCHAR(160) NULL,
    status ENUM('pending', 'active', 'rejected', 'transferred', 'graduated', 'suspended', 'inactive') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_students_class_status (class_stream_id, status),
    CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_students_class FOREIGN KEY (class_stream_id) REFERENCES class_streams(id) ON DELETE SET NULL,
    CONSTRAINT fk_students_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE student_parent (
    student_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (student_id, parent_id),
    CONSTRAINT fk_student_parent_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_student_parent_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE teacher_subjects (
    teacher_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    class_stream_id BIGINT UNSIGNED NULL,
    PRIMARY KEY (teacher_id, subject_id, class_stream_id),
    CONSTRAINT fk_teacher_subjects_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_subjects_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_subjects_class FOREIGN KEY (class_stream_id) REFERENCES class_streams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    class_stream_id BIGINT UNSIGNED NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    remarks VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_attendance_student_date (student_id, attendance_date),
    INDEX idx_attendance_date_class (attendance_date, class_stream_id),
    CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_class FOREIGN KEY (class_stream_id) REFERENCES class_streams(id) ON DELETE SET NULL,
    CONSTRAINT fk_attendance_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE fee_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NULL,
    invoice_no VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(190) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    balance DECIMAL(12,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('draft', 'issued', 'part_paid', 'paid', 'overdue', 'void') NOT NULL DEFAULT 'issued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fee_invoices_student_status (student_id, status),
    CONSTRAINT fk_fee_invoices_student FOREIGN KEY (student_id) REFERENCES students(id),
    CONSTRAINT fk_fee_invoices_term FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE fee_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    receipt_no VARCHAR(80) NOT NULL UNIQUE,
    amount_paid DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'bank', 'card') NOT NULL,
    payment_reference VARCHAR(120) NULL,
    payment_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    received_by BIGINT UNSIGNED NULL,
    verified_by BIGINT UNSIGNED NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    INDEX idx_fee_payments_invoice (invoice_id),
    CONSTRAINT fk_fee_payments_invoice FOREIGN KEY (invoice_id) REFERENCES fee_invoices(id),
    CONSTRAINT fk_fee_payments_user FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_fee_payments_verified_user FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE exams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    term_id BIGINT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    exam_type ENUM('cat', 'midterm', 'endterm', 'mock', 'national') NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    status ENUM('draft', 'open', 'closed', 'published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_exams_term FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE exam_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    marks DECIMAL(5,2) NOT NULL,
    grade VARCHAR(4) NOT NULL,
    remarks VARCHAR(160) NULL,
    entered_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_exam_result (exam_id, student_id, subject_id),
    INDEX idx_exam_results_student (student_id),
    CONSTRAINT fk_exam_results_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_results_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_results_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
    CONSTRAINT fk_exam_results_user FOREIGN KEY (entered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity VARCHAR(80) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_logs_user_created (user_id, created_at),
    INDEX idx_audit_logs_entity (entity, entity_id),
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO roles (name, slug, description) VALUES
('Super Admin', 'super-admin', 'Full platform owner'),
('School Administrator', 'school-administrator', 'Daily school administration'),
('Principal', 'principal', 'Academic and institutional leadership'),
('Deputy Principal', 'deputy-principal', 'Deputy leadership'),
('Bursar', 'bursar', 'Fee and finance operations'),
('Accountant', 'accountant', 'Accounting and reporting'),
('Teacher', 'teacher', 'Teacher portal user'),
('Class Teacher', 'class-teacher', 'Class-level management'),
('Student', 'student', 'Student portal user'),
('Parent', 'parent', 'Parent portal user'),
('Librarian', 'librarian', 'Library operations'),
('Nurse', 'nurse', 'Clinic and medical records'),
('Transport Manager', 'transport-manager', 'Transport operations'),
('Hostel Manager', 'hostel-manager', 'Boarding and hostel operations'),
('Security Officer', 'security-officer', 'Gate and security records'),
('Store Keeper', 'store-keeper', 'Inventory store operations'),
('Admissions Officer', 'admissions-officer', 'Admissions workflow');

INSERT INTO permissions (name, slug, module) VALUES
('View Dashboard', 'dashboard.view', 'dashboard'),
('View Users', 'users.view', 'users'),
('Manage Users', 'users.manage', 'users'),
('View Students', 'students.view', 'students'),
('Manage Students', 'students.manage', 'students'),
('View Teachers', 'teachers.view', 'teachers'),
('Manage Teachers', 'teachers.manage', 'teachers'),
('View Classes', 'classes.view', 'classes'),
('Manage Classes', 'classes.manage', 'classes'),
('View Attendance', 'attendance.view', 'attendance'),
('Manage Attendance', 'attendance.manage', 'attendance'),
('View Finance', 'finance.view', 'finance'),
('Manage Finance', 'finance.manage', 'finance'),
('View Reports', 'reports.view', 'reports'),
('Manage Settings', 'settings.manage', 'settings');

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
CROSS JOIN permissions
WHERE roles.slug = 'super-admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN (
    'dashboard.view', 'users.view', 'students.view', 'students.manage',
    'teachers.view', 'teachers.manage', 'classes.view', 'classes.manage',
    'attendance.view', 'attendance.manage', 'finance.view', 'reports.view'
)
WHERE roles.slug IN ('school-administrator', 'principal', 'deputy-principal', 'admissions-officer');

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('dashboard.view', 'finance.view', 'finance.manage', 'reports.view')
WHERE roles.slug IN ('bursar', 'accountant');

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('dashboard.view', 'students.view', 'teachers.view', 'classes.view', 'attendance.view', 'attendance.manage')
WHERE roles.slug IN ('teacher', 'class-teacher');

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug = 'dashboard.view'
WHERE roles.slug = 'student';

INSERT INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
JOIN permissions ON permissions.slug IN ('dashboard.view', 'students.view')
WHERE roles.slug IN ('parent', 'librarian', 'nurse', 'transport-manager', 'hostel-manager', 'security-officer', 'store-keeper');

INSERT INTO users (role_id, first_name, last_name, email, phone, password_hash, status)
SELECT id, 'System', 'Admin', 'admin@school.test', '+254700000000',
       '$2y$10$FBVJjt7cPSNMKhfVT3HIkOuD3BhUkQP.S5o.3UXai/6Tn6DUotD2S', 'active'
FROM roles WHERE slug = 'super-admin';

INSERT INTO academic_years (id, name, starts_on, ends_on, is_current) VALUES
(1, '2025/2026', '2025-09-01', '2026-07-31', 0),
(2, '2026/2027', '2026-09-01', '2027-07-31', 1);

INSERT INTO terms (id, academic_year_id, name, starts_on, ends_on, is_current) VALUES
(1, 1, 'First Semester', '2025-09-01', '2026-01-31', 0),
(2, 1, 'Second Semester', '2026-02-01', '2026-07-31', 0),
(3, 2, 'First Semester', '2026-09-01', '2027-01-31', 1),
(4, 2, 'Second Semester', '2027-02-01', '2027-07-31', 0);

INSERT INTO departments (id, name, code) VALUES
(1, 'Computer Science', 'CS'),
(2, 'Computer Engineering Technology', 'CE'),
(3, 'Electrical/Electronics Engineering Technology', 'EE'),
(4, 'Science Laboratory Technology', 'SLT'),
(5, 'Statistics', 'STAT'),
(6, 'Accountancy', 'ACC'),
(7, 'Business Administration & Management', 'BA'),
(8, 'Public Administration', 'PA'),
(9, 'Office Technology & Management', 'OTM'),
(10, 'Marketing', 'MKT'),
(11, 'Banking & Finance', 'BF'),
(12, 'Mass Communication', 'MASS'),
(13, 'Library & Information Science', 'LIS');

-- Seed Standard Demo Student User Accounts (Password: Student@12345)
INSERT INTO users (id, role_id, first_name, last_name, email, phone, password_hash, status) VALUES
(2, (SELECT id FROM roles WHERE slug = 'student' LIMIT 1), 'Emmanuel', 'Okafor', 'student001@school.test', '08031112221', '$2y$10$6S6d0St1C2jcKzAgM8YSRePNXxhIzGcrbvfPsHE6qZuFFd0rnPBSC', 'active'),
(3, (SELECT id FROM roles WHERE slug = 'student' LIMIT 1), 'Blessing', 'Adebayo', 'student002@school.test', '08031112222', '$2y$10$6S6d0St1C2jcKzAgM8YSRePNXxhIzGcrbvfPsHE6qZuFFd0rnPBSC', 'active'),
(4, (SELECT id FROM roles WHERE slug = 'student' LIMIT 1), 'Chinedu', 'Chukwu', 'student003@school.test', '08031112223', '$2y$10$6S6d0St1C2jcKzAgM8YSRePNXxhIzGcrbvfPsHE6qZuFFd0rnPBSC', 'active'),
(5, (SELECT id FROM roles WHERE slug = 'student' LIMIT 1), 'Fatima', 'Abubakar', 'student004@school.test', '08031112224', '$2y$10$6S6d0St1C2jcKzAgM8YSRePNXxhIzGcrbvfPsHE6qZuFFd0rnPBSC', 'active'),
(6, (SELECT id FROM roles WHERE slug = 'student' LIMIT 1), 'Zainab', 'Usman', 'student005@school.test', '08031112225', '$2y$10$6S6d0St1C2jcKzAgM8YSRePNXxhIzGcrbvfPsHE6qZuFFd0rnPBSC', 'active');

INSERT INTO students (id, user_id, department_id, admission_no, matriculation_no, first_name, last_name, email, phone, gender, nationality, programme, level, academic_session, status) VALUES
(1, 2, 1, 'ADM-2026-0001', 'CS/ND/26/001', 'Emmanuel', 'Okafor', 'student001@school.test', '08031112221', 'male', 'Nigerian', 'Computer Science', 'ND I', '2026/2027', 'active'),
(2, 3, 1, 'ADM-2026-0002', 'CS/ND/26/002', 'Blessing', 'Adebayo', 'student002@school.test', '08031112222', 'female', 'Nigerian', 'Computer Science', 'ND II', '2026/2027', 'active'),
(3, 4, 6, 'ADM-2026-0003', 'ACC/ND/26/001', 'Chinedu', 'Chukwu', 'student003@school.test', '08031112223', 'male', 'Nigerian', 'Accountancy', 'ND I', '2026/2027', 'active'),
(4, 5, 3, 'ADM-2026-0004', 'EE/HND/26/001', 'Fatima', 'Abubakar', 'student004@school.test', '08031112224', 'female', 'Nigerian', 'Electrical/Electronics Engineering Technology', 'HND I', '2026/2027', 'active'),
(5, 6, 12, 'ADM-2026-0005', 'MASS/HND/26/001', 'Zainab', 'Usman', 'student005@school.test', '08031112225', 'female', 'Nigerian', 'Mass Communication', 'HND II', '2026/2027', 'active');

INSERT INTO fee_invoices (id, student_id, term_id, invoice_no, description, amount, balance, due_date, status) VALUES
(1, 1, 3, 'INV-2026-0001', '2026/2027 First Semester Tuition & School Fees', 100000.00, 30000.00, '2026-11-30', 'part_paid'),
(2, 2, 3, 'INV-2026-0002', '2026/2027 First Semester Tuition & School Fees', 100000.00, 0.00, '2026-11-30', 'paid'),
(3, 3, 3, 'INV-2026-0003', '2026/2027 First Semester Tuition & School Fees', 95000.00, 95000.00, '2026-11-30', 'issued');

INSERT INTO fee_payments (id, invoice_id, receipt_no, amount_paid, payment_method, payment_reference, payment_status, received_by, verified_by, paid_at, verified_at) VALUES
(1, 1, 'RCT-2026-0001', 70000.00, 'bank', 'BANK-2026-001', 'verified', 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 2, 'RCT-2026-0002', 100000.00, 'card', 'CARD-2026-002', 'verified', 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);


