
-- MySQL 8.x
CREATE TABLE roles (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone_encrypted TEXT NULL,
  timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL
);

CREATE TABLE role_user (
  role_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, user_id),
  FOREIGN KEY (role_id) REFERENCES roles(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE channels (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(50) UNIQUE NOT NULL, -- tiktok_ads, instagram_ads, meta_ads, organic, referral, event, etc
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE programs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  description TEXT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE leads (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  student_name VARCHAR(120) NOT NULL,
  school VARCHAR(150) NULL,
  whatsapp_encrypted TEXT NULL,
  email VARCHAR(150) NULL,
  channel_id BIGINT UNSIGNED NULL,
  owner_id BIGINT UNSIGNED NULL, -- admin penanggung jawab (users.id)
  status ENUM('new','contacted','follow_up','qualified','won','lost') DEFAULT 'new',
  stage ENUM('prospect','follow_up','closing') DEFAULT 'prospect',
  source_detail VARCHAR(120) NULL,
  probability TINYINT UNSIGNED DEFAULT 10, -- % untuk forecast
  expected_close_date DATE NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (channel_id) REFERENCES channels(id),
  FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE lead_programs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  program_id BIGINT UNSIGNED NOT NULL,
  interest_level TINYINT UNSIGNED DEFAULT 50,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (program_id) REFERENCES programs(id)
);

CREATE TABLE lead_activities (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL, -- admin yang follow-up
  type ENUM('call','whatsapp','email','meeting','note') NOT NULL,
  direction ENUM('inbound','outbound') DEFAULT 'outbound',
  content TEXT NULL,
  next_action VARCHAR(255) NULL,
  due_at DATETIME NULL,
  is_done TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE kpi_targets (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  month YEAR(4) NULL, -- use year + month columns instead for clarity
  period_year SMALLINT NOT NULL,
  period_month TINYINT NOT NULL, -- 1-12
  omzet_target DECIMAL(14,2) DEFAULT 0,
  student_target INT DEFAULT 0,
  leads_target INT DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uniq_period (period_year, period_month)
);

CREATE TABLE kpi_realizations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  period_year SMALLINT NOT NULL,
  period_month TINYINT NOT NULL,
  omzet_real DECIMAL(14,2) DEFAULT 0,
  student_real INT DEFAULT 0,
  leads_real INT DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uniq_real_period (period_year, period_month)
);

CREATE TABLE ads_performance (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  channel_id BIGINT UNSIGNED NOT NULL, -- FK to channels (tiktok_ads/instagram_ads/meta_ads)
  period_date DATE NOT NULL,
  impressions INT DEFAULT 0,
  clicks INT DEFAULT 0,
  leads INT DEFAULT 0,
  spend DECIMAL(12,2) DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uniq_ads (channel_id, period_date),
  FOREIGN KEY (channel_id) REFERENCES channels(id)
);

CREATE TABLE news_articles (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) UNIQUE,
  body MEDIUMTEXT NOT NULL,
  published_at DATETIME NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE notifications (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(120) NOT NULL, -- lead.reminder, lead.updated, etc
  payload JSON NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE todos (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  due_at DATETIME NULL,
  is_done TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE calendar_events (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NULL,
  location VARCHAR(150) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE whatsapp_messages (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  lead_id BIGINT UNSIGNED NULL,
  wa_number_hash CHAR(64) NULL,
  direction ENUM('inbound','outbound') NOT NULL,
  message TEXT NOT NULL,
  media_url VARCHAR(255) NULL,
  sent_at DATETIME NOT NULL,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id)
);

-- Indexing contoh
CREATE INDEX idx_leads_status ON leads(status);
CREATE INDEX idx_leads_stage ON leads(stage);
CREATE INDEX idx_lead_activities_due ON lead_activities(due_at, is_done);
