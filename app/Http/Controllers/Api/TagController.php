<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Project;
use App\Http\Requests\Tags\StoreTagRequest;
use App\Http\Requests\Tags\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Repositories\TagRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TagController extends Controller
{
    use AuthorizesRequests;

    protected TagRepositoryInterface $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Display a listing of the tags for a project.
     * GET /projects/{project}/tags
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Tag::class, $project]);
        $tags = $this->tagRepository->getAllForProject($project);
        return TagResource::collection($tags);
    }

    /**
     * Store a newly created tag for a project.
     * POST /projects/{project}/tags
     */
    public function store(StoreTagRequest $request, Project $project): TagResource
    {
        $this->authorize('create', [Tag::class, $project]);
        $tag = $this->tagRepository->create($request->validated(), $project);
        return new TagResource($tag);
    }

    /**
     * Display the specified tag.
     * GET /tags/{tag} (due to shallow nesting)
     */
    public function show(Tag $tag): TagResource
    {
        $this->authorize('view', $tag);
        return new TagResource($tag);
    }

    /**
     * Update the specified tag.
     * PUT/PATCH /tags/{tag}
     */
    public function update(UpdateTagRequest $request, Tag $tag): TagResource
    {
        $this->authorize('update', $tag);
        $updatedTag = $this->tagRepository->update($tag, $request->validated());
        return new TagResource($updatedTag);
    }

    /**
     * Remove the specified tag.
     * DELETE /tags/{tag}
     */
    public function destroy(Tag $tag): Response
    {
        $this->authorize('delete', $tag);
        $this->tagRepository->delete($tag);
        return response()->noContent();
    }
}
