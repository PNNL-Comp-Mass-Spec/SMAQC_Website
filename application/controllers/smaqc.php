<?php
/**
 * smaqc.php
 *
 * File containing the default CodeIgniter controller for SMAQC.
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @author Aaron Cain
 * @version 1.0
 * @copyright TODO
 * @license TODO
 * @package SMAQC
 * @subpackage controllers
 */
 
/**
 * CodeIgniter model for a SMAQC metric
 * 
 * @author Trevor Owen <trevor.owen@email.wsu.edu>
 * @author Aaron Cain
 * @version 1.0
 *
 * @package SMAQC
 * @subpackage controllers
 */
class Smaqc extends CI_Controller
{
    var $defaultstartdate;
    var $defaultenddate;
	var $DEFAULTWINDOWSIZE = 45;
    var $DEFAULTUNIT = "days";

    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('url');
        
        $this->defaultstartdate = date("m-d-Y", strtotime("-4 months"));
        $this->defaultenddate   = date("m-d-Y", time());
        $this->metriclist       = array();
        $this->metricShortDescription = array();
        $this->instrumentlist   = array();
        $this->datasetfilter    = '';
    
        // get a full list of the metric names
        foreach($this->db->list_fields('V_Dataset_QC_Metrics_Export') as $field)
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
                                    "PSM_Source_Job",
                                    "Smaqc_Last_Affected",
									"QCDM_Last_Affected"
                                  );
                                  
            if(!in_array($field, $ignoredfields))
            {
                $this->metriclist[] = $field;
            }
        }
    
        
        // Get the Short Description for each metric
        $this->db->select('Metric, Short_Description');
        $this->db->order_by("Metric", "asc");
        $result = $this->db->get('V_Dataset_QC_Metric_Definitions')->result();
        
        foreach($result as $row)
        {
            $this->metricShortDescription[$row->Metric] = $row->Short_Description;
        }

        // get a full list of the instruments
        $this->db->select('Instrument');
        $this->db->distinct();
        $this->db->order_by("Instrument", "asc");
        $result = $this->db->get('V_Dataset_QC_Metric_Instruments')->result();
        
        foreach($result as $row)
        {
            $this->instrumentlist[] = $row->Instrument;
        }
    }

    function index()
    {
        $data['title']          = " SMAQC ";
        $data['startdate']      = $this->defaultstartdate;
        $data['enddate']        = $this->defaultenddate;
        $data['metriclist']     = $this->metriclist;
        $data['metricShortDescription'] = $this->metricShortDescription;
        $data['instrumentlist'] = $this->instrumentlist;
        $data['windowsize']     = $this->DEFAULTWINDOWSIZE;
        $data['datasetfilter']  = $this->datasetfilter;
        $data['includegraph']   = FALSE;

        $this->load->view('headView.php', $data);
        $this->load->view('leftMenuView', $data);
        $this->load->view('topMenuView' , $data);
        $this->load->view('mainView'    , $data);
        $this->load->view('footView.php', $data);
    }

    public function instrument()
    {
    	// Display list of QC metric names and descriptions
    	// Example URL:      http://prismsupport.pnl.gov/smaqc/index.php/smaqc/instrument/VOrbiETD04
 	    // auto-expanded to  http://prismsupport.pnl.gov/smaqc/index.php/smaqc/instrument/VOrbiETD04/window/45/unit/datasets

        // Required URL parameters:
        // instrument: the name of the instrument

        // Optional URL parameters:
        // window: window size for calculating average and standard deviation
        // unit: days or datasets (for the window)
        // filterDS: used to select datasets based on a SQL 'LIKE' match
        // ignoreDS: used to exclude datasets based on a SQL 'LIKE' match

        $needRedirect = FALSE;  // use this variable to redirect to new URL if default parameters are used
        
        // use an array of defaults for the uri-to-assoc() call, if not supplied in the URI, the value will be set to FALSE
        $defaultURI = array('instrument', 'window', 'unit');

        $URI_array = $this->uri->uri_to_assoc(2, $defaultURI);

        $includedDatasets = array();
        $excludedDatasets = array();

        // make sure user supplied an instrument name, redirect to home page if not
        if($URI_array["instrument"] === FALSE)
        {
            redirect(site_url());
        }

        //TODO: check for valid instrument name (is it in the DB?)

        // set default window size if need be
        if($URI_array["window"] === FALSE)
        {
            $needRedirect = TRUE;
            $URI_array["window"] = $this->DEFAULTWINDOWSIZE;
        }

        // set default unit if need be
        if($URI_array["unit"] === FALSE)
        {
            $needRedirect = TRUE;
            $URI_array["unit"] = "datasets";
        }

        // get the filter list if supplied
        if(!empty($URI_array["filterDS"]))
        {
            $includedDatasets = explode(",", $URI_array["filterDS"]);
            //TODO: add WHERE LIKE to query
        }

        // get the ignore list if supplied
        if(!empty($URI_array["ignoreDS"]))
        {
            $excludedDatasets = explode(",", $URI_array["ignoreDS"]);
            //TODO: add WHERE NOT LIKE to query
        }

        // redirect if default values are to be used
        if($needRedirect)
        {
            redirect('smaqc/' . $this->uri->assoc_to_uri($URI_array));
        }

        // set the data that we will have access to in the view
        $data['title'] = $URI_array["instrument"];
        $data['instrument'] = $URI_array["instrument"];
        $data['datasetfilter'] = $includedDatasets;
        $data['datasetignore'] = $excludedDatasets;
  
        $data['metriclist'] = $this->metriclist;
        $data['metricShortDescription'] = $this->metricShortDescription;        
        $data['instrumentlist'] = $this->instrumentlist;

        $data['unit'] = $URI_array["unit"];

        // remove these later
        $data['startdate'] = $this->defaultstartdate;
        $data['enddate']   = $this->defaultenddate;

        $data['windowsize'] = (int)$URI_array["window"];

        $this->load->model('Instrumentmodel','',TRUE);
            
        $error = $this->Instrumentmodel->initialize(
            $URI_array["instrument"],
            $data['unit'],
            $data['windowsize']
        );
    
        if($error)
        {
            $redirecturlparts = array(
                "smaqc",
                "invaliditem",
                $error["type"],
                $error["value"]
            );
                                     
            redirect(site_url(join('/', $redirecturlparts)));
        }
    
        $data['metricnames']         = $this->Instrumentmodel->metricnames;
        $data['metricDescriptions']  = $this->Instrumentmodel->metricDescriptions;
        $data['metricCategories']    = $this->Instrumentmodel->metricCategories;
        $data['metricSources']       = $this->Instrumentmodel->metricSources;
        $data['latestmetrics']       = $this->Instrumentmodel->latestmetrics;
        $data['averagedmetrics']     = $this->Instrumentmodel->averagedmetrics;
        $data['stddevmetrics']       = $this->Instrumentmodel->stddevmetrics;
        $data['definition']          = $this->Instrumentmodel->definition;
        
        $data['includegraph'] = FALSE;

        // load the views
        $this->load->view('headView', $data);
        $this->load->view('instrumentView', $data);
    }

    public function metric()
    {
    	// Plot the given metric vs. time
        // Example URL:      http://prismsupport.pnl.gov/smaqc/index.php/smaqc/metric/C_1A/inst/VOrbiETD04
        // auto-expanded to  http://prismsupport.pnl.gov/smaqc/index.php/smaqc/metric/C_1A/inst/VOrbiETD04/from/08-02-2015/to/12-02-2015/window/45/unit/datasets

        // Required URL parameters:
        // metric: the name of the metric
        // instrument: the name of the instrument

        // Required With Defaults:
        // from: the beginning date for selecting datasets
        // to: the ending date for selecting datasets
        // window: window size for calculating average and standard deviation
        // unit: days or datasets (for the window)

        // Optional URL parameters:
        // filterDS: used to select datasets based on a SQL 'LIKE' match
        // ignoreDS: used to exclude datasets based on a SQL 'LIKE' match

        // use an array of defaults for the uri-to-assoc() call, if not supplied in the URI, the value will be set to FALSE
        $defaultURI = array('metric', 'inst', 'from', 'to', 'window', 'unit');

        $URI_array = $this->uri->uri_to_assoc(2, $defaultURI);

        $needRedirect = FALSE;

        $datasetFilter = "";
        $excludedDatasets = "";

        // make sure user supplied a metric name, redirect to home page if not
        if($URI_array["metric"] === FALSE)
        {
            redirect(site_url());
        }

        //TODO: check for valid metric name (is it in the DB?)

        // make sure user supplied an instrument name, redirect to home page if not
        if($URI_array["inst"] === FALSE)
        {
            redirect(site_url());
        }

        //TODO: check for valid instrument name (is it in the DB?)

        // set default from and to dates if need be
        if($URI_array["from"] === FALSE or $URI_array["to"] === FALSE)
        {
            $needRedirect = TRUE;
            $URI_array["from"] = $this->defaultstartdate;
            $URI_array["to"]   = $this->defaultenddate;
        }

        // set default window size if need be
        if($URI_array["window"] === FALSE)
        {
            $needRedirect = TRUE;
            $URI_array["window"] = $this->DEFAULTWINDOWSIZE;
        }

        // set default unit if need be
        if($URI_array["unit"] === FALSE)
        {
            $needRedirect = TRUE;
            $URI_array["unit"] = "datasets";
        }

        // get the filter list if supplied
        if(!empty($URI_array["filterDS"]))
        {
            $datasetFilter = $URI_array["filterDS"];            
        }

        // get the ignore list if supplied
        if(!empty($URI_array["ignoreDS"]))
        {
            $excludedDatasets = $URI_array["ignoreDS"];
            //TODO: add WHERE NOT LIKE to query
        }

        // redirect if default values are to be used
        if($needRedirect)
        {
            redirect('smaqc/' . $this->uri->assoc_to_uri($URI_array));
        }

        $data['title'] = $URI_array["inst"] . ' - ' . $URI_array["metric"];
        $data['metric']     = $URI_array["metric"];
        $data['instrument'] = $URI_array["inst"];
        $data['datasetfilter'] = $datasetFilter;
        $data['filterDS'] = $datasetFilter;
        $data['ignoreDS'] = $excludedDatasets;
  
        $data['metriclist'] = $this->metriclist;
        $data['metricShortDescription'] = $this->metricShortDescription;        
        $data['instrumentlist'] = $this->instrumentlist;

        $data['startdate'] = date("m-d-Y", strtotime(str_replace('-', '/', $URI_array["from"])));
        $data['enddate']   = date("m-d-Y", strtotime(str_replace('-', '/', $URI_array["to"])));

        $data['windowsize'] = (int)$URI_array["window"];

        $this->load->model('Metricmodel', '', TRUE);

        // TODO: add support for excluded datasets

        $error = $this->Metricmodel->initialize(
            $URI_array["inst"],
            $URI_array["metric"],
            $data['startdate'],
            $data['enddate'],
            $data['windowsize'],
            $datasetFilter
        );
    
        if($error)
        {
            $redirecturlparts = array(
                "smaqc",
                "invaliditem",
                $error["type"],
                $error["value"]
            );
                                     
            redirect(site_url(join('/', $redirecturlparts)));
        }

        
        $data['metrics']          = $this->Metricmodel->data;
        $data['definition']       = $this->Metricmodel->definition;
        $data['plotdata']         = $this->Metricmodel->plotdata;
        $data['plotDataBad']      = $this->Metricmodel->plotDataBad;
        $data['plotDataPoor']     = $this->Metricmodel->plotDataPoor;
        $data['plotdata_average'] = $this->Metricmodel->plotdata_average;
        $data['stddevupper']      = $this->Metricmodel->stddevupper;
        $data['stddevlower']      = $this->Metricmodel->stddevlower;
        $data['metric_units']     = $this->Metricmodel->metric_units;
            
        $data['includegraph'] = TRUE;

        // load the views
        $this->load->view('headView.php', $data);
        $this->load->view('metricView', $data);
    }

    public function qcart()
    {
    	// Plot the QC-ART value vs. time, including custom threshold lines
    	// Example URL:      http://prismsupport.pnl.gov/smaqc/index.php/smaqc/qcart/inst/VOrbi05
        // auto-expanded to  http://prismsupport.pnl.gov/smaqc/index.php/smaqc/qcart/inst/VOrbi05/from/08-02-2015/to/12-02-2015/window/45/unit/datasets

        // Required URL parameters:
        // instrument: the name of the instrument

        // Required With Defaults:
        // from: the beginning date for selecting datasets
        // to: the ending date for selecting datasets

        // Optional URL parameters:
        // filterDS: used to select datasets based on a SQL 'LIKE' match
        // ignoreDS: used to exclude datasets based on a SQL 'LIKE' match

        // use an array of defaults for the uri-to-assoc() call, if not supplied in the URI, the value will be set to FALSE
        $defaultURI = array('inst', 'from', 'to');

        $URI_array = $this->uri->uri_to_assoc(3, $defaultURI);

        $needRedirect = FALSE;

        $datasetFilter = "";
        $excludedDatasets = "";

        // make sure user supplied an instrument name, redirect to home page if not
        if($URI_array["inst"] === FALSE)
        {
            redirect(site_url() . "#InstNotDefined");
        }

        // set default from and to dates if need be
        if($URI_array["from"] === FALSE or $URI_array["to"] === FALSE)
        {
            $needRedirect = TRUE;
            $URI_array["from"] = $this->defaultstartdate;
            $URI_array["to"]   = $this->defaultenddate;
        }

        // get the filter list if supplied
        if(!empty($URI_array["filterDS"]))
        {
            $datasetFilter = $URI_array["filterDS"];            
        }

        // redirect if default values are to be used
        if($needRedirect)
        {
            redirect('smaqc/qcart/' . $this->uri->assoc_to_uri($URI_array));
        }

		// Note that metricplot.js is looking for a title of "QC-ART" to select the correct plot format for this data
        $data['title'] = $URI_array["inst"] . ' - QC-ART';
        $data['metric']     = 'QCART';
        $data['instrument'] = $URI_array["inst"];
        $data['datasetfilter'] = $datasetFilter;
        $data['filterDS'] = $datasetFilter;
        $data['instrumentlist'] = $this->instrumentlist;

        $data['startdate'] = date("m-d-Y", strtotime(str_replace('-', '/', $URI_array["from"])));
        $data['enddate']   = date("m-d-Y", strtotime(str_replace('-', '/', $URI_array["to"])));

        $this->load->model('QCArtModel', '', TRUE);

        // TODO: add support for excluded datasets

        $error = $this->QCArtModel->initialize(
            $URI_array["inst"],
            'QCART',
            $data['startdate'],
            $data['enddate'],
            $datasetFilter
        );
    
        if($error)
        {
            $redirecturlparts = array(
                "qcart",
                "invaliditem",
                $error["type"],
                $error["value"]
            );
                                     
            redirect(site_url(join('/', $redirecturlparts)));
        }

        
        $data['metrics']          = $this->QCArtModel->data;
        $data['definition']       = $this->QCArtModel->definition;
        $data['plotdata']         = $this->QCArtModel->plotdata;
        $data['plotDataBad']      = $this->QCArtModel->plotDataBad;
        $data['plotDataPoor']     = $this->QCArtModel->plotDataPoor;
        $data['plotdata_average'] = $this->QCArtModel->plotdata_average;
        $data['stddevupper']      = $this->QCArtModel->stddevupper;
        $data['stddevlower']      = $this->QCArtModel->stddevlower;
        $data['metric_units']     = $this->QCArtModel->metric_units;
            
        $data['includegraph'] = TRUE;

        // load the views
        $this->load->view('headViewQCArt.php', $data);
        $this->load->view('qcartView', $data);
    }
  
    public function invaliditem($requesteditemtype = NULL, $name = NULL)
    {
        $data['title']      = " SMAQC ";
        $data['startdate']  = $this->defaultstartdate;
        $data['enddate']    = $this->defaultenddate;
        
        $data['includegraph'] = FALSE;
    
        $data['metriclist']     = $this->metriclist;
        $data['metricShortDescription']     = $this->metricShortDescription;        
        $data['instrumentlist'] = $this->instrumentlist;
    
        $msg = "The requested #' does not exist.";
        
        if(($requesteditemtype == "instrument") && !empty($name))
        {
            $data['message'] = str_replace("#", "instrument '" . $name, $msg);
        }
        else if(($requesteditemtype == "metric") && !empty($name))
        {
            $data['message'] = str_replace("#", "metric '" . $name, $msg);
        }
        else
        {
            $data['message'] = "The page you requested was not found.";
        }

        $this->load->view('headView.php', $data);
        $this->load->view('leftMenuView', $data);
        $this->load->view('topMenuView', $data);
        $this->load->view('invaliditemView', $data);
        $this->load->view('footView.php', $data);
    }
}
?>
