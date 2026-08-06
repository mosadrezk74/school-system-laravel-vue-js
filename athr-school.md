# Athr School Management System

> Modular School Management System built with Laravel 13 + Vue 3 + Inertia.js + Vite + Vuetify + nwidart/laravel-modules.

---

# Project Philosophy

هذا المشروع مبني بحيث يكون:

- Modular Architecture
- Clean Architecture
- SOLID Principles
- Repository Pattern
- DTO Pattern
- Action Pattern
- Policy Authorization
- Inertia + Vue
- قابل للتوسع بدون تعديل الكود القديم.

أي Feature جديدة يجب أن تمشي بنفس المعمارية الموجودة.

---

# Tech Stack

## Backend

- Laravel 13
- PHP 8.5+
- MySQL
- Spatie Permission
- Laravel Modules
- DTO Pattern
- Repository Pattern
- Action Pattern

## Frontend

- Vue 3
- Inertia
- Vuetify
- Vite
- Vue-i18n

---

# Project Structure

```
Modules/

    Academic/
    Student/
    Teacher/
    Guardian/
    SchoolClass/
    Behavior/
    Setting/
    Authentication/
    ...

```

كل Module مستقل بنفس الشكل.

---

# Standard Module Structure

```
Module/

│
├── Actions/
│
├── DTOs/
│
├── Exceptions/
│
├── Http/
│   ├── Controllers/
│   └── Requests/
│
├── Models/
│
├── Policies/
│
├── Providers/
│
├── Queries/
│
├── Repositories/
│
├── Resources/
│
├── Transformers/
│
├── routes/
│
├── lang/
│
├── resources/
│   └── js/
│       └── Pages/
│
└── Database/

```

---

# Backend Flow

كل عملية CRUD تمشي بنفس الخطوات.

```
Route

↓

Controller

↓

Policy Authorization

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

Inertia Response
```

ممنوع تخطي أي Layer.

---

# Controller Rules

الـ Controller مسئول فقط عن:

- Authorization
- Request Validation
- إنشاء DTO
- استدعاء Action
- Return Response

ولا يحتوي أي Business Logic.

---

# Action Rules

الـ Action يحتوي Business Logic فقط.

مثل:

- Create
- Update
- Archive
- Restore

ويستخدم دائمًا:

```
execute()
```

أو

```
executeWithTransaction()
```

إذا كانت العملية تعدل أكثر من جدول.

---

# Repository Rules

Repository مسئول فقط عن:

- Database Queries

ولا يحتوي أي Validation.

ولا يحتوي أي Authorization.

ولا يستخدم:

```
auth()

request()

session()
```

داخل Repository.

---

# DTO Rules

كل DTO يجب أن يكون

```
final readonly class
```

مثال

```
CreateStudentDTO

UpdateStudentDTO

StudentFilterDTO
```

ولا يتم استخدام BaseDTO ديناميكي.

---

# Policies

كل Module له Policy خاص به.

مثال

```
StudentPolicy

TeacherPolicy

GuardianPolicy

BehaviorPolicy
```

ويتم تسجيله داخل

```
ServiceProvider
```

باستخدام

```
Gate::policy()
```

---

# Vue Structure

كل Resource كبير يجب أن يحتوي:

```
Pages/

    Index.vue

    Create.vue

    Edit.vue

    Show.vue

    _Form.vue
```

---

# Form Rules

كل Form يجب أن يكون:

- Tabs واضحة
- Section Header
- Icon
- Title
- Description

كل Field يحتوي:

- Label
- Description
- Required Indicator

---

# Show Page Rules

كل صفحة Show يجب أن تحتوي:

- Hero Header

- Quick Actions

- Information Cards

- Related Records

- Statistics (إن وجدت)

---

# Index Rules

كل صفحة Index يجب أن تحتوي:

- Title

- Subtitle

- Search

- Filters

- Empty State

- Pagination

- Actions

---

# UI Rules

كل الصفحات يجب أن تكون بنفس التصميم.

- نفس المسافات
- نفس الـ Cards
- نفس الـ Buttons
- نفس الـ Dialogs
- نفس الـ Colors
- نفس الـ Empty State

---

# Validation Rules

كل Validation داخل:

```
Form Request
```

وليس داخل Controller.

---

# Exceptions

أي Business Error يستخدم

```
Custom Exception
```

مثال

```
StudentOperationException

BehaviorOperationException
```

ولا يتم إرجاع

```
return [
    'success'=>false
]
```

من الـ Service أو Action.

---

# Resource Rules

أي Response يخرج للواجهة يستخدم

```
Json Resource
```

أو

```
Transformer
```

وليس Model مباشر.

---

# Routes

استخدام Resource Routes دائمًا.

```
Route::resource(...)
```

والـ Routes الإضافية فقط عند الحاجة.

مثل

```
restore

archive

assign

detach
```

---

# Database Rules

أي Module يحتوي:

- Migration
- Seeder (إذا احتاج)

ويجب احترام

- Foreign Keys
- Cascade
- Soft Delete

---

# Translation Rules

أي نص جديد يضاف داخل

```
Modules/Setting/resources/js/plugins/i18n/locales
```

ولا يتم كتابة Strings مباشرة داخل Vue.

---

# Coding Rules

ممنوع:

- Business Logic داخل Controller

- Query داخل Vue

- auth() داخل Repository

- request() داخل Repository

- return array للأخطاء

- استخدام Models مباشرة داخل Vue

---

# Current Modules

## Completed

- Academic
- Student
- Guardian
- Teacher
- School Class
- Behavior
- Setting

---

# Build Verification

قبل إنهاء أي Task يجب التأكد من:

```
vendor/bin/pint --dirty
```

ثم

```
npm run build
```

ولا يتم تشغيل Tests إلا إذا طُلب ذلك صراحة.

---

# Development Workflow

أي Feature جديدة يجب أن تسير بالترتيب التالي:

```
Migration

↓

Model

↓

DTO

↓

Repository

↓

Action

↓

Request

↓

Policy

↓

Controller

↓

Resource

↓

Vue Pages

↓

Translations

↓

Pint

↓

npm run build
```

---

# Future Modules

- Subjects
- Timetable
- Attendance
- Exams
- Grades
- Fees
- Payments
- Reports
- Notifications
- Parent Portal
- Student Portal
- Teacher Portal

---

# Golden Reference

يعتبر Module Student هو المرجع الأساسي (Golden Reference).

أي Module جديد يجب أن يتبع نفس:

- Architecture
- Folder Structure
- UI
- UX
- Coding Style
- DTO Pattern
- Repository Pattern
- Action Pattern
- Policy Pattern