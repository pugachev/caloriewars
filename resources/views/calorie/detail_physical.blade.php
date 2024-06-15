@extends('layouts.app')
@section('content')

@if(session('message'))
<div id="alert" class="alert alert-success">{{session('message')}}</div>
@endif

<?php

$cate_data="";
//戻りがオブジェクト型
foreach($physical_categories as $val){
    $cate_data .= "<option value='". $val->physical_cateid;
    $cate_data .= "'>". $val->physical_catename. "</option>";
}
?>

<div class="mx-auto col-12" style="text-align:center;">
    <div><h3>運動量・体重</h3></div>
    <div><h4><?php echo $tgtdate; ?></h4></div>
    <div class="d-flex flex-row bd-highlight justify-content-center">

    </div>
  </div>
  <div class="mx-auto col-12" style="text-align:center;">

  <div class="table-responsive-sm text-nowrap">
      <table class="table table-striped">
          <thead>
              <tr>
                  <th class="text-center">日付</th>
                  <th class="text-center">カテゴリ名</th>
                  <th class="text-center">データ</th>
                  <th class="text-center">編集</th>
                  <th class="text-center">削除</th>
              </tr>
          </thead>
          <tbody>
              <?php

              if(isset($results) && !empty($results)){
                foreach($results as $result){
                    // dd($result);
                    echo '<tr>';
                    echo '<td>' . date('Y-m-d',strtotime($result->tgt_physical_date)).'</td>';
                    echo '<td>' . $result->physical_catename.'</td>';
                    echo '<td class="text-center">'.$result->tgt_physical_data.'</td>';
                    echo '<td class="text-center">';
                    echo '<button type="button" class="btn btn-primary" data-toggle="modal" onclick="row_updatedata(this)" data-id="'.$result->id.'"data-target="#dataUpdate" data-tgt_physical_date="'.date('Y-m-d',strtotime($result->tgt_physical_date)).'" data-tgt_physical_category="'.$result->tgt_physical_category .'" data-tgt_physical_data="'.$result->tgt_physical_data.'">';
                    echo '編集';
                    echo '</button>';
                    echo '</td>';
                    echo '<td class="text-center">';
                    echo '<button type="button" class="btn btn-danger" data-toggle="modal" onclick="row_deletedata(this)" data-id="'.$result->id.'"data-target="#dataDelete" data-tgt_physical_date="'.date('Y-m-d',strtotime($result->tgt_physical_date)).'" data-tgt_physical_item="'.$result->tgt_physical_item .'" data-tgt_physical_data="'.$result->tgt_physical_data.'">';
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
    {{-- <div class="d-flex justify-content-center mt-5"> --}}
        {{-- {!! $results->links() !!} --}}
    {{-- </div> --}}
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



      $('.datepicker.datepicker-dropdown').datepicker({
          language:'ja', // 日本語化
          format: 'yyyy/mm/dd', // 日付表示をyyyy/mm/ddにフォーマット
      });

    });
    function row_updatedata(data) {
      let id = data.dataset.id;
      let tgt_physical_date =data.dataset.tgt_physical_date;
      let tgt_physical_category = data.dataset.tgt_physical_category;
      let tgt_physical_data = data.dataset.tgt_physical_data;
      $('#updateId').val(id);
      var modal = $(this);
      $('#dataUpdate').on('show.bs.modal', function(e) {
        var modal = $(this);
        modal.find('#utgt_physical_date').val(tgt_physical_date);
        modal.find('#utgt_physical_category').val(tgt_physical_category);
        modal.find('#utgt_physical_data').val(tgt_physical_data);
      });
    }
    function row_deletedata(data) {
      let id = data.dataset.id;
      let tgt_physical_date =data.dataset.tgt_physical_date;
      let tgt_physical_item = data.dataset.tgt_physical_item;
      let tgt_physical_data = data.dataset.tgt_physical_data;
      $('#deleteId').val(id);
      $('#dataDelete').on('show.bs.modal', function(e) {
        var modal = $(this);
        modal.find('#dtgt_physical_date').val(tgt_physical_date);
        modal.find('#dtgt_physical_item').val(tgt_physical_item);
        modal.find('#dtgt_physical_data').val(tgt_physical_data);
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
<!-- 運動量・体重モーダルダイアログ -->
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
                                    <?php echo $cate_data; ?>
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
  <div class="modal fade" id="dataUpdate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">編集</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{route('calorie.updatephysical')}}" class="form-inline" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group mb-1">
                            <span class="col-2">日付</span>
                            <input type="text" class="datepicker datepicker-dropdown" id="utgt_physical_date" name="utgt_physical_date">
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">カテゴリ名</span>
                                <select id="utgt_physical_category" name="utgt_physical_category" class="browser-default custom-select">
                                    <?php echo $cate_data; ?>
                                </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">データ</span>
                            <input type="text" id="utgt_physical_data" name="utgt_physical_data" class="form-control">
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="updateId" name="updateId" value="" />
            </form>
        </div>
    </div>
</div>

<!-- 削除モーダルダイアログ -->
<div class="modal fade" id="dataDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">削除</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{route('calorie.destroyphysical')}}" class="form-inline" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group mb-1">
                            <span class="col-2">日付</span>
                            <input type="text" class="datepicker datepicker-dropdown" id="dtgt_physical_date" name="dtgt_physical_date">
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">カテゴリ名</span>
                                <select id="dtgt_physical_category" name="dtgt_physical_category" class="browser-default custom-select">
                                    <?php echo $cate_data; ?>
                                </select>
                        </div>
                        <div class="form-group mb-1">
                            <span class="col-2">データ</span>
                            <input type="text" id="dtgt_physical_data" name="dtgt_physical_data" class="form-control">
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                            <button type="submit" class="btn btn-danger">削除</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="deleteId" name="deleteId" value="" />
            </form>
        </div>
    </div>
</div>

@endsection
