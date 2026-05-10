<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    private array $defaults = [
        'units'            => ['Pcs', 'Kg', 'Gm', 'Ltr', 'Mtr', 'Box', 'Dozen', 'Set', 'Pair', 'Bag', 'Ton'],
        'tax_categories'   => ['GST 0%', 'GST 5%', 'GST 12%', 'GST 18%', 'GST 28%', 'Exempt'],
        'categories'       => ['Electronics', 'Clothing', 'Food & Beverage', 'Furniture', 'Stationery', 'Other'],
        'stock_categories' => ['Raw Material', 'Finished Goods', 'Semi-Finished', 'Consumable', 'Other'],
    ];

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $data = [];

        foreach ($this->defaults as $type => $defaults) {
            $rows = SystemSetting::where('user_id', $userId)->where('type', $type)->orderBy('sort_order')->get();

            if ($rows->isEmpty()) {
                foreach ($defaults as $i => $val) {
                    SystemSetting::create(['user_id' => $userId, 'type' => $type, 'value' => $val, 'sort_order' => $i]);
                }
                $rows = SystemSetting::where('user_id', $userId)->where('type', $type)->orderBy('sort_order')->get();
            }

            $data[$type] = $rows;
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'  => 'required|in:units,tax_categories,categories,stock_categories',
            'value' => 'required|string|max:100',
        ]);

        $data['user_id']    = $request->user()->id;
        $data['sort_order'] = SystemSetting::where('user_id', $data['user_id'])->where('type', $data['type'])->max('sort_order') + 1;

        return response()->json(SystemSetting::create($data), 201);
    }

    public function update(Request $request, SystemSetting $systemSetting)
    {
        abort_if($systemSetting->user_id !== $request->user()->id, 403);
        $systemSetting->update($request->validate(['value' => 'required|string|max:100']));
        return response()->json($systemSetting);
    }

    public function destroy(Request $request, SystemSetting $systemSetting)
    {
        abort_if($systemSetting->user_id !== $request->user()->id, 403);
        $systemSetting->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
