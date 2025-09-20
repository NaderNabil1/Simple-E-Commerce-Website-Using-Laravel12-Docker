<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(){
        $products = Product::get();
        return view('Dashboard.Product.index', compact('products'));
    }

    public function add(request $request){
        $categories = Category::where('status',1)->get();

        if($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'name'                 => 'required|string|max:255',
                'description'          => 'nullable|string',
                'price'                => 'required|numeric',
                'sale_price'           => 'nullable|numeric',
                'sale_end_date'        => 'nullable',
                'category_id'          => 'required|integer|exists:categories,id',
                'stock_quantity'       => 'required|numeric'
            ]);


            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('alert', 'Please correct the errors and try again.');
            }

            $product = Product::create([
                'name'                 => $request->name,
                'description'           => $request->description,
                'price'                 => $request->price,
                'sale_price'            => $request->sale_price,
                'sale_end_date'         => $request->sale_end_date,
                'category_id'           => $request->category_id,
                'status'                => $request->status,
                'stock_quantity'        => $request->stock_quantity ?? 0,
            ]);

            return redirect()->route('products')->with('success', 'Product added successfully!');
        }
        return view('Dashboard.Product.add',compact('categories'));
    }

    public function edit(request $request, $id){
        $product = Product::findOrFail($id);
        $categories = Category::where('status',1)->get();


        if($request->isMethod('post')){

            $validator = Validator::make($request->all(), [
                'name'                 => 'required|string|max:255',
                'description'          => 'nullable|string',
                'price'                => 'required|numeric',
                'sale_price'           => 'nullable|numeric',
                'sale_end_date'        => 'nullable',
                'category_id'          => 'required|integer|exists:categories,id',
                'stock_quantity'       => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('alert', 'Please correct the errors and try again.');
            }

            $product->update([
                'name'                 => $request->name,
                'description'           => $request->description,
                'price'                 => $request->price,
                'sale_price'            => $request->sale_price,
                'sale_end_date'         => $request->sale_end_date,
                'category_id'           => $request->category_id,
                'status'                => $request->status,
                'stock_quantity'        => $request->stock_quantity ?? 0,
            ]);

            return redirect(route('edit-product',$product->id))->with('success', 'Product edited successfully!');
        }
        return view('Dashboard.Product.edit', compact('product', 'categories'));
    }

    public function delete($id){
        $product = Product::findorfail($id);
        if(!$product){
            return redirect()->route('products')->with('error', 'Product Not Found!');
        }

        $product->delete();
            return redirect()->route('products')->with('error', 'Product Not Found!');
    }
}
