-- Create database
CREATE DATABASE  course_registration_system;
USE course_registration_system;

-- Create departments table
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Create courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    seat_limit INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Create registrations table
CREATE TABLE registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (student_id, course_id)
);

-- Create registration_log table
CREATE TABLE registration_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    action ENUM('REGISTER', 'DROP') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_student_registrations ON registrations(student_id);
CREATE INDEX idx_course_registrations ON registrations(course_id);
CREATE INDEX idx_registration_log_timestamp ON registration_log(timestamp);

-- =====================================================
-- 2. CUSTOM FUNCTIONS
-- =====================================================

DELIMITER //

-- Function to get available seats for a course
CREATE FUNCTION get_available_seats(course_id_param INT)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_seats INT DEFAULT 0;
    DECLARE registered_count INT DEFAULT 0;
    DECLARE available_seats INT DEFAULT 0;
    
    -- Get total seats for the course
    SELECT seat_limit INTO total_seats 
    FROM courses 
    WHERE id = course_id_param;
    
    -- Get current registration count
    SELECT COUNT(*) INTO registered_count 
    FROM registrations 
    WHERE course_id = course_id_param;
    
    -- Calculate available seats
    SET available_seats = total_seats - registered_count;
    
    -- Return available seats (minimum 0)
    RETURN GREATEST(available_seats, 0);
END//

-- Function to check if student is already registered for a course
CREATE FUNCTION is_student_registered(student_id_param INT, course_id_param INT)
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE registration_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO registration_count
    FROM registrations
    WHERE student_id = student_id_param AND course_id = course_id_param;
    
    RETURN registration_count > 0;
END//

DELIMITER ;

-- =====================================================
-- 3. STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Stored procedure to register a student for a course
CREATE PROCEDURE register_student_course(
    IN student_id_param INT,
    IN course_id_param INT,
    OUT result_message VARCHAR(255),
    OUT success BOOLEAN
)
BEGIN
    DECLARE available_seats INT DEFAULT 0;
    DECLARE already_registered BOOLEAN DEFAULT FALSE;
    DECLARE student_exists BOOLEAN DEFAULT FALSE;
    DECLARE course_exists BOOLEAN DEFAULT FALSE;
    
    -- Initialize output parameters
    SET success = FALSE;
    SET result_message = '';
    
    -- Check if student exists
    SELECT COUNT(*) > 0 INTO student_exists
    FROM students WHERE id = student_id_param;
    
    -- Check if course exists
    SELECT COUNT(*) > 0 INTO course_exists
    FROM courses WHERE id = course_id_param;
    
    -- Validate inputs
    IF NOT student_exists THEN
        SET result_message = 'Student not found';
    ELSEIF NOT course_exists THEN
        SET result_message = 'Course not found';
    ELSE
        -- Check if student is already registered
        SELECT is_student_registered(student_id_param, course_id_param) INTO already_registered;
        
        IF already_registered THEN
            SET result_message = 'Student is already registered for this course';
        ELSE
            -- Check available seats
            SELECT get_available_seats(course_id_param) INTO available_seats;
            
            IF available_seats <= 0 THEN
                SET result_message = 'No available seats for this course';
            ELSE
                -- Register the student
                INSERT INTO registrations (student_id, course_id)
                VALUES (student_id_param, course_id_param);
                
                SET success = TRUE;
                SET result_message = 'Successfully registered for the course';
            END IF;
        END IF;
    END IF;
END//

-- Stored procedure to drop a student from a course
CREATE PROCEDURE drop_student_course(
    IN student_id_param INT,
    IN course_id_param INT,
    OUT result_message VARCHAR(255),
    OUT success BOOLEAN
)
BEGIN
    DECLARE registration_exists BOOLEAN DEFAULT FALSE;
    
    -- Initialize output parameters
    SET success = FALSE;
    SET result_message = '';
    
    -- Check if registration exists
    SELECT COUNT(*) > 0 INTO registration_exists
    FROM registrations
    WHERE student_id = student_id_param AND course_id = course_id_param;
    
    IF NOT registration_exists THEN
        SET result_message = 'Student is not registered for this course';
    ELSE
        -- Drop the registration
        DELETE FROM registrations
        WHERE student_id = student_id_param AND course_id = course_id_param;
        
        SET success = TRUE;
        SET result_message = 'Successfully dropped from the course';
    END IF;
END//

DELIMITER ;

-- =====================================================
-- 4. TRIGGERS
-- =====================================================

DELIMITER //

-- Trigger to log registration actions after INSERT
CREATE TRIGGER log_registration_insert
AFTER INSERT ON registrations
FOR EACH ROW
BEGIN
    INSERT INTO registration_log (student_id, course_id, action)
    VALUES (NEW.student_id, NEW.course_id, 'REGISTER');
