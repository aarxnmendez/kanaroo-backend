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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth; // Aunque no se use directamente en todos los métodos, es común tenerlo
use Illuminate\Http\Response;

class SectionController extends Controller
{
    use AuthorizesRequests; // Habilita $this->authorize()

    protected SectionRepositoryInterface $sectionRepository;

    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(SectionRepositoryInterface $sectionRepository)
    {
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * GET /projects/{project}/sections
     * Muestra una lista de todas las secciones para un proyecto específico.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project); // Explicitly authorize viewing the parent project
        $sections = $this->sectionRepository->getAllForProject($project);
        return SectionResource::collection($sections);
    }

    /**
     * POST /projects/{project}/sections
     * Crea una nueva sección para un proyecto específico.
     */
    public function store(StoreSectionRequest $request, Project $project): SectionResource
    {
        // Authorize based on the user's ability to update the parent project.
        $this->authorize('update', $project);
        $section = $this->sectionRepository->create($request->validated(), $project);
        return new SectionResource($section);
    }

    /**
     * GET /projects/{project}/sections/{section}
     * Muestra una sección específica.
     */
    public function show(Project $project, Section $section): SectionResource
    {
        $this->authorize('view', $section);
        return new SectionResource($section);
    }

    /**
     * PUT/PATCH /projects/{project}/sections/{section}
     * Actualiza una sección específica.
     */
    public function update(UpdateSectionRequest $request, Project $project, Section $section): SectionResource
    {
        $this->authorize('update', $section);
        $section = $this->sectionRepository->update($section, $request->validated());
        return new SectionResource($section);
    }

    /**
     * DELETE /projects/{project}/sections/{section}
     * Elimina una sección específica.
     */
    public function destroy(Project $project, Section $section): Response
    {
        $this->authorize('delete', $section);
        $this->sectionRepository->delete($section);
        return response()->noContent();
    }

    /**
     * POST /projects/{project}/sections/reorder  (o PATCH /projects/{project}/sections)
     * Reordena las secciones de un proyecto.
     */
    public function reorder(ReorderSectionsRequest $request, Project $project): AnonymousResourceCollection
    {
        // Authorize based on the user's ability to update the parent project.
        $this->authorize('update', $project);
        $sections = $this->sectionRepository->reorder($project, $request->validated()['ordered_ids']);
        return SectionResource::collection($sections);
    }
}
