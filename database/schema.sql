-- CRM System Database Schema
CREATE DATABASE IF NOT EXISTS crm_system;
USE crm_system;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Leads table
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    company VARCHAR(100),
    status ENUM('new', 'contacted', 'qualified', 'converted', 'lost') DEFAULT 'new',
    source VARCHAR(50),
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Customers table (converted leads)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    company VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    zip_code VARCHAR(10),
    country VARCHAR(50),
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    due_date DATE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    assigned_to INT,
    related_to_type ENUM('lead', 'customer'),
    related_to_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Notes table
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    content TEXT NOT NULL,
    related_to_type ENUM('lead', 'customer', 'task'),
    related_to_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, role) 
VALUES ('admin', 'admin@crm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin');

-- Sample data for testing
INSERT INTO leads (first_name, last_name, email, phone, company, status, source, assigned_to) VALUES
('John', 'Doe', 'john.doe@email.com', '555-0101', 'ABC Corp', 'new', 'website', 1),
('Jane', 'Smith', 'jane.smith@email.com', '555-0102', 'XYZ Ltd', 'contacted', 'referral', 1),
('Bob', 'Johnson', 'bob.johnson@email.com', '555-0103', 'Tech Solutions', 'qualified', 'cold_call', 1);

INSERT INTO customers (lead_id, first_name, last_name, email, phone, company, address, city, state, zip_code, country, assigned_to) VALUES
(NULL, 'Alice', 'Brown', 'alice.brown@email.com', '555-0201', 'Design Studio', '123 Main St', 'New York', 'NY', '10001', 'USA', 1),
(NULL, 'Charlie', 'Wilson', 'charlie.wilson@email.com', '555-0202', 'Marketing Pro', '456 Oak Ave', 'Los Angeles', 'CA', '90001', 'USA', 1);

INSERT INTO tasks (title, description, due_date, priority, status, assigned_to, related_to_type, related_to_id, created_by) VALUES
('Follow up with John Doe', 'Call to discuss requirements', '2024-01-15', 'high', 'pending', 1, 'lead', 1, 1),
('Send proposal to Jane Smith', 'Prepare and send detailed proposal', '2024-01-20', 'medium', 'pending', 1, 'lead', 2, 1),
('Customer onboarding - Alice Brown', 'Complete onboarding process', '2024-01-25', 'high', 'in_progress', 1, 'customer', 1, 1);

INSERT INTO notes (title, content, related_to_type, related_to_id, created_by) VALUES
('Initial contact', 'First phone call went well. Customer interested in our services.', 'lead', 1, 1),
('Meeting notes', 'Discussed pricing and timeline. Will send proposal by Friday.', 'lead', 2, 1),
('Onboarding progress', 'Customer setup completed. Training scheduled for next week.', 'customer', 1, 1);
