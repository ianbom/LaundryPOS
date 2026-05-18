<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreWhatsAppTemplateRequest;
use App\Http\Requests\Settings\UpdateWhatsAppTemplateRequest;
use App\Models\Outlet;
use App\Models\WhatsappTemplate;
use App\Services\ActivityLogger;
use App\Services\WhatsApp\WhatsAppTemplateRenderer;
use App\Support\OutletAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WhatsAppTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(OutletAccess::canManageSettings($request->user()), 403);
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('settings/whatsapp-templates/index', [
            'templates' => WhatsappTemplate::query()
                ->with('outlet:id,name')
                ->where(fn ($query) => $query->whereNull('outlet_id')->orWhereIn('outlet_id', $outletIds))
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
                ->when($request->filled('scope'), fn ($query) => $request->input('scope') === 'global' ? $query->whereNull('outlet_id') : $query->whereNotNull('outlet_id'))
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
                ->when($request->string('search')->toString(), function ($query, string $search) {
                    $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('body', 'like', "%{$search}%"));
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'type', 'scope', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless(OutletAccess::canManageSettings($request->user()), 403);

        return Inertia::render('settings/whatsapp-templates/form', [
            'template' => null,
            'outlets' => Outlet::query()->whereIn('id', OutletAccess::accessibleOutletIds($request->user()))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreWhatsAppTemplateRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $template = WhatsappTemplate::query()->create($request->safe()->merge([
            'is_active' => $request->boolean('is_active', true),
        ])->all());
        $logger->log($request, 'whatsapp_template_created', $template, $template->outlet_id, null, $template->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'WhatsApp template created.']);

        return redirect('/settings/whatsapp-templates');
    }

    public function edit(Request $request, WhatsappTemplate $template): Response
    {
        abort_unless(OutletAccess::canManageSettings($request->user(), $template->outlet_id), 403);

        return Inertia::render('settings/whatsapp-templates/form', [
            'template' => $template,
            'outlets' => Outlet::query()->whereIn('id', OutletAccess::accessibleOutletIds($request->user()))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateWhatsAppTemplateRequest $request, WhatsappTemplate $template, ActivityLogger $logger): RedirectResponse
    {
        $old = $template->getOriginal();
        $template->update($request->safe()->merge([
            'is_active' => $request->boolean('is_active'),
        ])->all());
        $logger->log($request, 'whatsapp_template_updated', $template, $template->outlet_id, $old, $template->fresh()->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'WhatsApp template updated.']);

        return redirect('/settings/whatsapp-templates');
    }

    public function destroy(Request $request, WhatsappTemplate $template, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(OutletAccess::canManageSettings($request->user(), $template->outlet_id), 403);
        $template->delete();
        $logger->log($request, 'whatsapp_template_deleted', $template, $template->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'WhatsApp template deleted.']);

        return redirect('/settings/whatsapp-templates');
    }

    public function toggleActive(Request $request, WhatsappTemplate $template, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(OutletAccess::canManageSettings($request->user(), $template->outlet_id), 403);
        $template->update(['is_active' => ! $template->is_active]);
        $logger->log($request, 'whatsapp_template_toggled', $template, $template->outlet_id);

        return redirect('/settings/whatsapp-templates');
    }

    public function preview(WhatsappTemplate $template, WhatsAppTemplateRenderer $renderer): JsonResponse
    {
        return response()->json([
            'message' => $renderer->render($template->body, $renderer->sampleData()),
        ]);
    }
}
