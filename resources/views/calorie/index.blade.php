@extends('layouts.app')

@section('content')
<?php
// 年度データの準備
$years = [];
$currentYear = date('Y');
for ($i = $currentYear; $i >= 2023; $i--) {
    $years[] = $i;
}

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
    <div><h4><small>目標:<strong>1450kcal</strong></small></h4></div>
</div>

<!-- タブナビゲーション -->
<ul class="nav nav-tabs" id="yearTabs" role="tablist">
    @foreach ($yearlyData as $year => $items)
        <li class="nav-item">
            <a class="nav-link {{
                    (request()->has('page_'.$year) ||
                    (!request()->hasAny(array_map(function($y) { return 'page_'.$y; }, array_keys($yearlyData))) && $year == date('Y')))
                    ? 'active' : ''
                }}"
               id="y{{ $year }}-tab"
               data-toggle="tab"
               href="#y{{ $year }}"
               role="tab"
               aria-controls="y{{ $year }}"
               aria-selected="{{
                    (request()->has('page_'.$year) ||
                    (!request()->hasAny(array_map(function($y) { return 'page_'.$y; }, array_keys($yearlyData))) && $year == date('Y')))
                    ? 'true' : 'false'
                }}">
                {{ $year }}年
            </a>
        </li>
    @endforeach
</ul>

<!-- タブコンテンツ -->
<div class="tab-content" id="yearTabContent">
    @foreach ($yearlyData as $year => $paginatedItems)
        <div class="tab-pane fade {{
                (request()->has('page_'.$year) ||
                (!request()->hasAny(array_map(function($y) { return 'page_'.$y; }, array_keys($yearlyData))) && $year == date('Y')))
                ? 'show active' : ''
            }}"
             id="y{{ $year }}"
             role="tabpanel"
             aria-labelledby="y{{ $year }}-tab">

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th style="width: 8%; text-align: center;">週番号</th>
                            <th style="width: 15%; text-align: center;">日付</th>
                            <th style="width: 10%; text-align: center;">摂取熱量合計</th>
                            <th style="width: 8%; text-align: center;">歩行時間</th>
                            <th style="width: 8%; text-align: center;">歩数</th>
                            <th style="width: 8%; text-align: center;">歩行距離</th>
                            <th style="width: 8%; text-align: center;">確定体重</th>
                            <th style="width: 8%; text-align: center;">確定熱量</th>
                            <th style="width: 12%; text-align: center;">熱量詳細</th>
                            <th style="width: 12%; text-align: center;">運動詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paginatedItems as $result)
                            <tr>
                                <td style="text-align: center;">{{ $result->weeknum }}</td>
                                <td style="text-align: center;">
                                    {{ date('Y-m-d', strtotime($result->tgtdate)) }}
                                    <span style="color: {{ $result->weekday == '日' ? 'red' : ($result->weekday == '土' ? 'blue' : 'inherit') }};">
                                        ({{ $result->weekday }})
                                    </span>
                                    @if($result->has_tgtcategory_104)
                                        <img src="{{ asset('img/beer.png') }}" alt="アルコール" style="width: 20px; height: 20px; margin-left: 5px;">
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $result->sumcolorie }}</td>
                                <td style="text-align: center;">{{ $result->walking_time }}</td>
                                <td style="text-align: center;">{{ $result->walking_steps }}</td>
                                <td style="text-align: center;">{{ $result->walking_distance }}</td>
                                <td style="text-align: center;">{{ $result->confirmed_weight }}</td>
                                <td style="text-align: center;">{{ $result->confirmed_calorie }}</td>
                                <td style="text-align: center;">
                                    <a class="btn btn-primary btn-sm" href="{{ url('/calorie/show/'.$result->tgtdate) }}">
                                        熱量詳細
                                    </a>
                                </td>
                                <td style="text-align: center;">
                                    <a class="btn btn-success btn-sm" href="{{ url('/calorie/showphysical/'.$result->tgtdate) }}">
                                        運動詳細
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $paginatedItems->appends(['year' => $year])->links() }}
            </div>
        </div>
    @endforeach
