-- Create OTP Sessions Table for secure OTP storage
CREATE TABLE IF NOT EXISTS otp_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp_code INT NOT NULL,
    is_used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_otp_code (otp_code),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