END//

-- Trigger to log registration actions after DELETE
CREATE TRIGGER log_registration_delete
AFTER DELETE ON registrations
FOR EACH ROW
BEGIN
    INSERT INTO registration_log (student_id, course_id, action)
    VALUES (OLD.student_id, OLD.course_id, 'DROP');
END//

DELIMITER ;

CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add password field to students table for student login
ALTER TABLE students ADD COLUMN password VARCHAR(255) DEFAULT NULL;
ALTER TABLE students ADD COLUMN is_active BOOLEAN DEFAULT TRUE;

-- Create sessions table for session management
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(128) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    user_type ENUM('admin', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_session_id (session_id),
    INDEX idx_expires_at (expires_at)
);


-- =====================================================
-- 5. SAMPLE DATA INSERTION
-- =====================================================

-- Insert departments
INSERT INTO departments (name) VALUES
('Computer Science'),
('Information Technology'),
('Software Engineering'),
('Data Science'),
('Cybersecurity');


INSERT INTO students (name, email, department_id) VALUES
('Aswin', 'aswin@icst.edu', 1),
('Hazeem', 'hazeem@icst.edu', 1),
('Nifra', 'nifra@icst.edu', 2),
('Hilma', 'hilma@icst.edu', 2),
('Anshaf', 'anshaf@icst.edu', 3),
('Shihab', 'shihab@icst.edu', 3),
('Afsan', 'afsan@icst.edu', 4);

-- Insert courses
INSERT INTO courses (name, course_code, department_id, seat_limit) VALUES
('Introduction to Programming', 'CS101', 1, 30),
('Data Structures and Algorithms', 'CS201', 1, 25),
('Database Systems', 'CS301', 1, 20),
('Web Development', 'IT101', 2, 35),
('Network Administration', 'IT201', 2, 20),
('Mobile App Development', 'IT301', 2, 25),
('Software Engineering Principles', 'SE101', 3, 30),
('Agile Development', 'SE201', 3, 20),
('Software Testing', 'SE301', 3, 25),
('Machine Learning', 'DS101', 4, 20),
('Big Data Analytics', 'DS201', 4, 15),
('Data Visualization', 'DS301', 4, 25),
('Ethical Hacking', 'CY101', 5, 20),
('Network Security', 'CY201', 5, 18),
('Digital Forensics', 'CY301', 5, 15);

-- Insert some sample registrations
INSERT INTO registrations (student_id, course_id) VALUES
(1, 1), (1, 2), (1, 4),
(2, 1), (2, 3), (2, 7),
(3, 4), (3, 5), (3, 6),
(4, 4), (4, 5), (4, 10),
(5, 7), (5, 8), (5, 9),
(6, 7), (6, 8), (6, 1),
(7, 10), (7, 11), (7, 12),
(8, 10), (8, 12), (8, 4),
(9, 13), (9, 14), (9, 15),
(10, 13), (10, 14), (10, 1);


INSERT INTO admin_users (username, password, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@icst.edu');

SET SQL_SAFE_UPDATES = 0;

UPDATE students 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE password IS NULL;


UPDATE admin_users
SET password = '$2y$10$f84Aig62k2V/OTCTGeevzOYlu9DDSw0LPFeTTDeTl2DG9q8P7HeTS' 
WHERE username = 'admin';

ALTER TABLE students ADD COLUMN password VARCHAR(255) DEFAULT NULL;


-- Create additional admin user for testing

UPDATE students 
SET password = '$2y$10$SizkBJ0g1ULW/D2kmQpYpODaaTi7FmT7dzSUPIfJnIcjfgZ947gxS';

-- =====================================================
-- 6. VERIFICATION QUERIES
-- =====================================================

-- Display setup completion message
SELECT 'Database setup completed successfully!' as Status;

-- Show summary statistics
SELECT 
    'Summary Statistics' as Info,
    (SELECT COUNT(*) FROM departments) as Departments,
    (SELECT COUNT(*) FROM students) as Students,
    (SELECT COUNT(*) FROM courses) as Courses,
    (SELECT COUNT(*) FROM registrations) as Active_Registrations;

-- Show sample course availability

SELECT 
    c.course_code,
    c.name as course_name,
    c.seat_limit,
    get_available_seats(c.id) as available_seats,
    (c.seat_limit - get_available_seats(c.id)) as enrolled_students
FROM courses c
ORDER BY c.course_code;

SELECT 'Authentication schema created successfully!' as Status;

UPDATE students 
SET password = '$2y$10$SizkBJ0g1ULW/D2kmQpYpODaaTi7FmT7dzSUPIfJnIcjfgZ947gxS';


SET SQL_SAFE_UPDATES = 1;



