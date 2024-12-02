<?php
use Illuminate\Support\Facades\DB;
use App\Models\Calorie;

$categories = DB::table('categories')
             ->select('cateid','catename')
             ->orderBy('cateid','asc')
             ->get();

$cate_data="<option value='0'>-----</option>";
//戻りがオブジェクト型
foreach($categories as $val){
    $cate_data .= "<option value='". $val->cateid;
    $cate_data .= "'>". $val->catename. "</option>";
}
?>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>caloriewars</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('/favicon.png') }}">

    {{-- 追加する --}}
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/bootstrap.min.js') }}" defer></script>
    <script src="{{ asset('js/main.js') }}" defer></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.4.1/chart.js"></script>
<script type="text/javascript">
  $(function(){
    $('#searchcategory').change(function(){
        var val = $(this).val();
        $('#hiddeCate').val(val);
    });
    $('.datepicker.datepicker-dropdown').datepicker({
        language:'ja', // 日本語化
        format: 'yyyy/mm/dd', // 日付表示をyyyy/mm/ddにフォーマット
    });
  });
  </script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        html {
            position: relative;
            min-height: 100%;
        }
        body {
            /* Margin bottom by footer height */
            margin-bottom: 60px;
        }
        .container{
          width: 100%;
        }
        .datepicker {
          /* z-index: 9999 !important; */
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            /* Set the fixed height of the footer here */
            height: 60px;
            text-align:center;
            background-color: #f5f5f5;
        }

        .footer > .container {
            width: auto;
            max-width: 680px;
            padding: 0 15px;
        }
        .footer >.container .text-muted {
            margin: 20px 0;
        }
      </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <a class="navbar-brand" href="{{route('calorie')}}">一覧画面</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                  <a class="nav-link" href="#" data-toggle="modal" data-target="#dataCreate">摂取熱量<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#store_physical_info">運動量・体重<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item dropdown active" style="width: 115px;">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      MAXチェック
                    </a>
                    <ul class="dropdown-menu">
                      <li><a class="nav-link" href="#" data-toggle="modal" data-target="#openMaxCalorieModal"><font color="#000">カロリー最大値</font></a></li>
                      <li><a class="nav-link" href="#" data-toggle="modal" data-target="#openMaxStepsModal"><font color="#000">歩数最大値</font></a></li>
                      <li><a class="nav-link" href="#" data-toggle="modal" data-target="#openMaxDistanceModal"><font color="#000">歩行距離最大値</font></a></li> 
                    </ul>
                </li>
                <li class="nav-item dropdown active" style="width:190px;">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      グラフ種類
                    </a>
                    <ul class="dropdown-menu">
                      <li><a style="color:black" class="nav-link" href="{{route('calorie.makegraph')}}" >摂取カロリーと確定体重</a></li>
                      <li><a style="color:black" class="nav-link" href="{{route('calorie.makegraph2')}}" >歩数と歩行距離</a></li>
                      <li><a style="color:black" class="nav-link" href="{{route('calorie.makegraph3')}}" >歩数と歩行時間</a></li>
                      <li><a style="color:black" class="nav-link" href="{{route('calorie.makegraph4')}}" >歩数と確定体重</a></li>
                    </ul>
                </li>
                <!-- <li class="nav-item active">
                    <a class="nav-link" href="{{route('calorie.chartgraph')}}" >チャートグラフ画面</a>
                </li> -->
              </ul>
              <!-- <form method="get" action="{{route('calorie')}}" class="form-inline my-2 my-lg-0" autocomplete="off">
                {{ csrf_field() }}
                <span class="mx-1" style="color:#fff;">開始日付</span>
                <input type="text" class="input-sm form-control datepicker mr-sm-2" id="from" name="from" value="{{old('from')}}">
                <span class="mx-1" style="color:#fff;">終了日付</span>
                <input type="text" class="input-sm form-control datepicker mr-sm-2" id="to" name="to" value="{{old('to')}}">
                <select id="searchcategory" name="searchcategory" class="browser-default custom-select mr-2 mt-1 mb-1">
                    <?php echo $cate_data; ?>
                </select>
                <!-- <input type="checkbox" class="form-check-input" id="overcalorie" name="overcalorie" value="1"> -->
                <!-- <label class="form-check-label" for="overcalorie" style="color:#fff;">摂取過多</label> -->
                <!-- <input class="form-control mr-sm-2" type="search" placeholder="検索" id="searchword" name="searchword" value="{{old('searchword')}}" aria-label="検索">
                <input type="hidden" value="" id="hiddenCate" name="hiddenCate">
                <button class="btn btn-info my-2 my-sm-0" style="color:#fff;" type="submit">検索</button> -->
              <!-- </form> -->
            </div>
        </nav>
        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <footer class="footer">
        <div class="container">
          <p class="text-muted">Place sticky footer content here.</p>
        </div>
    </footer>
</body>
</html>
