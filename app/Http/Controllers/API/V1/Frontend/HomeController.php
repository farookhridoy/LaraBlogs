<?php

namespace App\Http\Controllers\API\V1\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\V1\BaseController;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Product;
use App\Models\Tag;
use DB;
use Session;

class HomeController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::latest()->with('category', 'tags')->paginate(10);

        return $this->sendResponse($products, 'Product list');
    }
}