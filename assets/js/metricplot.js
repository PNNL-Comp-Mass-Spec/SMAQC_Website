var plot;
var test = Settings.title;
if(test.indexOf("QCDM") == -1)
{
	$(document).ready(function() {
		$.jqplot.config.enablePlugins = true;
		plot = $.jqplot('chartdiv', [Settings.plotdata, Settings.plotdata_average, Settings.stddevupper, Settings.stddevlower, Settings.plotDataPoor, Settings.plotDataBad], {
			title:Settings.title,
			axes: {
				xaxis:{
					renderer:$.jqplot.DateAxisRenderer,
					tickRenderer:$.jqplot.CanvasAxisTickRenderer,
					min:Settings.startdate,
					max:Settings.enddate,
					tickOptions: {
						angle: -60,
						fontSize: '8pt',
						formatString: '%b %#d, %Y'
					}
				},
				yaxis:{
					label:Settings.metric_units,
					labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				}
			},
			legend: {
				show: true,
				location: 'e',
				placement: 'outsideGrid'
			},
			cursor: {
				show: true,
				zoom: true,
				clickReset: true,
				showTooltip: false
			},
			seriesDefaults: {
				showLine: true,
				showMarker: false,
				trendline: {
					show: false
				}
			},
			series:[
				{
					label:'Data',
					lineWidth: 2,
					showLine: false,
					showMarker: true,
					markerOptions: {
						size: 5
					},
					highlighter: {
						show: true,
						showTooltip: true,
						tooltipAxes: 'y',
						tooltipLocation: 'se',
						yvalues:2
					}                
				},
				{
					label:'Median',
					linewidth: 1,
					markerOptions: { size: 4 },
					color: '#01818A',
					trendline: {
						show: false,
						color: '#FFEA00'
					}
					
				},
				{
					label:'1.5x MAD',
					lineWidth: 3,
					color: '#B00'                
				},
				{
					label:'1.5x MAD',
					lineWidth: 3,
					color: '#B00',
					showLabel: false
				},
				{
					label:'Low Quality',
					lineWidth: 2,
					showLine: false,
					showMarker: true,
					color: '#FA8100',
					markerOptions: {
						size: 5,
						color: '#FA8100'
					},
					highlighter: {
						show: true,
						showTooltip: true,
						tooltipAxes: 'y',
						tooltipLocation: 'se',
						yvalues:2
					}
				},
                                {
					label:'Bad Data',
					lineWidth: 2,
					showLine: false,
					showMarker: true,
					color: '#551A8B',
					markerOptions: {
						size: 5,
						color: '#551A8B'
					},
					highlighter: {
						show: true,
						showTooltip: true,
						tooltipAxes: 'y',
						tooltipLocation: 'se',
						yvalues:2
					}
				}
			]
		});
	});
}
else
{
	$(document).ready(function() {
		$.jqplot.config.enablePlugins = true;
		plot = $.jqplot('chartdiv', [Settings.plotdata, Settings.plotdata_average, Settings.stddevlower, Settings.plotDataPoor, Settings.plotDataBad], {
			title:Settings.title,
			axes: {
				xaxis:{
					renderer:$.jqplot.DateAxisRenderer,
					tickRenderer:$.jqplot.CanvasAxisTickRenderer,
					min:Settings.startdate,
					max:Settings.enddate,
					tickOptions: {
						angle: -60,
						fontSize: '8pt',
						formatString: '%b %#d, %Y'
					}
				},
				yaxis:{
					label:Settings.metric_units,
					labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				}
			},
			legend: {
				show: true,
				location: 'e',
				placement: 'outsideGrid'
			},
			cursor: {
				show: true,
				zoom: true,
				clickReset: true,
				showTooltip: false
			},
			seriesDefaults: {
				showLine: true,
				showMarker: false,
				trendline: {
					show: false
				}
			},
			series:[
				{
					label:'Data',
					lineWidth: 2,
					showLine: false,
					showMarker: true,
					markerOptions: {
						size: 5
					},
					highlighter: {
						show: true,
						showTooltip: true,
						tooltipAxes: 'y',
						tooltipLocation: 'se',
						yvalues:2
					}                
				},
				{
					label:'Median',
					linewidth: 1,
					markerOptions: { size: 4 },
					color: '#01818A',
					trendline: {
						show: false,
						color: '#FFEA00'
					}
				},
				{
					label:'Limit',
					lineWidth: 3,
					color: '#66CD00'                
				},
				{
					label:'Low Quality',
					lineWidth: 2,
					showLine: false,
					showMarker: true,
					color: '#FA8100',
					markerOptions: {
						size: 5,
						color: '#FA8100'
					},
					highlighter: {
						show: true,
						showTooltip: true,
						tooltipAxes: 'y',
						tooltipLocation: 'se',
						yvalues:2
					}
				},
                                {
					label:'Bad Data',
					lineWidth: 2,
					showLine: false,
					showMarker: true,
					color: '#551A8B',
					markerOptions: {
						size: 5,
						color: '#551A8B'
					},
					highlighter: {
						show: true,
						showTooltip: true,
						tooltipAxes: 'y',
						tooltipLocation: 'se',
						yvalues:2
					}
				}
			]
		});
	});
}

