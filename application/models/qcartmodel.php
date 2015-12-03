<?php
/**
 * qcartmodel.php
 *
 * File containing a CodeIgniter model for the QC-ART metric.
 * 
 */
 
class QCArtModel extends CI_Model
{
    /**
     * The name of the instrument.
     * @var string
     */
    private $instrument;
    
    /**
     * Optional dataset name filter
     * @var string
     */     
    private $datasetfilter;
    
    /**
     * The name of the metric.
     * @var string
     */
    private $metric;

    /**
     * The units for the metric
     * A string that is retrieved from a database.
     * @var string
     */
    private $metric_units;
    
    /**
     * The definition of the metric.
     * A string that is retrieved from a database.
     * @var string
     */
    private $definition;

    /**
     * The start/end date for grabbing metrics.
     * This should be a human readable string of the format m-d-Y.
     * (Example: 11-11-2011)
     * @var string
     */  
    private $querystartdate;
    private $queryenddate;
   
    /**
     * The start/end date for plotting metrics.; unix datetime
     */           
    private $unixstartdate;
    private $unixenddate;
    
    /**
     * The results of querying the database for the metric values.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object
     */
    private $data;
    
    /**
     * An array of (x,y) values for the metric being plotted; 
     * includes data outside the date range being plotted (to allow for more accurate computation of median and MAD)
     * The x value is a unix timestamp, in seconds
     * The y value is the metric value
     * metricdata[$i][0] is date
     * metricdata[$i][1] is the metric vaue
     * metricdata[$i][2] is the fractionset number
     * @var string
     */
    private $metricdata;
    
    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The y values are the metric values to plot
     * @var string
     */
    private $plotdata;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The y values are the metric values to plot purple because the data is not released
     * @var string
     */
    private $plotDataBad;
	
    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The y values are the metric values to plot orange because the QC-ART value is past a threshold
     * @var string
     */
    private $plotDataPoor;
	
    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The average metric value across the datasets in a given fraction set
     * @var string
     */    
    private $plotdata_average;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The y value 6.55, plotted with a red line, indicating a threshold for very bad scores
     * @var string
     */
    private $stddevupper;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The y value 4, plotted with a yellow line, indicating a threshold for poor scores
     * @var string
     */
    private $stddevlower;
	
    /**
     * Constructor
     *
     * The contructor for QCArtModel simply calls the constructor for the base
     * class (CI_Model). All initialization of the class must be done using the
     * initialize function. The reasoning for this has to do with the way CI
     * loads models in the controller (they cannot take arguments).
     *
     * @return QCArtModel
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * __get
     *
     * The custom __get() function is essentially a getter for our private
     * members in the class. The use of the variable $CI and get_instance()
     * allows us to access CI's loaded classes using the same syntax as in the
     * controller.
     * 
     * @param string $what The member of the class that we are looking for.
     *
     * @return mixed Returns whatever member was requested.
     */
    function __get($what)
    {
        $CI =& get_instance();  // get a reference to the base class (CI_Model)
        switch($what)
        {
            case 'instrument':
                return $this->$what;
            case 'datasetfilter':
            	return $this->$what;
            case 'metric':
                return $this->$what;
            case 'metric_units':
                return $this->$what;
            case 'definition':
                return $this->$what;
            case 'startdate':
                return $this->$what;
            case 'enddate':
                return $this->$what;
            case 'data':
                return $this->$what;
            case 'plotdata':
                return $this->$what;
            case 'plotDataBad':
            	return $this->$what;
            case 'plotDataPoor':
            	return $this->$what;
            case 'plotdata_average':
                return $this->$what;
            case 'stddevupper':
                return $this->$what;
            case 'stddevlower':
                return $this->$what;
            default:
                return $CI->$what;  // check base class CI_Model for member
        }
    }

	/*
     * Compute the average value for data in metricdata that comes from the same fraction set
     *
     * @param int $fractionSetFilter: The fraction set to filter on
     *
     * Returns the average or NULL if no matching data
     */
	function compute_fractionset_average($fractionSetFilter, $fractionSetDateSeconds)
	{
		$dataCount = count($this->metricdata);
        
		$runningSum = 0.0;
        $countToAverage = 0;
        
        for($i = 0; $i < $dataCount; $i++)
        {
            if ($this->metricdata[$i][2] == $fractionSetFilter) 
            {
            	if ($this->DateDiffDays($this->metricdata[$i][0], $fractionSetDateSeconds) < 30) 
            	{
                	$runningSum += $this->metricdata[$i][1];
	                $countToAverage += 1;
	            }
            }
        }
        
        if ($countToAverage > 0)
            return $runningSum / (float)$countToAverage;
        else
            return NULL;

	}	

