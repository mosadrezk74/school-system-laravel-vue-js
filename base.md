#!/bin/bash

CORE="Modules/Core"

mkdir -p $CORE/{Abstracts,Interfaces,Exceptions,Constants,Traits,lang/ar,lang/en}

# ========================================
# Interfaces
# ========================================
cat > $CORE/Interfaces/ActionInterface.php << 'EOF'
<?php
namespace Modules\Core\Interfaces;

interface ActionInterface
{
    public function execute(mixed $dto): mixed;
}
EOF

cat > $CORE/Interfaces/RepositoryInterface.php << 'EOF'
<?php
namespace Modules\Core\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function findById(int $id): ?Model;
    public function findOrFail(int $id): Model;
    public function findAll(array $filters = []): array;
    public function findAllPaginated(int $perPage = 15, array $filters = []);
    public function create(array $data): Model;
    public function update(int $id, array $data): ?Model;
    public function delete(int $id): bool;
    public function findByField(string $field, mixed $value): ?Model;
    public function findManyByField(string $field, mixed $value): array;
    public function withTrashed(int $id): ?Model;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
    public function query(): Builder;
}
EOF

cat > $CORE/Interfaces/EntityInterface.php << 'EOF'
<?php
namespace Modules\Core\Interfaces;

interface EntityInterface
{
    public function toArray(): array;
    public function toModel(): array;
}
EOF

cat > $CORE/Interfaces/MapperInterface.php << 'EOF'
<?php
namespace Modules\Core\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface MapperInterface
{
    public function toEntity(Model $model): EntityInterface;
    public function toModel(EntityInterface $entity): array;
    public function toEntityCollection(iterable $models): array;
}
EOF

# ========================================
# Constants
# ========================================
cat > $CORE/Constants/HttpStatus.php << 'EOF'
<?php
namespace Modules\Core\Constants;

final class HttpStatus
{
    const OK                    = 200;
    const CREATED               = 201;
    const NO_CONTENT            = 204;
    const BAD_REQUEST           = 400;
    const UNAUTHORIZED          = 401;
    const FORBIDDEN             = 403;
    const NOT_FOUND             = 404;
    const CONFLICT              = 409;
    const UNPROCESSABLE_ENTITY  = 422;
    const TOO_MANY_REQUESTS     = 429;
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE   = 503;
}
EOF

cat > $CORE/Constants/Pagination.php << 'EOF'
<?php
namespace Modules\Core\Constants;

final class Pagination
{
    const DEFAULT_PER_PAGE = 15;
    const MAX_PER_PAGE     = 100;
    const MIN_PER_PAGE     = 1;
    const DEFAULT_SORT     = 'created_at';
    const DEFAULT_ORDER    = 'desc';
}
EOF

cat > $CORE/Constants/Regex.php << 'EOF'
<?php
namespace Modules\Core\Constants;

final class Regex
{
    const PHONE       = '/^\+?[0-9]{9,15}$/';
    const OTP         = '/^[0-9]{5}$/';
    const POSTAL_CODE = '/^[0-9]{4,10}$/';
    const SLUG        = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    const PRICE       = '/^\d+(\.\d{1,2})?$/';
    const UUID        = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
}
EOF

