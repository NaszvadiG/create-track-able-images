<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Create extends CI_Controller
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
     * receive the data needed to create a unique image for a recipient
     */
    public function image()
    {
        $output = array();

        $received_data = json_decode(current($_POST), TRUE);

        $this->db->cache_on();

        if ($this->api_key_model->api_key_verified($received_data["api_key"])) // verify api key
        {
            $this->db->cache_off();

            if ($received_data["campaign_id"] != '' && $received_data["recipient"] != '') // verify campaign id and recipient
            {
                /*
                 * the received data is fine, create an image
                 */
                $output = array(
                    'status' => 'success',
                    'image' => $this->durl_model->create_image($received_data["api_key"], $received_data["campaign_id"], $received_data["campaign_title"], $received_data["recipient"])
                );
            }
            else
            {
                /**
                 * missing campaign_id or recipient
                 */
                if ($received_data["campaign_id"] == '')
                {
                    $output = array('status' => 'error', 'message' => 'missing campaign id');
                }

                if ($received_data["recipient"] == '')
                {
                    $output = array('status' => 'error', 'message' => 'missing recipient');
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

        $data["output"] = array_merge($received_data, $output);

        $this->load->view('output', $data);
    }

    /**
     * receive the data needed to create a unique image for an array of recipients
     */
    public function image_multiple()
    {
        $output = array();

        $received_data = json_decode(current($_POST), TRUE);

        $this->db->cache_on();

        if ($this->api_key_model->api_key_verified($received_data["api_key"])) // verify api key
        {
            $this->db->cache_off();

            if ($received_data["campaign_id"] != '' && is_array($received_data["recipients_array"]) && !empty($received_data["recipients_array"])) // verify campaign id and recipients_array
            {
                /*
                 * the received data is fine, create several images
                 */
                foreach ($received_data["recipients_array"] as $recipient)
                {
                    $output[] = array(
                        'recipient' => $recipient,
                        'status' => 'success',
                        'image' => $this->durl_model->create_image($received_data["api_key"], $received_data["campaign_id"], $received_data["campaign_title"], $recipient)
                    );
                }
            }
            else
            {
                /**
                 * missing campaign_id or recipient
                 */
                if ($received_data["campaign_id"] == '')
                {
                    $output = array('status' => 'error', 'message' => 'missing campaign id');
                }

                if (empty($received_data["recipients_array"]))
                {
                    $output = array('status' => 'error', 'message' => 'missing recipients');
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

        unset($received_data["recipients_array"]);

        $data["output"] = array_merge($output, $received_data);

        $this->load->view('output', $data);
    }

    /**
     * Use curl to test the image function
     */
    public function test_image($limit = '1')
    {
        for ($test = 1; $test <= $limit; $test++)
        {
            $test_data = array(
                'api_key' => 'abC45dGfg3',
                'campaign_id' => 'my campaign id',
                'campaign_title' => 'my first campaign',
                'recipient' => 'joe' . $test . 'bloggs.com'
            );

            $test_data = array(
                'data' => json_encode($test_data)
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/domain.com/index.php/v2/create/image');
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            echo $response;
            //echo "code : $httpCode<br />\n";
        }
        //echo "finished in " . (time() - $start) . " seconds<br>\n";
    }

    /**
     * Use curl to test the image function
     */
    public function test_images($limit = '1')
    {
        $start = time();

        $recipients_array = array();

        for ($test = 1; $test <= $limit; $test++)
        {
            $recipients_array[] = 'joe' . $test . 'bloggs.com';
        }

        $test_data = array(
            'api_key' => 'abC45dGfg3',
            'campaign_id' => 'my campaign id',
            'campaign_title' => 'my first campaign',
            'recipients_array' => $recipients_array
        );

        $test_data = array(
            'data' => json_encode($test_data)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/domain.com/index.php/v2/create/image_multiple');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $test_data);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo $response;

        //echo "code : $httpCode<br />\n";
        //echo "finished in " . (time() - $start) . " seconds<br>\n";
    }

}

/* End of file create.php */
/* Location: ./application/controllers/v2/create.php */