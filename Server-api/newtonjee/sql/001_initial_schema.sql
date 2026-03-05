-- ============================================================
-- NewtonJEE Portal — Master Database Migration
-- Engine: MySQL 8.0  |  Charset: utf8mb4
-- Region: AWS RDS ap-south-1 (Mumbai)
-- Run: mysql -u root -p newtonjee < 001_initial_schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS newtonjee
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE newtonjee;

SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────
-- BATCHES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS batches (
    id         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name       VARCHAR(120)     NOT NULL,
    year       SMALLINT         NOT NULL,
    is_active  TINYINT(1)       NOT NULL DEFAULT 1,
    created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_batch_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO batches (name, year) VALUES
    ('AI & Robotics 2026', 2026),
    ('AI & Robotics 2025', 2025);

-- ─────────────────────────────────────────────
-- USERS  (students, mentors, admins)
-- password is always NULL for students (Google OAuth only)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name              VARCHAR(120)     NOT NULL,
    email             VARCHAR(191)     NOT NULL,
    password          VARCHAR(255)     NULL     COMMENT 'Always NULL for students. Used only for admin/mentor accounts.',
    google_id         VARCHAR(100)     NULL,
    avatar_url        VARCHAR(500)     NULL,
    role              ENUM('student','mentor','admin','super_admin') NOT NULL DEFAULT 'student',
    batch_id          BIGINT UNSIGNED  NULL,
    student_id_custom VARCHAR(50)      NULL     COMMENT 'e.g. NJ-2026-0001, set on first login profile setup',
    profile_complete  TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 = completed first-login profile setup',
    is_active         TINYINT(1)       NOT NULL DEFAULT 1,
    last_login_at     TIMESTAMP        NULL,
    created_at        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        TIMESTAMP        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email),
    UNIQUE KEY uq_google_id (google_id),
    KEY idx_role (role),
    KEY idx_batch (batch_id),
    CONSTRAINT fk_users_batch FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: default super admin (password: Admin@123 — CHANGE IMMEDIATELY)
INSERT INTO users (name, email, password, role) VALUES
    ('Super Admin', 'admin@newtonjee.com',
     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin@123
     'super_admin');

