@extends('layouts.app')
@section('content')

@if(session('message'))
<div id="alert" class="alert alert-success">{{session('message')}}</div>
@endif

<?php
if(isset($from) && !empty($from)){
    $rcvfrom = "'".date('Y/m/d',strtotime($from))."'";
}else{
    $rcvfrom ="";
}
if(isset($to) && !empty($to)){
    $rcvto =  "'".date('Y/m/d',strtotime($to))."'";
}else{
    $rcvto ="";
}
if(isset($searchword) && !empty($searchword)){
    $rcvsearchword = $searchword;
}else{
    $rcvsearchword ="";
}

$cate_data="";
//戻りがオブジェクト型
foreach($categories as $val){
    $cate_data .= "<option value='". $val->cateid;
    $cate_data .= "'>". $val->catename. "</option>";
}
?>

<div class="mx-auto col-12" style="text-align:center;">
    <div><h3>食べすぎやろ</h3></div>
    <div><h4><?php echo $tgtdate; ?></h4></div>
    <div class="d-flex flex-row bd-highlight justify-content-center">
        <div class="p-2 bd-highlight">実熱量合計:<strong><?php echo $totalcaloriesum; ?></strong></div>
        <div class="p-2 bd-highlight">実運動量合計:<strong><?php echo $totalconsumptionsum; ?></strong></div>
        <?php if((intval($totalcaloriesum)-(intval($totalconsumptionsum)))>1600): ?>
            <div class="p-2 bd-highlight">計算済熱量合計:<strong style="color:red"><?php echo intval($totalcaloriesum)-(intval($totalconsumptionsum)); ?></strong></div>
        <?php else : ?>
            <div class="p-2 bd-highlight">計算済熱量合計:<strong><?php echo intval($totalcaloriesum)-(intval($totalconsumptionsum)); ?></strong></div>
        <?php endif; ?>
      </div>
  </div>
  <div class="mx-auto col-12" style="text-align:center;">

  <div class="table-responsive-sm text-nowrap">
      <table class="table table-striped">
          <thead>
              <tr>
                  <th class="text-center">#</th>
                  <th class="text-center">日付</th>
                  <th class="text-center">時間帯</th>
                  <th class="text-center">カテゴリ</th>
                  <th class="text-center">項目</th>
                  <th class="text-center">熱量</th>
                  <th class="text-center">編集</th>
                  <th class="text-center">削除</th>
              </tr>
          </thead>
          <tbody>
              <?php
              if(isset($results) && !empty($results)){
                foreach($results as $result){
                    echo '<tr>';
                    echo '<td>' . $result->id.'</td>';
                    echo '<td>' . date('Y-m-d',strtotime($result->tgtdate)).'</td>';
                    if($result->tgttimezone==0){
                        echo '<td class="text-center">朝</td>';
                    }else if($result->tgttimezone==1){
                        echo '<td class="text-center">昼</td>';
                    }else if($result->tgttimezone==2){
                        echo '<td class="text-center">夜</td>';
                    }
                    echo '<td>' . $result->catename.'</td>';
                    echo '<td class="text-center">'.$result->tgtitem.'</td>';
                    echo '<td class="text-center">'.$result->tgtcalorie.'</td>';
                    echo '<td class="text-center">';
                    echo '<button type="button" class="btn btn-primary" data-toggle="modal" onclick="row_updatedata(this)" data-id="'.$result->id.'"data-target="#dataUpdate" data-tgtdate="'.date('Y-m-d',strtotime($result->tgtdate)).'" data-tgttimezone="'.$result->tgttimezone .'" data-tgtitem="'.$result->tgtitem.'" data-tgtcalorie="'.$result->tgtcalorie.'" data-tgtcategory="'.$result->cateid.'">';
                    echo '編集';
                    echo '</button>';
                    echo '</td>';
                    echo '<td class="text-center">';
                    echo '<button type="button" class="btn btn-danger" data-toggle="modal" onclick="row_deletedata(this)" data-id="'.$result->id.'"data-target="#dataDelete" data-tgtdate="'.date('Y-m-d',strtotime($result->tgtdate)).'" data-tgttimezone="'.$result->tgttimezone .'" data-tgtitem="'.$result->tgtitem.'" data-tgtcalorie="'.$result->tgtcalorie.'" data-tgtcategory="'.$result->cateid.'">';
                    echo '削除';
                    echo '</button>';
                    echo '</td>';
                    echo '</tr>';
                }
              }else{
                echo "データなし";
              }

              ?>
          </tbody>
      </table>
    <div class="d-flex justify-content-center mt-5">
        {!! $results->links() !!}
    </div>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<!-- bootstrap-datepicker -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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

    $('#from').val(<?php echo $rcvfrom; ?>);
    $('#to').val(<?php echo $rcvto; ?>);
    $('#searchword').val(<?php echo $rcvsearchword; ?>);

    $('.datepicker.datepicker-dropdown').datepicker({
        language:'ja', // 日本語化
        format: 'yyyy/mm/dd', // 日付表示をyyyy/mm/ddにフォーマット
    });

  });
  function row_updatedata(data) {
    let id = data.dataset.id;
    let tgtdate =data.dataset.tgtdate;
    let tgttimezone = data.dataset.tgttimezone;
    let tgtcategory = data.dataset.tgtcategory;
    let tgtitem = data.dataset.tgtitem;
    let tgtcalorie = data.dataset.tgtcalorie;
    $('#updateId').val(id);
    var modal = $(this);
    $('#dataUpdate').on('show.bs.modal', function(e) {
      var modal = $(this);
      modal.find('#utgtdate').val(tgtdate);
      modal.find('#utgttimezone').val(tgttimezone);
      modal.find('#utgtcategory').val(tgtcategory);
      modal.find('#utgtitem').val(tgtitem);
      modal.find('#utgtcalorie').val(tgtcalorie);
    });
  }
  function row_deletedata(data) {
    let id = data.dataset.id;
    let tgtdate =data.dataset.tgtdate;
    let tgttimezone = data.dataset.tgttimezone;
    let tgtcategory = data.dataset.tgtcategory;
    let tgtitem = data.dataset.tgtitem;
    let tgtcalorie = data.dataset.tgtcalorie;
    $('#deleteId').val(id);
    $('#dataDelete').on('show.bs.modal', function(e) {
      var modal = $(this);
      modal.find('#dtgtdate').val(tgtdate);
      modal.find('#dtgttimezone').val(tgttimezone);
      modal.find('#dtgtcategory').val(tgtcategory);
      modal.find('#dtgtitem').val(tgtitem);
      modal.find('#dtgtcalorie').val(tgtcalorie);
    });
  }

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
                                <select id="tgtcategory" name="tgtcategory" class="browser-default custom-select">
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
