# 🎓 Athr School Management System

> Enterprise School Management System built with Laravel 13 + Vue 3 + Inertia + Vuetify using Modular Architecture.

---

# Table of Contents

1. Project Overview
2. Vision
3. Objectives
4. Technology Stack
5. Architecture
6. Folder Structure
7. Modules
8. User Roles
9. Authentication
10. Authorization
11. Portal System
12. Dashboard System
13. Report Engine
14. Translation System
15. Settings System
16. Notification System
17. Communication System
18. Finance
19. Attendance
20. Timetable
21. Assignment
22. Examination
23. Study Materials
24. Meetings
25. Repository Pattern
26. DTO Pattern
27. Action Pattern
28. Query Pattern
29. Services
30. Policies
31. API
32. Frontend
33. UI Components
34. Security
35. Performance
36. Module Dependencies
37. Development Workflow
38. Future Roadmap

---

# 1. Project Overview

Athr School is an enterprise-level School Management System designed for a single school deployment.

The project focuses on:

- scalability
- maintainability
- clean architecture
- reusable modules
- multi-language support
- role-based access
- portal-based user experience

The system is built to support schools of different sizes without changing the architecture.

---

# 2. Vision

The project follows one principle:

> Every feature must be reusable.

Business logic must never be duplicated.

The Admin Panel remains the source of truth.

Every Portal consumes existing business logic instead of implementing its own.

---

# 3. Technology Stack

Backend

- Laravel 13
- PHP 8.5
- MySQL
- Laravel Modules
- Spatie Permission
- Laravel Fortify

Frontend

- Vue 3
- Inertia
- Vuetify
- Vite

Development

- Pint
- Composer
- NPM

---

# 4. Architecture

The project follows strict Clean Architecture.

```
Route

↓

Controller

↓

Policy

↓

DTO

↓

Action

↓

Repository

↓

Model

↓

Resource

↓

Vue
```

Controllers contain no business logic.

Repositories never return HTTP responses.

Actions execute business rules.

DTOs transport validated data.

Resources format output.

---

# 5. Folder Structure

app/

Modules/

resources/

routes/

bootstrap/

config/

database/

public/

---

# 6. Modules

Current modules

- Academic
- Assignment
- Attendance
- Auth
- Behavior
- Classroom
- Communication
- Core
- Examination
- Finance
- Guardian
- Meeting
- Notification
- Report
- SchoolClass
- Setting
- Student
- StudyMaterial
- Subject
- Teacher
- Timetable

Every module follows the same internal structure.

Controllers

Actions

Repositories

Interfaces

DTOs

Requests

Resources

Policies

Queries

Services

Routes

Views

---

# 7. User Roles

Owner

Admin

Teacher

Guardian

Student

Each role has different permissions.

Authorization uses Spatie Permission.

---

# 8. Authentication

Laravel Fortify

Custom LoginResponse

PortalRouter

Automatic redirect

Owner → Admin Dashboard

Teacher → Teacher Portal

Guardian → Parent Portal

Student → Student Portal

---

# 9. Authorization

Authorization has three layers.

Middleware

↓

Policy

↓

PortalScopeService

Ownership is enforced in the Query layer.

Never inside Controllers.

---

# 10. PortalScopeService

PortalScopeService is the ownership authority.

It resolves

Teacher Classes

Teacher Students

Guardian Children

Student Profile

Accessible Students

Accessible Classes

Every portal depends on PortalScopeService.

No portal bypasses it.

---

# 11. Portal System

Three dedicated portals.

Teacher Portal

Parent Portal

Student Portal

Admin is completely isolated.

Portals never duplicate Admin logic.

Instead they reuse

Actions

Repositories

Queries

Policies

Resources

---

# 12. Dashboard System

Shared DashboardWidget

Shared DashboardWidgetCollector

Widgets define

permission

module

priority

visibility

route

No dashboard contains hardcoded widgets.

---

# 13. Teacher Portal

Dashboard

Today's Schedule

Classes

Attendance

Homework

Study Materials

Meetings

Notifications

