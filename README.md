# Online-Doctor-Appointment-System
🏥 NK Hospitals Online Appointment System

A Full-Stack Database Management System (DBMS) Project

This application is a modern, high-fidelity web portal designed to replace manual scheduling and administrative processes within a healthcare environment. Built using PHP, MySQL (MariaDB), and vanilla JavaScript/CSS, the system demonstrates mastery of complex transactional logic, security best practices, and robust relational database design.

🚀 Key Project Highlights

Feature

Technical Implementation Focus

Transactional Integrity

Enforced via a 10-table schema (PK/FK constraints) and atomic transactions (BEGIN/COMMIT).

Time-Sensitive Business Logic

Automated calculation for the 24-Hour Fee Cutoff during patient cancellation.

Advanced Scheduling

Logic to generate 1-Hour Available Slots dynamically, filtered by date and specialization.

Security

Used SHA256 Hashing for all password storage and Role-Based Access Control (RBAC) to separate user permissions.

UI/UX

Professional, full-screen Aqua & Rose interface with dynamic elements (Live Clock, Custom Accordions).

Analytics

Functional Admin Dashboard featuring live Chart.js graphs for system metrics (e.g., user registration trends, revenue vs. cancellation rates).

💡 Core Functionality & Logic

The system is segmented into three distinct user portals (Patient, Doctor, Admin), each with specialized access:

1. Patient Portal (Features patient_dashboard.php, book_appointment.php)

Filtered Booking: Patients select appointments based on Specialization and Date.

Availability Check: Slots are dynamically filtered out in real-time if already booked or if the time has passed (for same-day viewing).

Conditional Cancellation (Advanced Logic):

If a patient cancels an appointment more than 24 hours in advance, the slot is immediately released (Status ID 4).

If the cancellation is within the 24-hour window, the system redirects the user to a secure Payment Portal (payment_portal.php) to simulate the ₹500 fee payment, preventing unauthorized cancellation.

2. Doctor Portal (Features doctor_dashboard.php)

Centralized Schedule: Doctors view a clean, consolidated list of their Confirmed and Completed appointments.

Workflow Management: Ability to mark a confirmed appointment as Complete (Status ID 3), updating the database for revenue and report generation.

Clinical Tools: Access to patient lists and a dedicated Calendar for planning personal events and viewing availability.

3. Admin Portal (Features admin_dashboard.php, admin_settings.php)

Metric Dashboard: High-level overview using Chart.js visuals (Monthly Users, Appointment Success Rate).

System Configuration: The admin_settings.php page allows the administrator to dynamically adjust cancellation fee amounts and max booking lead times, with changes stored live in the System_Config table.

System Integrity: Tools for managing lookup data (Specializations, Clinics) and performing password resets for staff.

🛠️ Technical Structure

A. Database Schema (MySQL/MariaDB)

The system is built on 11 tables normalized to avoid data redundancy.

Table

Purpose

Key Relationships

Users / Roles

Central authentication and permission control.

1:1 link to Doctor and Patient tables.

Appointments

Core junction record, linking 5 FKs (Patient, Doctor, Schedule, Status).



Doctor_Schedules

Stores defined availability blocks (e.g., Monday 9 AM - 1 PM).



Patient_Demographics

Stores extensive patient data (Nationality, Marital Status, Emergency Contacts) gathered via the multi-column registration form.



Payments

Tracks records of cancellation fees paid (linked 1:1 to the appointment).



B. Deployment Stack

Frontend Generation: PHP (used to generate all HTML, CSS, and dynamic content).

Styling & UI: Custom CSS, Poppins Font, Font Awesome Icons.

Backend Database: MySQL/MariaDB.

Libraries: Chart.js (for analytics/graphs).

Installation and Testing

Start XAMPP/MAMP: Ensure Apache and MySQL services are running.

Create Database: Create a database named doctor_appointment_db.

Run SQL Scripts: Execute the CREATE TABLE and INSERT scripts to build the schema and populate initial doctors/schedules.

Access: Navigate to http://localhost/online_doc_app/role_select.php.

Default Admin Test Credentials:

Role: Admin

Email: admin.a@sys.com

Password: Admin#2025

This project successfully demonstrates high-level proficiency in secure full-stack web development and database engineering.
