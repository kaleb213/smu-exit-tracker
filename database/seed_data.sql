-- ============================================================
--  seed_data.sql - DEMONSTRATION DATA ONLY
--  Clearly labeled DEMO content for testing the system.
--  It does NOT contain official SMU examination questions.
--  Import AFTER smu_exit_exam_schema.sql (fresh import).
-- ============================================================

USE smu_exit_exam;

-- ---------- Subjects ----------
INSERT INTO subjects (name, description) VALUES
('Programming Fundamentals', 'Core programming concepts: variables, data types, control flow.'),
('Database Systems',         'Relational databases, SQL, and data integrity.'),
('Software Engineering',     'Development processes, models, and best practices.');

-- ---------- Topics ----------
INSERT INTO topics (subject_id, name) VALUES
(1, 'Variables & Data Types'),
(1, 'Control Flow'),
(2, 'SQL Basics'),
(3, 'Introduction to Software Engineering');

-- ---------- Demo questions ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES
(1, 'Which data type is most appropriate for storing a single letter grade such as A?',
 'int', 'char', 'bool', 'float', 'B',
 'A single character is stored using the char data type.'),
(1, 'How many bytes does an int typically use on a 32-bit system?',
 '1', '2', '4', '8', 'C',
 'On most 32-bit systems an int occupies 4 bytes of memory.'),
(1, 'Which of the following is NOT a primitive data type in most programming languages?',
 'int', 'char', 'string', 'boolean', 'C',
 'In most languages string is a reference/object type, not a primitive.'),
(2, 'Which loop structure is guaranteed to execute its body at least once?',
 'for', 'while', 'do-while', 'foreach', 'C',
 'A do-while loop checks its condition after the body, so it always runs once.'),
(2, 'What does the break statement do inside a switch block?',
 'Restarts the switch', 'Exits the switch block', 'Pauses the program', 'Skips one case only', 'B',
 'break immediately exits the switch and continues after it.'),
(3, 'Which SQL statement is used to retrieve data from a table?',
 'INSERT', 'UPDATE', 'SELECT', 'DELETE', 'C',
 'SELECT queries and returns rows from one or more tables.'),
(3, 'Which clause filters individual rows BEFORE grouping takes place?',
 'HAVING', 'WHERE', 'GROUP BY', 'ORDER BY', 'B',
 'WHERE filters rows before grouping; HAVING filters after grouping.'),
(4, 'Which development model builds a system in small repeating cycles with continuous feedback?',
 'Waterfall', 'Agile', 'Big Bang', 'V-Model', 'B',
 'Agile develops in short iterations with frequent feedback after each phase.');