-- ─────────────────────────────────────────────
-- OAUTH STATES  (CSRF protection for Google OAuth)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oauth_states (
    id         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    state      VARCHAR(64)      NOT NULL,
    expires_at TIMESTAMP        NOT NULL,
    created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_state (state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- ADMIN / MENTOR PASSWORD RESETS
-- (Students have no passwords — Google OAuth only)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_password_resets (
    id         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    email      VARCHAR(191)     NOT NULL,
    token_hash VARCHAR(64)      NOT NULL COMMENT 'SHA-256 of raw token sent in email',
    expires_at TIMESTAMP        NOT NULL,
    created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- COURSES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS courses (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    title           VARCHAR(200)     NOT NULL,
    slug            VARCHAR(200)     NOT NULL,
    description     TEXT             NOT NULL,
    category        ENUM('AI','Robotics') NOT NULL,
    level           ENUM('Beginner','Intermediate','Advanced') NOT NULL,
    price           DECIMAL(10,2)    NOT NULL DEFAULT 0.00 COMMENT '0 = free; Phase 2 will handle paid',
    thumbnail_url   VARCHAR(500)     NULL,
    mentor_id       BIGINT UNSIGNED  NULL,
    is_published    TINYINT(1)       NOT NULL DEFAULT 0,
    prerequisites   JSON             NULL COMMENT 'Array of course IDs',
    sort_order      SMALLINT         NOT NULL DEFAULT 0,
    created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_slug (slug),
    KEY idx_category (category),
    KEY idx_published (is_published),
    CONSTRAINT fk_courses_mentor FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- MODULES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS modules (
    id          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    course_id   BIGINT UNSIGNED  NOT NULL,
    title       VARCHAR(200)     NOT NULL,
    description TEXT             NULL,
    sort_order  SMALLINT         NOT NULL DEFAULT 0,
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_course (course_id),
    CONSTRAINT fk_modules_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- LESSONS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lessons (
    id               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    module_id        BIGINT UNSIGNED  NOT NULL,
    title            VARCHAR(200)     NOT NULL,
    type             ENUM('video','pdf','text') NOT NULL,
    content_url      VARCHAR(500)     NULL COMMENT 'YouTube embed URL for video; file path for PDF',
    content_text     LONGTEXT         NULL COMMENT 'For text-type lessons',
    duration_min     SMALLINT         NULL,
    sort_order       SMALLINT         NOT NULL DEFAULT 0,
    is_free_preview  TINYINT(1)       NOT NULL DEFAULT 0,
    created_at       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_module (module_id),
    CONSTRAINT fk_lessons_module FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- ENROLLMENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS enrollments (
    id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id      BIGINT UNSIGNED  NOT NULL,
    course_id    BIGINT UNSIGNED  NOT NULL,
    status       ENUM('active','completed','suspended') NOT NULL DEFAULT 'active',
    progress_pct TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0–100',
    enrolled_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_enrollment (user_id, course_id),
    KEY idx_user (user_id),
    KEY idx_course (course_id),
    CONSTRAINT fk_enroll_user   FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_enroll_course FOREIGN KEY (course_id) REFERENCES courses(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- LESSON PROGRESS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lesson_progress (
    id             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id        BIGINT UNSIGNED  NOT NULL,
    lesson_id      BIGINT UNSIGNED  NOT NULL,
    is_completed   TINYINT(1)       NOT NULL DEFAULT 0,
    last_position  INT              NOT NULL DEFAULT 0 COMMENT 'Video resume in seconds',
    completed_at   TIMESTAMP        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_progress (user_id, lesson_id),
    KEY idx_user (user_id),
    CONSTRAINT fk_lp_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_lp_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- ASSIGNMENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS assignments (
    id               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    course_id        BIGINT UNSIGNED  NOT NULL,
    module_id        BIGINT UNSIGNED  NULL,
    title            VARCHAR(200)     NOT NULL,
    description      TEXT             NULL,
    deadline         DATETIME         NOT NULL,
    max_score        SMALLINT         NOT NULL DEFAULT 100,
    allow_resubmit   TINYINT(1)       NOT NULL DEFAULT 0,
    notebook_path    VARCHAR(500)     NULL COMMENT 'Server path to .ipynb starter (outside web root)',
    notebook_filename VARCHAR(200)    NULL COMMENT 'Original filename shown to student',
    colab_url        VARCHAR(1000)    NULL COMMENT 'Direct Google Colab link for Open in Colab button',
    submission_type  ENUM('drive_link','file_upload','both') NOT NULL DEFAULT 'drive_link',
    is_published     TINYINT(1)       NOT NULL DEFAULT 0,
    created_by       BIGINT UNSIGNED  NULL,
    created_at       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_course (course_id),
    CONSTRAINT fk_assign_course  FOREIGN KEY (course_id) REFERENCES courses(id)  ON DELETE CASCADE,
    CONSTRAINT fk_assign_module  FOREIGN KEY (module_id) REFERENCES modules(id)  ON DELETE SET NULL,
    CONSTRAINT fk_assign_creator FOREIGN KEY (created_by) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- SUBMISSIONS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS submissions (
    id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    assignment_id   BIGINT UNSIGNED  NOT NULL,
    user_id         BIGINT UNSIGNED  NOT NULL,
    submission_type ENUM('drive_link','file_upload') NOT NULL,
    drive_url       VARCHAR(1000)    NULL COMMENT 'Google Drive shareable view-only URL',
    drive_shared_confirmed TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Student confirmed share with mentor@newtonjee.com',
    file_path       VARCHAR(500)     NULL COMMENT 'Server path if file uploaded',
    file_name       VARCHAR(200)     NULL,
    score           SMALLINT         NULL COMMENT 'NULL until graded',
    feedback        TEXT             NULL,
    graded_by       BIGINT UNSIGNED  NULL,
    submitted_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graded_at       TIMESTAMP        NULL,
    PRIMARY KEY (id),
    KEY idx_assignment (assignment_id),
    KEY idx_user (user_id),
    CONSTRAINT fk_sub_assignment FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_user       FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE CASCADE,
    CONSTRAINT fk_sub_grader     FOREIGN KEY (graded_by)     REFERENCES users(id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- EXAMS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS exams (
    id            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    course_id     BIGINT UNSIGNED  NOT NULL,
    title         VARCHAR(200)     NOT NULL,
    duration_min  SMALLINT         NOT NULL,
    max_attempts  TINYINT          NOT NULL DEFAULT 1,
    pass_score    TINYINT          NOT NULL DEFAULT 50,
    scheduled_at  DATETIME         NOT NULL,
    closes_at     DATETIME         NULL,
    is_published  TINYINT(1)       NOT NULL DEFAULT 0,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_course (course_id),
    CONSTRAINT fk_exam_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- EXAM QUESTIONS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS exam_questions (
    id             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    exam_id        BIGINT UNSIGNED  NOT NULL,
    question_text  TEXT             NOT NULL,
    option_a       VARCHAR(500)     NOT NULL,
    option_b       VARCHAR(500)     NOT NULL,
    option_c       VARCHAR(500)     NOT NULL,
    option_d       VARCHAR(500)     NOT NULL,
    correct_option ENUM('a','b','c','d') NOT NULL,
    explanation    TEXT             NULL,
    sort_order     SMALLINT         NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_exam (exam_id),
    CONSTRAINT fk_question_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- EXAM ATTEMPTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS exam_attempts (
    id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    exam_id      BIGINT UNSIGNED  NOT NULL,
    user_id      BIGINT UNSIGNED  NOT NULL,
    answers      JSON             NULL COMMENT '{"question_id": "a", ...}',
    score        TINYINT          NULL,
    passed       TINYINT(1)       NULL,
    started_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP        NULL,
    PRIMARY KEY (id),
    KEY idx_exam (exam_id),
    KEY idx_user (user_id),
    CONSTRAINT fk_attempt_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- CERTIFICATES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS certificates (
    id            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id       BIGINT UNSIGNED  NOT NULL,
    course_id     BIGINT UNSIGNED  NOT NULL,
    file_path     VARCHAR(500)     NULL COMMENT 'Generated PDF path',
    verify_token  VARCHAR(64)      NOT NULL,
    issued_at     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at    TIMESTAMP        NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_course (user_id, course_id),
    UNIQUE KEY uq_verify_token (verify_token),
    CONSTRAINT fk_cert_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_cert_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- ANNOUNCEMENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS announcements (
    id          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    title       VARCHAR(300)     NOT NULL,
    body        TEXT             NOT NULL,
    type        ENUM('event','update','deadline','mentor') NOT NULL,
    send_email  TINYINT(1)       NOT NULL DEFAULT 0,
    created_by  BIGINT UNSIGNED  NULL,
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at  TIMESTAMP        NULL,
    PRIMARY KEY (id),
    KEY idx_created_at (created_at),
    CONSTRAINT fk_ann_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- ANNOUNCEMENT READS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS announcement_reads (
    user_id         BIGINT UNSIGNED  NOT NULL,
    announcement_id BIGINT UNSIGNED  NOT NULL,
    read_at         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, announcement_id),
    CONSTRAINT fk_ar_user FOREIGN KEY (user_id)         REFERENCES users(id)         ON DELETE CASCADE,
    CONSTRAINT fk_ar_ann  FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- CALENDAR EVENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS calendar_events (
    id          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    title       VARCHAR(200)     NOT NULL,
    description TEXT             NULL,
    course_id   BIGINT UNSIGNED  NULL,
    mentor_id   BIGINT UNSIGNED  NULL,
    tag         VARCHAR(50)      NULL COMMENT 'AI | Robotics | Deadline | Workshop',
    starts_at   DATETIME         NOT NULL,
    ends_at     DATETIME         NULL,
    created_by  BIGINT UNSIGNED  NULL,
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_starts_at (starts_at),
    CONSTRAINT fk_cal_course  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_cal_mentor  FOREIGN KEY (mentor_id) REFERENCES users(id)   ON DELETE SET NULL,
    CONSTRAINT fk_cal_creator FOREIGN KEY (created_by) REFERENCES users(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- SITE SETTINGS  (key-value store)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS site_settings (
    `key`       VARCHAR(100)  NOT NULL,
    `value`     TEXT          NULL,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_settings (`key`, `value`) VALUES
    ('site_name',     'NewtonJEE'),
    ('site_email',    'hello@newtonjee.com'),
    ('primary_color', '#1E3A5F'),
    ('logo_url',      '/public/img/logo.png');

-- ─────────────────────────────────────────────
-- AUDIT LOG  (admin/mentor actions)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
    id          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED  NULL,
    action      VARCHAR(100)     NOT NULL COMMENT 'e.g. course.create, user.deactivate',
    target_type VARCHAR(50)      NULL COMMENT 'e.g. course, user, assignment',
    target_id   BIGINT UNSIGNED  NULL,
    detail      JSON             NULL,
    ip          VARCHAR(45)      NULL,
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    KEY idx_action (action),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────
-- USEFUL VIEWS
-- ─────────────────────────────────────────────

CREATE OR REPLACE VIEW v_student_progress AS
SELECT
    e.user_id,
    u.name           AS student_name,
    u.email,
    b.name           AS batch,
    c.id             AS course_id,
    c.title          AS course_title,
    c.category,
    e.progress_pct,
    e.status         AS enrollment_status,
    e.enrolled_at,
    e.completed_at,
    (SELECT COUNT(*) FROM lessons l
     JOIN modules m ON l.module_id = m.id
     WHERE m.course_id = c.id)  AS total_lessons,
    (SELECT COUNT(*) FROM lesson_progress lp
     JOIN lessons l ON lp.lesson_id = l.id
     JOIN modules m ON l.module_id = m.id
     WHERE m.course_id = c.id
       AND lp.user_id = e.user_id
       AND lp.is_completed = 1) AS completed_lessons
FROM enrollments e
JOIN users u   ON e.user_id   = u.id AND u.deleted_at IS NULL
JOIN courses c ON e.course_id = c.id AND c.deleted_at IS NULL
LEFT JOIN batches b ON u.batch_id = b.id;

CREATE OR REPLACE VIEW v_unread_announcements AS
SELECT
    u.id   AS user_id,
    a.id   AS announcement_id,
    a.title,
    a.type,
    a.created_at
FROM users u
CROSS JOIN announcements a
LEFT JOIN announcement_reads ar
    ON ar.user_id = u.id AND ar.announcement_id = a.id
WHERE a.deleted_at IS NULL
  AND ar.announcement_id IS NULL
  AND u.role = 'student'
  AND u.is_active = 1;
