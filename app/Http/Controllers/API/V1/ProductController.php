<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Products\ProductRequest;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use File;

class ProductController extends BaseController
{

    protected $product = '';
    protected $blog_image_path;
    protected $blog_image_relative_path;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->middleware('auth:api');
        $this->product = $product;

        $this->category_image_path = public_path('uploads/category');
        $this->category_image_relative_path = '/uploads/category';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = $this->product->latest()->with('category', 'tags')->paginate(10);

        return $this->sendResponse($products, 'Product list');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\Products\ProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {

        if ($request->get('photo')) {
            $image = $request->get('photo');
            $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
            \Image::make($request->get('photo'))->save(public_path('upload/product/') . $name);
            $photo = $name;
        } else {
            $photo = '';
        }

        $product = $this->product->create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'category_id' => $request->get('category_id'),
            'photo' => $photo,
        ]);

        // update pivot table
        $tag_ids = [];
        foreach ($request->get('tags') as $tag) {
            $existingtag = Tag::whereName($tag['text'])->first();
            if ($existingtag) {
                $tag_ids[] = $existingtag->id;
            } else {
                $newtag = Tag::create([
                    'name' => $tag['text']
                ]);
                $tag_ids[] = $newtag->id;
            }
        }
        $product->tags()->sync($tag_ids);

        return $this->sendResponse($product, 'Product Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = $this->product->with(['category', 'tags'])->findOrFail($id);

        return $this->sendResponse($product, 'Product Details');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        $input = $request->all();

        $product = $this->product->findOrFail($id);

        if ($request->get('photo')) {

            //delete file
            $image_path = public_path("upload/product/") . $product->photo;
            if (\File::exists($image_path)) {
                \File::delete($image_path);
            }
            //upload file
            $image = $request->get('photo');
            $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
            \Image::make($request->get('photo'))->save(public_path('upload/product/') . $name);
            $input['photo'] = $name;
        } else {
            $input['photo'] = '';
        }

        $product->update($input);

        // update pivot table
        $tag_ids = [];
        foreach ($request->get('tags') as $tag) {
            $existingtag = Tag::whereName($tag['text'])->first();
            if ($existingtag) {
                $tag_ids[] = $existingtag->id;
            } else {
                $newtag = Tag::create([
                    'name' => $tag['text']
                ]);
                $tag_ids[] = $newtag->id;
            }
        }
        $product->tags()->sync($tag_ids);

        return $this->sendResponse($product, 'Product Information has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $this->authorize('isAdmin');

        $product = $this->product->findOrFail($id);

        $image_path = public_path("upload/product/") . $product->photo;
        if (\File::exists($image_path)) {
            \File::delete($image_path);
        }

        $product->delete();

        return $this->sendResponse($product, 'Product has been Deleted');
    }

    public function upload(Request $request)
    {
        $fileName = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move(public_path('upload'), $fileName);

        return response()->json(['success' => true]);
    }
}