	// Returns the number of days between two second-based dates
	// Always returns a positive number
	function DateDiffDays($dateOneSeconds, $dateTwoSeconds) 
	{
		$dateDiffSeconds = abs($dateOneSeconds - $dateTwoSeconds);
		
		return $dateDiffSeconds / 86400.0;
	}
	
    /**
     * Initializer for the QC-ART model
     *
     * Gets all of the needed values for the class/model from the database.
     * Calculates any values that need calculating.
     *
     * @param string $instrument The name of the instrument.
     * @param string $metric The name of the metric (should always be "QCART" for now)
     * @param string $start A human readable string for the start of the date range. Assumed to be in m-d-Y format. (Example: 11-11-2011)
     * @param string $end A human readable string for the end of the date range. Assumed to be in m-d-Y format. (Example: 12-12-2012)
     * @param string $datasetfilter Optional dataset name filter
     *
     * @return array|boolean An array containing error information if there is
     * an error, FALSE otherwise.
     * Error Array Format: ['type' => string, 'value' => string]
     */
    public function initialize($instrument, $metric, $start, $end, $datasetfilter = '')
    {
        // change the string format of the dates, since strtotime doesn't work
        // right with -'s
        $start = str_replace('-', '/', $start);
        $end   = str_replace('-', '/', $end);

		// Do not load data outside of $start or $end
      	$windowRadiusLeft = 0;
      	$windowRadiusRight = 1;
      	
        // set all the proper values
        $this->instrument = $instrument;
        $this->metric     = $metric;
		
        $this->unixstartdate  = strtotime($start);
        $this->unixenddate    = strtotime($end);
        
        // Set the query start date to $windowradius days prior to $start
        $this->querystartdate  = date("Y-m-d", strtotime('-' . $windowRadiusLeft  . ' day', $this->unixstartdate));
        $this->queryenddate    = date("Y-m-d", strtotime(      $windowRadiusRight . ' day', $this->unixenddate));
    
    	$this->datasetfilter  = $datasetfilter;
    	
        // check to see that this is a valid instrument/metric
        $this->db->where('Instrument', $instrument);
        
        $query = $this->db->get('V_Dataset_QC_Metrics_Export', 1);
        
        if($query->num_rows() < 1)
        {
            return array("type" => "instrument", "value" => $instrument);
        }
        
        if(!$this->db->field_exists($metric, 'V_Dataset_QC_Metrics_Export'))
        {
            return array("type" => "metric", "value" => $metric);
        }    
    
        // Lookup the Description, purpose, units, and Source for this metric
        $this->db->select('Description, Purpose, Units, Source');
        $this->db->where('Metric', $metric);
        $query = $this->db->get('V_Dataset_QC_Metric_Definitions', 1);

        if($query->num_rows() < 1)
        {
            $this->definition = $metric . " (definition not found in DB)";
        }
        else 
        {
			$row = $query->row();
			$this->definition = $metric . " (" . $row->Source . "): " . $row->Description . " <br>" . $row->Purpose;
			
			$this->metric_units =$row->Units;
        }
    
        // build the query to get all the metric points in the specified range
        $columns = array(
                         'Acq_Time_Start',
                         'Dataset_ID',
                         'Dataset',
                         'Quameter_Job',
                         'SMAQC_Job',
                         'Quameter_Last_Affected',
                         'Smaqc_Last_Affected',
                         'Dataset_Rating',
                         'Dataset_Rating_ID',
                          $metric,
						 'QCDM'
                        );
                        
        $this->db->select(join(',', $columns));
        $this->db->from('V_Dataset_QC_Metrics_Export');
        $this->db->where('Instrument =', $this->instrument);
        $this->db->where('Acq_Time_Start >=', $this->querystartdate);
        $this->db->where('Acq_Time_Start <=', $this->queryenddate . 'T23:59:59.999');
                
        if (strlen($this->datasetfilter) > 0)
        {
	        $this->db->like('Dataset', $this->datasetfilter);
		}
		
        $this->db->order_by('Acq_Time_Start', 'desc');

        // run the query, we may not actually need to store this in the model,
        // but for now we will
        $this->data = $this->db->get();

        // Initialize the data arrays so that we can append data
        $this->metricdata = array();
		
        $this->plotdata = array();
        $this->plotDataBad = array();			// Not Released (aka bad)
        $this->plotDataPoor = array();			// QC-ART value out-of-range (aka low quality)
        
		// QC-ART threshold for very bad scores
        $qcArtRedThreshold = 6.55;
        
        // QC-ART threshold for poor scores
        $qcArtYellowThreshold = 4;

		// This array tracks date values and fraction set numbers
		// Used to compute (and plot) the average QC-ART value for each fraction set
		// $fractionSetList[i][0] is date
		// $fractionSetList[i][1] is fractionSet number
		$fractionSetList = array();

        // get just the data we want for plotting
        foreach($this->data->result() as $row)
        {
            // Skip the value if it's null
            // We unforunately cannot do this during the query, since codeigniter returns no rows
            if(is_null($row->$metric))
            {
                continue;
            }

            // need to convert the date from the mssql format to one that
            // jqplot will like

            // cutoff fractional seconds, leaving only the date data we want
            $pattern = '/:[0-9][0-9][0-9]/';
            $date = preg_replace($pattern, '', $row->Acq_Time_Start);
            
            $date = strtotime($date);

            $datasetIsBad = 0;

            if (!is_null($row->QCART) && $row->QCART >= $qcArtRedThreshold)
            {
                $datasetIsBad = 1;
            }
            
            if ($row->Dataset_Rating_ID >= -5 && $row->Dataset_Rating_ID <= 1)
            {
                $datasetIsBad = 2;
            }
			
			// Parse out the fraction set, for example "40" from
			// TEDDY_DISCOVERY_SET_40_23_30Nov15_Frodo_15-08-38
			
			$fractionSetForDataset = 0;
			$patternSetNumber = '/_SET_([0-9]+)_/';
			if (preg_match($patternSetNumber, $row->Dataset, $matches)) {
				$fractionSetForDataset = (int)$matches[1];
			}
			
			// Uncomment to debug
		    // else
			//	echo "No match to " . patternSetNumber . " for " . $row->Dataset . "<br>";

			
            if ($datasetIsBad == 0 || $datasetIsBad == 1)
            {
                // add the value to the metricdata array
                $this->metricdata[] =      array($date, $row->$metric, $fractionSetForDataset);
            }

            // add the value to the plotdata array if it is within the user-specified plotting range
            if ($date >= $this->unixstartdate && $date <= $this->unixenddate)
            {
                if ($datasetIsBad != 0)
                {
                    if($datasetIsBad == 1)
                    {
                    	// Dataset with QC-ART score over the threshold
                        // javascript likes milliseconds, so multiply $date by 1000 when appending to the array
                        $this->plotDataPoor[] = array($date * 1000, $row->$metric, $row->Dataset);
                    }
                    if($datasetIsBad == 2)
                    {
                    	// Not Released dataset
                        // javascript likes milliseconds, so multiply $date by 1000 when appending to the array
                        $this->plotDataBad[] = array($date * 1000, $row->$metric, $row->Dataset);
                    }
                }
                else
                {
                    // javascript likes milliseconds, so multiply $date by 1000 when appending to the array
                    $this->plotdata[] = array($date * 1000, $row->$metric, $row->Dataset);
                }
	                
	            // Append to $fractionSetList
				$fractionSetList[] = array($date, $fractionSetForDataset);
				
            }
        }

        $this->plotdata_average = array();
        $this->stddevupper = array();
        $this->stddevlower = array();

        $s0 = count($fractionSetList);

        // Calculate the average QC-ART value for each fraction set
        if($s0 > 0)
        {
            $cachedFractionSet = 0;
            $cachedAverage = 0.0;
			$cachedFractionSetDateSeconds;
			
			// Uncomment to debug
			// echo "DataIndex, Date, FractionSet, FractionSetAverage<br>";
            
            for($dataIndex = 0; $dataIndex < $s0; $dataIndex++)
            {
	    		// Compute the average for the fraction set

				$currentFractionSetDate = $fractionSetList[$dataIndex][0];
				$currentFractionSet = $fractionSetList[$dataIndex][1];
				
				if ($currentFractionSet == 0)
				{
					// Uncomment to debug
					// echo $dataIndex . ", " . date('m/d/Y H:i:s', $currentFractionSetDate) . ", " . $currentFractionSet . ", InvalidFractionSet<br>";
					continue;
				}

				// Javascript likes milliseconds, so multiply $date by 1000 when appending to the array
				$currentDateMillisec = $currentFractionSetDate * 1000;
			
				if ($cachedFractionSet != $currentFractionSet || $this->DateDiffDays($cachedFractionSetDateSeconds, $currentFractionSetDate) > 30) {
				
					$newAverage = $this->compute_fractionset_average($currentFractionSet, $currentFractionSetDate);

					/*
					if ($cachedFractionSet != 0) 
					{
						// Add some additional values to make the line be a step functin
						$midPointDateMillisec = (int)(($cachedFractionSetDateSeconds * 1000 + $currentDateMillisec) / 2.0);
						
						$leftPoint = $midPointDateMillisec - 3600000;
						if ($leftPoint < $cachedFractionSetDateSeconds * 1000 + 10000)
							$leftPoint = $cachedFractionSetDateSeconds * 1000 + 10000;
							
						$rightPoint = $midPointDateMillisec + 3600000;
						if ($rightPoint > $currentDateMillisec - 10000)
							$rightPoint = $currentDateMillisec - 10000;
						
						$this->plotdata_average[] = array(
	                    $leftPoint,
	                    $cachedAverage
	                    );

						echo $dataIndex . ", " . date('m/d/Y H:i:s', $leftPoint/1000.0) . ", " . $cachedFractionSet . ", " . $cachedAverage . " (filler)<br>";

						$this->plotdata_average[] = array(
	                    $rightPoint,
	                    $newAverage
	                    );
	                    
	                    echo $dataIndex . ", " . date('m/d/Y H:i:s', $rightPoint/1000.0) . ", " . $currentFractionSet . ", " . $newAverage . " (filler)<br>";
	                    
					}
					*/
					
					$cachedFractionSet = $currentFractionSet;
					$cachedFractionSetDateSeconds = $currentFractionSetDate;
					
		            $cachedAverage = $newAverage;
				}
				
				if (is_null($cachedAverage))
					continue;
				
                $this->plotdata_average[] = array(
                    $currentDateMillisec,
                    $cachedAverage
                    );
				
				$this->stddevlower[] = array(
					$currentDateMillisec,
					$qcArtYellowThreshold
					);

				$this->stddevupper[] = array(
					$currentDateMillisec,
					$qcArtRedThreshold
					);
					
				// Uncomment to debug
				// echo $dataIndex . ", " . date('m/d/Y H:i:s', $currentFractionSetDate) . ", " . $cachedFractionSet . ", " . $cachedAverage . "<br>";
                    
            } // end of loop
        } // end of calculating stddev
        
        // check to see if there were any data points in the date range
        if(count($this->plotdata) < 1)
        {
            // put an empty array in there so that jqplot will display
            // properly, and not break javascript on the page
            $this->plotdata[] = array();
        }

		if(count($this->plotDataBad) < 1)
        {
            // put an empty array in there so that jqplot will display
            // properly, and not break javascript on the page
            $this->plotDataBad[] = array();
        }
        
        if(count($this->plotDataPoor) < 1)
        {
            // put an empty array in there so that jqplot will display
            // properly, and not break javascript on the page
            $this->plotDataPoor[] = array();
        }
        
        // put everything for jqplot into a json encoded array
        $this->plotdata = json_encode($this->plotdata);
        $this->plotdata_average = json_encode($this->plotdata_average);
        $this->stddevupper = json_encode($this->stddevupper);
        $this->stddevlower = json_encode($this->stddevlower);
        $this->plotDataBad = json_encode($this->plotDataBad); 
        $this->plotDataPoor = json_encode($this->plotDataPoor);
        $this->metric_units = json_encode($this->metric_units);
     
        return FALSE; // no errors, so return false
    }
}
?>
