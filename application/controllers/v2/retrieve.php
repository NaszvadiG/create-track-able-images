<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Retrieve extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if ($_SERVER['SERVER_NAME'] == 'localhost')
        {
            //$this->output->enable_profiler(TRUE);
        }
    }

    public function index()
    {
        
    }

    /**
     * Give an API key, return the clients campaigns
     */
    public function campaigns()
    {
        
    }

    /**
     * Given an API key and a campaign title, return the campaign data such as open rates etc
     */
    public function campaign()
    {
        $received_data = json_decode(current($_POST), TRUE);

        //$received_data = array('api_key' => '', 'campaign_title' => ''); // for testing

        /*
         * the data that will be returned to the user
         */
        $campaign_data = array();

        $this->db->cache_on();

        if ($this->api_key_model->api_key_verified($received_data["api_key"])) // verify api key
        {
            $this->db->cache_off();

            $campaign_title = $received_data["campaign_title"];

            if ($campaign_title != '')
            {
                $campaign_data['campaign_title'] = $campaign_title;
                $campaign_data['distinct_opens'] = array();

                $campaign_client_and_capaign_images = $this->durl_model->client_and_campaign_image($received_data["api_key"], $campaign_title);

                if (!empty($campaign_client_and_capaign_images))
                {
                    $client_image = $campaign_client_and_capaign_images["client_image"];
                    $campaign_image = $campaign_client_and_capaign_images["campaign_image"];
                    $campaign_data['distinct_opens'] = $this->durl_model->campaign_distinct_opens($client_image, $campaign_image);
                }
            }
            else
            {
                /**
                 * missing campaign title
                 */
                if ($campaign_title == '')
                {
                    $output = array('status' => 'error', 'message' => 'missing campaign title');
                }
            }
        }
        else
        {
            /*
             * Invalid api key
             */
            $output = array('status' => 'error', 'message' => 'missing or invalid api key');
        }

        $data["output"] = $campaign_data;

        $this->load->view('output', $data);
    }

}

/* End of file Retrieve.php */
/* Location: ./application/controllers/v2/Retrieve.php */