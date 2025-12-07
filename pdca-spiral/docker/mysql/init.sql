-- PDCA Spiral Database Schema
-- Created: 2025-12-06

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Teams Table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    team_code VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_team_name (team_name),
    INDEX idx_team_code (team_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    team_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_team_id (team_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PDCA Cycles Table
CREATE TABLE IF NOT EXISTS pdca_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    cycle_number INT NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_cycle (team_id, cycle_number),
    INDEX idx_team_status (team_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluations Table
CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    team_id INT NOT NULL,
    cycle_id INT NOT NULL,
    score INT NOT NULL CHECK (score >= 0 AND score <= 10),
    reflection TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (cycle_id) REFERENCES pdca_cycles(id) ON DELETE CASCADE,
    INDEX idx_team_cycle (team_id, cycle_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Next Actions Table
CREATE TABLE IF NOT EXISTS next_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    cycle_id INT NOT NULL,
    user_id INT NOT NULL,
    description TEXT NOT NULL,
    target_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (cycle_id) REFERENCES pdca_cycles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_team_cycle (team_id, cycle_id),
    INDEX idx_status (status),
    INDEX idx_target_date (target_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data for Development
INSERT INTO teams (team_name, team_code) VALUES ('サンプルチーム', 'SAMPLE');

INSERT INTO users (username, email, password_hash, team_id) VALUES 
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
-- Password: password

INSERT INTO pdca_cycles (team_id, cycle_number, status) VALUES (1, 1, 'active');

INSERT INTO evaluations (user_id, team_id, cycle_id, score, reflection) VALUES 
(1, 1, 1, 7, 'チームのコミュニケーションが改善されてきました。ただし、まだタスクの優先順位付けに課題があります。'),
(1, 1, 1, 8, '前回の振り返りを活かして、優先順位の明確化ができました。次はスピード感を上げたいです。');

INSERT INTO next_actions (team_id, cycle_id, user_id, description, target_date, status) VALUES 
(1, 1, 1, '毎朝15分のスタンドアップミーティングを実施する', '2025-12-20', 'in_progress'),
(1, 1, 1, 'タスク管理ツールの導入を検討する', '2025-12-31', 'pending');
