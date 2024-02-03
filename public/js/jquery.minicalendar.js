/*
 * jQuery Mini Calendar
 * https://github.com/k-ishiwata/jQuery.MiniCalendar
 *
 * Copyright 2016, k.ishiwata
 * http://www.webopixel.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

;(function($) {
    $.wop = $.wop || {};
    $.wop.miniCalendar = function(targets,option){
      this.opts = $.extend({},$.wop.miniCalendar.defaults,option);
      this.ele = targets;

      // jsonファイルから読み込んだデータを入れる変数
      this.events = {};
      this.date = new Date();
      this.month = "";
      this.year = "";
      this.holiday = "";

      //表示する年月
      if(Object.keys(option).length !== 0){
          this.year = option.split('/')[0];
          this.month  = option.split('/')[1];
      }else{
          this.year = this.year || new Date().getFullYear();
        //月　後ろから２桁の文字取得することで、1月は01、12月は12月になるようする。
          this.month = this.month || ("0"+(this.date.getMonth() + 1)).slice(-2);
          this.createFrame();
      }

      // jsonファイルから読み込む
      this.loadData(this.year, this.month);

      this.printType(this.year, this.month);

      // 取得したイベントを表示
      this.setEvent();
    };

    $.wop.miniCalendar.prototype = {
      /**
       * 枠を作成
       */
      createFrame : function() {
        this.ele.append('<div class="calendar-head" id="yearmonth"><p class="calendar-year-month"></p></div>');
        $('#yearmonth').append('<h4><p id="tgtamount" style="text-align:center;"></p></h4>');
        $('#yearmonth').append('<h4><p id="monthlyresult" style="text-align:center;"></p></h4>');
        $('#yearmonth').append('<div class="button-container-calendar"><button type="button" id="previous" onclick="previous()">前月</button><button type="button" id="next" onclick="next()">翌月</button></div>');

        var outText = '<table><thead><tr>';
        for (var i = 0; i < this.opts.weekType.length; i++) {
          if (i === 0) {
            outText += '<th class="calendar-sun">';
          } else if (i === this.opts.weekType.length-1) {
            outText += '<th class="calendar-sat">';
          } else {
            outText += '<th>';
          }

          outText += this.opts.weekType[i] +'</th>';
        }
        outText += '</thead><tbody></tbody></table>';
        this.ele.find('.calendar-head').after(outText);
      },

      /**
       * 日付・曜日の配置
       */
      printType : function(thisYear, thisMonth) {
        $(this.ele).find('#tgtamount').text();
        $(this.ele).find('.calendar-year-month').text(thisYear + '/' + thisMonth);
        var thisDate = new Date(thisYear, thisMonth-1, 1);

        // 開始の曜日
        var startWeek = thisDate.getDay();

        var lastday = new Date(thisYear, thisMonth, 0).getDate();
        // 縦の数
        var rowMax = Math.ceil((lastday + startWeek) / 7);

        var outText = '<tr>';
        var countDate = 1;
        // 最初の空白を出力
        for (var i = 0; i < startWeek; i++) {
          outText += '<td class="calendar-none">&nbsp;</td>';
        }
        for (var row = 0; row < rowMax; row++) {
          // 最初の行は曜日の最初から
          if (row == 0) {
            for (var col = startWeek; col < 7; col++) {
              outText += printTD(countDate, col);
              countDate++;
            }
          } else {
            // 2行目から
            outText += '<tr>';
            for (var col = 0; col < 7; col++) {
              if (lastday >= countDate) {
                outText += printTD(countDate, col);
              } else {
                outText += '<td class="calendar-none">&nbsp;</td>';
              }
              countDate++;
            }
          }
          outText += '</tr>';
        }
        $(this.ele).find('tbody').html(outText);

        function printTD(count, col) {
          var dayText = "";
          var tmpId = ' id="calender-id'+ count + '"';
          // 曜日classを割り当てる
          if (col === 0) tmpId += ' class="calendar-sun"';
          if (col === 6) tmpId += ' class="calendar-sat"';
          return '<td' + tmpId + ' onclick="detail('+thisYear+','+thisMonth+','+count+');"><i class="calendar-day-number">' + count + '</i><div class="calendar-labels">' + dayText + '</div></td>';
       }
        //今日の日付をマーク
        var toDay = new Date();
        if (thisYear === toDay.getFullYear()) {
          if (thisMonth === (toDay.getMonth()+1)) {
            var dateID = 'calender-id' + toDay.getDate();
            $(this.ele).find('#' + dateID).addClass('calendar-today');
          }
        }
      },
      /**
       * イベントの表示
       */
      setEvent : function() {
        $(this.ele).find('#tgtamount').text('月間目標額： '+this.tgtamount+' 円');
        if(Number(this.monthlydbresult) >= (Number(this.tgtamount)*0.75)){
          $(this.ele).find('#monthlyresult').text('現在出費額： '+this.monthlydbresult+' 円');
          $(this.ele).find('#monthlyresult').css('color','red');
        }else{
          $(this.ele).find('#monthlyresult').text('現在出費額： '+this.monthlydbresult+' 円');
          $(this.ele).find('#monthlyresult').css('color','black');
        }

        for(var i = 0; i < this.events.length; i++) {
          var dateID = 'calender-id' + this.events[i].day;
          var getText = $('<textarea>' + this.events[i].espense + '</textarea>');
          // typeがある場合classを付与
          var type = "";
          if (this.events[i].type) {
            type = '-' + this.events[i].type;
          }
          $(this.ele)
                 .find('#' + dateID + ' .calendar-labels').append('<span class="calender-label' + type + '">' + getText.val() + '</span>');

        }

        // 休日
        for (var i=0; i<this.holiday.length; i++) {
          $(this.ele).find('#calender-id' + this.holiday[i]).addClass('calendar-holiday');
        }
      },

      /**
       * jsonファイルからデータを読み込む
       */
      loadData : function(year, month) {
        var self = this;
        let tgtyearmonth = year+"/"+month;
        $.ajax({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          },
          type: "GET",
          url: 'getdata',
          dataType: "json",
          async: false,
          data:{tgtdate:tgtyearmonth},
          success: function(data){
            self.events = data.event;
            self.year = data.year;
            self.month = data.month;
            self.date = new Date(data.date);
            self.tgtamount = data.tgtamount;
            self.monthlydbresult = data.monthlydbresult;
          },
          error: function (data) {
              console.log(data);
          }
        });
      }
    };

    $.wop.miniCalendar.defaults = {
      weekType : ["日", "月", "火", "水", "木", "金", "土"],
      jsonData: 'event.json'
    };
    $.fn.miniCalendar = function(option){
      option = option || {};
      var api = new $.wop.miniCalendar(this,option);
      return option.api ? api : this;
    };
  })(jQuery);
