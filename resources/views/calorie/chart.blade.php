@extends('layouts.app')
@section('content')

@if(session('message'))
<div id="alert" class="alert alert-success">{{session('message')}}</div>
@endif
<?php
$tmpcategoris = $categories;
$categories = json_encode($tmpcategoris,JSON_UNESCAPED_UNICODE);

$tmpsumcalorie = $sumcalorie;
$sumcalorie = json_encode($tmpsumcalorie,JSON_UNESCAPED_UNICODE);

$cate_data="";
//戻りがオブジェクト型
foreach($categorieslist as $val){
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
<div class="mx-auto col-12 d-flex flex-row justify-content-center  mb-3">
    <form method="get" action="{{route('calorie.chartgraph')}}" class="form-inline my-2 my-lg-0" autocomplete="off">
        {{ csrf_field() }}
        <span class="mx-1" style="color:#000;">開始日付</span>
        <input type="text" class="input-sm form-control datepicker mr-sm-2" id="from" name="from" value="<?php echo $from; ?>">
        <span class="mx-1" style="color:#000;">終了日付</span>
        <input type="text" class="input-sm form-control datepicker mr-sm-2" id="to" name="to" value="<?php echo $to; ?>">
        <button class="btn btn-info my-2 my-sm-0" style="color:#fff;" type="submit">表示</button>
    </form>
</div>
<div class="mx-auto col-12" style="text-align:center;">
    <div class="container">
        <div style="width:800px;margin:0 auto;">
            <canvas id="mychart"></canvas>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@next/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<!-- bootstrap-datepicker -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
</style>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
<script type="text/javascript">
  $(function(){
    let ctx = $("#mychart");
    var myPieChart = new Chart(ctx, {
    type: 'pie',
        data: {
            labels: <?php echo $categories; ?>,
            datasets: [{
                backgroundColor: [
                    "#BB5179",
                    "#FAFF67",
                    "#FF1493",
                    "#3C00FF",
                    "#FF0000",
                    "#FF4500",
                    "#228B22",
                    "#BDB76B",
                    "#EE82EE",
                    "#FF00FF",
                    "#9370DB",
                    "#ADFF2F",
                    "#00FFFF"
                ],
                data: <?php echo $sumcalorie; ?>
            }]
        },
        plugins: [ChartDataLabels],
        options: {
            title: {
                display: true,
                text: 'カロリー摂取 割合'
            },
            datalabels: {
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 30,
                },
                formatter: (value, ctx) => {
                     let label = ctx.chart.data.labels[ctx.dataIndex];
                    return label + '\n' + value + '%';
                },
            },

        }
    });

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
                            <span class="col-2">時間帯</span>
                                <select id="utgttimezone" name="tgttimezone" class="browser-default custom-select">
                                <option value="0">朝</option>
                                <option value="1">昼</option>
                                <option value="2">夜</option>
                            </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">種類</span>
                                <select id="utgtcategory" name="tgtcategory" class="browser-default custom-select">
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
<div class="modal fade" id="dataUpdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">編集</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{route('calorie.update')}}" class="form-inline" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group mb-1">
                            <span class="col-2">日付</span>
                            <input type="text" class="datepicker datepicker-dropdown" id="utgtdate" name="tgtdate">
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">時間</span>
                                <select id="utgttimezone" name="tgttimezone" class="browser-default custom-select">
                                <option value="0">朝</option>
                                <option value="1">昼</option>
                                <option value="2">夜</option>
                            </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">種類</span>
                                <select id="utgtcategory" name="tgtcategory" class="browser-default custom-select">
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

                        <input type="hidden" name="updateId" id="updateId" value="">
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
<!-- 削除モーダルダイアログ -->
<div class="modal fade" id="dataDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" autocomplete="off">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">削除</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{route('calorie.destroy')}}" class="form-inline" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group mb-0">
                            <div class="col-2">日付</div>
                            <input type="text" id="dtgtdate" name="tgtdate"  class="form-control" disabled="readonly">
                        </div>
                        <div class="form-group mb-0">
                            <div class="col-2">時間</div>
                            <select name="tgttimezone" id="dtgttimezone" class="browser-default custom-select" disabled="readonly">
                                <option value="0">朝</option>
                                <option value="1">昼</option>
                                <option value="2">夜</option>
                            </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">種類</span>
                                <select id="dtgtcategory" name="tgtcategory" class="browser-default custom-select" disabled="readonly">
                                    <?php echo $cate_data; ?>
                                </select>
                        </div>
                        <div class="form-group mb-0">
                            <div class="col-2">項目</div>
                            <input type="text" id="dtgtitem" name="tgtitem" class="form-control" disabled="readonly">
                        </div>
                        <div class="form-group mb-0">
                            <div class="col-2">熱量</div>
                            <input type="text" id="dtgtcalorie" name="tgtcalorie" class="form-control" disabled="readonly">
                        </div>

                        <input type="hidden" name="deleteId" id="deleteId" value="">
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                            <button type="submit" class="btn btn-danger">削除</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
