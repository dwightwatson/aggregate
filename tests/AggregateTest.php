<?php

namespace Watson\Aggregate\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Watson\Aggregate\AggregateModel;

class AggregateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DB::table('orders')->insert([
            'reference' => '12345678',
        ]);

        DB::table('product_orders')->insert([
            ['name' => 'imac', 'quantity' => '1', 'price' => '1500', 'order_id' => 1],
            ['name' => 'galaxy s9', 'quantity' => '2', 'price' => '1000', 'order_id' => 1],
            ['name' => 'Apple Watch', 'quantity' => '3', 'price' => '1200', 'order_id' => 1],
        ]);
    }

    public function testWithCount()
    {
        $actual = Order::withAggregate('products', 'count', '*')->first();

        $expected = DB::select(
            DB::raw('select (select count(*) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_count" from "orders"')
        )[0];

        $this->assertEquals($expected->products_count, $actual->products_count);
    }

    public function testWithSum()
    {
        $actual = Order::withSum('products', 'quantity')->first();

        $expected = DB::select(
            DB::raw('select (select sum(quantity) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_sum" from "orders"')
        )[0];

        $this->assertEquals($expected->products_sum, $actual->products_sum);
    }

    public function testWithAvg()
    {
        $actual = Order::withAvg('products', 'price')->first();

        $expected = DB::select(
            DB::raw('select (select avg(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_avg" from "orders"')
        )[0];

        $this->assertEquals($expected->products_avg, $actual->products_avg);
    }

    public function testWithMin()
    {
        $actual = Order::withMin('products', 'price')->first();

        $expected = DB::select(
            DB::raw('select (select min(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_min" from "orders"')
        )[0];

        $this->assertEquals($expected->products_min, $actual->products_min);
    }

    public function testWithMax()
    {
        $actual = Order::withMax('products', 'price')->first();

        $expected = DB::select(
            DB::raw('select (select max(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_max" from "orders"')
        )[0];

        $this->assertEquals($expected->products_max, $actual->products_max);
    }

    public function testWithMinAndAlias()
    {
        $actual = Order::withMin('products as min_price', 'price')->first();

        $expected = DB::select(
            DB::raw('select (select min(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "min_price" from "orders"')
        )[0];

        $this->assertEquals($expected->min_price, $actual->min_price);
    }

    public function testWithMaxWithAliasWithWhere()
    {
        $actual = Order::withMax(['products as higher_price' => function ($query) {
            $query->where('quantity', '>', 1);
        }], 'price')->first();

        $expected = DB::select(
            DB::raw('select (select max(price) from "product_orders" where "orders"."id" = "product_orders"."order_id" and "quantity" > 1) as "higher_price" from "orders"')
        )[0];

        $this->assertEquals($expected->higher_price, $actual->higher_price);
    }

    public function testWithSumPricesAndCountQuantityWithAliases()
    {
        $actual = Order::withSum('products as order_price', 'price')->withSum('products as order_products_count', 'quantity')->withCount('products')->first();

        $expected = DB::select(
            DB::raw('select (select sum(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "order_price", (select sum(quantity) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "order_products_count", (select count(*) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_count" from "orders"')
        )[0];

        $this->assertEquals($expected->order_price, $actual->order_price);
        $this->assertEquals($expected->products_count, $actual->products_count);
        $this->assertEquals($expected->order_products_count, $actual->order_products_count);
    }

    public function testWithSumPricesAndCountUsingAggregate()
    {
        $actual = OrderWithAggregate::first();

        $expected = DB::select(
            DB::raw('select (select sum(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "order_price", (select sum(quantity) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "order_products_count", (select count(*) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_count" from "orders"')
        )[0];

        $this->assertEquals($expected->order_price, $actual->order_price);
        $this->assertEquals($expected->products_count, $actual->products_count);
        $this->assertEquals($expected->order_products_count, $actual->order_products_count);
    }
}

class OrderWithAggregate extends AggregateModel
{

    protected $table = "orders";

    protected $withCount = [
        "products"
    ];

    protected $withSum = [
        ["products as order_price", "price"],
        ["products as order_products_count", "quantity"]
    ];

    protected $withMin = [
        ["products", "price"]
    ];

    protected $withMax = [
        ["products", "price"]
    ];

    protected $withAvg = [
        ["products", "price"]
    ];

    public function products()
    {
        return $this->hasMany(ProductOrder::class, 'order_id');
    }
}

class Order extends Model
{
    public function products()
    {
        return $this->hasMany(ProductOrder::class, 'order_id');
    }
}

class ProductOrder extends Model
{
    //
}