Profile

---

# 14. Parent Portal

Dashboard

Children

Attendance

Homework

Grades

Fees

Payments

Study Materials

Meetings

Notifications

Profile

---

# 15. Student Portal

Dashboard

Homework

Attendance

Grades

Schedule

Study Materials

Meetings

Notifications

Profile

---

# 16. Report Engine

Metadata-driven reporting system.

Providers

Attendance

Student

Finance

Invoices

Meetings

Study Materials

Every provider returns

Rows

Columns

Summary

Metadata

Frontend automatically renders reports.

---

# 17. Translation System

Languages stored in database.

Default language

RTL support

Automatic translation service

Translatable models

JSON translations

---

# 18. Settings

School Information

Organization

Languages

AI Translation

Modules

Branding

System Configuration

---

# 19. Attendance

Attendance uses

AttendanceRepository

AttendanceRosterService

MarkAttendanceAction

Student attendance history

Teacher attendance

Reports

Dashboard widgets

---

# 20. Timetable

Timetable

Timetable Entries

Periods

Schedule

Teacher Schedule

Student Schedule

---

# 21. Assignment

Assignments

Submission

Publishing

Grading

Teacher workflows

Student workflows

---

# 22. Examination

Exams

Exam Results

Grades

Statistics

Policies

---

# 23. Study Materials

Upload

Download

Preview

Teacher ownership

Student access

Guardian access

---

# 24. Meetings

Create

Update

Cancel

Join

Portal filtering

---

# 25. Finance

Invoices

Payments

Outstanding Fees

Ledger

Reports

---

# 26. Notification System

Shared Notification Center

Unread count

Mark as read

Portal notifications

Admin notifications

---

# 27. Repository Pattern

Repositories are responsible only for database operations.

No business logic.

---

# 28. DTO Pattern

Every Create and Update operation uses readonly DTOs.

DTOs are immutable.

---

# 29. Action Pattern

Business logic belongs inside Actions.

Controllers never implement business logic.

---

# 30. Query Pattern

Filtering

Searching

Sorting

Pagination

Ownership

Everything happens through Query objects.

---

# 31. Policies

Policies enforce

Permissions

Ownership

Portal restrictions

No frontend-only authorization exists.

---

# 32. Frontend

Vue 3

Inertia

Vuetify

Composable architecture

Reusable components

---

# 33. Shared Components

DynamicTable

DynamicFilters

PortalHeader

PortalStatCard

PortalNav

Theme Switcher

---

# 34. Security

PortalScopeService

Policies

Middleware

Permission checks

Ownership checks

Query-layer filtering

Request ownership enforcement

Teacher spoof prevention

Student spoof prevention

Guardian child validation

---

# 35. Performance

Memoization

Lazy loading

Eager loading

Pagination

Dashboard aggregation

Reusable queries

No duplicated SQL

---

# 36. Development Workflow

Controller

↓

Request

↓

DTO

↓

Action

↓

Repository

↓

Resource

↓

Vue

Never violate this flow.

---

# 37. Coding Standards

- SOLID
- Clean Code
- Small Methods
- readonly DTOs
- Interface-based Repositories
- No duplicated logic
- Reuse existing Actions
- Admin is the source of truth
- PortalScopeService is the ownership authority

---

# 38. Future Roadmap

- AI Assistant
- Parent Mobile App
- Student Mobile App
- Teacher Mobile App
- Push Notifications
- Live Classes
- LMS Integration
- Online Exams
- Report PDF Builder
- Excel Export
- Analytics
- Audit Logs

---

# Final Notes

This project follows a reusable modular architecture.

Every new feature must answer these questions before implementation:

1. Does it already exist in Admin?
2. Can an existing Action be reused?
3. Can an existing Repository be reused?
4. Can an existing Query be reused?
5. Can PortalScopeService enforce ownership?
6. Is a new Action really necessary?
7. Will Admin remain unaffected?

If the answer to reuse is YES, duplication is forbidden.

The Admin Panel is always the source of truth.

Portals are specialized views over existing business logic.