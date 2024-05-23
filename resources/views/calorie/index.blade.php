@extends('layouts.app')
@section('content')

@if(session('message'))
<div id="alert" class="alert alert-success">{{session('message')}}</div>
@endif

<?php
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection; //追記
use Illuminate\Pagination\LengthAwarePaginator; //追記
// 摂取熱量カテゴリ
$cate_data="";
//戻りがオブジェクト型
foreach($categories as $val){
    $cate_data .= "<option value='". $val->cateid;
    $cate_data .= "'>". $val->catename. "</option>";
}
// 運動量・体重カテゴリ
$physical_cate_data="";
//戻りがオブジェクト型
foreach($physical_categories as $val){
    $physical_cate_data .= "<option value='". $val->physical_cateid;
    $physical_cate_data .= "'>". $val->physical_catename. "</option>";
}

?>
<div class="mx-auto col-12" style="text-align:center;">
    <div><h3>食べすぎやろ</h3></div>
</div>
<div class="mx-auto col-12 d-flex flex-row justify-content-center">
    <div class="mr-2"><h4><small>目標:<strong>1600kcal</strong></small></h4></div>
</div>
<div class="mx-auto col-12" style="text-align:center;">
    <div class="table-responsive-sm text-nowrap">
      <table class="table table-striped" id="mytable">
          <thead>
              <tr>
                  <th class="text-center">日付</th>
                  <th class="text-center">摂取熱量合計</th>
                  <th class="text-center">歩行時間</th>
                  <th class="text-center">歩数</th>
                  <th class="text-center">歩行距離</th>
                  <th class="text-center">確定体重</th>
                  <th class="text-center">確定体重</th>
                  <th class="text-center">詳細</th>
              </tr>
          </thead>
          <tbody>
              <?php
                foreach($paginatedItems as $key => $result){
                    $tmpdate = date('Y-m-d',strtotime($result->tgtdate));
                    echo '<tr>';
                    echo '<td>' . date('Y-m-d',strtotime($result->tgtdate)).'</td>';
                    echo '<td class="text-center">'.$result->sumcolorie.'</td>';
                    echo '<td class="text-center">'.$result->walking_time.'</td>';
                    echo '<td class="text-center">'.$result->walking_steps.'</td>';
                    echo '<td class="text-center">'.$result->walking_distance.'</td>';
                    echo '<td class="text-center">'.$result->confirmed_weight.'</td>';
                    echo '<td class="text-center">'.$result->confirmed_calorie.'</td>';
                    echo '<td class="text-center"><a class="btn btn-primary" href='.url("/calorie/show/$tmpdate").'>詳細</a></td>';
                    echo '</tr>';
                }
                // for($i=0;$i<count($merged_data);$i++){
                //     $tmpdate = date('Y-m-d',strtotime($merged_data[$i]->tgtdate));
                //     echo '<tr>';
                //     echo '<td>' . date('Y-m-d',strtotime($merged_data[$i]->tgtdate)).'</td>';
                //     echo '<td class="text-center">'.$merged_data[$i]->sumcolorie.'</td>';
                //     echo '<td class="text-center">'.$merged_data[$i]->walking_time.'</td>';
                //     echo '<td class="text-center">'.$merged_data[$i]->walking_steps.'</td>';
                //     echo '<td class="text-center">'.$merged_data[$i]->walking_distance.'</td>';
                //     echo '<td class="text-center">'.$merged_data[$i]->confirmed_weight.'</td>';
                //     echo '<td class="text-center">'.$merged_data[$i]->confirmed_calorie.'</td>';
                //     echo '<td class="text-center"><a class="btn btn-primary" href='.url("/calorie/show/$tmpdate").'>詳細</a></td>';
                //     echo '</tr>';
                // }
              ?>
          </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-center mt-5">
        {{-- {!! $results->links() !!} --}}
        {{-- {{$merged_data->appends(request()->query())->links()}} --}}
        {{-- {{$paginatedData->appends(request()->query())->links()}} --}}
        {{ $paginatedItems->links() }}
    </div>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.min.js"></script>
<!-- bootstrap-datepicker -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/css/theme.default.min.css">
<style>
</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
<script type="text/javascript">
  $(function(){
    setTimeout(function () {
        //保存後に画面がリダイレクトされることを利用している
        $('#alert').fadeOut(3000);
    }, 3000);

    $('.datepicker.datepicker-dropdown').datepicker({
        beforeShow: function(input, inst){
            setTimeout(function(){
                $('#tgtdate')
                    .css(
                        'z-index',
                        String(parseInt($(input).parents('.modal').css('z-index'),10) + 1)
                    );
            },0);
        }
    });

    // $('#mytable').tablesorter();

  });
  $('.datepicker').datepicker({
    // オプションを設定
    language:'ja', // 日本語化
    format: 'yyyy/mm/dd', // 日付表示をyyyy/mm/ddにフォーマット
  });
