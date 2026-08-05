#!/bin/bash

# 1. إنشاء المجلدات المطلوبة
mkdir -p Modules/Student/DTOs \
         Modules/Student/Exceptions \
         Modules/Student/Repositories \
         Modules/Student/Services \
         Modules/Student/Http/Controllers \
         Modules/Student/Http/Requests \
         Modules/Student/Providers \
         Modules/Student/Routes \
         Modules/Student/Resources/assets/js/Pages

# 2. DTOs
cat << 'PHP' > Modules/Student/DTOs/CreateStudentDTO.php
<?php

namespace Modules\Student\DTOs;

use Modules\Student\Http\Requests\StoreStudentRequest;

readonly class CreateStudentDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $username,
        public string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public ?string $phone,
        public bool $isActive,
        public int $academicYearId,
        public int $currentClassId,
        public ?string $admissionDate,
        public ?float $scholarshipPercentage,
        public bool $isGraduated,
        public ?array $extraFields
    ) {}

    public static function fromRequest(StoreStudentRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            username: $request->validated('username'),
            gender: $request->validated('gender'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            phone: $request->validated('phone'),
            isActive: (bool) $request->validated('is_active', true),
            academicYearId: (int) $request->validated('academic_year_id'),
            currentClassId: (int) $request->validated('current_class_id'),
            admissionDate: $request->validated('admission_date'),
            scholarshipPercentage: $request->validated('scholarship_percentage') ? (float) $request->validated('scholarship_percentage') : null,
            isGraduated: (bool) $request->validated('is_graduated', false),
            extraFields: $request->validated('extra_fields', [])
        );
    }

    public function toUserData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'username' => $this->username,
            'gender' => $this->gender,
            'birth_date' => $this->birthDate,
            'national_id' => $this->nationalId,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
            'role' => 'student',
        ];
    }

    public function toProfileData(int $userId): array
    {
        return [
            'user_id' => $userId,
            'academic_year_id' => $this->academicYearId,
            'current_class_id' => $this->currentClassId,
            'admission_date' => $this->admissionDate,
            'scholarship_percentage' => $this->scholarshipPercentage,
            'is_graduated' => $this->isGraduated,
            'extra_fields' => $this->extraFields ? json_encode($this->extraFields) : null,
        ];
    }
}
PHP

cat << 'PHP' > Modules/Student/DTOs/UpdateStudentDTO.php
<?php

namespace Modules\Student\DTOs;

use Modules\Student\Http\Requests\UpdateStudentRequest;

readonly class UpdateStudentDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $password,
        public string $username,
        public string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public ?string $phone,
        public bool $isActive,
        public int $academicYearId,
        public int $currentClassId,
        public ?string $admissionDate,
        public ?float $scholarshipPercentage,
        public bool $isGraduated,
        public ?array $extraFields
    ) {}

    public static function fromRequest(UpdateStudentRequest $request): self
    {
        return new self(
            id: (int) $request->route('student'),
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            username: $request->validated('username'),
            gender: $request->validated('gender'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            phone: $request->validated('phone'),
            isActive: (bool) $request->validated('is_active', true),
            academicYearId: (int) $request->validated('academic_year_id'),
            currentClassId: (int) $request->validated('current_class_id'),
            admissionDate: $request->validated('admission_date'),
            scholarshipPercentage: $request->validated('scholarship_percentage') ? (float) $request->validated('scholarship_percentage') : null,
            isGraduated: (bool) $request->validated('is_graduated', false),
            extraFields: $request->validated('extra_fields', [])
        );
    }

    public function toUserData(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'gender' => $this->gender,
            'birth_date' => $this->birthDate,
            'national_id' => $this->nationalId,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
        ];

        if (!empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        return $data;
    }

    public function toProfileData(): array
    {
        return [
            'academic_year_id' => $this->academicYearId,
            'current_class_id' => $this->currentClassId,
            'admission_date' => $this->admissionDate,
            'scholarship_percentage' => $this->scholarshipPercentage,
            'is_graduated' => $this->isGraduated,
            'extra_fields' => $this->extraFields ? json_encode($this->extraFields) : null,
        ];
    }
}
PHP

# 3. Exceptions
cat << 'PHP' > Modules/Student/Exceptions/StudentNotFoundException.php
<?php

namespace Modules\Student\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;

class StudentNotFoundException extends Exception
{
    public function render(): RedirectResponse
    {
        return redirect()->route('students.index')->with('error', 'الطالب غير موجود');
    }
}
PHP

cat << 'PHP' > Modules/Student/Exceptions/StudentCreationFailedException.php
<?php

