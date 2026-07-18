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

<!-- 週次バジェット・ストリークのダッシュボード -->
<div class="px-5 mb-3">
    <div class="row">
        <!-- 今週の予算 -->
        <div class="col-md-5 mb-2">
            <div class="card h-100">
                <div class="card-body py-2">
                    <div class="text-muted small">今週の予算（第{{ $currentWeek }}週）</div>
                    <div class="d-flex align-items-baseline">
                        @if ($weekRemaining >= 0)
                            <h3 class="mb-1 text-success">残り {{ number_format($weekRemaining) }}</h3>
                        @else
                            <h3 class="mb-1 text-danger">{{ number_format(abs($weekRemaining)) }} 超過</h3>
                        @endif
                        <span class="ml-1">kcal</span>
                    </div>
                    @php
                        $usedPercent = $weekBudget > 0 ? min(100, round($weekConsumed / $weekBudget * 100)) : 0;
                    @endphp
                    <div class="progress mb-1" style="height: 10px;">
                        <div class="progress-bar {{ $weekRemaining >= 0 ? 'bg-success' : 'bg-danger' }}"
                             role="progressbar" style="width: {{ $usedPercent }}%;"
                             aria-valuenow="{{ $usedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="text-muted small">
                        摂取 {{ number_format($weekConsumed) }} / 予算 {{ number_format($weekBudget) }} kcal
                        （基礎代謝1450×7日 + 運動 {{ number_format($weekExercise) }}）
                    </div>
                </div>
            </div>
        </div>
        <!-- ストリーク -->
        <div class="col-md-4 mb-2">
            <div class="card h-100">
                <div class="card-body py-2">
                    <div class="text-muted small">連続達成ストリーク</div>
                    <div class="d-flex align-items-baseline">
                        <h3 class="mb-1">🔥 {{ $currentStreak }}</h3><span class="ml-1">日連続</span>
                    </div>
                    <div class="text-muted small">自己ベスト: {{ $bestStreak }}日</div>
                </div>
            </div>
        </div>
        <!-- 今週の達成日数 -->
        <div class="col-md-3 mb-2">
            <div class="card h-100">
                <div class="card-body py-2">
                    <div class="text-muted small">今週の達成</div>
                    <div class="d-flex align-items-baseline">
                        <h3 class="mb-1">{{ $weekAchieved }} / {{ $weekElapsedDays }}</h3><span class="ml-1">日</span>
                    </div>
                    <div class="text-muted small">達成 = 確定摂取熱量が0以下</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- タブ全体をpx-5で囲む -->
<div class="px-5">
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
</div>

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

            <!-- 達成度ヒートマップ（草カレンダー） -->
            <div class="px-5 mt-3">
                @include('calorie.partials.heatmap', [
                    'year' => $year,
                    'data' => $heatmapData[$year] ?? [],
                ])
            </div>

            <div class="table-responsive px-5">
                <table class="table table-hover table-sm table-bordered" style="border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th class="text-center d-none d-md-table-cell">週番号</th>
                            <th class="text-center">日付</th>
                            <th class="text-center d-none d-lg-table-cell">摂取熱量合計</th>
                            <th class="text-center">歩行時間</th>
                            <th class="text-center">歩数</th>
                            <th class="text-center">歩行距離</th>
                            <th class="text-center d-none d-lg-table-cell">確定体重</th>
                            <th class="text-center">ステッパー</th>
                            <th class="text-center d-none d-lg-table-cell">確定運動量</th>
                            <th class="text-center d-none d-lg-table-cell">確定摂取熱量</th>
                            <th class="text-center">熱量詳細</th>
                            <th class="text-center">運動詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paginatedItems as $result)
                            <tr>
                                <td class="text-center d-none d-md-table-cell">{{ $result->weeknum ?? 0 }}</td>
                                <td class="text-left">
                                    {{ date('Y-m-d', strtotime($result->tgtdate)) }}
                                    <span style="color: {{ ($result->weekday ?? '') == '日' ? 'red' : (($result->weekday ?? '') == '土' ? 'blue' : 'inherit') }};">
                                        ({{ $result->weekday ?? '' }})
                                    </span>
                                    @if(isset($result->has_tgtcategory_104) && $result->has_tgtcategory_104)
                                        <img src="{{ asset('img/beer.png') }}" alt="アルコール" style="width: 20px; height: 20px; margin-left: 5px;">
                                    @endif
                                </td>
                                <td class="text-center d-none d-lg-table-cell">{{ $result->sumcolorie ?? 0 }}</td>
                                <td class="text-center">{{ $result->walking_time ?? 0 }}</td>
                                <td class="text-center">{{ $result->walking_steps ?? 0 }}</td>
                                <td class="text-center">{{ $result->walking_distance ?? 0 }}</td>
                                <td class="text-center d-none d-lg-table-cell">{{ $result->confirmed_weight ?? 0 }}</td>
                                <td class="text-center">{{ $result->stepper_count ?? 0 }}</td>
                                <td class="text-center d-none d-lg-table-cell">{{ $result->confirmed_physical_calorie ?? 0 }}</td>
                                <td class="text-center d-none d-lg-table-cell">{{ $result->confirmed_calorie ?? 0 }}</td>
                                <td class="text-center">
                                    <a class="btn btn-primary btn-sm" href="{{ url('/calorie/show/'.$result->tgtdate) }}">
                                        熱量詳細
                                    </a>
                                </td>
                                <td class="text-center">
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
          language: 'ja',
          format: 'yyyy/mm/dd',
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

      /* 達成度ヒートマップ（草カレンダー） */
      .heatmap-scroll {
          overflow-x: auto;
          padding-bottom: 4px;
      }
      .heatmap-months {
          position: relative;
          height: 16px;
          font-size: 11px;
          color: #6c757d;
      }
      .heatmap-months span {
          position: absolute;
          top: 0;
          white-space: nowrap;
      }
      .heatmap-grid {
          display: grid;
          grid-auto-flow: column;
          grid-template-rows: repeat(7, 12px);
          grid-auto-columns: 12px;
          gap: 3px;
          width: max-content;
      }
      .hm-cell {
          width: 12px;
          height: 12px;
          border-radius: 2px;
      }
      .hm-empty { background: transparent; }
      .hm-none  { background: #ebedf0; }
      .hm-g1    { background: #9be9a8; }
      .hm-g2    { background: #40c463; }
      .hm-g3    { background: #216e39; }
      .hm-r1    { background: #ffb3b3; }
      .hm-r2    { background: #e05252; }
      .heatmap-legend {
          display: flex;
          align-items: center;
          gap: 3px;
          margin-top: 6px;
          font-size: 11px;
      }
  </style>
@endsection
