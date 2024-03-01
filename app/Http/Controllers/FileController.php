<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = File::where('category', 'waskita')->get([
            'id',
            'files',
            'details'
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diambil!',
            "data" => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileRequest $request)
    {
        $data_files = [];
        $data_details = [];

        if ($request->hasFile('files')) {
            $files = $request->file('files');
            foreach ($files as $key => $file) {
                $file_name = $file->getClientOriginalName();
                $file_path = 'documents/' . str()->random(40) . "." . $file->extension();
                Storage::disk('public')->putFileAs('', $file, $file_path);

                // $file_url = Storage::url($file_path);
                $data_files[] = [
                    'name' => $file_name,
                    'path' => $file_path
                ];

            }
        }

        if(is_array($request->input('details'))) {
            foreach ($request->input('details') as $key => $value) {
                $data_details["detail_$key"] = $value;
            }
        }
        

        $data = File::create([
            'user_id' => auth()->check() ? auth()->id() : NULL,
            'category' => 'waskita',
            'files' => $data_files,
            'details' => $data_details,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan!',
            "data" => $data
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