namespace Modules\Student\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;

class StudentCreationFailedException extends Exception
{
    public function render(): RedirectResponse
    {
        return back()->with('error', 'حدث خطأ أثناء إنشاء حساب الطالب');
    }
}
PHP

# 4. Repositories
cat << 'PHP' > Modules/Student/Repositories/StudentRepositoryInterface.php
<?php

namespace Modules\Student\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;

interface StudentRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?User;
    public function create(array $userData, array $profileData): User;
    public function update(User $student, array $userData, array $profileData): bool;
    public function delete(User $student): bool;
}
PHP

cat << 'PHP' > Modules/Student/Repositories/StudentRepository.php
<?php

namespace Modules\Student\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StudentRepository implements StudentRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return User::where('role', 'student')
            ->with(['studentProfile'])
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::where('role', 'student')
            ->with(['studentProfile'])
            ->find($id);
    }

    public function create(array $userData, array $profileData): User
    {
        return DB::transaction(function () use ($userData, $profileData) {
            $user = User::create($userData);
            $profileData['user_id'] = $user->id;
            $user->studentProfile()->create($profileData);
            return $user->load('studentProfile');
        });
    }

    public function update(User $student, array $userData, array $profileData): bool
    {
        return DB::transaction(function () use ($student, $userData, $profileData) {
            $student->update($userData);
            $student->studentProfile()->updateOrCreate(
                ['user_id' => $student->id],
                $profileData
            );
            return true;
        });
    }

    public function delete(User $student): bool
    {
        return DB::transaction(function () use ($student) {
            $student->studentProfile()->delete();
            return $student->delete();
        });
    }
}
PHP

# 5. Services & Controller
cat << 'PHP' > Modules/Student/Services/StudentService.php
<?php

namespace Modules\Student\Services;

use Modules\Student\DTOs\CreateStudentDTO;
use Modules\Student\DTOs\UpdateStudentDTO;
use Modules\Student\Exceptions\StudentNotFoundException;
use Modules\Student\Exceptions\StudentCreationFailedException;
use Modules\Student\Repositories\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;
use Exception;

class StudentService
{
    public function __construct(
        private readonly StudentRepositoryInterface $studentRepository
    ) {}

    public function getAllStudents(int $perPage = 15): LengthAwarePaginator
    {
        return $this->studentRepository->getAllPaginated($perPage);
    }

    public function getStudentById(int $id): User
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new StudentNotFoundException();
        }

        return $student;
    }

    public function createStudent(CreateStudentDTO $dto): User
    {
        try {
            return $this->studentRepository->create(
                $dto->toUserData(),
                $dto->toProfileData(0)
            );
        } catch (Exception $e) {
            throw new StudentCreationFailedException();
        }
    }

    public function updateStudent(UpdateStudentDTO $dto): User
    {
        $student = $this->getStudentById($dto->id);
        
        $this->studentRepository->update(
            $student,
            $dto->toUserData(),
            $dto->toProfileData()
        );

        return $student->fresh(['studentProfile']);
    }

    public function deleteStudent(int $id): bool
    {
        $student = $this->getStudentById($id);
        return $this->studentRepository->delete($student);
    }
}
PHP

cat << 'PHP' > Modules/Student/Http/Controllers/StudentController.php
<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Student\Services\StudentService;
use Modules\Student\DTOs\CreateStudentDTO;
use Modules\Student\DTOs\UpdateStudentDTO;
use Modules\Student\Http\Requests\StoreStudentRequest;
use Modules\Student\Http\Requests\UpdateStudentRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService
    ) {}

    public function index(): Response
    {
        $students = $this->studentService->getAllStudents();
        return Inertia::render('Student::Index', [
            'students' => $students
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Student::Create');
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $dto = CreateStudentDTO::fromRequest($request);
        $this->studentService->createStudent($dto);
        return redirect()->route('students.index')->with('success', 'تم إنشاء الطالب بنجاح');
    }

    public function show(int $id): Response
    {
        $student = $this->studentService->getStudentById($id);
        return Inertia::render('Student::Show', [
            'student' => $student
        ]);
    }

    public function edit(int $id): Response
    {
        $student = $this->studentService->getStudentById($id);
        return Inertia::render('Student::Edit', [
            'student' => $student
        ]);
    }

    public function update(UpdateStudentRequest $request, int $id): RedirectResponse
    {
        $dto = UpdateStudentDTO::fromRequest($request);
        $this->studentService->updateStudent($dto);
        return redirect()->route('students.index')->with('success', 'تم تحديث البيانات بنجاح');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->studentService->deleteStudent($id);
        return redirect()->route('students.index')->with('success', 'تم حذف الطالب بنجاح');
    }
}
PHP

# 6. Routes & Provider
cat << 'PHP' > Modules/Student/Routes/web.php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('students', StudentController::class);
});
PHP

