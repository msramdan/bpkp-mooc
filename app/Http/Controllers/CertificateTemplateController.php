<?php

namespace App\Http\Controllers;

use App\Http\Requests\CertificateTemplates\UpdateCertificateLayoutRequest;
use App\Models\CertificateTemplate;
use App\Support\CertificateVariables;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateTemplateController extends Controller
{
    public function index(): View
    {
        $templates = CertificateTemplate::query()->latest()->paginate(10);
        return view('certificate-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('certificate-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'background_file' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:5120',
            'signature_file' => 'nullable|image|mimes:png,webp|max:2048',
            'signature_image_url' => 'nullable|string',
            'signer_name' => 'nullable|string|max:255',
            'signer_title' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->hasFile('background_file')) {
            $validated['background_image_url'] = $request->file('background_file')->store('certificates/backgrounds', 'public');
        } else {
            $validated['background_image_url'] = null;
        }
        unset($validated['background_file']);

        if ($request->hasFile('signature_file')) {
            $validated['signature_image_url'] = $request->file('signature_file')->store('certificates/signatures', 'public');
        } else {
            $validated['signature_image_url'] = null;
        }
        unset($validated['signature_file']);

        if (!empty($validated['is_default'])) {
            CertificateTemplate::query()->update(['is_default' => false]);
        }

        CertificateTemplate::create($validated);

        return to_route('certificate-templates.index')->with('success', 'Template sertifikat berhasil ditambahkan.');
    }

    public function edit(CertificateTemplate $certificateTemplate): View
    {
        return view('certificate-templates.edit', compact('certificateTemplate'));
    }

    public function update(Request $request, CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'background_file' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:5120',
            'signature_file' => 'nullable|image|mimes:png,webp|max:2048',
            'signature_image_url' => 'nullable|string',
            'signer_name' => 'nullable|string|max:255',
            'signer_title' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->hasFile('background_file')) {
            // Delete old uploaded file if it exists in storage
            $old = $certificateTemplate->background_image_url;
            if ($old && ! str_starts_with($old, 'backend/') && ! str_contains($old, '://') && ! str_starts_with($old, '/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($old);
            }
            $validated['background_image_url'] = $request->file('background_file')->store('certificates/backgrounds', 'public');
        } else {
            unset($validated['background_image_url']);
        }
        unset($validated['background_file']);

        if ($request->hasFile('signature_file')) {
            $oldSig = $certificateTemplate->signature_image_url;
            if ($oldSig && ! str_starts_with($oldSig, 'backend/') && ! str_contains($oldSig, '://') && ! str_starts_with($oldSig, '/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldSig);
            }
            $validated['signature_image_url'] = $request->file('signature_file')->store('certificates/signatures', 'public');
        } else {
            unset($validated['signature_image_url']);
        }
        unset($validated['signature_file']);

        if (!empty($validated['is_default'])) {
            CertificateTemplate::query()->update(['is_default' => false]);
        }

        $certificateTemplate->update($validated);

        return to_route('certificate-templates.index')->with('success', 'Template sertifikat berhasil diperbarui.');
    }

    public function destroy(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $certificateTemplate->delete();
        return to_route('certificate-templates.index')->with('success', 'Template sertifikat berhasil dihapus.');
    }

    public function editLayout(CertificateTemplate $certificateTemplate): View
    {
        $variables = CertificateVariables::DEFINITIONS;
        $sampleData = CertificateVariables::samples();

        // Include any custom position already saved, or fall back to default
        $existingLayout = $certificateTemplate->layout();

        return view('certificate-templates.layout', compact(
            'certificateTemplate', 'variables', 'sampleData', 'existingLayout'
        ));
    }

    public function updateLayout(UpdateCertificateLayoutRequest $request, CertificateTemplate $certificateTemplate): RedirectResponse
    {
        // Transform incoming indexed array from JS [{key, x, y, ...}] into keyed associative array
        $layoutData = [];
        foreach ($request->validated('variable_positions') as $row) {
            $key = $row['key'];
            unset($row['key']);

            // Strip null values so JSON stays compact
            $layoutData[$key] = array_filter($row, static fn ($v) => $v !== null && $v !== '');
        }

        $certificateTemplate->update([
            'variable_positions' => $layoutData,
        ]);

        return to_route('certificate-templates.layout.edit', $certificateTemplate)
            ->with('success', 'Layout sertifikat berhasil disahkan dan diperbarui.');
    }
}
