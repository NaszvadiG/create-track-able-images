<?php

class api_key_model extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Simple check to see if the api key is set and exists
     * @param string $api_key
     * @return boolean
     */
    function api_key_verified($api_key)
    {
        /**
         * TODO: Validate API Key
         */
        if ($api_key == '')
        {
            return false;
        }
        else
        {
            $this->db->where('api_key', $api_key);

            $this->db->limit(1);

            $this->db->from('clients');

            if ($this->db->count_all_results() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * Get a clients ID from their API key
     * @param type $api_key
     * @return int
     */
    function client_id_from_api_key($api_key)
    {
        /**
         * TODO: Update to get a clients API key
         */
        return 1;
    }

    /**
     * Get a clients unique image from their api key
     * @param string $api_key
     * @return string $image
     */
    function client_image_from_api_key($api_key)
    {
        /**
         * TODO: Update to get a clients API key
         */
        $image = 'sBG';

        return $image;
    }

    /**
     * Get a clients fill details from its api key
     * @param string $api_key
     * @return array $details
     */
    function client_details_from_api_key($api_key)
    {
        $details = array();

        $this->db->select('id, client, api_key, image, created');

        $this->db->where('api_key', $api_key);

        $query = $this->db->get('clients');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $row)
            {
                $details = $row;
            }
        }

        return $details;
    }

}

/* End of file api_key_model.php */
/* Location: ./application/models/api_key_model.php */