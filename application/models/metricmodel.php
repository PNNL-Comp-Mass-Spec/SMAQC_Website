<?php
/**
 * metricmodel.php
 *
 * File containing a CodeIgniter model for a SMAQC metric.
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @version 1.0
 * @copyright TODO
 * @license TODO
 * @package SMAQC
 * @subpackage models
 */
 
/**
 * CodeIgniter model for a SMAQC metric
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @version 1.0
 *
 * @package SMAQC
 * @subpackage models
 */
class Metricmodel extends CI_Model
{
    /**
     * The name of the instrument.
     * @var string
     */
    private $instrument;
    
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
     * The definition of the instrument.
     * A string that is retrieved from a database.
     * @var string
     */
    private $definition;
    
    /**
     * The start date for grabbing metrics.
     * This should be a human readable string of the format m-d-Y.
     * (Example: 11-11-2011)
     * @var string
     */  
    private $startdate;
    
    /**
     * The end date for grabbing metrics.
     * This should be a human readable string of the format m-d-Y.
     * (Example: 12-12-2012)
     * @var string
     */ 
    private $enddate;
    
    /**
     * The average value of the metric over the provided date range.
     * @var float
     */
    private $average = 0;
    
    /**
     * The results of querying the database for the metric values.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object
     */
    private $data;
    
    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var string
     */
    private $plotdata;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var string
     */
	private $plotdata_average;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var string
     */
    private $stddevupper;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var string
     */
    private $stddevlower;

