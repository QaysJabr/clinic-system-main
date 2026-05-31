<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * كتالوج الخدمات للفواتير (أسعار موحّدة).
 */
class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::query()->orderBy('name')->paginate(25);

        $pageTitle = 'كتالوج الخدمات';

        if ($request->ajax()) {
            return view('services.partials.content', compact('services', 'pageTitle'));
        }

        return view('services.index', compact('services', 'pageTitle'));
    }

    public function create(Request $request): View
    {
        $pageTitle = 'إضافة خدمة';

        if ($request->ajax()) {
            return view('services.partials.create', compact('pageTitle'));
        }

        return view('services.create', compact('pageTitle'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'doctor_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $service = Service::query()->create($validated);

        AuditLogger::log(
            'create',
            'services',
            $service->id,
            'إنشاء خدمة: '.$service->name,
            null,
            $service->only(['name', 'price', 'doctor_percentage', 'is_active'])
        );

        return redirect()->route('services.index')->with('success', 'تم حفظ الخدمة.');
    }

    public function edit(Request $request, Service $service): View
    {
        $pageTitle = 'تعديل خدمة';

        if ($request->ajax()) {
            return view('services.partials.edit', compact('service', 'pageTitle'));
        }

        return view('services.edit', compact('service', 'pageTitle'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'doctor_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $old = $service->only(['name', 'price', 'doctor_percentage', 'is_active']);
        $service->update($validated);

        AuditLogger::log(
            'update',
            'services',
            $service->id,
            'تحديث خدمة: '.$service->name,
            $old,
            $service->fresh()->only(['name', 'price', 'doctor_percentage', 'is_active'])
        );

        return redirect()->route('services.index')->with('success', 'تم تحديث الخدمة.');
    }
}
