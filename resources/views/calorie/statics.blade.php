@extends('layouts.app')
@section('content')

@if(session('message'))
<div id="alert" class="alert alert-success">{{session('message')}}</div>
@endif
<?php
    $labels = json_encode($labels);
    $weeksum = json_encode($weeksum);

    $cate_data="";
    //戻りがオブジェクト型
    foreach($categories as $val){
        $cate_data .= "<option value='". $val->cateid;
        $cate_data .= "'>". $val->catename. "</option>";
    }
?>

<div class="mx-auto col-12" style="text-align:center;">
    <div><h3>食べすぎやろ</h3></div>
</div>
<div class="mx-auto col-12 d-flex flex-row justify-content-center">
    <div class="mr-2"><h4><small>目標:<strong>1600kcal</strong></small></h4></div>
  </div>
<div class="mx-auto col-12" style="text-align:center;">
    <div class="container">
        <div class="row">
            <div class="container" style="text-align:center;">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<!-- bootstrap-datepicker -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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
  });
  $('.datepicker').datepicker({
    // オプションを設定
    language:'ja', // 日本語化
    format: 'yyyy/mm/dd', // 日付表示をyyyy/mm/ddにフォーマット
  });

    let lineCtx = document.getElementById("lineChart");
    // 線グラフの設定
    let lineConfig = {
        type: 'line',
        data: {
        // ※labelとデータの関係は得にありません
        labels: <?php echo $labels; ?>,
        datasets: [{
            label: 'カロリー週計',
            data: <?php echo $weeksum; ?>,
            borderColor: '#f88',
        }],
        },
        options: {
        scales: {
            // Y軸の最大値・最小値、目盛りの範囲などを設定する
            y: {
            suggestedMin: 5000,
            suggestedMax: 16000,
            ticks: {
                stepSize: 100,
            }
            }
        },
        },
    };
    let lineChart = new Chart(lineCtx, lineConfig);


</script>
</body>
</html>
<!-- 新規作成モーダルダイアログ -->
<div class="modal fade" id="dataCreate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">新規作成</h5>
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
                            <span class="col-2">時間</span>
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
                            <span class="col-2">項目</span>
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
        <div class="mb-1">
          <span>時間</span>
          <select name="tgttimezone" id="tgttimezone" style="text-align:-webkit-center;width: 15%;">
            <option value="0">朝</option>
            <option value="1">昼</option>
            <option value="2">夜</option>
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
            <div class="mb-1">
              <span>時間</span>
              <select name="tgttimezone" id="tgttimezone" style="text-align:-webkit-center;width: 15%;">
                <option value="0">朝</option>
                <option value="1">昼</option>
                <option value="2">夜</option>
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
