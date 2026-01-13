-- Education Hub Database Schema for MySQL (XAMPP)
-- Run this in phpMyAdmin

CREATE DATABASE IF NOT EXISTS education_hub;
USE education_hub;

-- Users table with roles (student, teacher, admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subjects table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'book',
    color VARCHAR(20) DEFAULT '#0099ff',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Notes table
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    file_path VARCHAR(255),
    subject_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Questions table for quizzes
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Quiz results table
CREATE TABLE quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    time_taken INT DEFAULT 0,
    taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@educationhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Teacher Demo', 'teacher@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Raj Kumar', 'raj@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Insert sample subjects
INSERT INTO subjects (name, description, icon, color, created_by) VALUES
('Mathematics', 'Algebra, Calculus, Statistics and more', 'calculator', '#0099ff', 1),
('Physics', 'Mechanics, Thermodynamics, Optics', 'atom', '#7c3aed', 1),
('Chemistry', 'Organic, Inorganic, Physical Chemistry', 'flask', '#10b981', 1),
('Computer Science', 'Programming, Data Structures, Algorithms', 'code', '#f59e0b', 1),
('English', 'Grammar, Literature, Writing Skills', 'book-open', '#ec4899', 1);

-- Insert sample notes
INSERT INTO notes (title, content, subject_id, uploaded_by) VALUES
('Algebra Fundamentals', 'Basic algebra concepts and formulas', 1, 2),
('Calculus Introduction', 'Derivatives and integrals basics', 1, 2),
('Newton Laws of Motion', 'First, Second and Third laws explained', 2, 2),
('Organic Chemistry Basics', 'Introduction to organic compounds', 3, 2),
('Python Programming', 'Getting started with Python', 4, 2);

-- Insert sample questions
INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, created_by) VALUES
(1, 'What is the value of x in 2x + 5 = 15?', '5', '10', '7', '3', 'A', 2),
(1, 'What is the derivative of x²?', 'x', '2x', '2', 'x²', 'B', 2),
(2, 'What is the SI unit of force?', 'Joule', 'Newton', 'Watt', 'Pascal', 'B', 2),
(2, 'Speed of light is approximately?', '3×10⁶ m/s', '3×10⁸ m/s', '3×10¹⁰ m/s', '3×10⁴ m/s', 'B', 2),
(3, 'What is the atomic number of Carbon?', '4', '6', '8', '12', 'B', 2),
(4, 'Which is not a programming language?', 'Python', 'Java', 'HTML', 'C++', 'C', 2),
(4, 'What does CPU stand for?', 'Central Processing Unit', 'Computer Personal Unit', 'Central Program Utility', 'Core Processing Unit', 'A', 2);