# ========================================
# Traits
# ========================================
cat > $CORE/Traits/ApiResponseTrait.php << 'EOF'
<?php
namespace Modules\Core\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    protected function success(mixed $data = null, string $message = '', int $code = 200): JsonResponse
    {
        if ($data instanceof LengthAwarePaginator) {
            return $this->paginated($data, $message);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => $message ?: __('base.success'),
            'data'      => $data,
            'timestamp' => now()->toISOString(),
        ], $code);
    }

    protected function error(string $message = '', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'status'    => 'error',
            'message'   => $message ?: __('base.bad_request'),
            'timestamp' => now()->toISOString(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function created(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message ?: __('base.created'), 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function validationError(array $errors, string $message = ''): JsonResponse
    {
        return $this->error($message ?: __('base.validation_failed'), 422, $errors);
    }

    protected function unauthorized(string $message = ''): JsonResponse
    {
        return $this->error($message ?: __('base.unauthorized'), 401);
    }

    protected function forbidden(string $message = ''): JsonResponse
    {
        return $this->error($message ?: __('base.forbidden'), 403);
    }

    protected function notFound(string $message = ''): JsonResponse
    {
        return $this->error($message ?: __('base.not_found'), 404);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = ''): JsonResponse
    {
        return response()->json([
            'status'    => 'success',
            'message'   => $message ?: __('base.success'),
            'data'      => [
                'items' => $paginator->items(),
                'meta'  => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last'  => $paginator->url($paginator->lastPage()),
                    'prev'  => $paginator->previousPageUrl(),
                    'next'  => $paginator->nextPageUrl(),
                ],
            ],
            'timestamp' => now()->toISOString(),
        ], 200);
    }
}
EOF

cat > $CORE/Traits/CacheableTrait.php << 'EOF'
<?php
namespace Modules\Core\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheableTrait
{
    protected function cacheRemember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return Cache::remember($this->buildCacheKey($key), $ttl, $callback);
    }

    protected function cachePut(string $key, mixed $data, int $ttl = 3600): bool
    {
        return Cache::put($this->buildCacheKey($key), $data, $ttl);
    }

    protected function cacheGet(string $key): mixed
    {
        return Cache::get($this->buildCacheKey($key));
    }

    protected function cacheForget(string $key): bool
    {
        return Cache::forget($this->buildCacheKey($key));
    }

    protected function cacheHas(string $key): bool
    {
        return Cache::has($this->buildCacheKey($key));
    }

    private function buildCacheKey(string $key): string
    {
        $prefix = method_exists($this, 'getCachePrefix')
            ? $this->getCachePrefix()
            : class_basename($this);

        return "{$prefix}_{$key}";
    }
}
EOF

