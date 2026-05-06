<?php

// namespace Modules\Form\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// namespace  Modules\Form\Entities;
// use Illuminate\Http\Request;

// class FormTemplateController extends Controller
// {
//     /**
//      * Display a listing of the resource.
//      */
//     public function index()
//     {
//         return FormTemplate::all();
//     }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(Request $request)
//     {
//         $validatedData = $request->validate([
//             'name' => 'required|string|max:255',
//             'description' => 'nullable|string',
//         ]);

//         $formTemplate = FormTemplate::create($validatedData);

//         return response()->json($formTemplate, 201);
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(FormTemplate $formTemplate)
//     {
//         return $formTemplate;
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, FormTemplate $formTemplate)
//     {
//         $validatedData = $request->validate([
//             'name' => 'string|max:255',
//             'description' => 'nullable|string',
//         ]);

//         $formTemplate->update($validatedData);

//         return response()->json($formTemplate);
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(FormTemplate $formTemplate)
//     {
//         $formTemplate->delete();

//         return response()->json(null, 204);
//     }
// }
