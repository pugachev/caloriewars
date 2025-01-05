<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>caloriewars</title>

    <!-- CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('/img/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    @yield('styles')

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link" href="#" data-toggle="modal" data-target="#dataCreate">摂取熱量</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="#" data-toggle="modal" data-target="#store_physical_info">運動量・体重</a>
                    </li>
                    <li class="nav-item dropdown active" style="width: 115px;">
                        <a class="nav-link dropdown-toggle" href="#" id="maxCheckDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            MAXチェック
                        </a>
                        <div class="dropdown-menu" aria-labelledby="maxCheckDropdown">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#openMaxCalorieModal">カロリー最大値</a>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#openMaxStepsModal">歩数最大値</a>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#openMaxDistanceModal">歩行距離最大値</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown active" style="width:190px;">
                        <a class="nav-link dropdown-toggle" href="#" id="graphDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            グラフ種類
                        </a>
                        <div class="dropdown-menu" aria-labelledby="graphDropdown">
                            <a class="dropdown-item" href="{{route('calorie.makegraph')}}">摂取カロリーと確定体重</a>
                            <a class="dropdown-item" href="{{route('calorie.makegraph2')}}">歩数と歩行距離</a>
                            <a class="dropdown-item" href="{{route('calorie.makegraph3')}}">歩数と歩行時間</a>
                            <a class="dropdown-item" href="{{route('calorie.makegraph4')}}">歩数と確定体重</a>
                        </div>
                    </li>
                </ul>
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

    <!-- アプリケーション固有のスクリプト -->
    <script src="{{ asset('js/main.js') }}"></script>
    @yield('scripts')
</body>
</html>
