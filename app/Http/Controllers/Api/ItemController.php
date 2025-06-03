<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Section;
use App\Models\Item;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Requests\Api\ReorderItemsRequest;
use App\Http\Resources\ItemResource;
use App\Repositories\ItemRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItemController extends Controller
{
    use AuthorizesRequests;

    protected ItemRepositoryInterface $itemRepository;

    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Display a listing of items for a specific section.
     * GET /projects/{project}/sections/{section}/items
     */
    public function index(Project $project, Section $section): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Item::class, $section]);
        $items = $this->itemRepository->getAllForSection($section);
        // Repository handles eager loading of relations.
        return ItemResource::collection($items);
    }

    /**
     * Store a newly created item in a specific section.
     * POST /projects/{project}/sections/{section}/items
     */
    public function store(StoreItemRequest $request, Project $project, Section $section): ItemResource
    {
        $this->authorize('create', [Item::class, $section]);
        $validatedData = $request->validated();
        $item = $this->itemRepository->create($validatedData, $section);
        return new ItemResource($item);
    }

    /**
     * Display the specified item.
     * GET /items/{item} (shallow route)
     * or /projects/{project}/sections/{section}/items/{item}
     */
    public function show(Project $project, Section $section, Item $item): ItemResource
    {
        $this->authorize('view', $item);
        return new ItemResource($item);
    }

    /**
     * Update the specified item.
     * PUT/PATCH /items/{item} (shallow route)
     * or /projects/{project}/sections/{section}/items/{item}
     */
    public function update(UpdateItemRequest $request, Project $project, Section $section, Item $item): ItemResource
    {
        $this->authorize('update', $item);
        $validatedData = $request->validated();
        $updatedItem = $this->itemRepository->update($item, $validatedData);
        // Repository handles loading necessary relations for the resource.
        return new ItemResource($updatedItem);
    }

    /**
     * Remove the specified item.
     * DELETE /items/{item} (shallow route)
     * or /projects/{project}/sections/{section}/items/{item}
     */
    public function destroy(Project $project, Section $section, Item $item): Response
    {
        $this->authorize('delete', $item);
        $this->itemRepository->delete($item);
        return response()->noContent();
    }

    /**
     * Reorder items within a specific section.
     * POST /projects/{project}/sections/{section}/items/reorder
     */
    public function reorder(ReorderItemsRequest $request, Project $project, Section $section): AnonymousResourceCollection
    {
        $this->authorize('update', $section); // Policy: can user update the section to reorder its items?
        $validatedData = $request->validated();
        $reorderedItems = $this->itemRepository->reorder($section, $validatedData['item_ids']);
        return ItemResource::collection($reorderedItems);
    }

    /**
     * Display a listing of items for a specific project, with filtering.
     * GET /projects/{project}/items
     */
    public function indexForProject(Project $project, Request $request): AnonymousResourceCollection
    {
        $this->authorize('view', $project); // User must be able to view the project to list its items

        $items = $this->itemRepository->getFilteredItemsForProject($project, $request);

        return ItemResource::collection($items);
    }
}
