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

    <style>
        body {
            padding-top: 60px; /* navbarの高さ分のパディングを追加 */
        }
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
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
    <!-- モーダルダイアログ群 -->
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
                                        <?php echo $cate_data ?? ''; ?>
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

                            @isset($frequentFoods)
                                @if (count($frequentFoods))
                                    <div class="w-100 mt-2">
                                        <div class="text-muted small mb-1">よく使う入力（タップで反映）</div>
                                        <div class="d-flex flex-wrap">
                                            @foreach ($frequentFoods as $food)
                                                <button type="button" class="btn btn-outline-primary btn-sm mr-1 mb-1 food-chip"
                                                        data-category="{{ $food->tgtcategory }}"
                                                        data-item="{{ $food->tgtitem }}"
                                                        data-calorie="{{ $food->tgtcalorie }}">
                                                    {{ $food->catename }}@if (trim($food->tgtitem ?? '') !== '')・{{ \Illuminate\Support\Str::limit($food->tgtitem, 10) }}@endif
                                                    {{ $food->tgtcalorie }}kcal
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endisset

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
                                <input type="text" class="datepicker datepicker-dropdown" id="phys_tgtdate" name="tgtdate">
                            </div>
                            <div class="form-group mb-1">
                                <span class="col-2">種類</span>
                                    <select name="tgtcategory" id="phys_tgtcategory" class="browser-default custom-select">
                                        <?php echo $physical_cate_data ?? ''; ?>
                                    </select>
                            </div>
                            <div class="form-group mb-1">
                                <span class="col-2">メモ</span>
                                <input type="text" id="phys_tgtitem" name="tgtitem" class="form-control">
                            </div>
                            <div class="form-group mb-1">
                                <span class="col-2">数値</span>
                                <input type="text" id="phys_tgtcalorie" name="tgtcalorie" class="form-control">
                            </div>

                            @isset($frequentSteppers)
                                @if (count($frequentSteppers))
                                    <div class="w-100 mt-2">
                                        <div class="text-muted small mb-1">よく使う入力（タップで反映）</div>
                                        <div class="d-flex flex-wrap">
                                            @foreach ($frequentSteppers as $stepper)
                                                <button type="button" class="btn btn-outline-success btn-sm mr-1 mb-1 stepper-chip"
                                                        data-value="{{ 0 + $stepper->tgt_physical_data }}">
                                                    ステッパー {{ 0 + $stepper->tgt_physical_data }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endisset

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

    <footer class="footer">
        <div class="container">
            <p class="text-muted">Place sticky footer content here.</p>
        </div>
    </footer>

    <!-- アプリケーション固有のスクリプト -->
    <script src="{{ asset('js/main.js') }}"></script>
    <script type="text/javascript">
        $(function(){
            // datepickerの初期化
            $('.datepicker').datepicker({
                language: 'ja',
                format: 'yyyy/mm/dd',
                autoclose: true
            });

            // モーダルでのdatepickerのz-index調整
            // ※ format を必ず指定する。省略するとデフォルトの mm/dd/yyyy になり、
            //   後続の update() で日付が化ける（例: 10/07/18）ため。
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

            // 今日をdatepickerにセットする。
            // ※ 文字列ではなく Date オブジェクトを渡すこと。文字列を渡すと
            //   datepickerのformat次第で誤パースされ日付が化ける（例: 10/07/18）。
            function setToday($input) {
                if (!$input.val()) {
                    $input.datepicker('update', new Date());
                }
            }

            // 摂取熱量モーダル: 日付を今日、時間帯を現在時刻から自動選択
            $('#dataCreate').on('show.bs.modal', function() {
                setToday($(this).find('input[name="tgtdate"]'));
                const hour = new Date().getHours();
                const timezone = hour < 11 ? '0' : (hour < 17 ? '1' : '2');
                $(this).find('select[name="tgttimezone"]').val(timezone);
            });

            // 運動量・体重モーダル: 日付を今日に
            $('#store_physical_info').on('show.bs.modal', function() {
                setToday($(this).find('input[name="tgtdate"]'));
            });

            // よく使う入力チップ（摂取熱量）
            $(document).on('click', '.food-chip', function() {
                const $modal = $('#dataCreate');
                $modal.find('select[name="tgtcategory"]').val(String($(this).data('category')));
                $modal.find('input[name="tgtitem"]').val($(this).data('item'));
                $modal.find('input[name="tgtcalorie"]').val($(this).data('calorie'));
            });

            // よく使う入力チップ（ステッパー）
            $(document).on('click', '.stepper-chip', function() {
                const $modal = $('#store_physical_info');
                $modal.find('select[name="tgtcategory"]').val('205');
                $modal.find('input[name="tgtcalorie"]').val($(this).data('value'));
            });

            // 日付フォーマット関数
            function formatDate(dateString) {
                const date = new Date(dateString);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // カロリー最大値モーダル
            $('#openMaxCalorieModal').on('show.bs.modal', function() {
                $.ajax({
                    url: '{{ route('calorie.max') }}',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
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

            // 歩数最大値モーダル
            $('#openMaxStepsModal').on('show.bs.modal', function() {
                $.ajax({
                    url: '{{ route('calorie.steps') }}',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
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

            // 歩行距離最大値モーダル
            $('#openMaxDistanceModal').on('show.bs.modal', function() {
                $.ajax({
                    url: '{{ route('calorie.distance') }}',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
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
    </script>
    @yield('scripts')
</body>
</html>
