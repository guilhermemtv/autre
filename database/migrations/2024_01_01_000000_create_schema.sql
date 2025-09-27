-- Clients table
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    company VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Architects table
CREATE TABLE architects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Sellers table
CREATE TABLE sellers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    commission_rate DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Project designers table
CREATE TABLE project_designers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Obras table
CREATE TABLE obras (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    architect_id BIGINT UNSIGNED NULL,
    seller_id BIGINT UNSIGNED NULL,
    project_designer_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NULL,
    status VARCHAR(50) DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (architect_id) REFERENCES architects(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id),
    FOREIGN KEY (project_designer_id) REFERENCES project_designers(id)
);

-- Categories
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Products table
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    unit_price DECIMAL(12,2) DEFAULT 0,
    cost_price DECIMAL(12,2) DEFAULT 0,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Budgets table
CREATE TABLE budgets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL UNIQUE,
    client_id BIGINT UNSIGNED NOT NULL,
    obra_id BIGINT UNSIGNED NOT NULL,
    architect_id BIGINT UNSIGNED NULL,
    seller_id BIGINT UNSIGNED NULL,
    project_designer_id BIGINT UNSIGNED NULL,
    subtotal DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    freight DECIMAL(12,2) DEFAULT 0,
    markup DECIMAL(5,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (obra_id) REFERENCES obras(id),
    FOREIGN KEY (architect_id) REFERENCES architects(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id),
    FOREIGN KEY (project_designer_id) REFERENCES project_designers(id)
);

CREATE TABLE budget_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    markup DECIMAL(5,2) DEFAULT 0,
    freight DECIMAL(12,2) DEFAULT 0,
    grouping VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (budget_id) REFERENCES budgets(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id BIGINT UNSIGNED NOT NULL,
    order_number VARCHAR(100) NOT NULL UNIQUE,
    status VARCHAR(50) DEFAULT 'pending',
    total DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (budget_id) REFERENCES budgets(id)
);

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
);

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
);

CREATE TABLE role_permission (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id)
);

ALTER TABLE users ADD CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(id);
ALTER TABLE role_permission ADD CONSTRAINT fk_role_permission_role FOREIGN KEY (role_id) REFERENCES roles(id);
ALTER TABLE role_permission ADD CONSTRAINT fk_role_permission_permission FOREIGN KEY (permission_id) REFERENCES permissions(id);
