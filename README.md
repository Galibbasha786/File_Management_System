1. Project Overview
Goals
Develop a secure system for managing files with robust authentication, encryption, and threat detection.

Allow users to perform file operations (read, write, share, view metadata) securely.

Protect against common security threats like buffer overflow and malware.

Expected Outcomes
A fully functional file management system with:

Secure user authentication (password-based and 2FA).

Encrypted file storage and sharing.

Detection and prevention of security threats.

Intuitive user interface for file operations.

Scope
The system will support:

User registration and authentication.

File upload, download, and sharing with access control.

Metadata viewing (e.g., file size, creation date).

Threat detection for uploaded files.

Logging and monitoring of user activities.
Key Features:
User Registration and Login:
•	Users can register with a strong password and enable 2FA.
Example: Use OTP via email for 2FA.
Role-Based Access Control (RBAC):
•	Admins can assign roles (e.g., Admin, User) and permissions.
Example: Admins can delete,rename,share,download,upload files, while Users can only read/download/upload.
Session Management:
•	Use secure tokens (CRSF token and session_start( ) and session_destroyfrom PHP ) for session management.
Example: Invalidate tokens after logout.
Module 2: File Management and Encryption
Key Features:
File Upload and Download:
•	Users can upload files, which are encrypted before storage.
Example: Used AES-256 for encryption.
File Sharing:
•	Generate secure, expirable links for file sharing.
Example: Share a file with 24-hour expiration.
Metadata Viewing:
•	Display file metadata (e.g., size, creation date, owner).
Example: Show metadata in a table on the user dashboard.
Module 3: Threat Detection and Monitoring
Key Features:
Malware Scanning:
•	Scan uploaded files using antivirus APIs (e.g., ClamAV).
Example: Block files flagged as malicious.
Buffer Overflow Prevention:
•	Validate input sizes and use secure coding practices.
Example: Reject files larger than a specified limit.
Logging and Monitoring:
•	Log all file operations and authentication attempts.
Example: Use ELK Stack for real-time monitoring.
Technology Used
Programming Languages
Backend: PHP, Java script.
Frontend: HTML, CSS.
Libraries and Tools
Authentication: PHP MAILER for  Authentication.
Encryption: PHP
Threat Detection: ClamAV (malware scanning).
Database:  MySQL.
![image](https://github.com/user-attachments/assets/95749edd-040a-4dad-abc0-7f3e0b2cb01c)

