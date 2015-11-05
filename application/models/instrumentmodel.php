<?php
/**
 * instrumentmodel.php
 *
 * File containing a CodeIgniter model for an instrument.
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @version 1.0
 * @copyright TODO
 * @license TODO
 * @package SMAQC
 * @subpackage models
 */
 
/**
 * CodeIgniter model for an instrument
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @version 1.0
 *
 * @package SMAQC
 * @subpackage models
 */
class Instrumentmodel extends CI_Model
{
    /**
     * The name of the instrument.
     * @var string
     */
    private $instrument;
    
    /**
     * The definition of the instrument.
     * A string that is retrieved from a database.
     * @var string
     *
     * @todo Need/want this?
     * Also needs implementing (the string is just set to lorem ipsum).
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
     * The status of the instrument (green/yellow/red)
     * @var string
     * @todo Actually use/implement this
     */ 
    private $status;
    
    /**
     * A list (php array) of the metrics available in the database.
     * @var array
     */ 
    private $metricnames;

    /**
     * A list (php array) of the metric descriptions
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object 
     */
    private $metricDescriptions;
	
    /**
     * A list (php array) of the category of each metric
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object 
     */
    private $metricCategories;

    /**
     * A list (php array) of the source of each metric
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object 
     */
    private $metricSources;
    
    /**
     * The latest metrics for the instrument.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object 
     */
    private $latestmetrics;
    
    /**
     * The averaged metrics for the instrument over the provided date range.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object 
     */
    private $averagedmetrics;

    /**
     * The standard deviation for each of the metrics for the instrument over the
     * provided date range.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var object 
     */
    private $stddevmetrics;
    
    /**
     * Constructor
     *
     * The contructor for Instrumentmodel simply calls the constructor for the 
     * base class (CI_Model). All initialization of the class must be done
     * using the initialize function. The reasoning for this has to do with the
     * way CI uses loads models (they cannot take arguments).
     *
     * @return Instrumentmodel
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
     * @return mixed Whatever member was requested.
     */
    function __get($what)
    {
        $CI =& get_instance();
        switch($what)
        {
            case 'instrument':
                return $this->$what;
            case 'definition':
                return $this->$what;
            case 'startdate':
                return $this->$what;
            case 'enddate':
                return $this->$what;
            case 'status':
                return $this->$what;
            case 'metricnames':
                return $this->$what;
            case 'metricDescriptions':
            	return $this->$what;
            case 'metricCategories':
           		return $this->$what;
            case 'metricSources':
           		return $this->$what;
            case 'latestmetrics':
                return $this->$what;
            case 'averagedmetrics':
                return $this->$what;
            case 'stddevmetrics':
                return $this->$what;
            default:
                return $CI->$what;  // check base class CI_Model for member
        }
    }

