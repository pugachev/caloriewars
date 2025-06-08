<?php
namespace App\Http\Controllers;

// include("jpgraph/src/jpgraph.php");
// include("jpgraph/src/jpgraph_line.php");

use App\Models\Calorie;
use App\Models\Physical_data;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalorieController extends Controller
{
    public function __construct()
    {
        // ログイン認証のミドルウェアをコメントアウト
        // $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): \Illuminate\Contracts\View\View  | \Illuminate\Contracts\View\Factory
    {
        $currentYear = date('Y');

        // カロリーデータを取得
        $results = DB::table('calories')
            ->selectRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') as tgtdate,
                        YEAR(calories.tgtdate) as year,
                        sum(calories.tgtcalorie) as sumcolorie,
                        MAX(CASE WHEN calories.tgtcategory = '104' THEN 1 ELSE 0 END) as has_tgtcategory_104")
            ->where('tgtcategory', '!=', '106')
            ->where('tgtdate', '>=', '2023-01-01')
            ->groupByRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d'), YEAR(calories.tgtdate)")
            ->orderByRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') desc")
            ->get();

        $merged_data = [];

        // 各データに運動量データを追加
        foreach ($results as $result) {
            // 週番号と曜日の追加
            $result->weeknum = CalorieController::getWeekOfYear($result->tgtdate);
            $week            = ["日", "月", "火", "水", "木", "金", "土"];
            $datetime        = new DateTime($result->tgtdate);
            $result->weekday = $week[$datetime->format("w")];

            // 運動量データの初期化
            $result->walking_time               = 0;
            $result->walking_steps              = 0;
            $result->walking_distance           = 0;
            $result->confirmed_weight           = 0;
            $result->confirmed_calorie          = 0;
            $result->stepper_count              = 0; // ステッパー数を追加
            $result->confirmed_physical_calorie = 0;

            // 該当日の運動量データを取得
            $physical_results = DB::table('physical_datas')
                ->select('tgt_physical_category', "tgt_physical_data")
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y-%m-%d') = :tgtday", ['tgtday' => $result->tgtdate])
                ->orderByRaw("physical_datas.tgt_physical_category asc")
                ->get();

            if (isset($physical_results)) {
                foreach ($physical_results as $val) {
                    switch ($val->tgt_physical_category) {
                        case 200: // 歩行時間
                            $result->walking_time = $val->tgt_physical_data;
                            break;
                        case 201: // 歩数
                            $result->walking_steps = $val->tgt_physical_data;
                            break;
                        case 202: // 歩行距離
                            $result->walking_distance = $val->tgt_physical_data;
                            break;
                        case 203: // 確定体重
                            $result->confirmed_weight = $val->tgt_physical_data;
                            break;
                        case 204: // 確定運動量
                            $result->confirmed_physical_calorie = $val->tgt_physical_data;
                            break;
                        case 205: // ステッパー
                            $result->stepper_count = $val->tgt_physical_data;
                            break;

                    }
                }
            }
            // 確定摂取熱量の計算
            $result->confirmed_calorie = $result->sumcolorie - (1450 + $result->confirmed_physical_calorie);

            $merged_data[] = $result;
        }

        // コレクションに変換してページネーション
        $collection = collect($merged_data);

        // 年度別にデータを分類
        $yearlyData = $collection->groupBy(function ($item) {
            return date('Y', strtotime($item->tgtdate));
        });

        $perPage             = 10;
        $paginatedYearlyData = [];

        foreach ($yearlyData as $year => $yearData) {
            $currentPage                = request()->get('page_' . $year, 1);
            $paginatedYearlyData[$year] = new \Illuminate\Pagination\LengthAwarePaginator(
                $yearData->forPage($currentPage, $perPage),
                $yearData->count(),
                $perPage,
                $currentPage,
                [
                    'path'     => request()->url(),
                    'pageName' => 'page_' . $year,
                    'query'    => ['year' => $year],
                ]
            );
        }

        return view('calorie.index', [
            'yearlyData'          => $paginatedYearlyData,
            'categories'          => DB::table('categories')->get(),
            'physical_categories' => DB::table('physical_categories')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('calorie.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $calorie              = new Calorie();
        $calorie->tgtdate     = date('Y-m-d', strtotime($request->tgtdate));
        $calorie->tgttimezone = $request->tgttimezone;
        $calorie->tgtcategory = $request->tgtcategory;
        $calorie->tgtitem     = $request->tgtitem;

        //2023-06-11 fitbit用の修正 入力値から基礎代謝を控除する
        if ($calorie->tgtcategory == 106) {
            if (intval($request->tgtcalorie) > 1430) {
                $calorie->tgtcalorie = intval($request->tgtcalorie) - intval(1430);
            } else {
                $calorie->tgtcalorie = intval($request->tgtcalorie);
            }
        } else {
            $calorie->tgtcalorie = $request->tgtcalorie;
        }

        $calorie->save();
        return redirect()->to('calorie')->with('message', 'データを保存しました');
    }

    /**
     * 運動量・体重情報を登録する
     */
    public function store_physical_info(Request $request)
    {
        // 同じ日付の同じカテゴリのデータが存在するか確認
        $existing_data = DB::table('physical_datas')
            ->where('tgt_physical_date', date('Y-m-d', strtotime($request->tgtdate)))
            ->where('tgt_physical_category', $request->tgtcategory)
            ->first();

        if ($existing_data) {
            // 既存のデータがある場合は、値を加算して更新
            DB::table('physical_datas')
                ->where('id', $existing_data->id)
                ->update([
                    'tgt_physical_data' => $existing_data->tgt_physical_data + floatval($request->tgtcalorie),
                    'updated_at'        => now(),
                ]);
        } else {
            // 新規データの作成
            $physical_data                        = new physical_data();
            $physical_data->tgt_physical_date     = date('Y-m-d', strtotime($request->tgtdate));
            $physical_data->tgt_physical_category = $request->tgtcategory;
            if (isset($request->tgtitem) && trim($request->tgtitem) != "") {
                $physical_data->tgt_physical_item = $request->tgtitem;
            } else {
                $physical_data->tgt_physical_item = "記載なし";
            }
            $physical_data->tgt_physical_data = $request->tgtcalorie;
            $physical_data->save();
        }

        // ステッパーの値が入力された場合の計算処理
        if ($request->tgtcategory == 205) {
            $stepper_value = floatval($request->tgtcalorie);

            // 歩行時間の計算
            $walking_time = $stepper_value * (6.0 / 4.0);

            // 歩数の計算
            $steps = $walking_time * 110;

            // 歩行距離の計算
            $walking_distance = ($steps * 0.7) / 1000;

            // 確定熱量の計算（ステッパー値を時間に変換）
            $confirmed_calorie = 6.0 * 64 * ($stepper_value / 60) * 1.05;

            // 既存の値を取得
            $existing_data = DB::table('physical_datas')
                ->where('tgt_physical_date', date('Y-m-d', strtotime($request->tgtdate)))
                ->whereIn('tgt_physical_category', [200, 201, 202, 204])
                ->get();

            // 各カテゴリの既存値を更新または新規作成
            $categories = [
                200 => $walking_time,
                201 => $steps,
                202 => $walking_distance,
                204 => $confirmed_calorie,
            ];

            foreach ($categories as $category_id => $value) {
                $existing = $existing_data->where('tgt_physical_category', $category_id)->first();

                if ($existing) {
                    // 既存の値に加算
                    DB::table('physical_datas')
                        ->where('id', $existing->id)
                        ->update(['tgt_physical_data' => $existing->tgt_physical_data + $value]);
                } else {
                    // 新規作成
                    DB::table('physical_datas')->insert([
                        'tgt_physical_date'     => date('Y-m-d', strtotime($request->tgtdate)),
                        'tgt_physical_category' => $category_id,
                        'tgt_physical_item'     => '記載なし',
                        'tgt_physical_data'     => $value,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                }
            }
        }

        return redirect()->to('calorie')->with('message', 'データを保存しました');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($tgtdate): \Illuminate\Contracts\View\View  | \Illuminate\Contracts\View\Factory
    {
        // 先頭10文字だけ取得（例：2025-05-28 00:00:00 → 2025-05-28）
        $tgtdate = substr($tgtdate, 0, 10);

        $results = DB::table('calories')
            ->select('calories.id as id', 'tgtdate', 'categories.cateid as cateid', 'categories.catename as catename', 'tgttimezone', 'tgtitem', 'tgtcalorie')
            ->leftJoin('categories', 'categories.cateid', '=', 'calories.tgtcategory')
            ->whereRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') = :tgtday", ['tgtday' => $tgtdate])
            ->orderBy('tgttimezone', 'asc')
            ->paginate(10);

        $categories = DB::table('categories')
            ->select('cateid', 'catename')
            ->orderBy('cateid', 'asc')
            ->get();

        $totalcaloriesum = DB::table('calories')
            ->select(DB::raw("sum(calories.tgtcalorie) as totalcaloriesum"))
            ->where('tgtdate', $tgtdate)
            ->where('tgtcategory', '<>', '106')
            ->groupBy('calories.tgtdate')
            ->value('totalcaloriesum');

        $totalconsumptionsum = DB::table('physical_datas')
            ->select(DB::raw("physical_datas.tgt_physical_data as totalcaloriesum"))
            ->where('physical_datas.tgt_physical_category', '=', '204')
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y-%m-%d') = ?", $tgtdate)
            ->value('totalconsumptionsum');

        $week     = ["日", "月", "火", "水", "木", "金", "土"];
        $datetime = new DateTime($tgtdate);
        $weekday  = $week[$datetime->format("w")];

        return view('calorie.detail', compact('results', 'categories', 'tgtdate', 'totalcaloriesum', 'totalconsumptionsum', 'weekday'));
    }

    /**
     * 特定日付の運動量・消費カロリーを集める
     */
    public function showphysical($tgtdate)
    {
        $results = DB::table('physical_datas')
            ->select('physical_categories.physical_cateid as physical_cateid', 'tgt_physical_date', 'physical_categories.physical_catename as physical_catename', 'tgt_physical_item', 'tgt_physical_data', 'physical_datas.id', 'tgt_physical_category')
            ->leftJoin('physical_categories', 'physical_categories.physical_cateid', '=', 'physical_datas.tgt_physical_category')
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y-%m-%d') = :tgtday", ['tgtday' => $tgtdate])
            ->orderByRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y-%m-%d') asc")->get();

        // 運動量・体重カテゴリ
        $physical_categories = DB::table('physical_categories')
            ->select('physical_cateid', 'physical_catename')
            ->orderBy('physical_cateid', 'asc')
            ->get();

        $week     = ["日", "月", "火", "水", "木", "金", "土"];
        $datetime = new DateTime($tgtdate);
        $weekday  = $week[$datetime->format("w")];

        return view('calorie.detail_physical', compact('results', 'physical_categories', 'tgtdate', 'weekday'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): void
    {
        // 空のメソッド
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $calorie              = Calorie::find($request->updateId);
        $calorie->tgtdate     = $request->tgtdate;
        $calorie->tgttimezone = $request->tgttimezone;
        $calorie->tgtcategory = $request->tgtcategory;
        $calorie->tgtitem     = $request->tgtitem;
        //2023-06-11 fitbit用の修正 入力値から基礎代謝を控除する
        if ($calorie->tgtcategory == 106) {
            if (intval($request->tgtcalorie) > 1430) {
                $calorie->tgtcalorie = intval($request->tgtcalorie) - intval(1430);
            } else {
                $calorie->tgtcalorie = intval($request->tgtcalorie);
            }
        } else {
            $calorie->tgtcalorie = $request->tgtcalorie;
        }

        $tmpdate = $calorie->tgtdate;

        $calorie->save();
        return redirect()->route('calorie.show', ['tgtdate' => date('Y-m-d', strtotime($tmpdate))])->with('message', 'データを更新しました');
    }

    public function updatephysical(Request $request)
    {
        $physical_data                        = Physical_data::find($request->updateId);
        $physical_data->tgt_physical_date     = $request->utgt_physical_date;
        $physical_data->tgt_physical_category = $request->utgt_physical_category;
        $physical_data->tgt_physical_data     = $request->utgt_physical_data;
        $physical_data->save();

        // ステッパーの値が更新された場合の計算処理
        if ($request->utgt_physical_category == 205) {
            $stepper_value = floatval($request->utgt_physical_data);

            // 歩行時間の計算
            $walking_time = $stepper_value * (6.0 / 4.0);

            // 歩数の計算
            $steps = $walking_time * 110;

            // 歩行距離の計算
            $walking_distance = ($steps * 0.7) / 1000;

            // 確定熱量の計算（ステッパー値を時間に変換）
            $confirmed_calorie = 6.0 * 64 * ($stepper_value / 60) * 1.05;

            // 既存の値を取得
            $existing_data = DB::table('physical_datas')
                ->where('tgt_physical_date', date('Y-m-d', strtotime($request->utgt_physical_date)))
                ->whereIn('tgt_physical_category', [200, 201, 202, 204])
                ->get();

            // 各カテゴリの既存値を更新または新規作成
            $categories = [
                200 => $walking_time,
                201 => $steps,
                202 => $walking_distance,
                204 => $confirmed_calorie,
            ];

            foreach ($categories as $category_id => $value) {
                $existing = $existing_data->where('tgt_physical_category', $category_id)->first();

                if ($existing) {
                    // 既存の値を更新
                    DB::table('physical_datas')
                        ->where('id', $existing->id)
                        ->update(['tgt_physical_data' => $value]);
                } else {
                    // 新規作成
                    DB::table('physical_datas')->insert([
                        'tgt_physical_date'     => date('Y-m-d', strtotime($request->utgt_physical_date)),
                        'tgt_physical_category' => $category_id,
                        'tgt_physical_item'     => '記載なし',
                        'tgt_physical_data'     => $value,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);
                }
            }
        }

        return redirect()->route('calorie.showphysical', ['tgtdate' => $request->utgt_physical_date])->with('message', 'データを更新しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyphysical(Request $request): \Illuminate\Http\RedirectResponse
    {
        $physical_data = Physical_data::find($request->deleteId);
        $tmpdate       = $request->dtgt_physical_date;
        $physical_data->delete();
        return redirect()->route('calorie.showphysical', ['tgtdate' => $tmpdate])->with('message', 'データを更新しました');
    }

    public function destroy(Request $request)
    {
        $calorie = Calorie::find($request->deleteId);
        $tmpdate = date("Y-m-d", strtotime($calorie->tgtdate));
        $calorie->delete();
        return redirect()->route('calorie.show', ['tgtdate' => $tmpdate])->with('message', 'データを更新しました');
    }

    public function search(Request $request)
    {

        $results = "";
        $query   = DB::table('calories')
            ->select('calories.id', 'tgtdate', 'tgttimezone', 'tgtcategory', 'tgtitem', 'tgtcalorie', 'categories.catename as catename', )
            ->leftJoin('categories', 'calories.tgtcategory', '=', 'categories.cateid');

        //検索ワードの存在チェック
        if (isset($request->searchword) && ! empty($request->searchword)) {
            $pat = '%' . addcslashes($request->searchword, '%_\\') . '%';
            $query->where('tgtitem', 'LIKE', $pat);
        }

        //カテゴリの存在チェック
        if (isset($request->searchcategory) && ! empty($request->searchcategory)) {
            $query->where('tgtcategory', $request->searchcategory);
        }

        //日付の存在チェック
        //開始日と終了日の両方が存在する場合
        if ((isset($request->from) && ! empty($request->from)) && (isset($request->to) && ! empty($request->to))) {
            $query->whereBetween('tgtdate', [$request->from, $request->to]);
        }
        //開始日だけが存在する場合
        else if (isset($request->from) && ! empty($request->from)) {
            $query->where('tgtdate', '>=', $request->from);
        }
        //終了日だけが存在する場合
        else if (isset($request->to) && ! empty($request->to)) {
            $query->where('tgtdate', '=<', $request->to);
        }

        $query->orderBy('calories.tgtdate', 'desc');

        $results = $query->paginate(10);

        // dd(preg_replace_array('/\?/', $query->getBindings(), $query->toSql()));

        return $results;
    }

    public function searchconsump(Request $request)
    {

        $results = "";
        $query   = DB::table('calories')
            ->select('calories.tgtdate as tgtdate', DB::raw("sum(tgtcalorie) as sumcolorie"));

        //運動量の合計をあつめる
        $query->where('tgtcategory', '106');

        //日付の存在チェック
        //開始日と終了日の両方が存在する場合
        if ((isset($request->from) && ! empty($request->from)) && (isset($request->to) && ! empty($request->to))) {
            $query->whereBetween('tgtdate', [$request->from, $request->to]);
        }
        //開始日だけが存在する場合
        else if (isset($request->from) && ! empty($request->from)) {
            $query->where('tgtdate', '>=', $request->from);
        }
        //終了日だけが存在する場合
        else if (isset($request->to) && ! empty($request->to)) {
            $query->where('tgtdate', '=<', $request->to);
        }

        $query->groupBy('calories.tgtdate');

        $query->orderBy('calories.tgtdate', 'desc');
        //摂取過多のチェック
        if (isset($request->overcalorie) && ! empty($request->overcalorie) && ($request->overcalorie == 1)) {
            $query->orderBy(DB::raw("sumcolorie"), 'desc');
        }

        $results = $query->paginate(10);

        return $results;
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 摂取カロリーと確定体重
     */
    public function makegraph()
    {
        try {
            $currentYear = date('Y');

            // (A) 週単位でカロリー接収データを集める
            $results = DB::table('calories')
                ->selectRaw("sum(tgtcalorie) as weeksum, FLOOR((DAYOFYEAR(tgtdate) - 1) / 7) as week")
                ->where('tgtcategory', '!=', '106')
                ->whereRaw("DATE_FORMAT(calories.tgtdate,'%Y') = ?", [$currentYear])
                ->groupBy("week")->get();

            // 横軸に表示する第x週ラベル
            $labels = [];
            // 第x週のカロリー合計値
            $weeksum = [];
            foreach ($results as $result) {
                // labelの追加
                array_push($labels, $result->week);
                array_push($weeksum, $result->weeksum);
            }

            // (B) 週単位で確定体重データを集める
            $physical_results = DB::table('physical_datas')
                ->selectRaw("round(avg(tgt_physical_data),2) as week_avg_weight, FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7) as week")
                ->where("tgt_physical_category", "=", "203")
                ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [$currentYear])
                ->groupBy("week")->get();

            // 週単位の平均体重を格納する配列
            $week_avg_weight = [];

            // カロリーの週単位の配列を利用して平均確定配列を0で初期化
            for ($i = 0; $i < count($labels); $i++) {
                $week_avg_weight[$i] = 0;
            }

            // 平均体重の配列に第x週を添え字にして平均体重を格納する
            foreach ($physical_results as $result) {
                $week_avg_weight[$result->week] = $result->week_avg_weight;
            }

            // フィジカルデータ用のカテゴリを集める
            $categories = DB::table('categories')
                ->select('cateid', 'catename')
                ->orderBy('cateid', 'asc')
                ->get();

            return view('calorie.statics_cal_weight', compact('labels', 'weeksum', 'categories', 'week_avg_weight'));
        } catch (Exception $e) {
            error_log($e->getMessage());
            return redirect()->route('calorie')->with('error', 'グラフの生成に失敗しました。');
        }
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 摂取カロリーと確定体重
     */
    public function makegraphajax(Request $request)
    {
        try {
            $year = $request->input('tgtyear', date('Y'));

            // 52週分の配列を0で初期化（0週から51週まで）
            $all_weeks       = range(0, 51);
            $weeksum         = array_fill(0, 52, 0);
            $week_avg_weight = array_fill(0, 52, 0);

            // (A) 週単位でカロリー接収データを集める
            $results = DB::table('calories')
                ->selectRaw("
                    sum(tgtcalorie) as weeksum,
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgtdate, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgtdate) + DAYOFWEEK(DATE_FORMAT(tgtdate, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgtdate) - 1) / 7)
                    END as week,
                    date_format(tgtdate ,'%Y') as year
                ")
                ->where('tgtcategory', '!=', '106')
                ->whereRaw("DATE_FORMAT(calories.tgtdate,'%Y') = ?", [$year])
                ->groupBy("week", "year")
                ->orderBy("week")
                ->get();

            // カロリーデータの処理
            foreach ($results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $weeksum[$result->week] = round($result->weeksum, 0);
                }
            }

            // (B) 週単位で確定体重データを集める
            $physical_results = DB::table('physical_datas')
                ->selectRaw("
                    round(avg(tgt_physical_data),2) as week_avg_weight,
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgt_physical_date) + DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7)
                    END as week,
                    date_format(tgt_physical_date ,'%Y') as year
                ")
                ->where("tgt_physical_category", "=", "203")
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y') = ?", [$year])
                ->groupBy("week", "year")
                ->orderBy("week")
                ->get();

            // 確定体重データの処理
            foreach ($physical_results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $week_avg_weight[$result->week] = round($result->week_avg_weight, 1);
                }
            }

            return response()->json([
                'labels'          => $all_weeks,
                'weeksum'         => $weeksum,
                'week_avg_weight' => $week_avg_weight,
                'year'            => $year,
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return response()->json(['error' => 'データの取得に失敗しました。'], 500);
        }
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 歩数と歩行距離
     */
    public function makegraph2()
    {
        try {
            $currentYear = date('Y');
            $labels      = ['2025', '2024', '2023'];
            return view('calorie.statics_steps_distance', compact('labels'));
        } catch (Exception $e) {
            error_log($e->getMessage());
            return redirect()->route('calorie')->with('error', 'グラフの生成に失敗しました。');
        }
    }

    /**
     * 歩数と歩行距離のAjaxデータ取得
     */
    public function makegraph2ajax(Request $request)
    {
        try {
            $year = $request->input('tgtyear', date('Y'));

            // 52週分の配列を0で初期化（0週から51週まで）
            $all_weeks     = range(0, 51);
            $steps_data    = array_fill(0, 52, 0);
            $distance_data = array_fill(0, 52, 0);

            // (A) 週単位で歩数データを集める
            $steps_results = DB::table('physical_datas')
                ->selectRaw("
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgt_physical_date) + DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7)
                    END as week,
                    date_format(tgt_physical_date ,'%Y') as year,
                    SUM(tgt_physical_data) / 7 as avg_steps  -- 7で割って週平均を計算
                ")
                ->where('tgt_physical_category', '=', '201') // 歩数
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y') = ?", [$year])
                ->groupBy('week', 'year')
                ->orderBy('week')
                ->get();

            // (B) 週単位で歩行距離データを集める
            $distance_results = DB::table('physical_datas')
                ->selectRaw("
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgt_physical_date) + DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7)
                    END as week,
                    date_format(tgt_physical_date ,'%Y') as year,
                    SUM(tgt_physical_data) / 7 as avg_distance  -- 7日で割って週平均を計算
                ")
                ->where('tgt_physical_category', '=', '202') // 歩行距離
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y') = ?", [$year])
                ->groupBy('week', 'year')
                ->orderBy('week')
                ->get();

            // 歩数データの処理
            foreach ($steps_results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $steps_data[$result->week] = round($result->avg_steps, 0);
                }
            }

            // 歩行距離データの処理
            foreach ($distance_results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $distance_data[$result->week] = round($result->avg_distance, 1);
                }
            }

            return response()->json([
                'labels'        => $all_weeks,
                'steps_data'    => $steps_data,
                'distance_data' => $distance_data,
                'year'          => $year,
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return response()->json(['error' => 'データの取得に失敗しました。'], 500);
        }
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 歩数と歩行時間
     */
    public function makegraph3()
    {
        $labels = ['2025', '2024', '2023'];
        return view('calorie.statics_steps_time', compact('labels'));
    }

    public function makegraph3ajax(Request $request)
    {
        try {
            $year = $request->input('tgtyear', date('Y'));

            // 52週分の配列を0で初期化（0週から51週まで）
            $all_weeks  = range(0, 51);
            $steps_data = array_fill(0, 52, 0);
            $time_data  = array_fill(0, 52, 0);

            // (A) 週単位で歩数データを集める
            $steps_results = DB::table('physical_datas')
                ->selectRaw("
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgt_physical_date) + DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7)
                    END as week,
                    date_format(tgt_physical_date ,'%Y') as year,
                    SUM(tgt_physical_data) / 7 as avg_steps  -- 7で割って週平均を計算
                ")
                ->where('tgt_physical_category', '=', '201') // 歩数
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y') = ?", [$year])
                ->groupBy('week', 'year')
                ->orderBy('week')
                ->get();

            // (B) 週単位で歩行時間データを集める
            $time_results = DB::table('physical_datas')
                ->selectRaw("
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgt_physical_date) + DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7)
                    END as week,
                    date_format(tgt_physical_date ,'%Y') as year,
                    SUM(tgt_physical_data) / 7 as avg_time  -- 7で割って週平均を計算
                ")
                ->where('tgt_physical_category', '=', '200') // 歩行時間
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y') = ?", [$year])
                ->groupBy('week', 'year')
                ->orderBy('week')
                ->get();

            // 歩数データの処理
            foreach ($steps_results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $steps_data[$result->week] = round($result->avg_steps, 0);
                }
            }

            // 歩行時間データの処理
            foreach ($time_results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $time_data[$result->week] = round($result->avg_time, 0);
                }
            }

            return response()->json([
                'labels'     => $all_weeks,
                'steps_data' => $steps_data,
                'time_data'  => $time_data,
                'year'       => $year,
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return response()->json(['error' => 'データの取得に失敗しました。'], 500);
        }
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 歩数と歩行時間
     */
    public function makegraph4()
    {
        $labels = ['2025', '2024', '2023'];
        return view('calorie.statics_steps_weight', compact('labels'));
    }

    public function makegraph4ajax(Request $request)
    {
        try {
            $year = $request->input('tgtyear', date('Y'));

            // 52週分の配列を0で初期化（0週から51週まで）
            $all_weeks   = range(0, 51);
            $steps_data  = array_fill(0, 52, 0);
            $weight_data = array_fill(0, 52, 0);

            // (A) 週単位で歩数データを集める
            $steps_results = DB::table('physical_datas')
                ->selectRaw("
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgt_physical_date) + DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7)
                    END as week,
                    date_format(tgt_physical_date ,'%Y') as year,
                    SUM(tgt_physical_data) / 7 as avg_steps  -- 7で割って週平均を計算
                ")
                ->where('tgt_physical_category', '=', '201') // 歩数
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y') = ?", [$year])
                ->groupBy('week', 'year')
                ->orderBy('week')
                ->get();

            // (B) 週単位で確定体重データを集める
            $weight_results = DB::table('physical_datas')
                ->selectRaw("
                    CASE
                        WHEN DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) > 1
                        THEN FLOOR((DAYOFYEAR(tgt_physical_date) + DAYOFWEEK(DATE_FORMAT(tgt_physical_date, '%Y-01-01')) - 2) / 7)
                        ELSE FLOOR((DAYOFYEAR(tgt_physical_date) - 1) / 7)
                    END as week,
                    date_format(tgt_physical_date ,'%Y') as year,
                    SUM(tgt_physical_data) / COUNT(*) as avg_weight  -- データ個数で割って平均を計算
                ")
                ->where('tgt_physical_category', '=', '203') // 確定体重
                ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y') = ?", [$year])
                ->groupBy('week', 'year')
                ->orderBy('week')
                ->get();

            // 歩数データの処理
            foreach ($steps_results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $steps_data[$result->week] = round($result->avg_steps, 0);
                }
            }

            // 確定体重データの処理
            foreach ($weight_results as $result) {
                if ($result->week >= 0 && $result->week <= 51) {
                    $weight_data[$result->week] = round($result->avg_weight, 1);
                }
            }

            return response()->json([
                'labels'      => $all_weeks,
                'steps_data'  => $steps_data,
                'weight_data' => $weight_data,
                'year'        => $year,
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return response()->json(['error' => 'データの取得に失敗しました。'], 500);
        }
    }

    /**
     * チャートグラフを作成
     */
    public function chartgraph(Request $request)
    {
        $rsumcalorie = "";
        $from        = "";
        $to          = "";
        try {
            //カテゴリIDとカテゴリ別の合計値の取得
            if ((isset($request->from) && ! empty($request->from)) && (isset($request->to) && ! empty($request->to))) {
                $from        = $request->from;
                $to          = $request->to;
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory', DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->whereBetween('tgtdate', [$request->from, $request->to])
                    ->groupBy('calories.tgtcategory')
                    ->get();
            } else if (isset($request->from) && ! empty($request->from)) {
                $from        = $request->from;
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory', DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->where('tgtdate', '>=', $request->from)
                    ->groupBy('calories.tgtcategory')
                    ->get();
            } else if (isset($request->to) && ! empty($request->to)) {
                $to          = $request->to;
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory', DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->where('tgtdate', '<=', $request->to)
                    ->groupBy('calories.tgtcategory')
                    ->get();
            } else {
                //日付の指定がない場合は当月の1日から末日まで検索する
                $first_date  = date("Y-m-01");
                $last_date   = date("Y-m-t");
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory', DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->whereBetween('tgtdate', [$first_date, $last_date])
                    ->groupBy('calories.tgtcategory')
                    ->get();
            }

            //カテゴリIDからカテゴリ名を取得
            $rcategories = [];
            foreach ($rsumcalorie as $val) {
                $rcategories[] = DB::table('categories')
                    ->select('categories.catename')
                    ->where('categories.cateid', '=', $val->tgtcategory)
                    ->get();
            }
            //SQL結果からカテゴリ名のみを配列に格納する
            $categories = [];
            foreach ($rcategories as $val) {
                $categories[] = $val[0]->catename;
            }
            //SQL結果からカテゴリ合計値のみ配列に格納する
            $sumcalorie = [];
            foreach ($rsumcalorie as $val) {
                $sumcalorie[] = $val->sumcalorie;
            }

            $categorieslist = DB::table('categories')
                ->select('cateid', 'catename')
                ->orderBy('cateid', 'asc')
                ->get();

            return view('calorie.chart', compact('categories', 'sumcalorie', 'from', 'to', 'categorieslist'));

        } catch (Exception $ex) {
            error_log($ex->getMessage());
        }
    }

    // 現在が第x週かを調べる
    public static function weeks()
    {
        $today = time();
        $start = mktime(0, 0, 0, 1, 1, date('Y'));
        while (date('w', $start) != 0) {
            // 日曜日になるまで1日ずつ移動させる
            // 24(時間) * 60(分) * 60(秒)
            $start += 24 * 60 * 60;
        }
        // 今日までの週を計算していく
        $weeks = 0;
        while ($start < $today) {
            // 週ずつかけていく。
            $start += 7 * 24 * 60 * 60;
            $weeks++;
        }
        return $weeks;
    }

    /**
     * 特定の日付が第x週かを調べる
     */
    public function getWeekOfYear($date)
    {
        // DateTimeオブジェクトを作成
        $dateTime = new DateTime($date);

        // 年の最初の日を取得
        $firstDayOfYear = new DateTime($dateTime->format('Y') . '-01-01');

        // 年初からの経過日数を取得（0始まり）
        $dayOfYear = $dateTime->format('z');

        // 年初の曜日を取得（0:日曜日 ～ 6:土曜日）
        $firstDayOfWeek = (int) $firstDayOfYear->format('w');

        // 最初の日曜日までの日数を調整
        if ($firstDayOfWeek > 0) {
            // 年初が日曜日以外の場合、次の日曜日までを第0週とする
            $daysUntilFirstSunday = 7 - $firstDayOfWeek;
            if ($dayOfYear < $daysUntilFirstSunday) {
                return 0;
            }
            $dayOfYear -= $daysUntilFirstSunday;
            return floor($dayOfYear / 7) + 1;
        } else {
            // 年初が日曜日の場合
            return floor($dayOfYear / 7);
        }
    }

    /**
     * 接種カロリー最大値を取得する
     */
    public function getMaxColorie()
    {
        $maxCalories = DB::table('calories')
            ->select(DB::raw("tgtdate,sum(calories.tgtcalorie) as maxcalorie"))
            ->where('tgtcategory', '<>', '106')
            ->groupBy('tgtdate')
            ->orderBy('maxcalorie', 'desc')
            ->orderBy('tgtdate', 'desc')
            ->limit(10)
            ->get();

        return response()->json($maxCalories);
    }

    /**
     * 歩数最大値を取得する
     */
    public function getMaxSteps()
    {
        $maxSteps = DB::table('physical_datas')
            ->select(DB::raw('tgt_physical_date,tgt_physical_data as maxsteps'))
            ->where('tgt_physical_category', '=', '201')
            ->orderBy('tgt_physical_data', 'desc')
            ->orderBy('tgt_physical_date', 'desc')
            ->limit(10)
            ->get();

        return response()->json($maxSteps);
    }

    /**
     * 歩行距離最大値を取得する
     */
    public function getMaxDistance()
    {
        $maxDistance = DB::table('physical_datas')
            ->select(DB::raw('tgt_physical_date,tgt_physical_data as maxdistance'))
            ->where('tgt_physical_category', '=', '202')
            ->orderBy('tgt_physical_data', 'desc')
            ->orderBy('tgt_physical_date', 'desc')
            ->limit(10)
            ->get();

        return response()->json($maxDistance);
    }

    // 週番号計算用のヘルパーメソッドを追加
    private function calculateWeekNumber($date)
    {
        // DateTimeオブジェクトを作成
        $dateTime = new DateTime($date);

        // 年の最初の日を取得
        $firstDayOfYear = new DateTime($dateTime->format('Y') . '-01-01');

        // 年初からの経過日数を取得（0始まり）
        $dayOfYear = $dateTime->format('z');

        // 年初の曜日を取得（0:日曜日 ～ 6:土曜日）
        $firstDayOfWeek = (int) $firstDayOfYear->format('w');

        // 最初の日曜日までの日数を調整
        if ($firstDayOfWeek > 0) {
            // 年初が日曜日以外の場合、次の日曜日までを第0週とする
            $daysUntilFirstSunday = 7 - $firstDayOfWeek;
            if ($dayOfYear < $daysUntilFirstSunday) {
                return 0;
            }
            $dayOfYear -= $daysUntilFirstSunday;
            return floor($dayOfYear / 7) + 1;
        } else {
            // 年初が日曜日の場合
            return floor($dayOfYear / 7);
        }
    }
}
