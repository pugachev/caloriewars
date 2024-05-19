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



class CalorieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 摂取熱量データ
        $results = "";
        $results = DB::table('calories')
        ->selectRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') as tgtdate ,sum(calories.tgtcalorie) as sumcolorie")
        ->where('tgtcategory','!=','106')
        ->groupByRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d')")
        ->orderByRaw("DATE_FORMAT(calories.tgtdate,'%Y-%m-%d') desc")
        ->paginate(10);

        // 運動量・体重データ
        $physical_results = "";


        // dd($physical_results);
        // $results_consump = DB::table('calories')
        // ->select('calories.tgtdate as tgtdate', DB::raw("sum(calories.tgtcalorie) as sumcolorie"))
        // ->where('tgtcategory','106')
        // ->groupBy('calories.tgtdate')
        // ->orderBy('calories.tgtdate','desc')
        // ->paginate(10);

        //運動量をマージする
        foreach($results as $key=>$result){
            // dd($result->tgtdate);
            $physical_results = DB::table('physical_data')
            ->select('tgt_physical_category',"tgt_physical_data")
            ->whereRaw("DATE_FORMAT(tgt_physical_date,'%Y-%m-%d') = :tgtday",['tgtday'=>$result->tgtdate])->get();
            // ->orderBy("physical_data.tgt_physical_category desc")->get();
            foreach($physical_results as $key=>$val){
                // dd($val->tgt_physical_category.' '.$val->tgt_physical_data);
                dd($val->tgt_physical_data);
            }
        }

        // foreach($results as $key=>$result){
        //     if(empty($result->consump)){
        //         $result->consump=0;
        //     }
        // }

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

        return view('calorie.index', compact('results','categories','physical_categories'));
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
        dd($request);
        return redirect()->to('calorie')->with('message', 'テストです');
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
             ->leftJoin('physical_categories','DATE_FORMAT(calories.tgtdate,"%Y-%m-%d")','=','DATE_FORMAT(physical_categories.tgtcategory,"%Y-%m-%d")')
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

        $totalconsumptionsum = DB::table('calories')
            ->select(DB::raw("sum(calories.tgtcalorie) as totalcaloriesum"))
            ->where('tgtdate',$tgtdate)
            ->where('tgtcategory','=','106')
            ->groupBy('calories.tgtdate')
            ->value('totalcaloriesum');

        return view('calorie.detail', compact('results','categories','tgtdate','totalcaloriesum','totalconsumptionsum'));
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $calorie= Calorie::find($request->deleteId);
        $tmpdate = $calorie->tgtdate;
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

        // //検索ワードの存在チェック
        // if(isset($request->searchword) && !empty($request->searchword)){
        //     $query->where('tgtitem',$request->searchword);
        // }

        // //カテゴリの存在チェック
        // if(isset($request->searchcategory) && !empty($request->searchcategory)){
        //     $query->where('tgtcategory',$request->searchcategory);
        // }

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

        // dd(preg_replace_array('/\?/', $query->getBindings(), $query->toSql()));

        return $results;
    }

    /**
     * 第x週の合計折れ線グラフを作成
     */
    public function makegraph(Request $request) {

        $results = DB::select("select sum(tgtcalorie) as weeksum, date_format(tgtdate ,'%U') as week from calories where `tgtcategory`not in (106) group by week");
        $labels = array();
        $weeksum = array();
        foreach($results as $result){
            if($result->week=="00"){
                continue;
            }
            //labelの追加
            array_push($labels,$result->week);
            array_push($weeksum,$result->weeksum);
        }

        $categories = DB::table('categories')
        ->select('cateid','catename')
        ->orderBy('cateid','asc')
        ->get();

        return view('calorie.statics', compact('labels','weeksum','categories'));
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
}
