@extends('main.main')
@section('bodyContent')

  <div class="bg-light lter b-b wrapper-md">
    <h2 class="m-n font-thin h3">Home</h2>
  </div>

  <div class="col-lg-12">
    <a href="{{ url('/adminusers') }}">     <div class="homeIco icoUsers icon-user fa-fw homeicons"></div>               </a>
    <a href="{{ url('/buildings') }}">      <div class="homeIco icoBuilding glyphicon fa fa-building-o homeicons"></div> </a>
    <a href="{{ url('/customersdash') }}">  <div class="homeIco icoCustomers glyphicon fa fa-users homeicons"></div>     </a>
    <a href="{{ url('/supportdash') }}">    <div class="homeIco icoSupport icon-earphones-alt homeicons"></div>          </a>
    <a href="{{ url('/salesdash') }}">      <div class="homeIco icoSales icon-briefcase fa-fw homeicons"></div>          </a>
  </div>

  <div class="col-lg-12">

  <br />
    <div class="bg-black dker wrapper-lg col-lg-6" ng-controller="FlotChartDemoCtrl">q
      <ul class="nav nav-pills nav-xxs nav-rounded m-b-lg">
        <li class="active"><a href>Day</a></li>
        <li><a href ng-click="refreshData()">Week</a></li>
        <li><a href ng-click="refreshData()">Month</a></li>
      </ul>
      <div ui-jq="plot" ui-refresh="d0_1" ui-options="
        [
          { data: [ [0,7],[1,6.5],[2,12.5],[3,7],[4,9],[5,6],[6,11],[7,6.5],[8,8],[9,7] ], points: { show: true, radius: 2}, splines: { show: true, tension: 0.4, lineWidth: 1 } }
        ],
        {
          colors: ['#23b7e5', '#7266ba'],
          series: { shadowSize: 3 },
          xaxis:{ font: { color: '#507b9b' } },
          yaxis:{ font: { color: '#507b9b' }, max:16 },
          grid: { hoverable: true, clickable: true, borderWidth: 0, color: '#1c2b36' },
          tooltip: true,
          tooltipOpts: { content: 'Visits of %x.1 is %y.4',  defaultTheme: false, shifts: { x: 10, y: -25 } }
        }
        " style="min-height:360px" >
      </div>
    </div>
  <div class="col-lg-6">

    <iframe src="https://calendar.google.com/calendar/embed?height=600&amp;wkst=1&amp;src=silverip.com_lpd0q360svhv8dtg6gp3nhunt8%40group.calendar.google.com&amp;color=%2329527A&amp;ctz=America%2FChicago" style="padding:10px;width: 100%;" height="500" frameborder="0" scrolling="no"></iframe>

  </div>
    <div class="wrapper-md bg-white-only b-b">
      <div class="row text-center">
        <div class="col-sm-3 col-xs-6">
          <div>Buildings <i class="fa fa-fw fa-caret-up text-success text-sm"></i></div>
          <div class="h2 m-b-sm">78</div>
        </div>
        <div class="col-sm-3 col-xs-6">
          <div>Tickets <i class="fa fa-fw fa-caret-down text-warning text-sm"></i></div>
          <div class="h2 m-b-sm">16.7k</div>
        </div>
        <div class="col-sm-3 col-xs-6">
          <div>Orders <i class="fa fa-fw fa-caret-up text-success text-sm"></i></div>
          <div class="h2 m-b-sm">16.6k</div>
        </div>
        <div class="col-sm-3 col-xs-6">
          <div>Solved <i class="fa fa-fw fa-caret-down text-danger text-sm"></i></div>
          <div class="h2 m-b-sm">74</div>
        </div>
      </div>
    </div>
    <div class="wrapper-md">
      <div class="row text-center">
        <div class="col-sm-3 col-xs-6">
          <div>Todays tickets solved</div>
          <div ui-jq="easyPieChart" ui-options="{
                percent: 75,
                lineWidth: 4,
                trackColor: '#e8eff0',
                barColor: '#7266ba',
                scaleColor: false,
                size: 115,
                rotate: 90,
                lineCap: 'butt'
              }" class="inline m-t">
            <div>
              <span class="text-primary h3">75%</span>
            </div>
          </div>
        </div>
        <div class="col-sm-3 col-xs-6">
          <div>Active Users</div>
          <div ui-jq="easyPieChart" ui-options="{
                percent: 35,
                lineWidth: 4,
                trackColor: '#e8eff0',
                barColor: '#23b7e5',
                scaleColor: false,
                size: 115,
                rotate: 0,
                lineCap: 'butt'
              }" class="inline m-t">
            <div>
              <span class="text-info h3">35%</span>
            </div>
          </div>
        </div>
        <div class="col-sm-3 col-xs-6">
          <div>Remaining Tickets</div>
          <div ui-jq="easyPieChart" ui-options="{
                  percent: 15,
                  lineWidth: 4,
                  trackColor: '#e8eff0',
                  barColor: '#fad733',
                  scaleColor: false,
                  size: 115,
                  rotate: 180,
                  lineCap: 'butt'
                }" class="inline m-t">
            <div>
              <span class="text-warning h3">15%</span>
            </div>
          </div>
        </div>
        <div class="col-sm-3 col-xs-6">
          <div>Network Info</div>
          <div ui-jq="easyPieChart" ui-options="{
                percent: 60,
                lineWidth: 4,
                trackColor: '#e8eff0',
                barColor: '#27c24c',
                scaleColor: false,
                size: 115,
                rotate: 90,
                lineCap: 'butt'
              }" class="inline m-t">
            <div>
              <span class="text-success h3">60%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop