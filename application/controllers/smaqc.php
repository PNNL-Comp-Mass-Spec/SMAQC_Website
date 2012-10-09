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

    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('url');
        
        $this->defaultstartdate = date("m-d-Y", strtotime("-4 months"));
        $this->defaultenddate   = date("m-d-Y", time());
        $this->metriclist       = array();
        $this->instrumentlist   = array();
        $this->datasetfilter    = '';
    
        // get a full list of the metric names
        foreach($this->db->list_fields('V_Dataset_QC_Metrics') as $field)
        {
            // exclude fields that aren't actually metrics
            $ignoredfields = array(
                                    "Instrument Group",
                                    "Instrument",
                                    "Acq_Time_Start",
                                    "Dataset_ID",
                                    "Dataset",
                                    "Quameter_Job",
                                    "Quameter_Last_Affected",
                                    "SMAQC_Job",
                                    "Smaqc_Last_Affected"
                                  );
                                  
            if(!in_array($field, $ignoredfields))
            {
                $this->metriclist[] = $field;
            }
        }
    
        // get a full list of the instruments
        $this->db->select('Instrument');
        $this->db->distinct();
        $this->db->order_by("Instrument", "asc");
        $result = $this->db->get('V_Dataset_QC_Metrics')->result();
        
        $instrumentlist = array();
        
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

    public function instrument($name, $metric = NULL, $start = NULL, $end = NULL, $windowsize = NULL, $datasetfilter = NULL)
    {
        // if name is empty, redirect to home
        if(empty($name))
        {
            redirect(site_url());
            return;
        }
        
        $data['title'] = $name;
        $data['instrument'] = $name;
        $data['datasetfilter'] = $datasetfilter;
  
        $data['metriclist'] = $this->metriclist;
        $data['instrumentlist'] = $this->instrumentlist;
  
        if(empty($start) or empty($end))
        {
            $startdate = date("m-d-Y", strtotime("-2 months"));
            $enddate = date("m-d-Y", time());
        }
        else
        {
            /* strtotime doesn't parse dates properly that use -'s, as it
               thinks it's a european date. So, we're going to replace all -'s
               with /'s */
            $start = str_replace('-', '/', $start);
            $end = str_replace('-', '/', $end);

            $start = strtotime($start);
            $end = strtotime($end);

            // check to see if the dates were malformed (not valid)
            if(($start === FALSE) || ($end === FALSE))
            {
              $startdate = date("m-d-Y", strtotime("-2 months"));
              $enddate = date("m-d-Y", time());
            }
            else
            {
              $startdate = date("m-d-Y", $start);
              $enddate = date("m-d-Y", $end);
            }
        }

        $data['startdate'] = $startdate;
        $data['enddate']   = $enddate;

		// see if we need to use the default windowsize
		if(!is_numeric($windowsize) || $windowsize < 1)
		{
			$windowsize = $this->DEFAULTWINDOWSIZE;
		}
		else
		{
			// if they somehow entered a float, cut off the decimal
			$windowsize = (int)$windowsize;
		}

		$data['windowsize'] = $windowsize;

        // see if a specific metric was asked for
        if($metric == "all" or empty($metric))
        {
            $metric = NULL;
            
            $this->load->model('Instrumentmodel','',TRUE);
            
            $error = $this->Instrumentmodel->initialize(
                $name,
                $startdate,
                $enddate
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
            $data['definition']          = $this->Instrumentmodel->definition;
            
            $data['includegraph'] = FALSE;
        }
        else
        {
            $this->load->model('Metricmodel', '', TRUE);

            $error = $this->Metricmodel->initialize(
                $name,
                $metric,
                $startdate,
                $enddate,
				$windowsize,
				$datasetfilter
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
        
            $data['metric']     = $metric;
            $data['title']      = $data['title'] . ' - ' . $metric;
            $data['metrics']    = $this->Metricmodel->data;
            $data['definition'] = $this->Metricmodel->definition;
            $data['plotdata']         = $this->Metricmodel->plotdata;
            $data['plotdata_average'] = $this->Metricmodel->plotdata_average;
            $data['stddevupper']      = $this->Metricmodel->stddevupper;
            $data['stddevlower']      = $this->Metricmodel->stddevlower;
            $data['metric_units']     = $this->Metricmodel->metric_units;
            
            $data['includegraph'] = TRUE;
        }

        // load the views
        $this->load->view('headView.php', $data);
        $this->load->view('leftMenuView', $data);

        // Disabled in April 2012 since not needed: 
        // $this->load->view('topMenuView' , $data);

        if(empty($metric))
        {
            $this->load->view('instrumentView', $data);
        }
        else
        {
            $this->load->view('metricView', $data);
        }

        $this->load->view('footView.php', $data);
    }
  
    public function invaliditem($requesteditemtype = NULL, $name = NULL)
    {
        $data['title']      = " SMAQC ";
        $data['startdate']  = $this->defaultstartdate;
        $data['enddate']    = $this->defaultenddate;
        
        $data['includegraph'] = FALSE;
    
        $data['metriclist']     = $this->metriclist;
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