cat > $CORE/Traits/HasSearchTrait.php << 'EOF'
<?php
namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSearchTrait
{
    protected array $searchableFields = [];

    protected function applySearch(Builder $query, array $filters): Builder
    {
        $search = $filters['search'] ?? null;

        if (! $search || empty($this->searchableFields)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            foreach ($this->searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }
}
EOF

cat > $CORE/Traits/HasSortingTrait.php << 'EOF'
<?php
namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Constants\Pagination;

trait HasSortingTrait
{
    protected array $sortableFields = ['created_at'];

    protected function applySorting(Builder $query, array $filters): Builder
    {
        $column    = $filters['sort_by']    ?? Pagination::DEFAULT_SORT;
        $direction = $filters['sort_order'] ?? Pagination::DEFAULT_ORDER;

        if (! in_array($column, $this->sortableFields)) {
            $column = Pagination::DEFAULT_SORT;
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = Pagination::DEFAULT_ORDER;
        }

        return $query->orderBy($column, $direction);
    }
}
EOF

cat > $CORE/Traits/HasPaginationTrait.php << 'EOF'
<?php
namespace Modules\Core\Traits;

use Modules\Core\Constants\Pagination;

trait HasPaginationTrait
{
    protected function getPerPage(array $filters): int
    {
        $perPage = (int) ($filters['per_page'] ?? Pagination::DEFAULT_PER_PAGE);

        return min(
            max($perPage, Pagination::MIN_PER_PAGE),
            Pagination::MAX_PER_PAGE
        );
    }
}
EOF

cat > $CORE/Traits/ValidationTrait.php << 'EOF'
<?php
namespace Modules\Core\Traits;

use Modules\Core\Exceptions\BaseException;

trait ValidationTrait
{
    protected function validateData(array $data, array $rules, array $messages = []): array
    {
        $validator = validator($data, $rules, $messages);

        if ($validator->fails()) {
            throw BaseException::validation($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    protected function validateUnique(string $model, string $field, mixed $value, ?int $excludeId = null): void
    {
        $query = $model::where($field, $value);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw BaseException::alreadyExists();
        }
    }
}
EOF

# ========================================
# Exceptions
# ========================================
cat > $CORE/Exceptions/BaseException.php << 'EOF'
<?php
namespace Modules\Core\Exceptions;

use Exception;
use Modules\Core\Constants\HttpStatus;

class BaseException extends Exception
{
    protected array $errors     = [];
    protected int   $statusCode;

    public function __construct(
        string      $message    = 'Something went wrong',
        int         $statusCode = HttpStatus::INTERNAL_SERVER_ERROR,
        array       $errors     = [],
        ?\Throwable $previous   = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->errors     = $errors;
        $this->statusCode = $statusCode;
    }

    public function getErrors(): array   { return $this->errors; }
    public function getStatusCode(): int { return $this->statusCode; }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'    => 'error',
            'message'   => $this->getMessage(),
            'errors'    => $this->getErrors(),
            'timestamp' => now()->toISOString(),
        ], $this->getStatusCode());
    }

    public static function notFound(string $message = ''): static
    {
        return new static($message ?: __('base.not_found'), HttpStatus::NOT_FOUND);
    }

    public static function unauthorized(string $message = ''): static
    {
        return new static($message ?: __('base.unauthorized'), HttpStatus::UNAUTHORIZED);
    }

    public static function forbidden(string $message = ''): static
    {
        return new static($message ?: __('base.forbidden'), HttpStatus::FORBIDDEN);
    }

    public static function alreadyExists(string $message = ''): static
    {
        return new static($message ?: __('base.already_exists'), HttpStatus::CONFLICT);
    }

    public static function badRequest(string $message = ''): static
    {
        return new static($message ?: __('base.bad_request'), HttpStatus::BAD_REQUEST);
    }

    public static function validation(array $errors, string $message = ''): static
    {
        return new static(
            $message ?: __('base.validation_failed'),
            HttpStatus::UNPROCESSABLE_ENTITY,
            $errors
        );
    }

    public static function serverError(string $message = ''): static
    {
        return new static($message ?: __('base.server_error'), HttpStatus::INTERNAL_SERVER_ERROR);
    }

    public static function tooManyRequests(string $message = ''): static
    {
        return new static($message ?: __('base.too_many_requests'), HttpStatus::TOO_MANY_REQUESTS);
    }
}
EOF

# ========================================
# Abstracts
# ========================================
cat > $CORE/Abstracts/BaseAction.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Support\Facades\DB;
use Modules\Core\Interfaces\ActionInterface;
use Modules\Core\Traits\CacheableTrait;

abstract class BaseAction implements ActionInterface
{
    use CacheableTrait;

    abstract public function execute(mixed $dto): mixed;

    protected function executeWithTransaction(callable $callback): mixed
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
EOF

cat > $CORE/Abstracts/BaseRepository.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Interfaces\RepositoryInterface;
use Modules\Core\Traits\CacheableTrait;

abstract class BaseRepository implements RepositoryInterface
{
    use CacheableTrait;

    protected Model $model;
    protected array $searchableFields = [];
    protected array $filterableFields = [];
    protected array $sortableFields   = ['created_at'];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function findAll(array $filters = []): array
    {
        return $this->applyFilters($this->query(), $filters)->get()->toArray();
    }

    public function findAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->applyFilters($this->query(), $filters)->paginate($perPage);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Model
    {
        $model = $this->findById($id);

        if (! $model) {
            return null;
        }

        $model->update($data);
        return $model->fresh();
    }

    public function delete(int $id): bool
    {
        $model = $this->findById($id);

        if (! $model) {
            return false;
        }

        return $model->delete();
    }

    public function findByField(string $field, mixed $value): ?Model
    {
        return $this->query()->where($field, $value)->first();
    }

    public function findManyByField(string $field, mixed $value): array
    {
        return $this->query()->where($field, $value)->get()->toArray();
    }

    public function withTrashed(int $id): ?Model
    {
        return $this->model->withTrashed()->find($id);
    }

    public function restore(int $id): bool
    {
        $model = $this->withTrashed($id);
        return $model ? $model->restore() : false;
    }

    public function forceDelete(int $id): bool
    {
        $model = $this->withTrashed($id);
        return $model ? $model->forceDelete() : false;
    }

    public function beginTransaction(): void { DB::beginTransaction(); }
    public function commit(): void           { DB::commit(); }
    public function rollback(): void         { DB::rollBack(); }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (in_array($field, $this->searchableFields)) {
                $query->where($field, 'LIKE', "%{$value}%");
            } elseif (in_array($field, $this->filterableFields)) {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    protected function getCachePrefix(): string
    {
        return class_basename($this->model);
    }
}
EOF

cat > $CORE/Abstracts/BaseQuery.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Constants\Pagination;
use Modules\Core\Traits\CacheableTrait;
use Modules\Core\Traits\HasPaginationTrait;
use Modules\Core\Traits\HasSearchTrait;
use Modules\Core\Traits\HasSortingTrait;

abstract class BaseQuery
{
    use CacheableTrait, HasSearchTrait, HasSortingTrait, HasPaginationTrait;

    protected Builder $query;
    protected array   $filters       = [];
    protected array   $withRelations = [];

    public function __construct()
    {
        $this->query = $this->baseQuery();
    }

    abstract protected function baseQuery(): Builder;

    public function with(array $relations): static
    {
        $this->withRelations = array_merge($this->withRelations, $relations);
        return $this;
    }

    public function where(array $filters): static
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    public function paginate(int $perPage = Pagination::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return $this->buildQuery()->paginate($perPage);
    }

    public function get(): array
    {
        return $this->buildQuery()->get()->toArray();
    }

    public function first(): ?object
    {
        return $this->buildQuery()->first();
    }

    public function count(): int
    {
        return $this->buildQuery()->count();
    }

    public function exists(): bool
    {
        return $this->buildQuery()->exists();
    }

    protected function buildQuery(): Builder
    {
        $query = clone $this->query;
        $query = $this->applySearch($query, $this->filters);
        $query = $this->applySorting($query, $this->filters);

        if (! empty($this->withRelations)) {
            $query->with($this->withRelations);
        }

        return $query;
    }

    protected function getCachePrefix(): string
    {
        return class_basename($this);
    }
}
EOF

cat > $CORE/Abstracts/BaseController.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;
use Modules\Core\Traits\ApiResponseTrait;

abstract class BaseController extends LaravelController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponseTrait;
}
EOF

cat > $CORE/Abstracts/BaseRequest.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Traits\ApiResponseTrait;

abstract class BaseRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            $this->validationError($validator->errors()->toArray())
        );
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbidden()
        );
    }

    public function onlyFilled(): array
    {
        return array_filter(
            $this->validated(),
            fn($value) => ! is_null($value) && $value !== ''
        );
    }
}
EOF

