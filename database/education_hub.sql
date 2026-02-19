-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 08:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `education_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `downloads` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `title`, `content`, `file_path`, `subject_id`, `uploaded_by`, `downloads`, `created_at`, `updated_at`) VALUES
(1, 'C Programming Basics', 'Introduction to C language and syntax', NULL, 1, 2, 0, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(2, 'Functions in C', 'How to create and use functions', NULL, 1, 2, 0, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(3, 'Sant Sahitya Overview', 'Introduction to Marathi saint literature', NULL, 2, 2, 0, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(4, 'Business Plan Basics', 'How to create a business plan', NULL, 3, 2, 0, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(5, 'Computer Architecture', 'CPU, Memory, I/O devices explained', NULL, 4, 2, 0, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(6, 'SQL Fundamentals', 'Basic SQL queries and commands', NULL, 9, 2, 0, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(7, 'Numerical Methods Introduction', 'Basics of numerical analysis', NULL, 12, 2, 0, '2026-02-19 06:53:30', '2026-02-19 06:53:30');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `subject_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty`, `created_by`, `created_at`) VALUES
(1, 1, 'Which is the correct way to declare a variable in C?', 'int x;', 'variable x;', 'x = int;', 'declare int x;', 'A', 'medium', 2, '2026-02-19 06:53:30'),
(2, 1, 'What is the output of printf(\"%d\", 5+3);?', '53', '8', '5+3', 'Error', 'B', 'medium', 2, '2026-02-19 06:53:30'),
(3, 4, 'What is the brain of computer?', 'RAM', 'Hard Disk', 'CPU', 'Monitor', 'C', 'medium', 2, '2026-02-19 06:53:30'),
(4, 4, 'Which is an input device?', 'Monitor', 'Printer', 'Keyboard', 'Speaker', 'C', 'medium', 2, '2026-02-19 06:53:30'),
(5, 9, 'What does SQL stand for?', 'Structured Query Language', 'Simple Query Language', 'Standard Query Language', 'System Query Language', 'A', 'medium', 2, '2026-02-19 06:53:30'),
(6, 9, 'Which command is used to retrieve data?', 'INSERT', 'UPDATE', 'SELECT', 'DELETE', 'C', 'medium', 2, '2026-02-19 06:53:30'),
(7, 12, 'What is the purpose of numerical methods?', 'To solve equations approximately', 'To write programs', 'To design databases', 'To create graphics', 'A', 'medium', 2, '2026-02-19 06:53:30'),
(8, 1, 'C language was developed by?', 'Dennis Ritchie', 'James Gosling', 'Bjarne Stroustrup', 'Guido van Rossum', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(9, 1, 'Which symbol is used for comments in C?', '//', '##', '**', '%%', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(10, 1, 'Which data type is used for decimal values?', 'int', 'float', 'char', 'void', 'B', 'medium', 2, '2026-02-19 06:54:36'),
(11, 1, 'Which operator is used for modulus?', '%', '/', '*', '#', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(12, 1, 'Which function is entry point of C program?', 'start()', 'main()', 'run()', 'init()', 'B', 'medium', 2, '2026-02-19 06:54:36'),
(13, 2, 'Sant Dnyaneshwar wrote?', 'Bhagavad Gita', 'Dnyaneshwari', 'Ramayan', 'Mahabharat', 'B', 'medium', 2, '2026-02-19 06:54:36'),
(14, 2, 'Sant literature mainly promotes?', 'Humanity', 'Violence', 'Luxury', 'Power', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(15, 2, 'Abhang is form of?', 'Poetry', 'Dance', 'Drama', 'Music only', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(16, 2, 'Sant Tukaram belonged to?', 'Maharashtra', 'Punjab', 'Kerala', 'Assam', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(17, 2, 'Main theme of Sant Sahitya?', 'Devotion', 'War', 'Politics', 'Economy', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(18, 3, 'Entrepreneur takes?', 'Salary', 'Risk', 'Orders', 'Leave', 'B', 'medium', 2, '2026-02-19 06:54:36'),
(19, 3, 'Business plan includes?', 'Strategy', 'Gossip', 'Rumor', 'Drama', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(20, 3, 'Capital means?', 'Investment money', 'Building', 'Office', 'Employee', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(21, 3, 'Startup focuses on?', 'Innovation', 'Copying', 'Waiting', 'Government', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(22, 3, 'Profit equals?', 'Revenue - Cost', 'Cost - Revenue', 'Loss', 'Tax', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(23, 4, 'CPU stands for?', 'Central Processing Unit', 'Control Program Unit', 'Computer Personal Unit', 'Central Power Unit', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(24, 4, 'RAM is?', 'Volatile', 'Permanent', 'External', 'Input', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(25, 4, '1 KB equals?', '1024 bytes', '1000 bytes', '512 bytes', '2048 bytes', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(26, 4, 'Binary uses digits?', '0 and 1', '1 and 2', '0 to 9', 'A to F', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(27, 4, 'Output device example?', 'Monitor', 'Keyboard', 'Mouse', 'Scanner', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(28, 5, 'Effective communication needs?', 'Clarity', 'Anger', 'Noise', 'Confusion', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(29, 5, 'Formal letter must have?', 'Proper format', 'Slang', 'Emoji', 'Short form', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(30, 5, 'Listening is part of?', 'Communication', 'Programming', 'Marketing', 'Coding', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(31, 5, 'Barrier to communication?', 'Noise', 'Focus', 'Attention', 'Interest', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(32, 5, 'Resume should be?', 'Concise', 'Long', 'Decorative', 'Colorful only', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(33, 6, 'Pointer stores?', 'Address', 'Value', 'Loop', 'Char', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(34, 6, 'Dynamic memory uses?', 'malloc()', 'printf()', 'scanf()', 'return', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(35, 6, 'File open function?', 'fopen()', 'file()', 'open()', 'read()', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(36, 6, 'Array index starts from?', '0', '1', '-1', '2', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(37, 6, 'Structure defined by?', 'struct', 'class', 'object', 'define', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(38, 7, 'Marketing mix is?', '4Ps', '5Ws', '3Cs', '2Ms', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(39, 7, 'Break even means?', 'No profit no loss', 'High profit', 'High loss', 'Tax free', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(40, 7, 'Branding creates?', 'Identity', 'Loss', 'Risk', 'Debt', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(41, 7, 'Target market means?', 'Specific customers', 'All people', 'Government', 'Family', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(42, 7, 'Advertisement aims to?', 'Promote product', 'Hide product', 'Stop sales', 'Confuse', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(43, 8, 'Presentation requires?', 'Confidence', 'Fear', 'Silence', 'Anger', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(44, 8, 'Email must include?', 'Subject', 'Emoji', 'Memes', 'Shortcuts', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(45, 8, 'Group discussion tests?', 'Communication', 'Height', 'Weight', 'Looks', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(46, 8, 'Public speaking needs?', 'Practice', 'Luck', 'Shouting', 'Silence', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(47, 8, 'Report writing should be?', 'Structured', 'Random', 'Long only', 'Colorful', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(48, 9, 'Primary key is?', 'Unique', 'Duplicate', 'Null', 'Optional', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(49, 9, 'SQL command to fetch data?', 'SELECT', 'INSERT', 'DELETE', 'UPDATE', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(50, 9, 'Normalization reduces?', 'Redundancy', 'Speed', 'Color', 'Design', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(51, 9, 'Foreign key creates?', 'Relation', 'Loop', 'Array', 'Stack', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(52, 9, 'DBMS stands for?', 'Database Management System', 'Data Backup Main System', 'Digital Base Manage System', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(53, 10, 'Encapsulation means?', 'Data hiding', 'Looping', 'Sorting', 'Printing', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(54, 10, 'Inheritance provides?', 'Reusability', 'Error', 'Deletion', 'Crash', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(55, 10, 'Polymorphism means?', 'Many forms', 'One form', 'No form', 'Same form', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(56, 10, 'Class is?', 'Blueprint', 'Object', 'Function', 'Loop', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(57, 10, 'Object is instance of?', 'Class', 'Loop', 'Array', 'Method', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(58, 11, 'Stack follows?', 'LIFO', 'FIFO', 'Random', 'Priority', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(59, 11, 'Queue follows?', 'FIFO', 'LIFO', 'Random', 'Stack', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(60, 11, 'Linked list uses?', 'Nodes', 'Indexes', 'Tables', 'Files', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(61, 11, 'Tree root is?', 'Top node', 'Bottom node', 'Middle', 'Leaf', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(62, 11, 'Binary tree has max children?', '2', '3', '4', '1', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(63, 12, 'Bisection method finds?', 'Root', 'Loop', 'Array', 'Graph', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(64, 12, 'Newton method needs?', 'Derivative', 'Matrix', 'Queue', 'Stack', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(65, 12, 'Interpolation estimates?', 'Values', 'Color', 'Speed', 'Memory', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(66, 12, 'Gauss method solves?', 'Linear equations', 'Loops', 'Trees', 'Stack', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(67, 12, 'Numerical methods give?', 'Approximate solution', 'Exact poetry', 'Binary code', 'Hardware', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(68, 13, 'HTML used for?', 'Structure', 'Design only', 'Server', 'Database', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(69, 13, 'CSS used for?', 'Styling', 'Database', 'Server', 'Logic', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(70, 13, 'PHP is?', 'Server side language', 'Browser', 'Database', 'Protocol', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(71, 13, 'JavaScript runs in?', 'Browser', 'CPU', 'RAM', 'Printer', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(72, 13, 'HTTP stands for?', 'HyperText Transfer Protocol', 'High Text Transfer', 'Home Tool Transfer', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(73, 14, 'SDLC phase?', 'Planning', 'Sleeping', 'Cooking', 'Driving', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(74, 14, 'Waterfall is?', 'Model', 'Language', 'OS', 'Tool', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(75, 14, 'Testing ensures?', 'Quality', 'Decoration', 'Delay', 'Noise', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(76, 14, 'Requirement gathering is first step?', 'Yes', 'No', 'Maybe', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(77, 14, 'Agile promotes?', 'Iteration', 'Delay', 'Silence', 'Confusion', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(78, 15, 'IP stands for?', 'Internet Protocol', 'Internal Process', 'Input Port', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(79, 15, 'Router connects?', 'Networks', 'Keyboard', 'Monitor', 'Mouse', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(80, 15, 'OSI has layers?', '7', '5', '3', '10', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(81, 15, 'LAN stands for?', 'Local Area Network', 'Long Area Network', 'Line Area Network', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(82, 15, 'TCP ensures?', 'Reliable communication', 'Decoration', 'Design', 'Speed only', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(83, 16, 'AI simulates?', 'Human intelligence', 'Printer', 'Monitor', 'RAM', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(84, 16, 'Machine learning uses?', 'Data', 'Paint', 'Cable', 'Keyboard', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(85, 16, 'Supervised learning needs?', 'Labeled data', 'Noise', 'Virus', 'Firewall', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(86, 16, 'Neural network inspired by?', 'Brain', 'CPU', 'Router', 'Mouse', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(87, 16, 'AI used in?', 'Chatbots', 'Shoes', 'Fans', 'Chairs', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(88, 17, 'OS manages?', 'Resources', 'Decoration', 'Color', 'Noise', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(89, 17, 'Process is?', 'Program in execution', 'File', 'Folder', 'Virus', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(90, 17, 'Deadlock is?', 'Waiting state', 'Loop', 'Error only', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(91, 17, 'Virtual memory uses?', 'Disk', 'Keyboard', 'Mouse', 'Monitor', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(92, 17, 'Linux is?', 'OS', 'Language', 'Browser', 'App', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(93, 18, 'Cloud provides?', 'On demand service', 'Hardware', 'Cable', 'Mouse', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(94, 18, 'IaaS means?', 'Infrastructure as a Service', 'Internet as a Service', 'Internal as a Service', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(95, 18, 'SaaS example?', 'Google Docs', 'RAM', 'CPU', 'Printer', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(96, 18, 'Cloud storage example?', 'Drive', 'Monitor', 'Keyboard', 'Mouse', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(97, 18, 'Public cloud accessible by?', 'Everyone', 'One person', 'Teacher', 'Admin only', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(98, 19, 'Encryption protects?', 'Data', 'Color', 'Sound', 'Speed', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(99, 19, 'Firewall blocks?', 'Unauthorized access', 'Music', 'Video', 'Code', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(100, 19, 'Malware is?', 'Malicious software', 'Hardware', 'Driver', 'Monitor', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(101, 19, 'Phishing steals?', 'Credentials', 'Mouse', 'RAM', 'Cable', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(102, 19, 'Antivirus detects?', 'Threats', 'Design', 'Printer', 'Font', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(103, 20, 'Project needs?', 'Planning', 'Confusion', 'Delay', 'None', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(104, 20, 'Documentation helps?', 'Maintenance', 'Deletion', 'Crash', 'Ignore', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(105, 20, 'Testing phase ensures?', 'Quality', 'Noise', 'Color', 'Random', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(106, 20, 'Project report includes?', 'Objectives', 'Jokes', 'Memes', 'Drama', 'A', 'medium', 2, '2026-02-19 06:54:36'),
(107, 20, 'Final project requires?', 'Implementation', 'Skipping', 'Copying', 'Sleeping', 'A', 'medium', 2, '2026-02-19 06:54:36');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `time_taken` int(11) DEFAULT 0,
  `taken_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'book',
  `color` varchar(20) DEFAULT '#0099ff',
  `year` enum('FY','SY','TY') NOT NULL DEFAULT 'FY',
  `semester` int(11) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`, `icon`, `color`, `year`, `semester`, `created_by`, `created_at`) VALUES
(1, 'Procedural Programming-1', 'Programming fundamentals, C language, Functions', 'code', '#0099ff', 'FY', 1, 1, '2026-02-19 06:53:30'),
(2, 'MAR-Sant Sahitya Ani Manvi Mule', 'Marathi literature and cultural studies', 'book-open', '#7c3aed', 'FY', 1, 1, '2026-02-19 06:53:30'),
(3, 'Fundamental of Enterpreneurship-1', 'Business basics, Startup fundamentals', 'briefcase', '#10b981', 'FY', 1, 1, '2026-02-19 06:53:30'),
(4, 'Fundamental of Computer', 'Computer basics, Hardware, Software concepts', 'monitor', '#f59e0b', 'FY', 1, 1, '2026-02-19 06:53:30'),
(5, 'Communication Skills in English-1', 'English grammar, Writing, Speaking skills', 'message-circle', '#14b8a6', 'FY', 1, 1, '2026-02-19 06:53:30'),
(6, 'Procedural Programming-2', 'Advanced C programming, Pointers, File handling', 'code', '#0099ff', 'FY', 2, 1, '2026-02-19 06:53:30'),
(7, 'Fundamental of Enterpreneurship-2', 'Business plan development, Marketing basics', 'briefcase', '#10b981', 'FY', 2, 1, '2026-02-19 06:53:30'),
(8, 'Communication Skills in English-2', 'Advanced writing, Presentation skills', 'message-circle', '#14b8a6', 'FY', 2, 1, '2026-02-19 06:53:30'),
(9, 'Database Management System', 'SQL, Database design, Normalization', 'database', '#ec4899', 'SY', 3, 1, '2026-02-19 06:53:30'),
(10, 'Object Oriented Programming', 'OOP concepts, Java/C++, Classes', 'code', '#6366f1', 'SY', 3, 1, '2026-02-19 06:53:30'),
(11, 'Data Structures', 'Arrays, Linked Lists, Trees, Graphs', 'layers', '#f97316', 'SY', 3, 1, '2026-02-19 06:53:30'),
(12, 'Computational Numerical Methods', 'Numerical analysis, Algorithms, Mathematics', 'calculator', '#6366f1', 'SY', 4, 1, '2026-02-19 06:53:30'),
(13, 'Web Technology', 'HTML, CSS, JavaScript, PHP', 'globe', '#3b82f6', 'SY', 4, 1, '2026-02-19 06:53:30'),
(14, 'Software Engineering', 'SDLC, Testing, Project Management', 'settings', '#8b5cf6', 'SY', 4, 1, '2026-02-19 06:53:30'),
(15, 'Computer Networks', 'Networking fundamentals, Protocols, Security', 'network', '#0891b2', 'TY', 5, 1, '2026-02-19 06:53:30'),
(16, 'Artificial Intelligence', 'AI basics, Machine Learning, Neural Networks', 'brain', '#d946ef', 'TY', 5, 1, '2026-02-19 06:53:30'),
(17, 'Operating Systems', 'OS concepts, Process management, Memory', 'monitor', '#84cc16', 'TY', 5, 1, '2026-02-19 06:53:30'),
(18, 'Cloud Computing', 'Cloud platforms, AWS, Azure, Deployment', 'cloud', '#06b6d4', 'TY', 6, 1, '2026-02-19 06:53:30'),
(19, 'Cyber Security', 'Security principles, Cryptography, Threats', 'shield', '#ef4444', 'TY', 6, 1, '2026-02-19 06:53:30'),
(20, 'Project Work', 'Final year project development', 'folder', '#f59e0b', 'TY', 6, 1, '2026-02-19 06:53:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `year` enum('FY','SY','TY') DEFAULT 'FY',
  `semester` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `year`, `semester`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@educationhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'FY', 1, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(2, 'Teacher Demo', 'teacher@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'FY', 1, '2026-02-19 06:53:30', '2026-02-19 06:53:30'),
(3, 'Raj Kumar', 'raj@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'FY', 1, '2026-02-19 06:53:30', '2026-02-19 06:53:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
