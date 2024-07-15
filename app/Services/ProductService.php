<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductOption;
use App\Services\Helpers\FileUploadService;
use App\Services\Helpers\Imports\ProductGroupImport;
use App\Exceptions\NotFoundException;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ProductService
{
    private $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        $product_groups = ProductGroup::select('id', 'group_name')->with('products')->get();

        $products = Product::select(
            'products.id as product_id',
            'products.uuid',
            'products.product_group_id as group_id',
            'products.name',
            'products.avatar'
        )->get();

        $product_options = ProductOption::select(
            'product_options.id as option_id',
            'product_options.product_id',
            'product_options.option_name',
            'product_options.price',
            'product_options.expressPrice',
            'product_options.cost_price',
            'product_options.pieces'
        )->get();

        foreach ($product_groups as $product_group) {

            $products = $product_group->products;

            foreach ($products as $product) {
                $options_array = [];

                for ($i = count($product_options) - 1; $i >= 0; $i--) {
                    $obj = $product_options[$i];
                    if ($obj->product_id == $product->id) {
                        array_push($options_array, $obj);
                    }
                }
                $product->options = $options_array;
            }
        }

        return [
            "product_groups" => $product_groups
        ];
    }

    public function all()
    {
        $products = Product::all();

        return [
            "products" => $products
        ];
    }

    public function create($data)
    {
        if (isset($data['avatar'])) {
            $data['avatar'] = $this->fileUploadService->upload($data["avatar"], "avatar");
        }

        return Product::create($data);
    }

    public function edit($data, $uuid)
    {
        $product = Product::where('uuid', $uuid)->first();
        if (!$product) {
            throw new NotFoundException("Product not found");
        }

        if (isset($data['avatar'])) {
            $data['avatar'] = ($data['avatar'] instanceof UploadedFile) ? $this->fileUploadService->upload($data['avatar'], 'avatars') : $product->avatar;
        }

        $product->update($data);

        return $product;
    }

    public function getProduct($uuid)
    {
        $product = Product::where("uuid", $uuid)->first();
        if (!$product) {
            throw new NotFoundException("Product not found");
        }

        return $product;
    }

    public function destroy($uuid)
    {
        $product = Product::where("uuid", $uuid)->first();
        if (!$product) {
            throw new NotFoundException("Product not found");
        }

        $product->delete();
    }

    public function get_product_groups()
    {
        return ProductGroup::all();
    }

    public function getSingleGroup($uuid)
    {
        $product_group = ProductGroup::where('uuid', $uuid)->with('products')->first();

        foreach ($product_group->products as $product) {

            $product_options = ProductOption::where("product_id", $product->id)->get();

            $product->options = $product_options;
        }

        return $product_group;
    }

    public function add_product_group($data)
    {
        return ProductGroup::create($data);
    }

    public function edit_product_group($data, $uuid)
    {
        $product_group = ProductGroup::where("uuid", $uuid)->first();
        if (!$product_group) {
            throw new NotFoundException("Product group not found");
        }

        $product_group->update($data);

        return $product_group;
    }

    public function delete_product_group($uuid)
    {
        $product_group = ProductGroup::where("uuid", $uuid)->first();
        if (!$product_group) {
            throw new NotFoundException("Product group not found");
        }

        $product_group->delete();
    }

    public function getAllProductOption()
    {
        return ProductOption::all();
    }

    public function get_product_options($product_id)
    {
        $product_options = ProductOption::where('product_id', $product_id)
            ->select('id', 'option_name', 'price')
            ->get();
        if (!$product_options) {
            throw new NotFoundException('No product option with this product id');
        }

        return $product_options;
    }

    public function add_product_option($data)
    {
        return ProductOption::create($data);
    }

    public function edit_product_option($data, $uuid)
    {
        $product_option = ProductOption::where('uuid', $uuid)->first();
        if (!$product_option) {
            throw new NotFoundException('Product option not found');
        }

        $product_option->update($data);

        return $product_option;
    }

    public function delete_product_option($uuid)
    {
        $product_option = ProductOption::where('uuid', $uuid)->first();
        if (!$product_option) {
            throw new NotFoundException('Product option not found');
        }

        $product_option->delete();
    }

    public function bulkAddProductGroup($file)
    {
        Excel::import(new ProductGroupImport, $file);
    }
}
