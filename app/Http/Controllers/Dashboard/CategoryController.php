<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::get();
        return view('Dashboard.Category.index', compact('categories'));
    }

    public function add(request $request){
        if($request->isMethod('post')){

            $validator = \Validator::make($request->all(), [
                'name' => ['required'],
            ], [
                'name.required' => 'Name is required.',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('alert', 'Please correct the errors and try again.');
            }

            Category::create([
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return redirect()->route('be-categories')->with('success', 'Category Added Successfully!');
        }
        return view('Dashboard.Category.add');
    }

    public function edit(request $request, $id ){
        $category = Category::findorfail($id);
        if($request->isMethod('post')){
            $request->validate([
                'name' => ['required'],
            ], [
                'name.required' => 'Name is required.',
            ]);

            $category->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);

            return back()->with('success', 'Category Edited Successfully!');
        }
        return view('Dashboard.Category.edit', compact('category'));
    }

    public function delete($id){
        $category = Category::findorfail($id);
        if($category != NULL){
            $category->delete();
        }
        return back()->with('success', 'Category Deleted Successfully!');
    }
}
