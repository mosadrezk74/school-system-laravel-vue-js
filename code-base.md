# Athr School — Architecture Map

## نظرة عامة

المشروع مبني على `nwidart/laravel-modules` فوق Laravel 13 + Inertia + Vue 3.
كل ميزة (Domain) لها Module مستقل بالكامل تحت `Modules/`، وكل Module بيعتمد على `Modules/Core` كطبقة أساسية مشتركة.

النمط المعماري: **Repository + Action + DTO + Query**، مع فصل واضح بين الصفحات اللي بترجع Inertia والـ endpoints اللي بترجع JSON.

---

## Modules/Core — الطبقة الأساسية

| الملف | الدور |
|---|---|
| `Interfaces/ActionInterface.php` | عقد أي Action (`execute(mixed $dto): mixed`) |
| `Interfaces/RepositoryInterface.php` | عقد أي Repository (CRUD + soft delete + query) |
| `Interfaces/EntityInterface.php` | عقد Entity (لو استخدمت Mapper pattern) |
| `Interfaces/MapperInterface.php` | تحويل Model ↔ Entity |
| `Constants/HttpStatus.php` | أكواد HTTP ثابتة |
| `Constants/Pagination.php` | إعدادات الصفحات الافتراضية |
| `Constants/Regex.php` | أنماط تحقق شائعة (هاتف، UUID، سعر...) |
| `Traits/ApiResponseTrait.php` | ردود JSON موحدة — **يُستخدم فقط في `BaseApiController`** |
| `Traits/CacheableTrait.php` | Cache helpers بـ prefix تلقائي |
| `Traits/HasSearchTrait.php` | بحث عام حسب `searchableFields` |
| `Traits/HasSortingTrait.php` | فرز آمن حسب `sortableFields` |
| `Traits/HasPaginationTrait.php` | حساب `per_page` بحدود آمنة |
| `Traits/ValidationTrait.php` | تحقق يدوي خارج FormRequest عند الحاجة |
| `Exceptions/BaseException.php` | استثناء موحّد، بيرجع JSON تلقائيًا عبر `render()` |
| `Abstracts/BaseModel.php` | `SoftDeletes` + `guarded = ['id']` |
| `Abstracts/BaseRepository.php` | تطبيق `RepositoryInterface` كامل |
| `Abstracts/BaseQuery.php` | بناء استعلامات قابلة للتسلسل (`where()->with()->paginate()`) |
| `Abstracts/BaseAction.php` | `executeWithTransaction()` لأي عملية كتابة |
| `Abstracts/BaseDTO.php` | `fromRequest()` / `fromArray()` / `toArray()` |
| `Abstracts/BaseEnum.php` | `values()` / `options()` لأي PHP Enum |
| `Abstracts/BaseResource.php` | Resource أساسي لأي JSON output |
| `Abstracts/BaseController.php` | Controller خام (Authorize + Validate فقط) |
| `Abstracts/BaseApiController.php` | يورّث `BaseController` + `ApiResponseTrait` — **لأي endpoint بيرجع JSON** |
| `Abstracts/BaseInertiaController.php` | يورّث `BaseController` + `render()/back()/toRoute()` — **لأي صفحة Vue** |
| `Abstracts/BaseRequest.php` | FormRequest عادي (redirect أو JSON تلقائيًا حسب نوع الطلب) |

### قاعدة الاختيار بين الـ Controllers

```
صفحة UI كاملة (Index/Create/Edit)  →  extends BaseInertiaController
endpoint JSON (mobile / polling / webhook)  →  extends BaseApiController
```

---

## هيكل أي Module جديد

```
Modules/{ModuleName}/
├── module.json
├── composer.json
├── Actions/
│   └── {Verb}{Name}Action.php        extends BaseAction
├── Constants/
│   └── {ModuleName}Constants.php
├── DTOs/
│   └── {Name}DTO.php                 readonly, extends BaseDTO
├── Enums/
│   └── {Name}Enum.php                extends BaseEnum
├── Exceptions/
│   └── {ModuleName}Exception.php     extends BaseException
├── Http/
│   ├── Controllers/
│   │   └── {Name}Controller.php      extends BaseInertiaController
│   ├── Controllers/Api/
│   │   └── {Name}ApiController.php   extends BaseApiController
│   └── Requests/
│       ├── Store{Name}Request.php    extends BaseRequest
│       └── Update{Name}Request.php   extends BaseRequest
├── Interfaces/Repositories/
│   └── {Name}RepositoryInterface.php extends RepositoryInterface
├── Models/
│   └── {Name}.php                    extends BaseModel
├── Providers/
│   └── {ModuleName}ServiceProvider.php
├── Queries/
│   └── {Name}Query.php               extends BaseQuery
├── Repositories/
│   └── {Name}Repository.php          extends BaseRepository
├── Transformers/
│   └── {Name}Resource.php            extends BaseResource
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── routes/
│   └── web.php
├── lang/{ar,en}/{module}.php
└── resources/js/Pages/{ModuleName}/{Entity}/
    ├── Index.vue
    ├── Create.vue
    ├── Edit.vue
    └── _Form.vue
```

---

## تدفق العملية (Request Lifecycle)

```
Route  →  Controller  →  FormRequest (validate)
                      →  DTO::fromRequest()
                      →  Action::execute($dto)
                            └─ Repository (عبر Interface Binding)
                      →  Inertia::render() أو JSON Resource
```

- الـ Controller لا يحتوي منطق أعمال، فقط تنسيق.
- أي كتابة في قاعدة البيانات تمر عبر `Action` باستخدام `executeWithTransaction()`.
- الـ Repository Interface يُربط بالتطبيق داخل `{Module}ServiceProvider::register()`.

---

## قواعد التسمية

| العنصر | الصيغة | مثال |
|---|---|---|
| Model | مفرد | `GradeLevel` |
| Repository | `{Model}Repository` | `GradeLevelRepository` |
| Action | `{Verb}{Model}Action` | `CreateGradeLevelAction` |
| DTO | `{Model}DTO` | `GradeLevelDTO` |
| Query | `{Model}Query` | `GradeLevelQuery` |
| Route name | `{module}.{models}.{action}` | `academic.grade-levels.index` |
| Vue Page | `{Module}/{Models}/{Action}.vue` | `Academic/GradeLevels/Index.vue` |

---

## قواعد العمل مع مساعد الذكاء الاصطناعي (AI Collaboration Rules)

- تم تشغيل Graphify على كل Module لإنتاج خريطة معرفية (Knowledge Graph) للعلاقات بين Controllers/Repositories/Actions/DTOs. اقرأ الخريطة المولّدة أولًا بدل مسح الكود بالكامل، لتوفير الـ context.
- **Lazy loading إجباري**: عند طلب تعديل كود، اطلب من المستخدم اسم الملف المحدد المطلوب بدل قراءة الـ Module كامل.
- **ممنوع** مسح أو قراءة `vendor/`، `node_modules/`، أو أي مجلد غير متعلق مباشرة بالمهمة.
- أي إضافة جديدة تتبع القالب في قسم "هيكل أي Module جديد" أعلاه دون استثناء.
