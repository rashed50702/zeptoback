<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $filename = Null;

            $this->validate($request, [
                'name' => 'required|max:255',
                'price' => 'required|numeric',
            ]);

            if ($request->file('image')) {

                $this->validate($request, ['image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',]);
                $filename = time().'.'.$request->file('image')->getClientOriginalExtension();
                $request->image->move(public_path('assets/images/products'), $filename);
            }

            $product = new Product();
            $product->name  = $request->name;
            $product->price = $request->price;
            $product->image = $filename;
            $product->save();
            return response()->json(['status' => 'success', 'message' => 'Product successfully saved!'], 201);
        }catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => 'Error', 'errors' => $exception->errors()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Product::where('id', $id)->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // return $id;
            // return $request->all();
            $this->validate($request, [
                'name' => 'required|max:255',
                'price' => 'required',
            ]);

            $product = Product::find($id);
            $product->name  = $request->name;
            $product->price = $request->price;
            if ($request->file('image')) {

                $this->validate($request, ['image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',]);

                //remove old photo from folder if exist
                if ($product->image) {
                    $productImage = public_path("assets/images/products/{$product->image}");
                    if (File::exists($productImage)) {
                        unlink($productImage);
                    }
                }


                $filename = time().'.'.$request->file('image')->getClientOriginalExtension();
                $request->image->move(public_path('assets/images/products'), $filename);
                $product->image = $filename;
            }
            $product->save();            
            return response()->json(['status' => 'success', 'message' => 'Product successfully updated!'], 201);
        }catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => 'Error', 'errors' => $exception->errors()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product->image) {
            $productImage = public_path("assets/images/products/{$product->image}");
            if (File::exists($productImage)) {
                unlink($productImage);
            }
        }
        $product->delete();

        return Response::json(['message' => 'The product deleted successfully!'], 200);
    }
}