    /**
     * Constructor
     *
     * The contructor for Metricmodel simply calls the constructor for the base
     * class (CI_Model). All initialization of the class must be done using the
     * initialize function. The reasoning for this has to do with the way CI
     * loads models in the controller (they cannot take arguments).
     *
     * @return Metricmodel
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
            case 'average':
                return $this->$what;
            case 'data':
                return $this->$what;
            case 'plotdata':
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

    /**
     * Initializer for the Metric model
     *
     * Gets all of the needed values for the class/model from the database.
     * Calculates any values that need calculating.
     *
     * @param string $instrument The name of the instrument.
     * @param string $metric The name of the metric.
     * @param string $start A human readable string for the start of the date
     * range. Assumed to be in m-d-Y format. (Example: 11-11-2011)
     * @param string $end A human readable string for the end of the date
     * range. Assumed to be in m-d-Y format. (Example: 12-12-2012)
     * 
     * @return array|boolean An array containing error information if there is
     * an error, FALSE otherwise.
     * Error Array Format: ['type' => string, 'value' => string]
     */
    public function initialize($instrument, $metric, $start, $end, $windowsize = 20)
    {
        // change the string format of the dates, as strtotime doesn't work
        // right with -'s
        $start = str_replace('-', '/', $start);
        $end   = str_replace('-', '/', $end);

        // set all the proper values
        $this->instrument = $instrument;
        $this->metric     = $metric;
        $this->startdate  = date("Y-m-d", strtotime($start));
        $this->enddate    = date("Y-m-d", strtotime($end));
    
        // check to see that this is a valid instrument/metric
        $this->db->where('Instrument', $instrument);
        $query = $this->db->get('V_Dataset_QC_Metrics', 1);
        
        if($query->num_rows() < 1)
        {
            return array("type" => "instrument", "value" => $instrument);
        }
        
        if(!$this->db->field_exists($metric, 'V_Dataset_QC_Metrics'))
        {
            return array("type" => "metric", "value" => $metric);
        }    
    
   		// Lookup the definition, purpose, and units for this metric
        $this->db->select('Description, Purpose, Units');
        $this->db->where('Metric', $metric);
        $query = $this->db->get('V_Dataset_QC_Metric_Definitions', 1);

        if($query->num_rows() < 1)
        {
            $this->definition = $metric . " (definition not found in DB)";
        }
		else 
		{
			$row = $query->row();
			$this->definition = $metric . ": " . $row->Description . "; " . $row->Purpose;
			
			$this->metric_units =$row->Units;
		}
    
        // build the query to get all the metric points in the specified range
        $columns = array(
                         'Acq_Time_Start',
                         'Dataset_ID',
                         'Dataset',
                         'SMAQC_Job',
                         'Metrics_Last_Affected',
                         $metric
                        );
                        
        $this->db->select(join(',', $columns));
        $this->db->from('V_Dataset_QC_Metrics');
        $this->db->where('Instrument =', $this->instrument);
        $this->db->where('Acq_Time_Start >=', $this->startdate);
        $this->db->where('Acq_Time_Start <=', $this->enddate . 'T23:59:59.999');
        $this->db->order_by('Acq_Time_Start', 'asc');

        // run the query, we may not actually need to store this in the model,
        // but for now we will
        $this->data = $this->db->get();

        // set plotdata to an empty array so that we can append each plot point
        $this->plotdata = array();
        
        // get just the data we want for plotting
        foreach($this->data->result() as $row)
        {
            // skip the value if it's null
            // the reason ignoring nulls is not part of the query, is that CI
            // apparently has issues with that
            if(empty($row->$metric))
            {
                continue;
            }

            // need to convert the date from the mssql format to one that
            // jqplot will like

            // cutoff fractional seconds, leaving only the date data we want
            $pattern = '/:[0-9][0-9][0-9]/';
            $date = preg_replace($pattern, '', $row->Acq_Time_Start);

            // javascript likes milliseconds,so get them
            $date = strtotime($date) * 1000;

            // add the value to the plotdata array
            $this->plotdata[] = array($date, $row->$metric);
        }

        $this->plotdata_average = array();
        $this->stddevupper = array();
        $this->stddevlower = array();

		$s0 = count($this->plotdata);

		// calculate stddev using the provided window size
		if($s0 > 0)
		{
			// windowradius is how many points to the left/right to check (not including mid)
			$windowradius = (int)($windowsize / 2);
			$total = 0;
			$s1 = 0; // s1 is the running total of the squared differences
			$avg = 0;

			for($i = 0; $i < $s0; $i++)
			{
				// get the date to the left by the window radius
				$sqlDateTimeLeft = strtotime('-' . $windowradius . ' day', $this->plotdata[$i][0]/1000);
				$sqlDateTimeLeft = date('Y-m-d H:i:s', $sqlDateTimeLeft);
				
				// get the date to the right by the window radius
				$sqlDateTimeRight = strtotime($windowradius . ' day', $this->plotdata[$i][0]/1000);
				$sqlDateTimeRight = date('Y-m-d H:i:s', $sqlDateTimeRight);

				// get the average over the date range
				$this->db->select_avg($metric, 'avg');
				$this->db->where('Instrument', $instrument);
				$this->db->where('Acq_Time_Start >=', $sqlDateTimeLeft);
				$this->db->where('Acq_Time_Start <=', $sqlDateTimeRight);
				$avg = $this->db->get('V_Dataset_QC_Metrics')->row()->avg;

				$this->plotdata_average[] = array(
					$this->plotdata[$i][0],
					$avg
					);

				// get the standard deviation over the date range
				$this->db->select('STDEV(' . $metric . ') as stddev');
				$this->db->where('Instrument', $instrument);
				$this->db->where('Acq_Time_Start >=', $sqlDateTimeLeft);
				$this->db->where('Acq_Time_Start <=', $sqlDateTimeRight);
				$stddev = $this->db->get('V_Dataset_QC_Metrics')->row()->stddev;

				$this->stddevupper[] = array(
					$this->plotdata[$i][0],
					$avg + (2 * $stddev)
					);

				$this->stddevlower[] = array(
					$this->plotdata[$i][0],
					$avg - (2 * $stddev)
					);
			} // end of loop
		} // end of calculating stddev
		
        // check to see if there were any data points in the date range
        if(count($this->plotdata) < 1)
        {
            // put an empty array in there so that jqplot will display
            // properly, and not break javascript on the page
            $this->plotdata[] = array();
        }
    
        // put everything for jqplot into a json encoded array
        $this->plotdata = json_encode($this->plotdata);
        $this->plotdata_average = json_encode($this->plotdata_average);
        $this->stddevupper = json_encode($this->stddevupper);
        $this->stddevlower = json_encode($this->stddevlower);
        $this->metric_units = json_encode($this->metric_units);

        /* get the average (we'll use the select_avg() call for now, as it
           deals with nulls, but we may want to do this in php instead of using
           the db */
        $this->db->select_avg($metric, 'avg');
        $this->db->where('Acq_Time_Start >=', $this->startdate);
        $this->db->where('Acq_Time_Start <=', $this->enddate . 'T23:59:59.999');
		$this->db->where('Instrument', $instrument);
        $this->average = $this->db->get('V_Dataset_QC_Metrics')->row()->avg;

          /* get the average (we'll use the select_avg() call for now, as it
           deals with nulls, but we may want to do this in php instead of using
           the db */
        $this->db->select_avg($metric, 'avg');
        $this->db->where('Acq_Time_Start >=', $this->startdate);
        $this->db->where('Acq_Time_Start <=', $this->enddate . 'T23:59:59.999');
		$this->db->where('Instrument', $instrument);
        $this->average = $this->db->get('V_Dataset_QC_Metrics')->row()->avg;
    
        return FALSE; // no errors, so return false
    }
}
?>
