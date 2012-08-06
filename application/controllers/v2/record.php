<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Record extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if ($_SERVER['SERVER_NAME'] == 'localhost')
        {
            $this->output->enable_profiler(TRUE);
        }
    }

    public function index()
    {

    }

    /**
     * Record that an generated image was viewed
     * @param string $client_image
     * @param string $campaign_image
     * @param string $receipient_image
     */
    public function image($client_image, $campaign_image, $receipient_image)
    {
        $this->durl_model->record_image_load($client_image, $campaign_image, $receipient_image);

        $this->load->view('image');
    }

}

/* End of file record.php */
/* Location: ./application/controllers/v2/record.php */