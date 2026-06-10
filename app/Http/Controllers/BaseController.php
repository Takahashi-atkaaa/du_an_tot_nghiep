<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class BaseController extends Controller
{
    protected string $modelClass;
    protected string $viewPath;
    protected string $routePrefix;
    protected array $relations = [];
    protected array $searchFields = [];
    protected int $perPage = 15;
    protected array $viewData = [];
    protected string $collectionName = 'items';
    protected string $itemName = 'item';
    protected string $storeSuccessMessage = 'Lưu dữ liệu thành công.';
    protected string $updateSuccessMessage = 'Cập nhật dữ liệu thành công.';
    protected string $destroySuccessMessage = 'Xóa dữ liệu thành công.';

    protected function modelClass(): string
    {
        return $this->modelClass;
    }

    protected function query(): Builder
    {
        return ($this->modelClass())::query();
    }

    protected function getIndexRouteName(): string
    {
        return "{$this->routePrefix}.index";
    }

    protected function getViewData(array $extra = []): array
    {
        return array_merge($this->viewData, $extra);
    }

    public function index(Request $request): View
    {
        $query = $this->query();

        if (!empty($this->relations)) {
            $query->with($this->relations);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query = $this->applySearch($query, $search);
        }

        $items = $query->ordered()->paginate($this->perPage);

        return view("{$this->viewPath}.index", $this->getViewData([
            $this->collectionName => $items,
            'search' => $search,
        ]));
    }

    public function create(): View
    {
        return view("{$this->viewPath}.create", $this->getViewData([
            $this->itemName => $this->newModelInstance(),
        ]));
    }

    public function edit(int $id): View
    {
        $item = $this->findModel($id);

        return view("{$this->viewPath}.edit", $this->getViewData([
            $this->itemName => $item,
        ]));
    }

    public function destroy(int $id): RedirectResponse
    {
        $model = $this->findModel($id);
        $model->delete();

        return redirect()
            ->route($this->getIndexRouteName())
            ->with('success', $this->destroySuccessMessage);
    }

    protected function storeModel(Request $request): RedirectResponse
    {
        ($this->modelClass())::create($this->prepareData($request));

        return redirect()
            ->route($this->getIndexRouteName())
            ->with('success', $this->storeSuccessMessage);
    }

    protected function updateModel(Request $request, int $id): RedirectResponse
    {
        $model = $this->findModel($id);
        $model->update($this->prepareData($request));

        return redirect()
            ->route($this->getIndexRouteName())
            ->with('success', $this->updateSuccessMessage);
    }

    protected function prepareData(Request $request): array
    {
        return $request->except(['_token', '_method']);
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->when(!empty($this->searchFields), function (Builder $query) use ($search) {
            return $query->where(function (Builder $builder) use ($search) {
                foreach ($this->searchFields as $field) {
                    $builder->orWhere($field, 'like', "%{$search}%");
                }
            });
        });
    }

    protected function findModel(int $id): Model
    {
        return ($this->modelClass())::findOrFail($id);
    }

    protected function newModelInstance(): Model
    {
        return new ($this->modelClass());
    }
}
