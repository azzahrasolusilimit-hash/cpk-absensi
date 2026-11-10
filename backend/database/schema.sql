CREATE TABLE id_counters (
  name VARCHAR(30) PRIMARY KEY,
  value BIGINT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO id_counters (name, value) VALUES ('user', 0);

CREATE TABLE users (
  id VARCHAR(32) PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('mahasiswa','dosen') NOT NULL DEFAULT 'mahasiswa',
  photo VARCHAR(255) NULL,
  fcm_token VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id VARCHAR(32) NOT NULL,
  contact_user_id VARCHAR(32) NOT NULL,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NOT NULL,
  UNIQUE KEY uq_owner_contact (owner_id, contact_user_id),
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (contact_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sender_id VARCHAR(32) NOT NULL,
  receiver_id VARCHAR(32) NOT NULL,
  category ENUM('umum','izin','sakit','hadir') NOT NULL DEFAULT 'umum',
  body TEXT NOT NULL,
  image VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sender (sender_id, receiver_id, id),
  INDEX idx_receiver (receiver_id, sender_id, id),
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;