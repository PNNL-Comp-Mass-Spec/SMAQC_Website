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
     * The definition of the instrument.
     * A string that is retrieved from a database.
     * @var string
     *
     * @todo Needs implementing (the string is just set to lorem ipsum).
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
    public function initialize($instrument, $metric, $start, $end)
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
        $this->db->where('Acq_Time_Start <=', $this->enddate);
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
    
        // check to see if there were any data points in the date range
        if(count($this->plotdata) < 1)
        {
            // put an empty array in there so that jqplot will display
            // properly, and not break javascript on the page
            $this->plotdata[] = array();
        }
    
        // put it into a json encoded array
        $this->plotdata = json_encode($this->plotdata);

        /* get the average (we'll use the select_avg() call for now, as it
           deals with nulls, but we may want to do this in php instead of using
           the db */
        $this->db->select_avg($metric, 'avg');
        $this->db->where('Acq_Time_Start >=', $this->startdate);
        $this->db->where('Acq_Time_Start <=', $this->enddate);
        $this->average = $this->db->get('V_Dataset_QC_Metrics')->row()->avg;

        // we'll set the definition here to lorem ipsum
        // but we'll eventually want to grab it from a db or something
        $this->definition = "Lorem ipsum dolor sit amet, consectetur adipis" .
                            "icing elit, sed do eiusmod tempor incididunt u" .
                            "t labore et dolore magna aliqua. Ut enim ad mi" .
                            "nim veniam, quis nostrud exercitation ullamco " .
                            "laboris nisi ut aliquip ex ea commodo consequa" .
                            "t. Duis aute irure dolor in reprehenderit in v" .
                            "oluptate velit esse cillum dolore eu fugiat nu" .
                            "lla pariatur. Excepteur sint occaecat cupidata" .
                            "t non proident, sunt in culpa qui officia dese" .
                            "runt mollit anim id est laborum.";
    
        return FALSE; // no errors, so return false
    }
}
?>
