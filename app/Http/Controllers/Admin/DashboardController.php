<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\HistoryDownload;
use Yajra\DataTables\DataTables;
use App\Models\WhiteBlowingSystem;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $total_admin = User::count();
        $total_client = Client::count();
        $total_download = HistoryDownload::count();

        // add statistic wbs per status
        $wbs_pending = WhiteBlowingSystem::whereStatus(WhiteBlowingSystem::STATUS_PENDING)->count();
        $wbs_verification = WhiteBlowingSystem::whereStatus(WhiteBlowingSystem::STATUS_VERIFICATION)->count();
        $wbs_investigation = WhiteBlowingSystem::whereStatus(WhiteBlowingSystem::STATUS_INVESTIGATION)->count();
        $wbs_in_progress = WhiteBlowingSystem::whereStatus(WhiteBlowingSystem::STATUS_IN_PROGRESS)->count();
        $wbs_completed = WhiteBlowingSystem::whereStatus(WhiteBlowingSystem::STATUS_COMPLETED)->count();
        $wbs_finished = WhiteBlowingSystem::whereStatus(WhiteBlowingSystem::STATUS_FINISHED)->count();

        $statistics = [
            'total_admin' => $total_admin,
            'total_client' => $total_client,
            'total_download' => $total_download,
        ];
        $wbs_statistics = [
            'pending' => $wbs_pending,
            'verification' => $wbs_verification,
            'investigation' => $wbs_investigation,
            'in_progress' => $wbs_in_progress,
            'completed' => $wbs_completed,
            'finished' => $wbs_finished,
        ];
        // Get chart data for total history download, white blowing system and article by day in this month
        $chart_etika = HistoryDownload::selectRaw('TO_CHAR(created_at, \'DD\') as day, count(*) as total')
            ->whereMonth('created_at', date('m'))
            ->groupBy('day')
            ->pluck('total', 'day');

        $chart_wbs = WhiteBlowingSystem::selectRaw('TO_CHAR(created_at, \'DD\') as day, count(*) as total')->whereStatus(WhiteBlowingSystem::STATUS_PENDING)
            ->whereMonth('created_at', date('m'))
            ->groupBy('day')
            ->pluck('total', 'day');

        $chart_article = Article::selectRaw('TO_CHAR(created_at, \'DD\') as day, count(*) as total')->whereStatus(Article::STATUS_PUBLISHED)
            ->whereMonth('created_at', date('m'))
            ->groupBy('day')
            ->pluck('total', 'day');
        // count this days in this month
        $total_date = Carbon::now()->daysInMonth;
        $labels = [];
        for ($i = 1; $i <= $total_date; $i++) {
            $labels[] = $i;
        }
        $etika_values = [];
        $etika_values = array_fill(0, $total_date, 0);
        foreach ($chart_etika as $key => $value) {
            $etika_values[$key - 1] = $value;
        }
        $wbs_values = [];
        $wbs_values = array_fill(0, $total_date, 0);
        foreach ($chart_wbs as $key => $value) {
            $wbs_values[$key - 1] = $value;
        }
        $article_values = [];
        $article_values = array_fill(0, $total_date, 0);
        foreach ($chart_article as $key => $value) {
            $article_values[$key - 1] = $value;
        }

        $data = WhiteBlowingSystem::whereStatus(WhiteBlowingSystem::STATUS_PENDING)->latest()->take(10)->get();
        if (request()->ajax()) {
            return DataTables::of($data)
                ->addColumn('category', function ($data) {
                    return $data->categoryWhiteBlowingSystem->name ?? 'Tidak ada';
                })
                ->addColumn('status', function ($data) {
                    if ($data->status == WhiteBlowingSystem::STATUS_PENDING) {
                        return "<span class='badge bg-warning text-dark'> Laporan Diterima </span>";
                    } elseif ($data->status == WhiteBlowingSystem::STATUS_VERIFICATION) {
                        return "<span class='badge bg-info text-dark'>Dalam Proses Verifikasi</span>";
                    } elseif ($data->status == WhiteBlowingSystem::STATUS_INVESTIGATION) {
                        return "<span class='badge bg-primary text-dark'>Dalam Proses Investigasi</span>";
                    } elseif ($data->status == WhiteBlowingSystem::STATUS_IN_PROGRESS) {
                        return "<span class='badge bg-secondary text-dark'>Tindakan Korektif Sedang Dilaksanakan</span>";
                    } elseif ($data->status == WhiteBlowingSystem::STATUS_COMPLETED) {
                        return "<span class='badge bg-success text-dark'>Tindakan Korekftif Selesai</span>";
                    } elseif ($data->status == WhiteBlowingSystem::STATUS_FINISHED) {
                        return "<span class='badge bg-danger text-dark'>Laporan Selesai</span>";
                    }
                })
                ->addColumn('reporter', function ($data) {
                    return $data->reporterIdentity->name ?? 'Tanpa Identitas';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('white-blowing-system.edit', $data->id);
                    $actionShow = route('white-blowing-system.show', $data->id);
                    // $actionDelete = route('white-blowing-system.destroy', $data->id);
                    return "<div class='d-flex gap-2 flex-nowrap'>" .
                        view('components.action.confirmation', ['action' => $actionEdit]) .
                        view('components.action.show', ['action' => $actionShow]) .
                        // view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]);
                        "</div>";
                })
                ->rawColumns(['action', 'status', 'reporter'])
                ->make(true);
        }
        return view('admins.dashboard.index', compact('statistics', 'wbs_statistics', 'labels', 'etika_values', 'wbs_values', 'article_values'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
