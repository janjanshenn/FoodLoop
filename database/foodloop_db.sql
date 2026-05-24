-- ============================================================
-- FOODLoop Database Schema
-- Import this file via phpMyAdmin > Import tab
-- ============================================================

CREATE DATABASE IF NOT EXISTS foodloop_db;
USE foodloop_db;

-- -----------------------------------------------
-- USERS TABLE
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    username            VARCHAR(50)  NOT NULL UNIQUE,
    email               VARCHAR(255) UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    role                ENUM('admin','staff','customer') NOT NULL DEFAULT 'staff',
    is_verified         TINYINT(1) DEFAULT 0,
    verification_token  VARCHAR(255) NULL,
    reset_token         VARCHAR(255) NULL,
    reset_token_expires DATETIME NULL,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default users: admin / admin123 | staff / staff123 (auto-verified)
INSERT INTO users (username, email, password_hash, role, is_verified) VALUES
('admin', 'admin@foodloop.com', '$2y$10$PLDyNkBdyxnDkGXplqZAquXou3S1gULaOAh99Gdx46nrFsutL9RIG', 'admin', 1),
('staff', 'staff@foodloop.com', '$2y$10$DB7Hk9p0qj3mpj1HnO7WUeajiVRs.3/tMRWe34UA4M5vWXJiuS5h2', 'staff', 1);

-- -----------------------------------------------
-- RATE LIMITS TABLE
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45) NOT NULL,
    action       VARCHAR(50) NOT NULL,
    attempts     INT DEFAULT 1,
    last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ip_action (ip_address, action)
);
-- Note: admin123 and staff123 are the passwords above (bcrypt hashed)

-- -----------------------------------------------
-- MENU TABLE
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS menu (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    category   ENUM('Main Dish','Beverage','Dessert') NOT NULL DEFAULT 'Main Dish',
    image      VARCHAR(255)  NOT NULL DEFAULT '',
    servings   INT           NOT NULL DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO menu (name, price, category, servings) VALUES
('Classic Pork Adobo',   85.00,  'Main Dish', 10),
('Sinigang na Baboy',   110.00,  'Main Dish', 10),
('Pancit Canton Espesyal', 60.00,'Main Dish', 10),
('Lumpiang Shanghai',    45.00,  'Main Dish', 10),
('Extra Rice',           15.00,  'Main Dish', 20),
('Sprite (Bottle)',      20.00,  'Beverage',  15),
('Coca-Cola (Bottle)',   20.00,  'Beverage',  15),
('Sizzling Sisig',      120.00,  'Main Dish', 5),
('Chopsuey',             55.00,  'Main Dish', 8),
('Bicol Express',        90.00,  'Main Dish', 10),
('Beef Bulalo Soup',    150.00,  'Main Dish', 5);

-- -----------------------------------------------
-- INGREDIENTS / STOCK TABLE
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS ingredients (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)   NOT NULL,
    quantity      DECIMAL(10,2)  NOT NULL DEFAULT 0,
    unit          VARCHAR(20)    NOT NULL DEFAULT 'kg',
    low_threshold DECIMAL(10,2)  NOT NULL DEFAULT 5,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO ingredients (name, quantity, unit, low_threshold) VALUES
('Pork',        3.5,  'kg', 5),
('Garlic',    500.0,  'g',  100),
('Soy Sauce',   1.0,  'L',  2),
('Rice',       25.0,  'kg', 5),
('Chicken',    12.0,  'kg', 5),
('Cooking Oil', 0.5,  'L',  1),
('Onions',      3.0,  'kg', 2),
('Pork Belly',  8.0,  'kg', 5);

-- -----------------------------------------------
-- TRANSACTIONS TABLE
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    cashier        VARCHAR(50)    NOT NULL DEFAULT 'Staff',
    items_summary  TEXT           NOT NULL,
    total          DECIMAL(10,2)  NOT NULL,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- RESERVATIONS TABLE
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS reservations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL,
    menu_id     INT NULL,
    item_name   VARCHAR(100) NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    order_type  ENUM('Reservation', 'Cart Order') NOT NULL DEFAULT 'Reservation',
    status      ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservations_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE SET NULL
);

-- -----------------------------------------------
-- FEEDBACK TABLE
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS feedback (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NULL,
    username    VARCHAR(50) NOT NULL,
    rating      INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comments    TEXT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

