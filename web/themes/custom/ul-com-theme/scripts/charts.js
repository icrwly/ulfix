// setup
// code will be optimized after finalize all needed functionality


(function ($, drupalSettings) {
$(document).ready(function() {
  var countries = {}, year = {}, chart_data = [];
  var countries_inj = {}, year_inj = {}, chart_data_inj = [];
  var countries_fatal = {}, year_fatal = {}, chart_data_fatal = [];
  var countries_projected = {}, year_projected = {}, chart_data_projected = [];
d3.csv("/sites/g/files/qbfpbp251/themes/site/ul_com_theme/files/total.csv", function(data) {
  var chart_div =  document.getElementById('chart_select');
  for (var i = 2; i < data.columns.length; i++) {
    var chart_options = document.getElementById("chart_options");
    var option = new Option(data.columns[i]);
    option.value = data.columns[i].toLowerCase().replace(/\s/g, '');
    chart_options.appendChild(option);
  }

  for (var k = 0; k < data.length-1; k++) {
    var row = data[k];
    chart_data.push({year:row.Year, countries: {totalincidentsbyyear:row['Total incidents by Year'],usa:row.USA,uk:row.UK,sweden:row.Sweden,singapore:row.Singapore,netherlands:row.Netherlands,japan:row.Japan,india:row.India,germany:row.Germany,china:row.China,southkorea:row['South Korea'],canada:row.Canada,norway:row.Norway}});
  }
});

d3.csv("/sites/g/files/qbfpbp251/themes/site/ul_com_theme/files/total_injuries.csv", function(data_inj) {
  for (var k = 0; k < data_inj.length-1; k++) {
    var row = data_inj[k];
    chart_data_inj.push({year:row.Year, countries: {totalincidentsbyyear:row['Total injuries by Year'],usa:row.USA,uk:row.UK,sweden:row.Sweden,singapore:row.Singapore,netherlands:row.Netherlands,japan:row.Japan,india:row.India,germany:row.Germany,china:row.China,southkorea:row['South Korea'],canada:row.Canada,norway:row.Norway}});
  }
});
d3.csv("/sites/g/files/qbfpbp251/themes/site/ul_com_theme/files/total_fatalities.csv", function(data_fatal) {
  for (var k = 0; k < data_fatal.length-1; k++) {
    var row = data_fatal[k];
    chart_data_fatal.push({year:row.Year, countries: {totalincidentsbyyear:row['Total fatalities by Year'],usa:row.USA,uk:row.UK,sweden:row.Sweden,singapore:row.Singapore,netherlands:row.Netherlands,japan:row.Japan,india:row.India,germany:row.Germany,china:row.China,southkorea:row['South Korea'],canada:row.Canada,norway:row.Norway}});
  }
});
d3.csv("/sites/g/files/qbfpbp251/themes/site/ul_com_theme/files/projected.csv", function(data_projected) {
  for (var k = 0; k < data_projected.length-1; k++) {
    var row = data_projected[k];
    chart_data_projected.push({year:row.Year, countries: {totalincidentsbyyear:row['Total incidents by Year'],usa:row.USA,uk:row.UK,sweden:row.Sweden,singapore:row.Singapore,netherlands:row.Netherlands,japan:row.Japan,india:row.India,germany:row.Germany,china:row.China,southkorea:row['South Korea'],canada:row.Canada,norway:row.Norway}});
  }
});
var main_chart_div =  document.getElementById('chart_select');
if (typeof(main_chart_div) != 'undefined' && main_chart_div != null)
{
    var data = {
      datasets: [{
        type: 'line',
        label: 'Injuries',
        data: chart_data_inj,
        fill: false,
        backgroundColor: [
          'rgba(201,0,32,255)'
        ],
        borderColor: 'rgba(201,0,32,255)',
        parsing: {
          xAxisKey: 'year',
          yAxisKey: 'countries.totalincidentsbyyear'
        }
      },{
        label: 'Incidents',
        data: chart_data,
        backgroundColor: [
          'rgba(187,228,246,255)'
        ],
        borderColor: [
          'rgba(187,228,246,255)'
        ],
        borderWidth: 1,
        parsing: {
          xAxisKey: 'year',
          yAxisKey: 'countries.totalincidentsbyyear'
        }
      },{
        type: 'bar',
        label: 'Projected Incidents',
        data: chart_data_projected,
        backgroundColor: [
          'rgba(255,97,84,255)'
        ],
        borderColor: 'rgba(255,97,84,255)',
        parsing: {
          xAxisKey: 'year',
          yAxisKey: 'countries.totalincidentsbyyear'
        }
      }]
    };

    // config
    const config = {
      type: 'bar',
      data,
      options: {
        scales: {
      x: {
        stacked: true,
      },
      y: {
        stacked: true
      }
    }
      }
    };
    var activities = document.getElementById("chart_options");
    if (typeof(activities) != 'undefined' && activities != null)
    {
      activities.addEventListener("change", function() {
      var value = activities.value;
      updateChart(value);
      });
    }

    function updateChart(option) {
      if(option != '1'){
        myChart.data.datasets[0].parsing.yAxisKey = `countries.${option}`;
        myChart.data.datasets[1].parsing.yAxisKey = `countries.${option}`;
        myChart.data.datasets[2].parsing.yAxisKey = `countries.${option}`;
        //myChart.data.datasets[3].parsing.yAxisKey = `countries.${option}`;
        myChart.update();
      }else{
        myChart.data.datasets[0].parsing.yAxisKey = `countries.totalincidentsbyyear`;
        myChart.data.datasets[1].parsing.yAxisKey = `countries.totalincidentsbyyear`;
        myChart.data.datasets[2].parsing.yAxisKey = `countries.totalincidentsbyyear`;
        //myChart.data.datasets[3].parsing.yAxisKey = `countries.totalincidentsbyyear`;
        myChart.update();
      }
    }
    // render init block
    var myChart = new Chart(
      document.getElementById('chart'),
      config
    );
    setTimeout(function(){
      updateChart(1);
    }, 1500);
  }
//Pie chart
var pie_chart_div =  document.getElementById('chart_select_pie');
if (typeof(pie_chart_div) != 'undefined' && pie_chart_div != null)
{
// render init block
    var data_pie = {
      labels: ['Fire', 'Explosion', 'Heat', 'Swelling', 'Venting'],
      datasets: [{
        label: 'Incidents',
        data: [7849,926,100,33,293],
        backgroundColor: [
          'rgba(201,0,34,255)',
          'rgba(87,126,159,255)',
          'rgba(255,97,84,255)',
          'rgba(2,225,230,255)',
          'rgba(91,4,39,255)'
        ],
        borderColor: [
          'rgba(54, 162, 235, 1)'
        ],
        borderWidth: 1
      }]
    };

    var config_pie = {
      type: 'pie',
      data: data_pie,
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'left',
          },
        }
      },
    };

    var activitiesPie = document.getElementById("chart_options_pie");
    if (typeof(activitiesPie) != 'undefined' && activitiesPie != null)
    {
      activitiesPie.addEventListener("change", function() {
      var value = activitiesPie.value;
      updatePieChart(value);
      });
    }
    function updatePieChart(option){
      //var pie = document.getElementById("chart_options_pie").value;
      pieChart.data.datasets[0].data = option.split(',');
      pieChart.update();
    }
    // render init block
    var pieChart = new Chart(
      document.getElementById('chart_pie'),
      config_pie
    );
}
//Bat chart
var bar_chart_div =  document.getElementById('chart_select_bars');
if (typeof(bar_chart_div) != 'undefined' && bar_chart_div != null)
{
  //document.getElementById("chart_select_bars").innerHTML +="";

  var data_lines = {
    labels: ['1995-2015', '2016','2017','2018','2019','2020','2021','2022','2023'],
    datasets: [
      {
        label: 'Explosion',
        data: [158,130,65,71,76,74,142,132,78],
        borderColor: 'rgba(87,126,159,255)',
        backgroundColor: 'rgba(87,126,159,255)',
      },
      {
        label: 'Fire',
        data: [507,402,553,507,686,590,1149,2121,1334],
        borderColor: 'rgba(201,0,34,255)',
        backgroundColor: 'rgba(201,0,34,255)',
      },
      {
        label: 'Heat',
        data: [34,7,16,7,5,6,9,12,4],
        borderColor: 'rgba(255,97,84,255)',
        backgroundColor: 'rgba(255,97,84,255)',
      },
      {
        label: 'Swelling',
        data: [17,1,2,1,1,3,7,1,0],
        borderColor: 'rgba(2,225,230,255)',
        backgroundColor: 'rgba(2,225,230,255)',
      },
      {
        label: 'Venting',
        data: [38,18,32,38,34,25,55,42,11],
        borderColor: 'rgba(91,4,39,255)',
        backgroundColor: 'rgba(91,4,39,255)',
      }
    ]
  };
  var config_lines = {
    type: 'bar',
    data: data_lines,
    options: {
      responsive: true,
      scales: {
        x: {
          stacked: true,
        },
        y: {
          stacked: true
        }
      },
      plugins: {
        legend: {
          position: 'bottom',
        }
      }
    },
  };
  var activitiesBar = document.getElementById("chart_options_bars");
  if (typeof(activitiesBar) != 'undefined' && activitiesBar != null)
  {
    activitiesBar.addEventListener("change", function() {
    var value = activitiesBar.value;
    updateBarChart(value);
    });
  }
  function updateBarChart(bar){

    if (bar=='total_bar') {
      barChart.data.datasets[0].data = [158,130,65,71,76,74,142,132,78];
      barChart.data.datasets[1].data = [507,402,553,507,686,590,1149,2121,1334];
      barChart.data.datasets[2].data = [34,7,16,7,5,6,9,12,4];
      barChart.data.datasets[3].data = [17,1,2,1,1,3,7,1,0];
      barChart.data.datasets[4].data = [38,18,32,38,34,25,55,42,11];
    }else if (bar=='el_veh') {
      barChart.data.datasets[0].data = [0,0,0,2,3,3,5,6,5];
      barChart.data.datasets[1].data = [28,11,8,24,52,60,158,275,139];
      barChart.data.datasets[2].data = [4,0,0,0,0,0,0,0,1];
      barChart.data.datasets[3].data = [0,0,0,0,0,0,0,0,0];
      barChart.data.datasets[4].data = [0,0,0,0,0,1,1,5,0];
    }else if(bar=='en_st_sys'){
      barChart.data.datasets[0].data = [0,0,0,1,2,1,3,7,1];
      barChart.data.datasets[1].data = [5,1,2,20,13,12,16,24,35];
      barChart.data.datasets[2].data = [0,0,0,0,0,0,0,0,0];
      barChart.data.datasets[3].data = [0,0,0,0,0,0,0,0,0];
      barChart.data.datasets[4].data = [0,0,0,0,0,0,2,3,0];
    }else if(bar=='el_mob'){
      barChart.data.datasets[0].data = [2,1,7,8,13,25,48,49,32];
      barChart.data.datasets[1].data = [81,134,270,180,276,253,519,840,543];
      barChart.data.datasets[2].data = [0,0,0,0,0,0,0,0,1];
      barChart.data.datasets[3].data = [0,0,0,0,0,0,0,0,0];
      barChart.data.datasets[4].data = [0,1,2,0,1,1,7,8,0];
    }else{
      barChart.data.datasets[0].data = [156,129,58,60,58,45,86,70,40];
      barChart.data.datasets[1].data = [393,256,273,283,345,265,456,982,617];
      barChart.data.datasets[2].data = [30,7,16,7,5,6,9,12,2];
      barChart.data.datasets[3].data = [17,1,2,1,1,3,7,1,0];
      barChart.data.datasets[4].data = [38,17,30,38,33,23,45,26,11];
    }
    barChart.update();
  }
  var barChart = new Chart(
    document.getElementById('canvas'),
    config_lines
  );
}
});
})(jQuery, drupalSettings);
