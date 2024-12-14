<?php

namespace App\Http\Controllers;

// include("jpgraph/src/jpgraph.php");
// include("jpgraph/src/jpgraph_line.php");

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Calorie;
use App\Models\Categorie;
use App\Exceptions\Exception;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Physical_data;
use Illuminate\Support\Facades\Log;
use DateTime;
use Carbon\Carbon;


class CalorieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 画面に渡すデータ 摂取熱量データ + 運動量データ
        $totaldata = [];

        // 摂取熱量データ
        $results = "";
        $results = DB::table('calories')
        ->selectRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') as tgtdate,sum(calories.tgtcalorie) as sumcolorie,MAX(CASE WHEN calories.tgtcategory = '104' THEN 1 ELSE 0 END) as has_tgtcategory_104")
        ->where('tgtcategory','!=','106')
        ->whereRaw("DATE_FORMAT(calories.tgtdate,'%Y') = ?", [date('Y')])
        ->groupByRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d')")
        ->orderByRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') desc")->get();
        // ->orderByRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') desc")->dd(); // SQLのデバッグ文
        // ->paginate(10);

        // 運動量・体重データ
        $physical_results = "";

        $merged_data = array();
        //運動量をマージする
        foreach($results as $result){
            // dd($result->tgtdate);
            $result->weeknum = CalorieController::getWeekOfYear($result->tgtdate);
            $week = array( "日", "月", "火", "水", "木", "金", "土" );
            $datetime = new DateTime($result->tgtdate);
            $result->weekday = $week[$datetime->format("w")];
            $result->walking_time = 0;
            $result->walking_steps = 0;
            $result->walking_distance = 0;
            $result->confirmed_weight = 0;
            $result->confirmed_calorie = 0;
            $physical_results = DB::table('physical_datas')
            ->select('tgt_physical_category',"tgt_physical_data")
            ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y-%m-%d') = :tgtday",['tgtday'=>$result->tgtdate])
            ->orderByRaw("physical_datas.tgt_physical_category asc")->get();

            if(isset($physical_results)){
                // physicalデータを格納するための配列を用意する
                foreach($physical_results as $key=>$val){
                    switch($val->tgt_physical_category){
                        // 歩行時間
                        case 200:
                            $result->walking_time = $val->tgt_physical_data;
                            // Log::debug("歩行時間 ".$val->tgt_physical_data);
                        break;
                        // 歩数
                        case 201:
                            $result->walking_steps = $val->tgt_physical_data;
                            // Log::debug("歩行時間 ".$val->tgt_physical_data);
                        break;
                        // 歩行距離
                        case 202:
                            $result->walking_distance = $val->tgt_physical_data;
                        break;
                        // 確定体重
                        case 203:
                            $result->confirmed_weight = $val->tgt_physical_data;
                        break;
                        // 確定熱量
                        case 204:
                            $result->confirmed_calorie = $val->tgt_physical_data;
                            // dd($result);
                        break;
                        default:
                        break;
                    }
                }
            }


            $merged_data[] = $result;

        }

        $merged_data = collect($merged_data);


        // 摂取熱量カテゴリ
        $categories = DB::table('categories')
             ->select('cateid','catename')
             ->orderBy('cateid','asc')
             ->get();

        // 運動量・体重カテゴリ
        $physical_categories = DB::table('physical_categories')
             ->select('physical_cateid','physical_catename')
             ->orderBy('physical_cateid','asc')
             ->get();

        // 配列からコレクションへ変換
        $collection = collect($merged_data);

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // 1ページあたりのアイテム数
        $perPage = 10;

        // 現在のページに表示するアイテムのスライスを取得
        $currentItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->all();

        // ページネーションのインスタンスを作成
        $paginatedItems = new LengthAwarePaginator(
            $currentItems, // 現在のページに表示するアイテム
            $collection->count(), // 全アイテム数
            $perPage, // 1ページあたりのアイテム数
            $currentPage, // 現在のページ
            ['path' => LengthAwarePaginator::resolveCurrentPath()] // ページネーションのパス
        );

        return view('calorie.index', compact('paginatedItems','categories','physical_categories'));
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
        $calorie=new Calorie();
        $calorie->tgtdate=date('Y-m-d',strtotime($request->tgtdate));
        $calorie->tgttimezone=$request->tgttimezone;
        $calorie->tgtcategory=$request->tgtcategory;
        $calorie->tgtitem=$request->tgtitem;

        //2023-06-11 fitbit用の修正 入力値から基礎代謝を控除する
        if($calorie->tgtcategory==106){
            if(intval($request->tgtcalorie)>1430){
                $calorie->tgtcalorie=intval($request->tgtcalorie)-intval(1430);
            }else{
                $calorie->tgtcalorie=intval($request->tgtcalorie);
            }
        }else{
            $calorie->tgtcalorie=$request->tgtcalorie;
        }


        $calorie->save();
        return redirect()->to('calorie')->with('message', 'データを保存しました');
    }

    /**
     * 運動量・体重情報を登録する
     */
    public function store_physical_info(Request $request)
    {
        $physical_data=new physical_data();
        $physical_data->tgt_physical_date=date('Y-m-d',strtotime($request->tgtdate));
        $physical_data->tgt_physical_category=$request->tgtcategory;
        if(isset($request->tgtitem) && trim($request->tgtitem)!=""){
            $physical_data->tgt_physical_item=$request->tgtitem;
        }else{
            $physical_data->tgt_physical_item="記載なし";
        }

        $physical_data->tgt_physical_data=$request->tgtcalorie;

        $physical_data->save();
        return redirect()->to('calorie')->with('message', 'データを保存しました');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($tgtdate)
    {
        $results = DB::table('calories')
            ->select('calories.id as id','tgtdate','categories.cateid as cateid','categories.catename as catename','tgttimezone','tgtitem','tgtcalorie')
            ->leftJoin('categories','categories.cateid','=','calories.tgtcategory')
            ->whereRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') = :tgtday",['tgtday'=>$tgtdate])
            ->orderBy('tgttimezone','asc')
            ->paginate(10);

        $categories = DB::table('categories')
             ->select('cateid','catename')
             ->orderBy('cateid','asc')
             ->get();

        $totalcaloriesum = DB::table('calories')
            ->select(DB::raw("sum(calories.tgtcalorie) as totalcaloriesum"))
            ->where('tgtdate',$tgtdate)
            ->where('tgtcategory','<>','106')
            ->groupBy('calories.tgtdate')
            ->value('totalcaloriesum');

        $totalconsumptionsum = DB::table('physical_datas')
            ->select(DB::raw("physical_datas.tgt_physical_data as totalcaloriesum"))
            ->where('physical_datas.tgt_physical_category','=','204')
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y-%m-%d') = ?", $tgtdate)
            ->value('totalconsumptionsum');

            $week = array( "日", "月", "火", "水", "木", "金", "土" );
            $datetime = new DateTime($tgtdate);
            $weekday = $week[$datetime->format("w")];

        return view('calorie.detail', compact('results','categories','tgtdate','totalcaloriesum','totalconsumptionsum','weekday'));
    }

    /**
     * 特定日付の運動量・消費カロリーを集める
     */
    public function showphysical($tgtdate)
    {
        $results = DB::table('physical_datas')
            ->select('physical_categories.physical_cateid as physical_cateid','tgt_physical_date','physical_categories.physical_catename as physical_catename','tgt_physical_item','tgt_physical_data','physical_datas.id','tgt_physical_category')
            ->leftJoin('physical_categories','physical_categories.physical_cateid','=','physical_datas.tgt_physical_category')
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y-%m-%d') = :tgtday",['tgtday'=>$tgtdate])
            ->orderByRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y-%m-%d') asc")->get();

        // 運動量・体重カテゴリ
        $physical_categories = DB::table('physical_categories')
             ->select('physical_cateid','physical_catename')
             ->orderBy('physical_cateid','asc')
             ->get();

        $week = array( "日", "月", "火", "水", "木", "金", "土" );
        $datetime = new DateTime($tgtdate);
        $weekday = $week[$datetime->format("w")];

        return view('calorie.detail_physical', compact('results','physical_categories','tgtdate','weekday'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $calorie= Calorie::find($request->updateId);
        $calorie->tgtdate=$request->tgtdate;
        $calorie->tgttimezone=$request->tgttimezone;
        $calorie->tgtcategory=$request->tgtcategory;
        $calorie->tgtitem=$request->tgtitem;
        //2023-06-11 fitbit用の修正 入力値から基礎代謝を控除する
        if($calorie->tgtcategory==106){
            if(intval($request->tgtcalorie)>1430){
                $calorie->tgtcalorie=intval($request->tgtcalorie)-intval(1430);
            }else{
                $calorie->tgtcalorie=intval($request->tgtcalorie);
            }
        }else{
            $calorie->tgtcalorie=$request->tgtcalorie;
        }

        $tmpdate = $calorie->tgtdate;

        $calorie->save();
        return redirect()->route('calorie.show', ['tgtdate' => $tmpdate])->with('message', 'データを更新しました');
    }

    public function updatephysical(Request $request)
    {
        $physical_data= Physical_data::find($request->updateId);
        $physical_data->tgt_physical_date=$request->utgt_physical_date;
        $physical_data->tgt_physical_category=$request->utgt_physical_category;
        $physical_data->tgt_physical_data=$request->utgt_physical_data;
        $physical_data->save();

        return redirect()->route('calorie.showphysical', ['tgtdate' => $request->utgt_physical_date])->with('message', 'データを更新しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyphysical(Request $request)
    {
        $physical_data= Physical_data::find($request->deleteId);
        $tmpdate = $request->dtgt_physical_date;
        $physical_data->delete();
        return redirect()->route('calorie.showphysical', ['tgtdate' => $tmpdate])->with('message', 'データを更新しました');
    }

    public function destroy(Request $request)
    {
        $calorie= Calorie::find($request->deleteId);
        $tmpdate = date("Y-m-d", strtotime($calorie->tgtdate));
        $calorie->delete();
        return redirect()->route('calorie.show', ['tgtdate' => $tmpdate])->with('message', 'データを更新しました');
    }

    public function search(Request $request) {

        $results="";
        $query = DB::table('calories')
        ->select('calories.id','tgtdate', 'tgttimezone','tgtcategory','tgtitem','tgtcalorie','categories.catename as catename',)
        ->leftJoin('categories','calories.tgtcategory','=','categories.cateid');

        //検索ワードの存在チェック
        if(isset($request->searchword) && !empty($request->searchword)){
            $pat = '%' . addcslashes($request->searchword, '%_\\') . '%';
            $query->where('tgtitem', 'LIKE', $pat);
        }

        //カテゴリの存在チェック
        if(isset($request->searchcategory) && !empty($request->searchcategory)){
            $query->where('tgtcategory',$request->searchcategory);
        }

        //日付の存在チェック
        //開始日と終了日の両方が存在する場合
        if((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))){
            $query->whereBetween('tgtdate',[$request->from,$request->to]);
        }
        //開始日だけが存在する場合
        else if(isset($request->from) && !empty($request->from)){
            $query->where('tgtdate','>=',$request->from);
        }
        //終了日だけが存在する場合
        else if(isset($request->to) && !empty($request->to)){
            $query->where('tgtdate','=<',$request->to);
        }

        $query->orderBy('calories.tgtdate','desc');

        $results = $query->paginate(10);

        // dd(preg_replace_array('/\?/', $query->getBindings(), $query->toSql()));

        return $results;
    }

    public function searchconsump(Request $request) {

        $results="";
        $query = DB::table('calories')
        ->select('calories.tgtdate as tgtdate', DB::raw("sum(calories.tgtcalorie) as sumcolorie"));

        //運動量の合計をあつめる
        $query->where('tgtcategory','106');


        //日付の存在チェック
        //開始日と終了日の両方が存在する場合
        if((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))){
            $query->whereBetween('tgtdate',[$request->from,$request->to]);
        }
        //開始日だけが存在する場合
        else if(isset($request->from) && !empty($request->from)){
            $query->where('tgtdate','>=',$request->from);
        }
        //終了日だけが存在する場合
        else if(isset($request->to) && !empty($request->to)){
            $query->where('tgtdate','=<',$request->to);
        }

        $query->groupBy('calories.tgtdate');

        $query->orderBy('calories.tgtdate','desc');
        //摂取過多のチェック
        if(isset($request->overcalorie) && !empty($request->overcalorie) && ($request->overcalorie==1) ){
            $query->orderBy(DB::raw("sumcolorie"),'desc');
        }

        $results = $query->paginate(10);

        return $results;
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 摂取カロリーと確定体重
     */
    public function makegraph(Request $request) {
    
        // (A) 週単位でカロリー接収データを集める
        $results = DB::table('calories')
            ->selectRaw("sum(tgtcalorie) as weeksum, date_format(tgtdate ,'%U') as week")
            ->where('tgtcategory', '!=', '106')
            ->whereRaw("DATE_FORMAT(calories.tgtdate,'%Y') = ?", [date('Y')])
            ->groupBy("week")->get();
    
        // 横軸に表示する第x週ラベル
        $labels = array();
        // 第x週のカロリー合計値
        $weeksum = array();
        foreach($results as $result){
            // labelの追加
            array_push($labels, $result->week);
            array_push($weeksum, $result->weeksum);
        }
    
        // (B) 週単位で確定体重データを集める
        $physical_results = DB::table('physical_datas')
        ->selectRaw("round(avg(tgt_physical_data),2) as week_avg_weight,date_format(tgt_physical_date ,'%U') as week")
        ->where("tgt_physical_category","=","203")
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [date('Y')])
        ->groupBy("week")->get();
    
        // 週単位の平均体重を格納する配列
        $week_avg_weight = array();
    
        // カロリーの週単位の配列を利用して平均確定配列を0で初期化
        for($i = 0; $i < count($labels); $i++){
            $week_avg_weight[$i] = 0;
        }
    
        // 平均体重の配列に第x週を添え字にして平均体重を格納する
        foreach($physical_results as $result){
            $week_avg_weight[$result->week] = $result->week_avg_weight;
        }
    
        // フィジカルデータ用のカテゴリを集める
        $categories = DB::table('categories')
            ->select('cateid', 'catename')
            ->orderBy('cateid', 'asc')
            ->get();
    
        return view('calorie.statics_cal_weight', compact('labels', 'weeksum', 'categories', 'week_avg_weight'));
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 歩数と歩行距離
     */
    public function makegraph2(Request $request) {
    
        // (A) 週単位で歩数データを集める
        $results = DB::table('physical_datas')
            ->selectRaw("date_format(tgt_physical_date ,'%Y-%m-%d') as date, date_format(tgt_physical_date ,'%U') as week, tgt_physical_data")
            ->where("tgt_physical_category", "=", "201")
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [date('Y')])
            ->get()
            ->groupBy('week')
            ->map(function ($weekData, $week) {
                $weekData = $weekData->pluck('tgt_physical_data')->toArray();
                $weekData = array_pad($weekData, 7, 0); // 7個に満たない場合は0で補完
                return (object)[
                    'week' => $week,
                    'week_avg_steps' => round(array_sum($weekData) / 7, 0) // 7個の平均を計算
                ];
            });
    
        // 横軸に表示する第x週ラベル
        $labels = array();
        // 第x週のカロリー合計値
        $weeksum = array();
        // 週単位の歩行距離を格納する配列
        $week_avg_distance = array();
    
        // 配列を初期化する
        $weeks = CalorieController::weeks();
        for($i=0;$i<=$weeks;$i++){
            $w = sprintf("%02d",($i));
            $labels[$i] = $w;
            $weeksum[$i] = 0;
            $week_avg_distance[$i] = 0;
        }
    
        // データを追加する
        foreach($results as $result){
            $weeksum[$result->week] = $result->week_avg_steps;
        }
    
        // (B) 週単位で歩行距離データを集める
        $physical_results = DB::table('physical_datas')
            ->selectRaw("date_format(tgt_physical_date ,'%Y-%m-%d') as date, date_format(tgt_physical_date ,'%U') as week, tgt_physical_data")
            ->where("tgt_physical_category", "=", "202")
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [date('Y')])
            ->get()
            ->groupBy('week')
            ->map(function ($weekData, $week) {
                $weekData = $weekData->pluck('tgt_physical_data')->toArray();
                $weekData = array_pad($weekData, 7, 0); // 7個に満たない場合は0で補完
                return (object)[
                    'week' => $week,
                    'week_avg_distance' => round(array_sum($weekData) / 7, 2) // 7個の平均を計算
                ];
            });
    
        // 平均体重の配列に第x週を添え字にして平均体重を格納する
        foreach($physical_results as $result){
            $week_avg_distance[$result->week] = $result->week_avg_distance;
        }
    
        // フィジカルデータ用のカテゴリを集める
        $categories = DB::table('categories')
            ->select('cateid','catename')
            ->orderBy('cateid','asc')
            ->get();
    
        return view('calorie.statics_steps_distance', compact('labels','weeksum','week_avg_distance','categories'));
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 歩数と歩行時間
     */
    public function makegraph3(Request $request) {
    
        // (A) 週単位で歩数データを集める
        $results = DB::table('physical_datas')
            ->selectRaw("date_format(tgt_physical_date ,'%Y-%m-%d') as date, date_format(tgt_physical_date ,'%U') as week, tgt_physical_data")
            ->where("tgt_physical_category", "=", "201")
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [date('Y')])
            ->get()
            ->groupBy('week')
            ->map(function ($weekData, $week) {
                $weekData = $weekData->pluck('tgt_physical_data')->toArray();
                $weekData = array_pad($weekData, 7, 0); // 7個に満たない場合は0で補完
                return (object)[
                    'week' => $week,
                    'week_avg_steps' => round(array_sum($weekData) / 7, 0) // 7個の平均を計算
                ];
            });
    
        // 横軸に表示する第x週ラベル
        $labels = array();
        // 第x週のカロリー合計値
        $weeksum = array();
        // 週単位の歩行距離を格納する配列
        $week_avg_time = array();
    
        // 配列を初期化する
        $weeks = CalorieController::weeks();
        for($i=0;$i<=$weeks;$i++){
            $w = sprintf("%02d",($i));
            $labels[$i] = $w;
            $weeksum[$i] = 0;
            $week_avg_time[$i] = 0;
        }
    
        // データを追加する
        foreach($results as $result){
            $weeksum[$result->week] = $result->week_avg_steps;
        }
    
        // (B) 週単位で歩行距離データを集める
        $physical_results = DB::table('physical_datas')
            ->selectRaw("date_format(tgt_physical_date ,'%Y-%m-%d') as date, date_format(tgt_physical_date ,'%U') as week, tgt_physical_data")
            ->where("tgt_physical_category", "=", "200")
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [date('Y')])
            ->get()
            ->groupBy('week')
            ->map(function ($weekData, $week) {
                $weekData = $weekData->pluck('tgt_physical_data')->toArray();
                $weekData = array_pad($weekData, 7, 0); // 7個に満たない場合は0で補完
                return (object)[
                    'week' => $week,
                    'week_avg_time' => round(array_sum($weekData) / 7, 2) // 7個の平均を計算
                ];
            });
    
        // 平均体重の配列に第x週を添え字にして平均体重を格納する
        foreach($physical_results as $result){
            $week_avg_time[$result->week] = $result->week_avg_time;
        }
    
        // フィジカルデータ用のカテゴリを集める
        $categories = DB::table('categories')
            ->select('cateid','catename')
            ->orderBy('cateid','asc')
            ->get();
    
        return view('calorie.statics_steps_time', compact('labels','weeksum','week_avg_time','categories'));
    }

    /**
     * 第x週の合計折れ線グラフを作成
     * 歩数と確定体重
     */
    public function makegraph4(Request $request) {
    
        // (A) 週単位で歩数データを集める
        $results = DB::table('physical_datas')
            ->selectRaw("date_format(tgt_physical_date ,'%Y-%m-%d') as date, date_format(tgt_physical_date ,'%U') as week, tgt_physical_data")
            ->where("tgt_physical_category", "=", "201")
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [date('Y')])
            ->get()
            ->groupBy('week')
            ->map(function ($weekData, $week) {
                $weekData = $weekData->pluck('tgt_physical_data')->toArray();
                $weekData = array_pad($weekData, 7, 0); // 7個に満たない場合は0で補完
                return (object)[
                    'week' => $week,
                    'week_avg_steps' => round(array_sum($weekData) / 7, 0) // 7個の平均を計算
                ];
            });
    
        // 横軸に表示する第x週ラベル
        $labels = array();
        // 第x週のカロリー合計値
        $weeksum = array();
        // 週単位の歩行距離を格納する配列
        $week_avg_weight = array();
    
        // 配列を初期化する
        $weeks = CalorieController::weeks();
        for($i=0;$i<=$weeks;$i++){
            $w = sprintf("%02d",($i));
            $labels[$i] = $w;
            $weeksum[$i] = 0;
            $week_avg_weight[$i] = 0;
        }
    
        // データを追加する
        foreach($results as $result){
            $weeksum[$result->week] = $result->week_avg_steps;
        }
    
        // (B) 週単位で確定体重データを集める
        $physical_results = DB::table('physical_datas')
            ->selectRaw("date_format(tgt_physical_date ,'%Y-%m-%d') as date, date_format(tgt_physical_date ,'%U') as week, tgt_physical_data")
            ->where("tgt_physical_category", "=", "203")
            ->whereRaw("DATE_FORMAT(physical_datas.tgt_physical_date,'%Y') = ?", [date('Y')])
            ->get()
            ->groupBy('week')
            ->map(function ($weekData, $week) {
                $weekData = $weekData->pluck('tgt_physical_data')->toArray();
                $weekData = array_pad($weekData, 7, 0); // 7個に満たない場合は0で補完
                return (object)[
                    'week' => $week,
                    'week_avg_weight' => round(array_sum($weekData) / 7, 2) // 7個の平均を計算
                ];
            });
    
        // 平均体重の配列に第x週を添え字にして平均体重を格納する
        foreach($physical_results as $result){
            $week_avg_weight[$result->week] = $result->week_avg_weight;
        }
    
        // フィジカルデータ用のカテゴリを集める
        $categories = DB::table('categories')
            ->select('cateid','catename')
            ->orderBy('cateid','asc')
            ->get();
    
        return view('calorie.statics_steps_weight', compact('labels','weeksum','week_avg_weight','categories'));
    }

    /**
     * チャートグラフを作成
     */
    public function chartgraph(Request $request) {
        $rsumcalorie="";
        $from="";
        $to="";
        try{
            //カテゴリIDとカテゴリ別の合計値の取得
            if((isset($request->from) && !empty($request->from)) && (isset($request->to) && !empty($request->to))){
                $from=$request->from;
                $to=$request->to;
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory',DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->whereBetween('tgtdate',[$request->from,$request->to])
                    ->groupBy('calories.tgtcategory')
                    ->get();
            }
            else if(isset($request->from) && !empty($request->from)){
                $from=$request->from;
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory',DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->where('tgtdate','>=',$request->from)
                    ->groupBy('calories.tgtcategory')
                    ->get();
            }
            else if(isset($request->to) && !empty($request->to)){
                $to=$request->to;
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory',DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->where('tgtdate','<=',$request->to)
                    ->groupBy('calories.tgtcategory')
                    ->get();
            }else{
                //日付の指定がない場合は当月の1日から末日まで検索する
                $first_date = date("Y-m-01");
                $last_date = date("Y-m-t");
                $rsumcalorie = DB::table('calories')
                    ->select('tgtcategory',DB::raw("sum(calories.tgtcalorie) as sumcalorie"))
                    ->whereBetween('tgtdate',[$first_date,$last_date])
                    ->groupBy('calories.tgtcategory')
                    ->get();
            }

            //カテゴリIDからカテゴリ名を取得
            $rcategories=[];
            foreach($rsumcalorie as $val){
                $rcategories[]=DB::table('categories')
                ->select('categories.catename')
                ->where('categories.cateid','=',$val->tgtcategory)
                ->get();
            }
            //SQL結果からカテゴリ名のみを配列に格納する
            $categories=[];
            foreach($rcategories as $val){
                $categories[]=$val[0]->catename;
            }
            //SQL結果からカテゴリ合計値のみ配列に格納する
            $sumcalorie=[];
            foreach($rsumcalorie as $val){
                $sumcalorie[]=$val->sumcalorie;
            }

            $categorieslist = DB::table('categories')
            ->select('cateid','catename')
            ->orderBy('cateid','asc')
            ->get();

            return view('calorie.chart', compact('categories','sumcalorie','from','to','categorieslist'));

        }catch(Exception $ex){
          error_log($ex->getMessage());
        }
    }


    // 現在が第x週かを調べる
    public static function weeks () {
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
    function getWeekOfYear($date) {
        // DateTimeオブジェクトを作成
        $dateTime = new DateTime($date);

        // 年の始まりを取得
        $yearStart = new DateTime($dateTime->format('Y') . '-01-01');

        // 日曜日を1週の始まりとするため、週番号の計算においてサンデースタートを設定
        $weekNumber = (int)$dateTime->format('W');
        $dayOfWeek = (int)$dateTime->format('w'); // 日曜日が0

        // 1月1日が日曜日でない場合、1週目の調整
        if ($dayOfWeek != 0) {
            $weekNumber--;
        }

        return $weekNumber;
    }

    /**
     * 接種カロリー最大値を取得する
     */
    public function getMaxColorie()
    {
        $year = Carbon::now()->year;

        $maxCalories = DB::table('calories')
            ->select(DB::raw("tgtdate,sum(calories.tgtcalorie) as maxcalorie"))
            ->where('tgtcategory', '<>', '106')
            ->whereYear('tgtdate', $year)
            ->groupBy('tgtdate')
            ->orderBy('maxcalorie', 'desc')
            ->orderBy('tgtdate', 'desc')
            ->limit(10)
            ->get();
        // dd($maxCalories->dd());
        return response()->json($maxCalories);
    }

    /**
     * 歩数最大値を取得する
     */
    public function getMaxSteps()
    {
        $year = Carbon::now()->year;

        $maxSteps = DB::table('physical_datas')
            ->select(DB::raw('tgt_physical_date,tgt_physical_data as maxsteps'))
            ->where('tgt_physical_category', '=', '201')
            ->whereYear('tgt_physical_date', $year)
            ->orderBy('tgt_physical_data', 'desc')
            ->orderBy('tgt_physical_date', 'desc')
            ->limit(10)
            ->get();
        // dd($maxDistance);
        return response()->json($maxSteps);
    }

    /**
     * 歩行距離最大値を取得する
     */
    public function getMaxDistance()
    {
        $year = Carbon::now()->year;

        $maxDistance = DB::table('physical_datas')
            ->select(DB::raw('tgt_physical_date,tgt_physical_data as maxdistance'))
            ->where('tgt_physical_category', '=', '202')
            ->whereYear('tgt_physical_date', $year)
            ->orderBy('tgt_physical_data', 'desc')
            ->orderBy('tgt_physical_date', 'desc')
            ->limit(10)
            ->get();
        // dd($maxDistance);
        return response()->json($maxDistance);
    }
}
