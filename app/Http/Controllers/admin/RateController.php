<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{
    public function index()
    {
        // Group by golongan, pisah hari kerja & libur
        $rates = Rate::all()->groupBy('golongan')->map(function ($group) {
            return [
                'golongan'    => $group->first()->golongan,
                'hari_kerja'  => $group->firstWhere('day_type', 0),
                'hari_libur'  => $group->firstWhere('day_type', 1),
            ];
        })->values();

        return view('admin.tarif', compact('rates'));
    }

    public function update(Request $request, int $id_rate)
    {
        $validated = $request->validate([
            'uang_lembur' => 'required|integer|min:0',
            'uang_makan'  => 'required|integer|min:0',
            'pajak'       => 'required|numeric|min:0|max:1',
        ]);

        Rate::where('id_rate', $id_rate)->update($validated);

        return response()->json(['message' => 'Tarif berhasil diupdate']);
    }
}