cat << 'PHP' > Modules/Student/Providers/StudentServiceProvider.php
<?php

namespace Modules\Student\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Student\Repositories\StudentRepositoryInterface;
use Modules\Student\Repositories\StudentRepository;

class StudentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            StudentRepositoryInterface::class,
            StudentRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }
}
PHP

# 7. Vue 3 Pages (Inertia)
cat << 'VUE' > Modules/Student/Resources/assets/js/Pages/Index.vue
<script setup>
import { Link, router } from '@inertiajs/vue3'

defineProps({
  students: Object
})

const deleteStudent = (id) => {
  if (confirm('هل أنت تأكد من عملية الحذف؟')) {
    router.delete(route('students.destroy', id))
  }
}
</script>

<template>
  <div class="students-container">
    <div class="header">
      <h2>قائمة الطلاب</h2>
      <Link :href="route('students.create')" class="btn-primary">إضافة طالب جديد</Link>
    </div>

    <table class="students-table">
      <thead>
        <tr>
          <th>#</th>
          <th>الاسم</th>
          <th>البريد الإلكتروني</th>
          <th>اسم المستخدم</th>
          <th>الإجراءات</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students.data" :key="student.id">
          <td>{{ student.id }}</td>
          <td>{{ student.name }}</td>
          <td>{{ student.email }}</td>
          <td>{{ student.username }}</td>
          <td>
            <Link :href="route('students.edit', student.id)" class="btn-edit">تعديل</Link>
            <button @click="deleteStudent(student.id)" class="btn-delete">حذف</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.students-container { padding: 20px; }
.header { display: flex; justify-content: space-between; margin-bottom: 20px; }
.students-table { width: 100%; border-collapse: collapse; }
.students-table th, .students-table td { border: 1px solid #ddd; padding: 10px; text-align: right; }
.btn-primary { background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 4px; text-decoration: none; }
.btn-edit { background: #eab308; color: #fff; padding: 4px 8px; border-radius: 4px; text-decoration: none; margin-left: 5px; }
.btn-delete { background: #dc2626; color: #fff; padding: 4px 8px; border-radius: 4px; border: none; cursor: pointer; }
</style>
VUE

cat << 'VUE' > Modules/Student/Resources/assets/js/Pages/Create.vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  username: '',
  gender: 'male',
  birth_date: '',
  national_id: '',
  phone: '',
  is_active: true,
  academic_year_id: 1,
  current_class_id: 1,
  admission_date: '',
  scholarship_percentage: null,
  is_graduated: false,
  extra_fields: []
})

const submit = () => {
  form.post(route('students.store'))
}
</script>

<template>
  <div class="form-container">
    <h2>إضافة طالب جديد</h2>
    <form @submit.prevent="submit">
      <div class="form-group">
        <label>الاسم:</label>
        <input v-model="form.name" type="text" required />
        <span v-if="form.errors.name" class="error">{{ form.errors.name }}</span>
      </div>

      <div class="form-group">
        <label>البريد الإلكتروني:</label>
        <input v-model="form.email" type="email" required />
        <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
      </div>

      <div class="form-group">
        <label>اسم المستخدم:</label>
        <input v-model="form.username" type="text" required />
        <span v-if="form.errors.username" class="error">{{ form.errors.username }}</span>
      </div>

      <div class="form-group">
        <label>كلمة المرور:</label>
        <input v-model="form.password" type="password" required />
        <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
      </div>

      <div class="form-group">
        <label>الجنس:</label>
        <select v-model="form.gender">
          <option value="male">ذكر</option>
          <option value="female">أنثى</option>
        </select>
      </div>

      <button type="submit" :disabled="form.processing" class="btn-submit">حفظ</button>
    </form>
  </div>
</template>

<style scoped>
.form-container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; }
.form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; }
.btn-submit { background: #16a34a; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
.error { color: #dc2626; font-size: 12px; }
</style>
VUE

# 8. Inertia JS Config Setup
cat << 'JS' > resources/js/app.js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
  resolve: name => {
    let parts = name.split('::')
    if (parts.length > 1) {
      let module = parts[0]
      let page = parts[1]
      return import(`../../Modules/${module}/Resources/assets/js/Pages/${page}.vue`)
    }
    return import(`./Pages/${name}.vue`)
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
JS

echo "Setup completed successfully!"
