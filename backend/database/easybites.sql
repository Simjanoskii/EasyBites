CREATE DATABASE IF NOT EXISTS easybites
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE easybites;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS restaurants (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(32) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_restaurants_active_name (is_active, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    category VARCHAR(60) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_menu_items_restaurant_available (restaurant_id, is_available),
    CONSTRAINT chk_menu_items_price_nonnegative CHECK (price >= 0),
    CONSTRAINT fk_menu_items_restaurant
        FOREIGN KEY (restaurant_id) REFERENCES restaurants (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    restaurant_id INT UNSIGNED NOT NULL,
    status ENUM(
        'pending',
        'confirmed',
        'preparing',
        'out_for_delivery',
        'delivered',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    delivery_address VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_orders_user_created (user_id, created_at),
    KEY idx_orders_restaurant_status (restaurant_id, status),
    CONSTRAINT chk_orders_total_nonnegative CHECK (total >= 0),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_restaurant
        FOREIGN KEY (restaurant_id) REFERENCES restaurants (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    menu_item_id INT UNSIGNED NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_order_items_order_menu (order_id, menu_item_id),
    KEY idx_order_items_menu_item (menu_item_id),
    CONSTRAINT chk_order_items_quantity_positive CHECK (quantity > 0),
    CONSTRAINT chk_order_items_price_nonnegative CHECK (unit_price >= 0),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_menu_item
        FOREIGN KEY (menu_item_id) REFERENCES menu_items (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    restaurant_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reviews_user_restaurant (user_id, restaurant_id),
    KEY idx_reviews_restaurant_created (restaurant_id, created_at),
    CONSTRAINT chk_reviews_rating_range CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_restaurant
        FOREIGN KEY (restaurant_id) REFERENCES restaurants (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO restaurants (id, name, description, address, phone, is_active)
VALUES
    (1, 'Baščaršija Kitchen', 'Traditional Sarajevo-inspired comfort food.', 'Baščaršija, Sarajevo', '+387 33 555 101', 1),
    (2, 'Miljacka Pizza', 'Neighbourhood pizza made fresh to order.', 'Skenderija, Sarajevo', '+387 33 555 202', 1),
    (3, 'Green Spoon', 'Fresh salads and lighter lunch options.', 'Marijin Dvor, Sarajevo', '+387 33 555 303', 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    address = VALUES(address),
    phone = VALUES(phone),
    is_active = VALUES(is_active);

INSERT INTO menu_items (id, restaurant_id, name, description, category, price, is_available)
VALUES
    (1, 1, 'Ćevapi (5 pieces)', 'Grilled beef ćevapi served with somun.', 'Local', 8.50, 1),
    (2, 1, 'Burek slice', 'Flaky pastry filled with savoury beef.', 'Local', 4.00, 1),
    (3, 1, 'Sogan-dolma', 'Onions filled with seasoned minced beef.', 'Local', 9.00, 1),
    (4, 2, 'Margherita pizza', 'Tomato, mozzarella, and fresh basil.', 'Pizza', 11.50, 1),
    (5, 2, 'Funghi pizza', 'Mozzarella and locally sourced mushrooms.', 'Pizza', 13.00, 1),
    (6, 2, 'Rocket salad', 'Rocket, cherry tomatoes, and shaved cheese.', 'Healthy', 7.00, 1),
    (7, 3, 'Sarajevo garden bowl', 'Seasonal vegetables with a lemon dressing.', 'Healthy', 10.00, 1),
    (8, 3, 'Grilled chicken salad', 'Mixed greens topped with grilled chicken.', 'Healthy', 12.50, 1),
    (9, 3, 'Apple walnut bowl', 'Apple, walnuts, greens, and honey vinaigrette.', 'Healthy', 9.50, 1)
ON DUPLICATE KEY UPDATE
    restaurant_id = VALUES(restaurant_id),
    name = VALUES(name),
    description = VALUES(description),
    category = VALUES(category),
    price = VALUES(price),
    is_available = VALUES(is_available);