    /**
     * Initializer for the Instrument model
     *
     * Gets all of the needed values for the class/model from the database.
     * Calculates any values that need calculating.
     *
     * @param string $instrument The name of the instrument.
     * @param string $start A human readable string for the start of the date
     * range. Assumed to be in m-d-Y format. (Example: 11-11-2011)
     * @param string $end A human readable string for the end of the date
     * range. Assumed to be in m-d-Y format. (Example: 12-12-2012)
     * 
     * @return array|boolean An array containing error information if there is
     * an error, FALSE otherwise.
     * Error Array Format: ['type' => string, 'value' => string]
     */
    public function initialize($instrument, $unit, $window)
    {
        $this->instrument = $instrument;

        // we'll set the definition here to lorem ipsum
        // TODO: but we'll eventually want to grab it from a db or something
        $this->definition = "QC Metrics for " . $instrument;
                            
        
        if($unit == "days")
        {
            $this->startdate = date("Y-m-d", strtotime("-" . $window . "days"));
            $this->enddate = date("Y-m-d", time());
        }
        
        // TODO: actually figure out status. For now, just set it to "green"
        $this->status = "green";

        // Obtain the metric descriptions
        $this->db->select('Metric, Source, Category, Description, Short_Description');
        $this->db->order_by('SortKey');
        $query = $this->db->get('V_Dataset_QC_Metric_Definitions');

        $this->metricDescriptions = array();
        $this->metricCategories = array();
        $this->metricSources = array();

        // Populate the metric arrays
        foreach($query->result() as $row)
        {
	        $this->metricDescriptions[$row->Metric] = $row->Description;
    	    $this->metricCategories[$row->Metric] = $row->Category;
	        $this->metricSources[$row->Metric] = $row->Source;
        }


		return false;
		
		
        // attempt to get the latest data (retrieve just 1 row from V_Dataset_QC_Metrics_Export)
        $this->db->select();
        $this->db->where('Instrument', $instrument);
        $this->db->order_by('Acq_Time_Start', 'desc');
        $this->latestmetrics = $this->db->get('V_Dataset_QC_Metrics_Export', 1);


        // Check that the instrument even exists (did we get a result?)
        if($this->latestmetrics->num_rows() < 1)
        {
            return array("type" => "instrument", "value" => $instrument);
        }
        
        /*
        // Get a full list of the metric names and build a select statement
        foreach($this->latestmetrics->list_fields() as $field)
        {
            // exclude fields that aren't actually metrics
            $ignoredfields = array(
                                    "Instrument Group",
                                    "Instrument",
                                    "Acq_Time_Start",
                                    "Dataset_ID",
                                    "Dataset",
                                    "Dataset_Rating",
                                    "Dataset_Rating_ID",
                                    "Quameter_Job",
                                    "Quameter_Last_Affected",
                                    "SMAQC_Job",
                                    "Smaqc_Last_Affected",
                                    "QCDM_Last_Affected"
                                  );
            
            if(!in_array($field, $ignoredfields))
            {
                $this->metricnames[] = $field;
                $this->db->select_avg($field, "'" . $field . "'");
            }
        }
        
        
        // build the where clause to select averages only from the correct date/dataset range
        if($unit == "days")
        {
            $this->db->where('Acq_Time_start >=', $this->startdate);
            $this->db->where('Acq_Time_start <=', $this->enddate . 'T23:59:59.999');
        }
        else
        {
            $this->db->group_by('Acq_Time_Start');
            $this->db->order_by('Acq_Time_Start', 'desc');
            $this->db->limit($window);
        }
        

        $ratingIDExclusion = array(-5, -4, -3, -2, -1, 0, 1);
        $this->db->where_not_in('Dataset_Rating_ID', $ratingIDExclusion);

        $this->db->where('Instrument', $instrument);
        $this->averagedmetrics = $this->db->get('V_Dataset_QC_Metrics_Export');

		*/
		
		/*
        // get the std deviations of the metrics over the specified time period
        $this->stddevmetrics = array();

        if($unit == "days")
        {
            foreach($this->metricnames as $metric)
            {
                $this->db->select('STDEV(' . $metric . ') as stddev');
                $this->db->from("(
                                     SELECT " . $metric . "
                                     FROM V_Dataset_QC_Metrics_Export
                                     WHERE Instrument = '" . $instrument . "'
                                     AND Acq_Time_Start >= '" . $this->startdate . "'
                                     AND Acq_Time_Start <= '" . $this->enddate . "T23:59:59.999'
                                 ) as latest"
                );
                $this->stddevmetrics[$metric] = $this->db->get()->row()->stddev;
            }
        }
        else
        {
            foreach($this->metricnames as $metric)
            {
                $this->db->select('STDEV(' . $metric . ') as stddev');
                $this->db->from("(
                                     SELECT TOP " . $window . " " . $metric . "
                                     FROM V_Dataset_QC_Metrics_Export
                                     WHERE Instrument = '" . $instrument . "'
                                     AND " . $metric . " IS NOT NULL
                                     ORDER BY Acq_Time_Start DESC
                                 ) as latest"
                );
                
                $this->stddevmetrics[$metric] = $this->db->get()->row()->stddev;
            }
        }
    	*/
    	
        return FALSE; // no errors, so return false
    }    
}
?>
