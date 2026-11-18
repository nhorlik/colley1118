<?php

namespace App\Http\Controllers;

use App\Models\Izomcsoport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Gyakorlatböngésző. A listázás a v_gyakorlat nézetre épül (lásd
 * 2026_11_18_000008_create_nezetek.php) — ez már tartalmazza a fő izomcsoport nevét
 * egy korrelált allekérdezéssel, nincs szükség kézi JOIN-ra az elsődleges izomcsoportra.
 */
class GyakorlatController extends Controller
{
    public function index(Request $request): View
    {
        $szuro = $request->validate([
            'kereses' => ['nullable', 'string', 'max:100'],
            'izomcsoport_id' => ['nullable', 'integer', 'exists:izomcsoport,izomcsoport_id'],
            'otthon' => ['nullable', 'boolean'],
            'tipus' => ['nullable', 'string', 'max:30'],
        ]);

        $lekerdezes = DB::table('v_gyakorlat');

        if (! empty($szuro['kereses'])) {
            $lekerdezes->where('nev', 'like', '%' . $szuro['kereses'] . '%');
        }
        if (! empty($szuro['tipus'])) {
            $lekerdezes->where('tipus', $szuro['tipus']);
        }
        if ($request->boolean('otthon')) {
            $lekerdezes->where('otthon_vegezheto', 1);
        }
        if (! empty($szuro['izomcsoport_id'])) {
            $lekerdezes->whereIn('gyakorlat_id', function ($q) use ($szuro) {
                $q->select('gyakorlat_id')->from('gyakorlat_izomcsoport')->where('izomcsoport_id', $szuro['izomcsoport_id']);
            });
        }

        $gyakorlatok = $lekerdezes->orderBy('nev')->paginate(24)->withQueryString();

        return view('gyakorlat.index', [
            'gyakorlatok' => $gyakorlatok,
            'izomcsoportok' => Izomcsoport::orderBy('sorrend')->get(),
            'szuro' => $szuro,
        ]);
    }

    public function show(int $gyakorlat): View
    {
        $sor = DB::table('v_gyakorlat')->where('gyakorlat_id', $gyakorlat)->firstOrFail();

        $izomcsoportok = DB::table('gyakorlat_izomcsoport')
            ->join('izomcsoport', 'izomcsoport.izomcsoport_id', '=', 'gyakorlat_izomcsoport.izomcsoport_id')
            ->where('gyakorlat_izomcsoport.gyakorlat_id', $gyakorlat)
            ->orderByDesc('gyakorlat_izomcsoport.elsodleges')
            ->orderBy('izomcsoport.sorrend')
            ->select('izomcsoport.nev', 'gyakorlat_izomcsoport.elsodleges')
            ->get();

        return view('gyakorlat.show', ['g' => $sor, 'izomcsoportok' => $izomcsoportok]);
    }
}
