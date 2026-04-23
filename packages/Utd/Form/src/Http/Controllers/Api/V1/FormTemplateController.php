<?php

namespace Utd\Form\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Utd\Form\Entities\FormTemplate;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    public function index()
    {
        return FormTemplate::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $formTemplate = FormTemplate::create($validatedData);

        return response()->json($formTemplate, 201);
    }

    public function show(FormTemplate $formTemplate)
    {
        return $formTemplate;
    }

    public function update(Request $request, FormTemplate $formTemplate)
    {
        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        $formTemplate->update($validatedData);

        return response()->json($formTemplate);
    }

    public function destroy(FormTemplate $formTemplate)
    {
        $formTemplate->delete();

        return response()->json(null, 204);
    }
}