cat > $CORE/Abstracts/BaseResource.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    protected function whenRelationLoaded(string $relation, callable $callback): mixed
    {
        if ($this->resource->relationLoaded($relation)) {
            return $callback();
        }

        return null;
    }
}
EOF

cat > $CORE/Abstracts/BaseModel.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $perPage = 15;
}
EOF

cat > $CORE/Abstracts/BaseDTO.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseDTO
{
    abstract public static function fromRequest(FormRequest $request): static;
    abstract public static function fromArray(array $data): static;
    abstract public function toArray(): array;
}
EOF

cat > $CORE/Abstracts/BaseEntity.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Modules\Core\Interfaces\EntityInterface;

abstract class BaseEntity implements EntityInterface
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toModel(): array
    {
        return $this->toArray();
    }
}
EOF

cat > $CORE/Abstracts/BaseMapper.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Interfaces\EntityInterface;
use Modules\Core\Interfaces\MapperInterface;

abstract class BaseMapper implements MapperInterface
{
    abstract public function toEntity(Model $model): EntityInterface;
    abstract public function toModel(EntityInterface $entity): array;

    public function toEntityCollection(iterable $models): array
    {
        $items = is_array($models) ? $models : $models->all();

        return array_map(
            fn(Model $model) => $this->toEntity($model),
            $items
        );
    }
}
EOF

