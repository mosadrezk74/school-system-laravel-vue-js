# SCHOOL MANAGEMENT SYSTEM - PROJECT CONTEXT

## Project Overview
We are building a modern School Management System (ERP) for schools. This is a single-school product (not multi-tenant) that will be sold to individual schools. The system is built with:

- **Backend**: Laravel 13 (PHP 8.4+)
- **Frontend**: Vue 3 + JS
- **Database**: MySQL 8.4+
- **Architecture**: Modular Monolith
- **Core Packages**: Spatie (Permission, Activitylog, Media, Translatable)

## Database Schema
We use **DBML (Database Markup Language)** for our schema design. The schema is located in `docs/schema.dbml` and includes all tables with proper relationships, indexes, and constraints.

### Key Features
- 🏫 **Academic Management**: Grade levels, classes, sections, subjects
- 👨‍🎓 **Student Management**: Profiles, enrollment, academic history
- 👨‍🏫 **Teacher & Staff Management**: Profiles, attendance, payroll
- 👨‍👩‍👧‍👦 **Parent Management**: Parent-student relationships
- 📅 **Timetable & Scheduling**: Class schedules, room management
- 📝 **Attendance**: Student and employee attendance tracking
- 📚 **Exams & Grades**: Exam management, results, grade books
- 📄 **Assignments**: Homework, projects, submissions, grading
- 💰 **Fee Management**: Fee structures, invoices, payments, receipts
- 📚 **Library**: Books, borrowing, reservations
- 📦 **Inventory**: Stock management, purchases, suppliers
- 🚌 **Transport**: Routes, vehicles, student assignments
- 🏠 **Hostel**: Room management, student assignments
- ⭐ **Discipline**: Behavior tracking, rewards, points
- 💬 **Chat**: Real-time messaging between users
- 📢 **Notifications**: Email, SMS, Push, In-app
- 🎨 **Media**: File management with Spatie Media Library
- 🌐 **Multi-Language**: Spatie Translatable with JSON columns 
- 🔒 **RBAC**: Spatie Permission-based roles & permissions
- 📋 **Audit**: Spatie Activitylog for all changes
- 📊 **Reports**: Custom report generation

### Database Schema Structure
```dbml
// Main Tables (72 total)
Table users { ... }
Table school_settings { ... }
Table academic_years { ... }
Table semesters { ... }
Table grade_levels { ... }
Table classes { ... }
Table subjects { ... }
Table student_profiles { ... }
Table teacher_profiles { ... }
Table parent_profiles { ... }
Table staff_profiles { ... }
Table departments { ... }
// ... and more (see complete schema)
