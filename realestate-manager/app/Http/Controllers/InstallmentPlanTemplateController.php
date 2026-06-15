<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPlanTemplate;
use Illuminate\Http\Request;

class InstallmentPlanTemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index()
    {
        $templates = InstallmentPlanTemplate::orderBy('created_at', 'desc')->get();

        return view('templates.index', compact('templates'));
    }

    /**
     * Show form for creating a new template.
     */
    public function create()
    {
        return view('templates.create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:equal_split,graduated,balloon',
            'duration_months' => 'required|integer|min:1|max:360',
            'config' => 'nullable|array',
        ]);

        InstallmentPlanTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'duration_months' => $request->duration_months,
            'config' => $request->config,
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Show form for editing the specified template.
     */
    public function edit(InstallmentPlanTemplate $template)
    {
        return view('templates.edit', compact('template'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, InstallmentPlanTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:equal_split,graduated,balloon',
            'duration_months' => 'required|integer|min:1|max:360',
            'config' => 'nullable|array',
        ]);

        $template->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'duration_months' => $request->duration_months,
            'config' => $request->config,
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(InstallmentPlanTemplate $template)
    {
        $template->delete();

        return back()->with('success', 'Template deleted successfully.');
    }
}
