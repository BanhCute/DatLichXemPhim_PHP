-- Create database
CREATE DATABASE IF NOT EXISTS movie_booking;
USE movie_booking;

-- Create User table
CREATE TABLE IF NOT EXISTS User (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('USER', 'ADMIN') DEFAULT 'USER',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Movie table
CREATE TABLE IF NOT EXISTS Movie (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    duration INT NOT NULL,
    imageUrl VARCHAR(255),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create ShowTime table
CREATE TABLE IF NOT EXISTS ShowTime (
    id INT PRIMARY KEY AUTO_INCREMENT,
    movieId INT NOT NULL,
    startTime DATETIME NOT NULL,
    endTime DATETIME NOT NULL,
    room VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (movieId) REFERENCES Movie(id)
);

-- Create Seat table
CREATE TABLE IF NOT EXISTS Seat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    number VARCHAR(10) NOT NULL,
    showTimeId INT NOT NULL,
    status ENUM('AVAILABLE', 'BOOKED', 'RESERVED') DEFAULT 'AVAILABLE',
    bookingId INT,
    FOREIGN KEY (showTimeId) REFERENCES ShowTime(id)
);

-- Create Booking table
CREATE TABLE IF NOT EXISTS Booking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    showTimeId INT NOT NULL,
    totalPrice DECIMAL(10,2) NOT NULL,
    status ENUM('PENDING', 'CONFIRMED', 'CANCELLED') DEFAULT 'PENDING',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES User(id),
    FOREIGN KEY (showTimeId) REFERENCES ShowTime(id)
);

-- Add foreign key to Seat table after Booking table is created
ALTER TABLE Seat ADD FOREIGN KEY (bookingId) REFERENCES Booking(id);

-- Insert sample data
INSERT INTO User (email, password, name, role) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'ADMIN'),
('user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User Test', 'USER');

INSERT INTO Movie (title, description, duration, imageUrl) VALUES
('Avengers: Endgame', 'Biệt đội siêu anh hùng Marvel', 181, 'https://example.com/avengers.jpg'),
('Spider-Man: No Way Home', 'Người Nhện: Không còn nhà', 148, 'https://example.com/spiderman.jpg');

INSERT INTO ShowTime (movieId, startTime, endTime, room, price) VALUES
(1, '2024-03-20 10:00:00', '2024-03-20 13:01:00', 'SCREEN 1', 120000),
(1, '2024-03-20 14:00:00', '2024-03-20 17:01:00', 'SCREEN 1', 120000),
(2, '2024-03-20 10:00:00', '2024-03-20 12:28:00', 'SCREEN 2', 100000);

-- Insert seats for each showtime
INSERT INTO Seat (number, showTimeId, status) 
SELECT 
    CONCAT(
        CHAR(65 + (number DIV 10)), -- Row letter (A-J)
        LPAD((number MOD 10) + 1, 2, '0') -- Seat number (01-10)
    ) as seat_number,
    showtime_id,
    'AVAILABLE' as status
FROM 
    (SELECT 0 AS number UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) row,
    (SELECT 0 AS x UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) col,
    (SELECT id as showtime_id FROM ShowTime) showtime
ORDER BY showtime_id, seat_number; 