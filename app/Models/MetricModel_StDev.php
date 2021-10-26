<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * MetricModel.php
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
class MetricModel extends Model
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
     * The name of the metric.
     * @var string
     */
    private $limit;

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
     * An array of (x,y) values for the metric being plotted; includes data
     * outside the date range being plotted (to allow for more accurate computation of avg and stdev)
     * The x value is a unix timestamp, in seconds
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var string
     */
    private $metricdata;

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
    private $plotDataBad;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The type is what is returned by a call to CI's Active Record db->get().
     * @var string
     */
    private $plotDataPoor;

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
     * The contructor for MetricModel simply calls the constructor for the base
     * class (CI_Model). All initialization of the class must be done using the
     * initialize function. The reasoning for this has to do with the way CI
     * loads models in the controller (they cannot take arguments).
     *
     * @return MetricModel
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
                return parent::__get($what); // check base class BaseModel for member
        }
    }

    /*
     * Compute the average of the values in metricdata, limiting to date within the specified date range
     *
     * @param datetime $windowStartDate: The start date; unix date/time
     * @param datetime $windowEndDate:   The end date; unix date/time
     *
     * Returns the average or NULL if no data in the time range
     */
    function compute_windowed_average($windowStartDate, $windowEndDate)
    {
        $dataCount = count($this->metricdata);

        $sum = 0.0;
        $count = 0;

        for($i = 0; $i < $dataCount; $i++)
        {
            if ($this->metricdata[$i][0] >= $windowStartDate && $this->metricdata[$i][0] <= $windowEndDate)
            {
                $sum += $this->metricdata[$i][1];
                $count += 1;
            }
        }

        if ($count > 0)
            return $sum / $count;
        else
            return NULL;
    }

    /*
     * Compute the standard deviation of the values in metricdata, limiting to date within the specified date range
     *
     * @param datetime $windowStartDate: The start date; unix date/time
     * @param datetime $windowEndDate:   The end date; unix date/time
     * @param float $avgInWindow:        Average of the values in the window; compute using compute_windowed_average prior to calling this function
     *
     * Returns the standard deviation or NULL if no data in the time range
     */
    function compute_windowed_stdev($windowStartDate, $windowEndDate, $avgInWindow)
    {
        // This method of computing standard deviation is used in Microsoft Excel for the StDev() function
        // It is also listed on Wikipedia: http://en.wikipedia.org/wiki/Standard_deviation

        $dataCount = count($this->metricdata);

        // $sumSquares holds the sum of (x - average)^2
        $sumSquares = 0.0;
        $count = 0;

        $stdev = NULL;

        for($i = 0; $i < $dataCount; $i++)
        {
            if ($this->metricdata[$i][0] >= $windowStartDate && $this->metricdata[$i][0] <= $windowEndDate)
            {
                $sumSquares += pow($this->metricdata[$i][1] - $avgInWindow, 2);
                $count += 1;
            }
        }

        if ($count == 1)
        {
            $stdev = 0.0;
        }

        if ($count > 1)
        {
            // The standard deviation is the square root of $sumSquares divided by n-1
            $stdev = sqrt($sumSquares / ($count - 1));
        }

        return $stdev;
    }

    /**
     * Initializer for the Metric model
     *
     * Gets all of the needed values for the class/model from the database.
     * Calculates any values that need calculating.
     *
     * @param string $instrument The name of the instrument.
     * @param string $metric The name of the metric.
     * @param string $start A human readable string for the start of the date range. Assumed to be in m-d-Y format. (Example: 11-11-2011)
     * @param string $end A human readable string for the end of the date range. Assumed to be in m-d-Y format. (Example: 12-12-2012)
     * @param string $datasetfilter Optional dataset name filter
     *
     * @return array|boolean An array containing error information if there is
     * an error, FALSE otherwise.
     * Error Array Format: ['type' => string, 'value' => string]
     */
    public function init($instrument, $metric, $start, $end, $windowsize = 20, $datasetfilter = '')
    {
        // change the string format of the dates, as strtotime doesn't work
        // right with -'s
        $start = str_replace('-', '/', $start);
        $end   = str_replace('-', '/', $end);

        // windowradius is how many days to the left/right to average around
        $windowradius = (int)($windowsize / 2);

        // set all the proper values
        $this->instrument = $instrument;
        $this->metric     = $metric;

        // Gives the limit depending on the instrument that is being run
        if(strstr($instrument,'Exact') !== FALSE)
        {
            $limit = 0.07;
        }
        if(strstr($instrument,'LTQ_2') !== FALSE || strstr($instrument,'LTQ_3') !== FALSE || strstr($instrument,'LTQ_4') !== FALSE || strstr($instrument,'LTQ_FB1') !== FALSE || strstr($instrument,'LTQ_ETD_1') !== FALSE)
        {
            $limit = 0.05;
        }
        if(strstr($instrument,'LTQ_Orb') !== FALSE || strstr($instrument,'Orbi_FB1') !== FALSE || strstr($instrument,'LTQ_FT1') !== FALSE)
        {
            $limit = 0.23;
        }
        if(strstr($instrument,'VOrbi') !== FALSE || strstr($instrument,'VPro') !== FALSE || strstr($instrument,'External_Orb') !== FALSE)
        {
            $limit = 0.11;
        }

        $this->unixstartdate  = strtotime($start);
        $this->unixenddate    = strtotime($end);

        // Set the query start date to $windowradius days prior to $start
        $this->querystartdate  = date("Y-m-d", strtotime('-' . $windowradius . ' day', $this->unixstartdate));
        $this->queryenddate    = date("Y-m-d", strtotime(      $windowradius . ' day', $this->unixenddate));

        $this->datasetfilter  = $datasetfilter;

        // check to see that this is a valid instrument/metric
        $builder = $this->db->table('V_Dataset_QC_Metrics_Export');
        $builder->where('Instrument', $instrument);

        $query = $builder->get(1);

        if($query->getNumRows() < 1)
        {
            return array("type" => "instrument", "value" => $instrument);
        }

        if(!$this->db->fieldExists($metric, 'V_Dataset_QC_Metrics_Export'))
        {
            return array("type" => "metric", "value" => $metric);
        }

        // Lookup the Description, purpose, units, and Source for this metric
        $builder = $this->db->table('V_Dataset_QC_Metric_Definitions');
        $builder->select('Description, Purpose, Units, Source');
        $builder->where('Metric', $metric);
        $query = $builder->get(1);

        if($query->getNumRows() < 1)
        {
            $this->definition = $metric . " (definition not found in DB)";
        }
        else
        {
            if(strstr($metric,'QCDM') !== FALSE)
            {
                if(strstr($instrument,'Exact') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->Source . "): " . $row->Description . "; " . $row->Purpose . "Metrics used: MS1_TIC_Q2, MS1_Density_Q1";
                }
                if(strstr($instrument,'LTQ_2') !== FALSE || strstr($instrument,'LTQ_3') !== FALSE || strstr($instrument,'LTQ_4') !== FALSE || strstr($instrument,'LTQ_FB1') !== FALSE || strstr($instrument,'LTQ_ETD_1') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->Source . "): " . $row->Description . "; " . $row->Purpose . "Metrics used: XIC_WideFrac, MS2_Density_Q1, P_2C";
                }
                if(strstr($instrument,'LTQ_Orb') !== FALSE || strstr($instrument,'Orbi_FB1') !== FALSE || strstr($instrument,'LTQ_FT1') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->Source . "): " . $row->Description . "; " . $row->Purpose . "Metrics used: XIC_WideFrac, MS1_TIC_Change_Q2, MS1_Density_Q1, MS1_Density_Q2, DS_2A, P_2B, P_2A, DS_2B";
                }
                if(strstr($instrument,'VOrbi') !== FALSE || strstr($instrument,'VPro') !== FALSE || strstr($instrument,'External_Orb') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->Source . "): " . $row->Description . "; " . $row->Purpose . "Metrics used: XIC_WideFrac, MS2_Density_Q1, MS1_2B, P_2B, P_2A, DS_2B";
                }
                $this->metric_units =$row->Units;
            }
            else
            {
                $row = $query->getRow();
                $this->definition = $metric . " (" . $row->Source . "): " . $row->Description . "; " . $row->Purpose;

                $this->metric_units =$row->Units;
            }
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

        $builder = $this->db->table('V_Dataset_QC_Metrics_Export');
        $builder->select(join(',', $columns));
        $builder->where('Instrument =', $this->instrument);
        $builder->where('Acq_Time_Start >=', $this->querystartdate);
        $builder->where('Acq_Time_Start <=', $this->queryenddate . 'T23:59:59.999');

        if (strlen($this->datasetfilter) > 0)
        {
            $builder->like('Dataset', $this->datasetfilter);
        }

        $builder->orderBy('Acq_Time_Start', 'desc');

        // run the query, we may not actually need to store this in the model,
        // but for now we will
        $this->data = $builder->get();

        // Initialize the data arrays so that we can append data
        $this->metricdata = array();
        $this->plotdata = array();
        $this->plotDataBad = array();
        $this->plotDataPoor = array();

        // get just the data we want for plotting
        foreach($this->data->getResult() as $row)
        {
            // skip the value if it's null
            // the reason ignoring nulls is not part of the query, is that CI
            // apparently has issues with that
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

            if ($row->QCDM > $limit)
            {
                $datasetIsBad = 1;
            }
            if ($row->Dataset_Rating_ID >= -5 && $row->Dataset_Rating_ID <= 1)
            {
                $datasetIsBad = 2;
            }

            if ($datasetIsBad == 0)
            {
                // add the value to the metricdata array
                $this->metricdata[] = array($date, $row->$metric);
            }

            // add the value to the plotdata array if it is within the user-specified plotting range
            if ($date >= $this->unixstartdate && $date <= $this->unixenddate)
            {
                if ($datasetIsBad != 0)
                {
                    if($datasetIsBad == 1)
                    {
                         // javascript likes milliseconds, so multiply $date by 1000
                        $this->plotDataPoor[] = array($date * 1000, $row->$metric, $row->Dataset);
                    }
                    if($datasetIsBad == 2)
                    {
                        // javascript likes milliseconds, so multiply $date by 1000
                        $this->plotDataBad[] = array($date * 1000, $row->$metric, $row->Dataset);
                    }
                }
                else
                {
                    // javascript likes milliseconds, so multiply $date by 1000
                    $this->plotdata[] = array($date * 1000, $row->$metric, $row->Dataset);
                }
            }
        }

        $this->plotdata_average = array();
        $this->stddevupper = array();
        $this->stddevlower = array();

        $s0 = count($this->plotdata);

        // calculate stddev using the provided window size
        if($s0 > 0)
        {
            $avg = 0.0;
            $stdev = 0.0;

            for($i = 0; $i < $s0; $i++)
            {
                // get the date to the left by the window radius
                $sqlDateTimeLeftUnix = strtotime('-' . $windowradius . ' day', $this->plotdata[$i][0]/1000);
                $sqlDateTimeLeft = date('Y-m-d H:i:s', $sqlDateTimeLeftUnix);

                // get the date to the right by the window radius
                $sqlDateTimeRightUnix = strtotime($windowradius . ' day', $this->plotdata[$i][0]/1000);
                $sqlDateTimeRight = date('Y-m-d H:i:s', $sqlDateTimeRightUnix);

                // get the average over the date range
                // This will just us the range even with the poor data sets//

                $this->db->select_avg($metric, 'avg');
                $this->db->where('Instrument', $instrument);
                $this->db->where('Acq_Time_Start >=', $sqlDateTimeLeft);
                $this->db->where('Acq_Time_Start <=', $sqlDateTimeRight);
                $avg = $this->db->get('V_Dataset_QC_Metrics_Export')->row()->avg;

                $this->plotdata_average[] = array(
                    $this->plotdata[$i][0],
                    $avg
                    );

                /*
                // Compute average via code
                //this just gets us the datasets that are good

                $avg = $this->compute_windowed_average($sqlDateTimeLeftUnix, $sqlDateTimeRightUnix);

                if (!is_null($avg))
                {
                    $this->plotdata_average[] = array(
                        $this->plotdata[$i][0],
                        $avg
                        );
                }
                */

                // get the standard deviation over the date range

                /*
                ** Could compute the standard deviation by querying the database, but this is very slow
                $this->db->select('STDEV(' . $metric . ') as stddev');
                $this->db->where('Instrument', $instrument);
                $this->db->where('Acq_Time_Start >=', $sqlDateTimeLeft);
                $this->db->where('Acq_Time_Start <=', $sqlDateTimeRight);
                $stddev = $this->db->get('V_Dataset_QC_Metrics')->row()->stddev;
                */

                if (!is_null($avg))
                {
                    if(strstr($metric,'QCDM') !== FALSE)
                    {
                        // Gives the limit depending on the instrument that is being run
                        $stddev = $this->compute_windowed_stdev($sqlDateTimeLeftUnix, $sqlDateTimeRightUnix, $avg);

                        $this->stddevlower[] = array(
                            $this->plotdata[$i][0],
                            $limit
                            );
                    }
                    else
                    {
                        // Compute the standard deviation via code
                        $stddev = $this->compute_windowed_stdev($sqlDateTimeLeftUnix, $sqlDateTimeRightUnix, $avg);

                        $this->stddevupper[] = array(
                            $this->plotdata[$i][0],
                            $avg + ($stddev)
                            );

                        $lowerBoundStDev = $avg - ($stddev);
                        if ($lowerBoundStDev < 0)
                            $lowerBoundStDev = 0;

                        $this->stddevlower[] = array(
                            $this->plotdata[$i][0],
                            $lowerBoundStDev
                            );
                    }
                }

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

        /* get the average (we'll use the select_avg() call for now, as it
           deals with nulls, but we may want to do this in php instead of using
           the db */
        /*
        ** Not used
        **
        $this->db->select_avg($metric, 'avg');
        $this->db->where('Acq_Time_Start >=', $this->startdate);
        $this->db->where('Acq_Time_Start <=', $this->enddate . 'T23:59:59.999');
        $this->db->where('Instrument', $instrument);
        $this->average = $this->db->get('V_Dataset_QC_Metrics')->row()->avg;
        */

        return FALSE; // no errors, so return false
    }
}
?>
