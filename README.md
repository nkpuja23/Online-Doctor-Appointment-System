# Online Doctor Appointment System
🏥 NK Hospitals Scheduling Portal
This project is a full-stack, secure web application designed to modernize the patient appointment workflow, moving beyond phone calls and manual records into a seamless digital system. It serves as a comprehensive demonstration of Database Management System (DBMS) principles, security best practices, and advanced PHP engineering.

I. Key Achievements & Core Components
This application successfully tackles real-world scheduling challenges through automation and strict data integrity rules.

11-Table Relational Schema: The database is built on a highly structured schema (normalized to include tables like Patient_Demographics and Payments), proving mastery of relational design.

Secure Authentication: We implemented SHA256 hashing for all user passwords, ensuring data security by never storing passwords in plaintext. Access is controlled by Role-Based Access Control (RBAC).

Professional UI/UX: The application features a stunning Aqua and Rose visual theme, custom iconography, a responsive layout, and interactive components (like the specialization accordion) for a flawless user experience.

II. Advanced Business Logic (The Project's "Engine")
The system's intelligence is defined by its ability to automate complex rules:

Time-Sensitive Cancellation Policy (Financial Logic):

The system accurately calculates the time difference down to the second against the appointment slot.

If a cancellation is attempted within 24 hours of the appointment time, the system enforces a mandatory ₹500 cancellation fee, redirecting the user to a dedicated payment portal.

The payment is recorded in the Payments table, protecting clinic revenue automatically.

Optimized Booking Flow:

Booking is instantaneously confirmed if a time slot is available, eliminating the "pending" status and administrative bottlenecks.

Slots are filtered by Specialization and limited to start one day after the current date (no chaotic same-day booking).

Multi-Tiered Portals: Distinct interfaces ensure data security:

Patient Portal: Focuses on booking, viewing history, and cancellation management.

Doctor Portal: Provides a concise daily schedule, a "Mark Complete" action button for managing workflow, and access to patient history.

III. Technical Stack
Backend Logic & Generation: PHP 7.4+ (Used for secure processing, validation, and dynamic HTML output).

Database: MySQL (MariaDB) / DBMS Implementation.

Security: SHA256 Hashing, Prepared Statements (mitigating SQL injection), and Session Management.

Presentation & Tools:

Styling: Custom CSS, Poppins Font, and Font Awesome Icons.

Analytics: Chart.js (used in the Admin Dashboard for visual reporting).

Installation & Demo
Environment: Ensure XAMPP/MAMP is running (Apache and MySQL services active).

Database Setup: Create a database named doctor_appointment_db and run the full schema creation and data insertion scripts.

Access: Navigate to http://localhost/online_doc_app/role_select.php.

Test Credentials:

Role: Admin

Email: admin.a@sys.com

Password: Admin#2025

This project successfully demonstrates a high level of proficiency in secure full-stack development, relational database integrity, and complex business logic modeling.