cat > $CORE/Abstracts/BaseEnum.php << 'EOF'
<?php
namespace Modules\Core\Abstracts;

abstract class BaseEnum
{
    public static function values(): array
    {
        return array_column(static::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, static::values());
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->name],
            static::cases()
        );
    }
}
EOF

# ========================================
# Lang Files
# ========================================
cat > $CORE/lang/ar/base.php << 'EOF'
<?php
return [
    'not_found'         => 'العنصر غير موجود.',
    'unauthorized'      => 'غير مصرح لك.',
    'forbidden'         => 'ممنوع الوصول.',
    'already_exists'    => 'العنصر موجود بالفعل.',
    'bad_request'       => 'طلب غير صحيح.',
    'validation_failed' => 'فشل التحقق من البيانات.',
    'server_error'      => 'حدث خطأ في الخادم.',
    'too_many_requests' => 'طلبات كثيرة. حاول لاحقاً.',
    'success'           => 'تمت العملية بنجاح.',
    'created'           => 'تم الإنشاء بنجاح.',
    'updated'           => 'تم التعديل بنجاح.',
    'deleted'           => 'تم الحذف بنجاح.',
];
EOF

cat > $CORE/lang/en/base.php << 'EOF'
<?php
return [
    'not_found'         => 'Record not found.',
    'unauthorized'      => 'Unauthorized.',
    'forbidden'         => 'Access forbidden.',
    'already_exists'    => 'Record already exists.',
    'bad_request'       => 'Bad request.',
    'validation_failed' => 'Validation failed.',
    'server_error'      => 'Server error occurred.',
    'too_many_requests' => 'Too many requests. Try again later.',
    'success'           => 'Operation successful.',
    'created'           => 'Created successfully.',
    'updated'           => 'Updated successfully.',
    'deleted'           => 'Deleted successfully.',
];
EOF
===========================================================================================
Modules/{Module}/
├── Actions/
│   └── {Name}Action.php          extends BaseAction
├── Constants/
│   └── {Module}Constants.php
├── DTOs/
│   └── {Name}DTO.php             readonly class
├── Enums/
│   └── {Name}Enum.php            extends BaseEnum
├── Exceptions/
│   └── {Module}Exception.php     extends BaseException
├── Http/
│   ├── Controllers/Api/
│   │   └── {Name}Controller.php  extends BaseController
│   └── Requests/
│       └── {Name}Request.php     extends BaseRequest
├── Interfaces/
│   └── Repositories/
│       └── {Name}RepositoryInterface.php
├── Models/
│   └── {Name}.php                extends BaseModel
├── Providers/
│   ├── {Module}ServiceProvider.php
│   └── RouteServiceProvider.php
├── Queries/
│   └── {Name}Query.php           extends BaseQuery
├── Repositories/
│   └── {Name}Repository.php      extends BaseRepository
└── Transformers/
    └── {Name}Resource.php        extends BaseResource
    



have run the Graphify extension on the current module. Please read the generated Knowledge Graph / Architectural Map to understand the relationships and dependencies between the Controllers, Services, Repositories, and DTOs to save context and optimize token usage.

When we need to modify code, apply lazy loading: ask me for the specific file required for the task rather than reading the entire module. Strictly avoid scanning or reading the vendor folder, node_modules, or any unrelated directories that waste tokens."
