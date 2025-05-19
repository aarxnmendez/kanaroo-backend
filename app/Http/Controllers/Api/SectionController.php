<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderSectionsRequest;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Resources\SectionResource;
use App\Models\Project;
use App\Models\Section;
use App\Repositories\SectionRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SectionController extends Controller
{
    use AuthorizesRequests;

    protected SectionRepositoryInterface $sectionRepository;

    /**
     * Constructor for dependency injection.
     */
    public function __construct(SectionRepositoryInterface $sectionRepository)
    {
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * Display a listing of sections for a specific project.
     * GET /projects/{project}/sections
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project); // Authorize viewing the parent project
        $sections = $this->sectionRepository->getAllForProject($project);
        return SectionResource::collection($sections);
    }

    /**
     * Store a newly created section for a specific project.
     * POST /projects/{project}/sections
     */
    public function store(StoreSectionRequest $request, Project $project): SectionResource
    {
        $this->authorize('update', $project); // Authorize based on ability to update parent project
        $section = $this->sectionRepository->create($request->validated(), $project);
        return new SectionResource($section);
    }

    /**
     * Display the specified section.
     * GET /sections/{section} (shallow)
     * or /projects/{project}/sections/{section}
     */
    public function show(Project $project, Section $section): SectionResource
    {
        $this->authorize('view', $section);
        // Fetch via repository to ensure consistent data loading (items, counts)
        $loadedSection = $this->sectionRepository->findById($section->id);
        if (!$loadedSection) {
            // Should not happen if Route Model Binding and policy checks passed,
            // but as a safeguard or if findById can return null for other reasons.
            abort(404, 'Section not found.');
        }
        return new SectionResource($loadedSection);
    }

    /**
     * Update the specified section.
     * PUT/PATCH /sections/{section} (shallow)
     * or /projects/{project}/sections/{section}
     */
    public function update(UpdateSectionRequest $request, Project $project, Section $section): SectionResource
    {
        $this->authorize('update', $section);
        $updatedSection = $this->sectionRepository->update($section, $request->validated());
        return new SectionResource($updatedSection);
    }

    /**
     * Remove the specified section.
     * DELETE /sections/{section} (shallow)
     * or /projects/{project}/sections/{section}
     */
    public function destroy(Project $project, Section $section): Response
    {
        $this->authorize('delete', $section);
        $this->sectionRepository->delete($section);
        return response()->noContent();
    }

    /**
     * Reorder sections within a project.
     * POST /projects/{project}/sections/reorder
     */
    public function reorder(ReorderSectionsRequest $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('update', $project); // Authorize based on ability to update parent project
        $sections = $this->sectionRepository->reorder($project, $request->validated()['ordered_ids']);
        return SectionResource::collection($sections);
    }
}