</div>
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
      function formatDate(dateString) {
                  const date = new Date(dateString);
                  const year = date.getFullYear();
                  const month = String(date.getMonth() + 1).padStart(2, '0');
                  const day = String(date.getDate()).padStart(2, '0');
                  return `${year}-${month}-${day}`;
      }
      $('#openMaxCalorieModal').on('show.bs.modal', function() {
          $.ajax({
              url: '{{ route('calorie.max') }}', // 相対パスを使用
              method: 'GET',
              dataType: 'json',
              success: function(data) {
                  console.log('Data received:', data); // デバッグ用にレスポンスをログに出力
                  if (!Array.isArray(data)) {
                      console.error('Expected an array but got something else');
                      return;
                  }
                  const list = $('#maxCalorieList');
                  list.empty();
                  data.forEach(item => {
                      const formattedDate = formatDate(item['tgtdate']);
                      const row = $(`
                          <tr>
                              <td>${formattedDate}</td>
                              <td>${item['maxcalorie']}</td>
                          </tr>
                      `);
                      list.append(row);
                  });
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  console.error('There was a problem with the ajax operation:', textStatus, errorThrown);
              }
          });
      });

      $('#openMaxStepsModal').on('show.bs.modal', function() {
          $.ajax({
              url: '{{ route('calorie.steps') }}', // 相対パスを使用
              method: 'GET',
              dataType: 'json',
              success: function(data) {
                  console.log('Data received:', data); // デバッグ用にレスポンスをログに出力
                  if (!Array.isArray(data)) {
                      console.error('Expected an array but got something else');
                      return;
                  }
                  const list = $('#maxStepsList');
                  list.empty();
                  data.forEach(item => {
                      const formattedDate = formatDate(item['tgt_physical_date']);
                      const row = $(`
                          <tr>
                              <td>${formattedDate}</td>
                              <td>${item['maxsteps']}</td>
                          </tr>
                      `);
                      list.append(row);
                  });
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  console.error('There was a problem with the ajax operation:', textStatus, errorThrown);
              }
          });
      });

      $('#openMaxDistanceModal').on('show.bs.modal', function() {
          $.ajax({
              url: '{{ route('calorie.distance') }}', // 相対パスを使用
              method: 'GET',
              dataType: 'json',
              success: function(data) {
                  console.log('Data received:', data); // デバッグ用にレスポンスをログに出力
                  if (!Array.isArray(data)) {
                      console.error('Expected an array but got something else');
                      return;
                  }
                  const list = $('#maxDistanceList');
                  list.empty();
                  data.forEach(item => {
                      const formattedDate = formatDate(item['tgt_physical_date']);
                      const row = $(`
                          <tr>
                              <td>${formattedDate}</td>
                              <td>${item['maxdistance']}</td>
                          </tr>
                      `);
                      list.append(row);
                  });
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  console.error('There was a problem with the ajax operation:', textStatus, errorThrown);
              }
          });
      });

    });
    $('.datepicker').datepicker({
      // オプションを設定
      language:'ja', // 日本語化
      format: 'yyyy/mm/dd', // 日付表示をyyyy/mm/ddにフォーマット
    });
  </script>
  <style>
      .no-bullets {
          list-style-type: none;
          padding-left: 0; /* インデントを削除する場合 */
      }
  </style>
<!-- 摂取熱量モーダルダイアログ -->
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
<!-- カロリー最大値モーダルダイアログ -->
<div class="modal fade" id="openMaxCalorieModal" tabindex="-1" role="dialog" aria-labelledby="maxCalorieModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maxCalorieModalLabel">最大カロリー</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h1 class="mb-4">カロリー一覧</h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">日付</th>
                            <th scope="col">カロリー</th>
                        </tr>
                    </thead>
                    <tbody id="maxCalorieList"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
<!-- 歩数最大値モーダルダイアログ -->
<div class="modal fade" id="openMaxStepsModal" tabindex="-1" role="dialog" aria-labelledby="maxStespModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maxStepsModalLabel">最大歩数</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h1 class="mb-4">歩数一覧</h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">日付</th>
                            <th scope="col">歩数</th>
                        </tr>
                    </thead>
                    <tbody id="maxStepsList"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
<!-- 歩行距離最大値モーダルダイアログ -->
<div class="modal fade" id="openMaxDistanceModal" tabindex="-1" role="dialog" aria-labelledby="maxDistanceModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maxDistanceModalLabel">最大歩行距離</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h1 class="mb-4">歩行距離一覧</h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">日付</th>
                            <th scope="col">歩行距離</th>
                        </tr>
                    </thead>
                    <tbody id="maxDistanceList"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endsection