</script>
</body>
</html>
<!-- 新規作成モーダルダイアログ -->
<div class="modal fade" id="dataCreate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">摂取熱量</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{route('calorie.store')}}" class="form-inline" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group mb-1">
                            <span class="col-2">日付</span>
                            <input type="text" class="datepicker datepicker-dropdown" id="tgtdate" name="tgtdate">
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">時間帯</span>
                                <select name="tgttimezone" id="utgttimezone" class="browser-default custom-select">
                                <option value="0">朝</option>
                                <option value="1">昼</option>
                                <option value="2">夜</option>
                            </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">種類</span>
                                <select name="tgtcategory" id="tgtcategory" class="browser-default custom-select">
                                    <?php echo $cate_data; ?>
                                </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">メモ</span>
                            <input type="text" id="utgtitem" name="tgtitem" class="form-control">
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">熱量</span>
                            <input type="text" id="utgtcalorie" name="tgtcalorie" class="form-control">
                        </div>

                        <div class="modal-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- 新規作成モーダルダイアログ -->
<div class="modal fade" id="store_physical_info" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">運動量・体重</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{route('calorie.store_physical_info')}}" class="form-inline" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group mb-1">
                            <span class="col-2">日付</span>
                            <input type="text" class="datepicker datepicker-dropdown" id="tgtdate" name="tgtdate">
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">種類</span>
                                <select name="tgtcategory" id="tgtcategory" class="browser-default custom-select">
                                    <?php echo $physical_cate_data; ?>
                                </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">メモ</span>
                            <input type="text" id="utgtitem" name="tgtitem" class="form-control">
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">数値</span>
                            <input type="text" id="utgtcalorie" name="tgtcalorie" class="form-control">
                        </div>

                        <div class="modal-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- 編集モーダルダイアログ -->
<div class="modal fade" id="dataUpdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" autocomplete="off">
<div class="modal-dialog" role="document">
<div class="modal-content">
  <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">編集</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <form method="post" action="{{route('calorie.update')}}" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="mb-1">
          <span>日付</span>
          <input type="text" id="tgtdate" name="tgtdate">
        </div>
        <div class="form-group mb-1">
            <span class="col-2">時間帯</span>
                <select name="tgttimezone" id="utgttimezone" class="browser-default custom-select">
                <option value="0">朝</option>
                <option value="1">昼</option>
                <option value="2">夜</option>
            </select>
        </div>
        <div class="form-group mb-1">
            <span class="col-2">種類</span>
                <select name="tgtcategory" id="tgtcategory" class="browser-default custom-select">
                    <?php echo $cate_data; ?>
                </select>
        </div>
        <div class="mb-1">
          <span>項目</span>
          <input type="text" id="tgtitem" name="tgtitem">
        </div>
        <div class="mb-1">
          <span>値段</span>
          <input type="text" id="tgtcalorie" name="tgtcalorie">
        </div>
      </div>
      <input type="hidden" name="updateId" id="updateId" value="">
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
      <button type="submit" class="btn btn-primary">保存</button>
    </div>
</form>
</div>
</div>
</div>
<!-- 削除モーダルダイアログ -->
<div class="modal fade" id="dataDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" autocomplete="off">
<div class="modal-dialog" role="document">
<div class="modal-content">
  <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">削除</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <form method="post" action="{{route('calorie.destroy')}}" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="modal-body">
            <div class="mb-1">
              <span>日付</span>
              <input type="text" id="tgtdate" name="tgtdate" disabled="readonly">
            </div>
            <div class="form-group mb-1">
                <span class="col-2">種類</span>
                    <select name="tgttimezone" id="utgttimezone" class="browser-default custom-select">
                    <option value="0">朝</option>
                    <option value="1">昼</option>
                    <option value="2">夜</option>
                </select>
            </div>
            <div class="form-group mb-1">
                <span class="col-3">カテゴリ</span>
                    <select name="tgtcategory" id="tgtcategory" class="browser-default custom-select">
                        <?php echo $cate_data; ?>
                    </select>
            </div>
            <div class="mb-1">
              <span>項目</span>
              <input type="text" id="tgtitem" name="tgtitem" disabled="readonly">
            </div>
            <div class="mb-1">
              <span>値段</span>
              <input type="text" id="tgtcalorie" name="tgtcalorie" disabled="readonly">
            </div>
        </div>
    </div>
    <input type="hidden" name="deleteId" id="deleteId" value="">
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
      <button type="submit" class="btn btn-danger">削除</button>
    </div>
</form>
</div>
</div>
</div>
@endsection
