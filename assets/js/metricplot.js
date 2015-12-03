var plot;
var test = Settings.title;
if(test.indexOf("QC-ART") > 0)
{
		// QC-ART specific plot (similar to the standard metric view but with custom colors and labels)
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
						// Settings.plotdata
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
						// Settings.plotdata_average (Fraction Set Average)						
						label:'Fraction Set Avg',
						linewidth: 2,
						showLine: true,
						showMarker: false,
						markerOptions: { size: 1 },
						color: '#01818A',
						trendline: {
							show: false,
							color: '#FFEA00'
						}
						
					},
					{
						// Settings.stddevupper (threshold for very bad scores)
						label:'Bad Threshold',
						lineWidth: 1,
						showLine: true,
						showMarker: false,
						markerOptions: { size: 1 },
						color: '#BB0000'
					},
					{
						// Settings.stddevlower (threshold for poor scores)
						label:'Poor Threshold',
						lineWidth: 1,
						showLine: true,
						showMarker: false,
						markerOptions: { size: 1 },
						color: '#FFFF00'
					},
					{
						// Settings.plotDataPoor (QC-ART value larger than the threshold for very bad scores))
						label:'Bad QC-ART Score',
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
		                // Settings.plotDataBad (dataset not released)
						label:'Bad Dataset',
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


} else {
	if(test.indexOf("QCDM") == -1)
	{
		// Standard metric (not QCDM or QCART)
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
						// Settings.plotdata
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
						// Settings.plotdata_average
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
						// Settings.stddevupper
						label:'1.5x MAD',
						lineWidth: 3,
						color: '#BB0000'                
					},
					{
						// Settings.stddevlower
						label:'1.5x MAD',
						lineWidth: 3,
						color: '#BB0000',
						showLabel: false
					},
					{
						// Settings.plotDataPoor (QCDM value out-of-range)
						label:'Bad QCDM Score',
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
		                // Settings.plotDataBad (dataset not released)
						label:'Bad Dataset',
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
		// QCDM metric
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
						// Settings.plotdata
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
						// Settings.plotdata_average
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
						// Settings.stddevlower
						label:'Limit',
						lineWidth: 3,
						color: '#66CD00'                
					},
					{
						// Settings.plotDataPoor (QCDM value out-of-range)
						label:'Bad QCDM Score',
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
		                // Settings.plotDataBad (dataset not released)
						label:'Bad Dataset',
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
}
