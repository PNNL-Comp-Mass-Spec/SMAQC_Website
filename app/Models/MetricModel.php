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
     * The QCDM threshold for flagging data as bad
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
     * outside the date range being plotted (to allow for more accurate computation of median and MAD)
     * The x value is a unix timestamp, in seconds
     * The type is what is returned by a call to CI's Active Record db->get().
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
     * The y values are the metric values to plot orange because the QCDM value is past a threshold
     * @var string
     */
    private $plotDataPoor;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The median metric value across a moving window
     * @var string
     */
    private $plotdata_average;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The y value is the upper bound standard deviation value to plot
     * @var string
     */
    private $stddevupper;

    /**
     * A JSON encoded array of (x,y) values for jqplot to use.
     * The x value is a time/date in milliseconds.
     * The y value is the lower bound standard deviation value to plot
     * However, when the metric is QCDM, this array is used to track the threshold (limit) for in control vs. out of control
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
                return parent::__get($what); // Check base class BaseModel for member
        }
    }

    /*
     * Compute the median of the values in metricdata, limiting to date within the specified date range
     *
     * @param datetime $windowStartDate: The start date; unix date/time
     * @param datetime $windowEndDate:   The end date; unix date/time
     *
     * Returns the median or NULL if no data in the time range
     */
    function compute_windowed_median($windowStartDate, $windowEndDate)
    {
        $dataCount = count($this->metricdata);

        $dataInWindow = array();

        for($i = 0; $i < $dataCount; $i++)
        {
            if ($this->metricdata[$i][0] >= $windowStartDate && $this->metricdata[$i][0] <= $windowEndDate)
            {
                $dataInWindow[] = $this->metricdata[$i][1];
            }
        }

        if (count($dataInWindow) > 0)
            return $this->compute_median($dataInWindow);
        else
            return NULL;
    }

    /*
     * Old, unused Function
     *
     * Compute the standard deviation of the values in metricdata, limiting to date within the specified date range
     *
     * @param datetime $windowStartDate: The start date; unix date/time
     * @param datetime $windowEndDate:   The end date; unix date/time
     * @param float $avgInWindow:        Average of the values in the window; compute using compute_windowed_average prior to calling this function
     *
     * Returns the standard deviation or NULL if no data in the time range
     */
    /*
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
    */

     /*
     * Compute the median median of the values in $values
     *
     */
    function compute_median($values)
    {
        $count = count($values);
        $median = 0;

        switch ($count)
        {
            case 0:
                $median = 0;
                break;

            case 1:
                $median = $values[0];
                break;

            default:
                sort($values);

                $midpoint = intval($count / 2);

                if($count % 2 == 0) {
                    $median = ($values[$midpoint] + $values[$midpoint-1]) / 2;
                } else {
                    $median = $values[$midpoint];
                }

                break;
        }

        return $median;
    }

     /*
     * Compute the median absolute deviation (MAD) of the values in metricdata, limiting to date within the specified date range
     *
     * @param datetime $windowStartDate: The start date; unix date/time
     * @param datetime $windowEndDate:   The end date; unix date/time
     * @param float $medianInWindow:        Median of the values in the window; compute using compute_windowed_median prior to calling this function
     *
     * Returns the median absolute deviation or NULL if no data in the time range
     */
    function compute_windowed_mad($windowStartDate, $windowEndDate, $medianInWindow)
    {
        // Method described at http://en.wikipedia.org/wiki/Median_absolute_deviation

        $dataCount = count($this->metricdata);
        $median = 0;

        // $residuals holds the absolute value of the residuals (deviations) from medianInWindow
        $residuals = array();

        for($i = 0; $i < $dataCount; $i++)
        {
            if ($this->metricdata[$i][0] >= $windowStartDate && $this->metricdata[$i][0] <= $windowEndDate)
            {
                $residuals[] = abs($this->metricdata[$i][1] - $medianInWindow);
            }
        }

         $median = $this->compute_median($residuals);

         return $median;
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
        // Change the string format of the dates, as strtotime doesn't work right with -'s
        $start = str_replace('-', '/', $start);
        $end   = str_replace('-', '/', $end);

        // Windowradius is how many days to the left/right to average around
        $windowradius = (int)($windowsize / 2);

        if ($windowradius < 1)
            $windowradius = 1;

        // Set all the proper values
        $this->instrument = $instrument;
        $this->metric     = $metric;

        // Use a limit customized for the given instrument
        // Default to 0.25 if the instrument is not recognized
        $limit = 0.25;

        if(strstr($instrument,'Exact') !== FALSE)
        {
            $limit = 0.07;
        }
        if(strstr($instrument,'LTQ_2') !== FALSE || strstr($instrument,'LTQ_3') !== FALSE || strstr($instrument,'LTQ_4') !== FALSE || strstr($instrument,'LTQ_FB1') !== FALSE || strstr($instrument,'LTQ_ETD_1') !== FALSE)
        {
            // Old: $limit = 0.05;
            $limit = 0.1;
        }
        if(strstr($instrument,'LTQ_Orb') !== FALSE || strstr($instrument,'Orbi_FB1') !== FALSE || strstr($instrument,'LTQ_FT1') !== FALSE)
        {
            $limit = 0.23;
        }
        if(strstr($instrument,'VOrbi') !== FALSE || strstr($instrument,'VPro') !== FALSE || strstr($instrument,'External_Orb') !== FALSE)
        {
            $limit = 0.11;
            // Old:
            $limit = 0.2;
        }

        $this->unixstartdate  = strtotime($start);
        $this->unixenddate    = strtotime($end);

        // Set the query start date to $windowradius days prior to $start
        $this->querystartdate  = date("Y-m-d", strtotime('-' . $windowradius . ' day', $this->unixstartdate));
        $this->queryenddate    = date("Y-m-d", strtotime(      $windowradius . ' day', $this->unixenddate));

        $this->datasetfilter  = $datasetfilter;

        // Check to see that this is a valid instrument/metric
        $builder = $this->db->table('v_dataset_qc_metrics_export');
        $builder->where('instrument', $instrument);

        $query = $builder->get(1);

        if($query->getNumRows() < 1)
        {
            return array("type" => "instrument", "value" => $instrument);
        }

        if(!$this->db->fieldExists($metric, 'v_dataset_qc_metrics_export'))
        {
            return array("type" => "metric", "value" => $metric);
        }

        // Lookup the Description, purpose, units, and Source for this metric
        $builder = $this->db->table('v_dataset_qc_metric_definitions');
        $builder->select('description, purpose, units, source');
        $builder->where('metric', $metric);
        $query = $builder->get(1);

        if($query->getNumRows() < 1)
        {
            $this->definition = $metric . " (definition not found in DB)";
        }
        else
        {
            if(strstr($metric,'qcdm') !== FALSE)
            {
                // QCDM metric
                if(strstr($instrument,'Exact') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->source . "): " . $row->description . " <br>" . $row->purpose . "Metrics used: MS1_TIC_Q2, MS1_Density_Q1";
                }
                if(strstr($instrument,'LTQ_2') !== FALSE || strstr($instrument,'LTQ_3') !== FALSE || strstr($instrument,'LTQ_4') !== FALSE || strstr($instrument,'LTQ_FB1') !== FALSE || strstr($instrument,'LTQ_ETD_1') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->source . "): " . $row->description . " <br>" . $row->purpose . "Metrics used: XIC_WideFrac, MS2_Density_Q1, P_2C";
                }
                if(strstr($instrument,'LTQ_Orb') !== FALSE || strstr($instrument,'Orbi_FB1') !== FALSE || strstr($instrument,'LTQ_FT1') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->source . "): " . $row->description . " <br>" . $row->purpose . "Metrics used: XIC_WideFrac, MS1_TIC_Change_Q2, MS1_Density_Q1, MS1_Density_Q2, DS_2A, P_2B, P_2A, DS_2B";
                }
                if(strstr($instrument,'VOrbi') !== FALSE || strstr($instrument,'VPro') !== FALSE || strstr($instrument,'External_Orb') !== FALSE)
                {
                    $row = $query->getRow();
                    $this->definition = $metric . " (" . $row->source . "): " . $row->description . " <br>" . $row->purpose . "Metrics used: XIC_WideFrac, MS2_Density_Q1, MS1_2B, P_2B, P_2A, DS_2B";
                }
                $this->metric_units =$row->units;
            }
            else
            {
                $row = $query->getRow();
                $this->definition = $metric . " (" . $row->source . "): " . $row->description . " <br>" . $row->purpose;

                $this->metric_units =$row->units;
            }
        }

        // Build the query to get all the metric points in the specified range, for example
        // SELECT dataset_id,
        //        acq_time_start,
        //        ms1_1,
        //        dataset_rating,
        //        dataset,
        //        quameter_job,
        //        smaqc_job,
        //        quameter_last_affected,
        //        smaqc_last_affected,
        //        dataset_rating_id,
        //        qcdm
        // FROM v_dataset_qc_metrics_export
        // WHERE instrument = 'Exploris04' AND
        //       acq_time_start >= '2025-02-02' AND
        //       acq_time_start <= '2025-06-03' AND
        //       ms1_1 IS NOT NULL
        // ORDER BY acq_time_start DESC;

        $columns = array(
                         'acq_time_start',
                         'dataset_id',
                         'dataset',
                         'quameter_job',
                         'smaqc_job',
                         'quameter_last_affected',
                         'smaqc_last_affected',
                         'dataset_rating',
                         'dataset_rating_id',
                         $metric,
                         'qcdm'
                        );

        $builder = $this->db->table('v_dataset_qc_metrics_export');
        $builder->select(join(',', $columns));
        $builder->where('instrument =', $this->instrument);
        $builder->where('acq_time_start >=', $this->querystartdate);
        $builder->where('acq_time_start <=', $this->queryenddate . 'T23:59:59.999');
        $valueNotNull = $metric . " IS NOT NULL";
        $builder->where($valueNotNull);

        if (strlen($this->datasetfilter) > 0)
        {
            $builder->like('dataset', $this->datasetfilter);
        }

        $builder->orderBy('acq_time_start', 'desc');

        // Run the query, we may not actually need to store this in the model, but for now we will
        $this->data = $builder->get();

        // Initialize the data arrays so that we can append data
        $this->metricdata = array();
        $this->plotdata = array();
        $this->plotDataBad = array();           // Not Released (aka bad)
        $this->plotDataPoor = array();          // QCDM value out-of-range (aka low quality)

        $dateList = array();                    // List of dates for which metric data exists

        // Get just the data we want for plotting
        foreach($this->data->getResult() as $row)
        {
            // Skip the value if it's null (no longer necessary in June 2025 since we now filter out null values using the where clause)
            if(is_null($row->$metric))
            {
                continue;
            }

            // Need to convert the date from the mssql format to one that jqplot will like

            // Cutoff fractional seconds, leaving only the date data we want
            $pattern = '/:[0-9][0-9][0-9]/';
            $date = preg_replace($pattern, '', $row->acq_time_start);

            $date = strtotime($date);

            $datasetIsBad = 0;

            if(strstr($row->dataset,'QC_Shew') !== FALSE)
            {
                // QC_Shew dataset
                if ($row->qcdm > $limit)
                {
                    $datasetIsBad = 1;
                }
            }

            if ($row->dataset_rating_id >= -5 && $row->dataset_rating_id <= 1)
            {
                $datasetIsBad = 2;
            }

            if ($datasetIsBad == 0 || $datasetIsBad == 1)
            {
                // Add the value to the metricdata array
                $this->metricdata[] = array($date, $row->$metric);
            }

            // Add the value to the plotdata array if it is within the user-specified plotting range
            if ($date >= $this->unixstartdate && $date <= $this->unixenddate)
            {
                if ($datasetIsBad != 0)
                {
                    if($datasetIsBad == 1)
                    {
                        // Dataset with poor QCDM score
                        // JavaScript likes milliseconds, so multiply $date by 1000 when appending to the array
                        $this->plotDataPoor[] = array($date * 1000, $row->$metric, $row->dataset);
                    }
                    if($datasetIsBad == 2)
                    {
                        // Not Released dataset
                        // JavaScript likes milliseconds, so multiply $date by 1000 when appending to the array
                        $this->plotDataBad[] = array($date * 1000, $row->$metric, $row->dataset);
                    }
                }
                else
                {
                    // JavaScript likes milliseconds, so multiply $date by 1000 when appending to the array
                    $this->plotdata[] = array($date * 1000, $row->$metric, $row->dataset);
                }

                // Append to $dateList if a new date
                // First round $date to the midnight of the given day
                $dateMidnight = strtotime("0:00", $date);
                if (count($dateList) == 0)
                {
                    // Data is returned from v_dataset_qc_metrics_export sorted descending
                    // Thus, add one day past $dateList so that the average and trend lines extend past the last data point
                    $dateList[] = strtotime('+1 day', $dateMidnight);
                    $dateList[] = $dateMidnight;
                }
                else {
                    if ($dateList[count($dateList)-1] != $dateMidnight)
                        $dateList[] = $dateMidnight;
                }
            }
        }

        $this->plotdata_average = array();
        $this->stddevupper = array();
        $this->stddevlower = array();

        $s0 = count($dateList);

        // Calculate median absolute deviation using the provided window size
        if($s0 > 0)
        {
            $medianInWindow = 0.0;
            $mad = 0.0;

            // Uncomment to debug
            // echo "Date, MedianInWindow, MAD, LowerBoundMAD, UpperBoundMAD<br>";

            for($dateIndex = 0; $dateIndex < $s0; $dateIndex++)
            {
                if(strstr($metric, 'qcdm') !== FALSE)
                {
                    // The metric is QCDM
                    // Use a limit customized for the given instrument

                    // Javascript likes milliseconds, so multiply $date by 1000 when appending to the array
                    $this->stddevlower[] = array(
                        $dateList[$dateIndex] * 1000,
                        $limit
                        );

                    continue;
                }

                // The metric is not QCDM
                // Compute the median value within a time period

                // Get the date to the left by the window radius
                $sqlDateTimeLeftUnix = strtotime('-' . $windowradius . ' day', $dateList[$dateIndex]);

                // Get the date to the right by the window radius
                $sqlDateTimeRightUnix = strtotime($windowradius . ' day', $dateList[$dateIndex]);

                // Compute the median of the metric values over the date range (using both good and "low quality" datasets)
                $medianInWindow = $this->compute_windowed_median($sqlDateTimeLeftUnix, $sqlDateTimeRightUnix);

                if (is_null($medianInWindow))
                    continue;

                // Javascript likes milliseconds, so multiply $date by 1000 when appending to the array
                $this->plotdata_average[] = array(
                    $dateList[$dateIndex] * 1000,
                    $medianInWindow
                    );

                // Compute the median absolute deviation over the date range
                $mad = $this->compute_windowed_mad($sqlDateTimeLeftUnix, $sqlDateTimeRightUnix, $medianInWindow);

                $lowerBoundMAD = $medianInWindow - (1.5 * $mad);
                $upperBoundMAD = $medianInWindow + (1.5 * $mad);

                if ($lowerBoundMAD < 0)
                    $lowerBoundMAD = 0;

                // Javascript likes milliseconds, so multiply $date by 1000 when appending to the array
                $this->stddevlower[] = array(
                    $dateList[$dateIndex] * 1000,
                    $lowerBoundMAD
                    );

                $this->stddevupper[] = array(
                    $dateList[$dateIndex] * 1000,
                    $upperBoundMAD
                    );

                // Uncomment to debug
                // echo date('m/d/Y H:i:s', $dateList[$dateIndex]) . ", " . $medianInWindow . ", " . $mad . ", " . $lowerBoundMAD . ", " . $upperBoundMAD . "<br>";

            } // End of for loop
        } // End of calculating stddev

        // Check to see if there were any data points in the date range
        if(count($this->plotdata) < 1)
        {
            // Put an empty array in there so that jqplot will display properly,
            // and not break JavaScript on the page
            $this->plotdata[] = array();
        }

        if(count($this->plotDataBad) < 1)
        {
            // Put an empty array in there so that jqplot will display properly,
            // and not break JavaScript on the page
            $this->plotDataBad[] = array();
        }

        if(count($this->plotDataPoor) < 1)
        {
            // Put an empty array in there so that jqplot will display properly,
            // and not break JavaScript on the page
            $this->plotDataPoor[] = array();
        }

        // Put everything for jqplot into a json encoded array
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
        $this->db->where('acq_time_start >=', $this->startdate);
        $this->db->where('acq_time_start <=', $this->enddate . 'T23:59:59.999');
        $this->db->where('instrument', $instrument);
        $this->average = $this->db->get('v_dataset_qc_metrics_export')->row()->avg;
        */

        return FALSE; // No errors, so return false
    }
}
?>
