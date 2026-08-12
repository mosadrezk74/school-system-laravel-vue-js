<?php

namespace Modules\News\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\News\Actions\CreateNewsAction;
use Modules\News\Actions\DeleteNewsAction;
use Modules\News\Actions\PublishNewsAction;
use Modules\News\Actions\TogglePinNewsAction;
use Modules\News\Actions\UnpublishNewsAction;
use Modules\News\Actions\UpdateNewsAction;
use Modules\News\DTOs\CreateNewsDTO;
use Modules\News\DTOs\NewsFilterDTO;
use Modules\News\DTOs\UpdateNewsDTO;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;
use Modules\News\Http\Requests\StoreNewsRequest;
use Modules\News\Http\Requests\UpdateNewsRequest;
use Modules\News\Models\News;
use Modules\News\Queries\NewsQuery;
use Modules\News\Transformers\NewsResource;

/**
 * The staff-facing news desk: drafts, scheduling, pinning and publishing.
 * Unlike the portal feed, this lists every audience and every status.
 */
class NewsController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', News::class);

        $filters = NewsFilterDTO::fromRequest($request);

        $news = (new NewsQuery)
            ->search($filters->search)
            ->status($filters->status)
            ->audience($filters->audience)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('News::News/Index', [
            'news' => NewsResource::collection($news),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'status', 'audience']),
            'statusOptions' => $this->statusOptions(),
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', News::class);

        return $this->render('News::News/Create', [
            'statusOptions' => $this->statusOptions(),
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function store(StoreNewsRequest $request, CreateNewsAction $action): RedirectResponse
    {
        $this->authorize('create', News::class);

        $action->execute(CreateNewsDTO::fromRequest($request));

        return $this->toRoute('news.index', [], __('news::news.created'));
    }

    public function edit(News $news): Response
    {
        $this->authorize('update', $news);

        return $this->render('News::News/Edit', [
            'item' => new NewsResource($news),
            'statusOptions' => $this->statusOptions(),
            'audienceOptions' => $this->audienceOptions(),
        ]);
    }

    public function update(UpdateNewsRequest $request, News $news, UpdateNewsAction $action): RedirectResponse
    {
        $this->authorize('update', $news);

        $action->execute(['news' => $news, 'dto' => UpdateNewsDTO::fromRequest($request)]);

        return $this->toRoute('news.index', [], __('news::news.updated'));
    }

    public function publish(News $news, PublishNewsAction $action): RedirectResponse
    {
        $this->authorize('publish', $news);

        $action->execute($news);

        return $this->backWithSuccess(__('news::news.published'));
    }

    public function unpublish(News $news, UnpublishNewsAction $action): RedirectResponse
    {
        $this->authorize('publish', $news);

        $action->execute($news);

        return $this->backWithSuccess(__('news::news.unpublished'));
    }

    public function togglePin(Request $request, News $news, TogglePinNewsAction $action): RedirectResponse
    {
        $this->authorize('update', $news);

        $isPinned = $request->boolean('is_pinned');

        $action->execute(['news' => $news, 'isPinned' => $isPinned]);

        return $this->backWithSuccess(
            $isPinned ? __('news::news.pinned') : __('news::news.unpinned'),
        );
    }

    public function destroy(News $news, DeleteNewsAction $action): RedirectResponse
    {
        $this->authorize('delete', $news);

        $action->execute($news);

        return $this->toRoute('news.index', [], __('news::news.deleted'));
    }

    /**
     * @return array<int, array{value: string, title: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            static fn (NewsStatus $status): array => [
                'value' => $status->value,
                'title' => $status->label(),
            ],
            NewsStatus::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, title: string}>
     */
    private function audienceOptions(): array
    {
        return array_map(
            static fn (NewsAudience $audience): array => [
                'value' => $audience->value,
                'title' => $audience->label(),
            ],
            NewsAudience::cases(),
        );
    }
}
