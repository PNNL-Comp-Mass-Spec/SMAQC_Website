<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * InstrumentModel.php
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
class InstrumentModel extends Model
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
     * The contructor for InstrumentModel simply calls the constructor for the
     * base class (CI_Model). All initialization of the class must be done
     * using the initialize function. The reasoning for this has to do with the
     * way CI uses loads models (they cannot take arguments).
     *
     * @return InstrumentModel
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
                return parent::__get($what); // check base class BaseModel for member
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
    public function init($instrument, $unit, $window)
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
        $builder = $this->db->table('V_Dataset_QC_Metric_Definitions');
        $builder->select('metric, source, category, description, short_description');
        $builder->orderBy('sort_key');
        $query = $builder->get();

        $this->metricDescriptions = array();
        $this->metricCategories = array();
        $this->metricSources = array();

        // Populate the metric arrays
        foreach($query->getResult() as $row)
        {
            $this->metricDescriptions[$row->metric] = $row->description;
            $this->metricCategories[$row->metric] = $row->category;
            $this->metricSources[$row->metric] = $row->source;
        }

        return false;

        // attempt to get the latest data (retrieve just 1 row from V_Dataset_QC_Metrics_Export)
        $builder = $this->db->table('V_Dataset_QC_Metrics_Export');
        $builder->select();
        $builder->where('instrument', $instrument);
        $builder->orderBy('acq_time_start', 'desc');
        $this->latestmetrics = $builder->get(1);

        // Check that the instrument even exists (did we get a result?)
        if($this->latestmetrics->getNumRows() < 1)
        {
            return array("type" => "instrument", "value" => $instrument);
        }

        return FALSE; // no errors, so return false
    }
}
?>
