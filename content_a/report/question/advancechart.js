function chartDrawer()
{
  if($("#chartdiv").length == 1){highChart1();}
}



function highChart1()
{

  var data = {{advanceChart.chart | raw}};

  // Splice in transparent for the center circle

  // Highcharts.getOptions().colors.splice(0, 0, 'transparent');


  Highcharts.chart('chartdiv',
  {
    chart:
    {
      // height: '100%'
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      style:
      {
        fontFamily: 'IRANSans, Tahoma, sans-serif'
      }
    },

    title:
    {
      text: '{{questionDetail.title}}'
    },
    subtitle: {
      // text: 'Advance chart'
    },
    series: [
    {
      type: "sunburst",
      data: data,
      allowDrillToNode: true,
      cursor: 'pointer',
      dataLabels:
      {
        useHTML: Highcharts.hasBidiBug,
        format: '{point.name}',
        style:
        {
          textOutline: false
        },
        filter:
        {
          property: 'innerArcLength',
          operator: '>',
          value: 16
        }
      },
      levels:
      [
        {
          level: 1,
          levelIsConstant: false,
          dataLabels:
          {
            useHTML: Highcharts.hasBidiBug,
            filter:
            {
              property: 'outerArcLength',
              operator: '>',
              value: 64
            }
          }
        },
        {
          level: 2,
          colorByPoint: true
        },
        {
          level: 3,
          colorVariation:
          {
            key: 'brightness',
            to: -0.5
          }
        },
        {
          level: 4,
          colorVariation:
          {
            key: 'brightness',
            to: 0.5
          }
        }
      ]

    }],
    credits:
    {
        text: '{{service.title}}',
        href: '{{service.url}}',
        position:
        {
            align: 'left',
            x: 45,
            verticalAlign: 'top',
            y: 25
        },
        style: {
            color: '#15677b',
            fontWeight: 'bold'
        }
    },
    exporting:
    {
      buttons:
      {
        contextButton:
        {
          menuItems:
          [
           'printChart',
           'separator',
           'downloadPNG',
           'downloadJPEG',
           'downloadSVG'
          ]
        }
      },
      allowHTML:true
    },
    legend: {
        useHTML: Highcharts.hasBidiBug
    },
    tooltip:
    {
      headerFormat: "",
      useHTML: true,
      pointFormat: '{point.name} <b>{point.value}</b>%'
    }
  },function(_chart)
  {
    _chart.renderer.image('{{service.logo}}', 10, 5, 30, 30).add();
  });
}