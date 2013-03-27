<?php

class durl_model extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Create an image to use for this recipient in this campaign
     * @param string $api_key
     * @param string $campaign_id
     * @param string $campaign_title
     * @param string $recipipent
     */
    function create_image($api_key, $campaign_id, $campaign_title, $recipient)
    {
        $this->db->cache_on();

        $client_details_array = $this->api_key_model->client_details_from_api_key($api_key);

        $this->db->cache_off();

        if (!empty($client_details_array))
        {
            $client_id = $client_details_array["id"];

            $client_image = $client_details_array["image"];

            $campaign_image = $this->campaign_image_from_title($client_id, $campaign_title);

            $recipient_image = $this->recipient_image_from_recipient($client_id, $recipient);

            return base_url() . 'p/' . $client_image . '/' . $campaign_image . '/' . $recipient_image . '.gif';
        }
    }

    /**
     * Return a unique image associated with a campaign
     * @param type $campaign_title
     */
    private function campaign_image_from_title($client_id, $campaign_title)
    {
        //$this->db->cache_on();

        $campaign_image = '';

        $existing_campaign_details = array();

        $this->db->select('id, client_id, campaign_title, image, created, ttl');

        $this->db->where('client_id', $client_id);

        $this->db->where('campaign_title', $campaign_title);

        $this->db->limit(1);

        $this->db->order_by('created', 'desc');

        $query = $this->db->get('campaigns');

        //$this->db->cache_off();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $existing_campaign_details)
            {
                /**
                 * If a campaign is found with the same title, check to see if it was created recently based on its TTL (Time to Live in hours)
                 * If its a recent one, its its campaign image
                 * if its older, create a new campaign image
                 */
                if (time() <= strtotime($existing_campaign_details['created']) + ($existing_campaign_details['ttl'] * 3600))
                {
                    /**
                     * Using an existing campaign image
                     */
                    $campaign_image = $existing_campaign_details["image"];
                }
                else
                {
                    /**
                     * Create and save a new campaign image
                     */
                    $campaign_image = $this->create_unique_image($client_id, 'campaigns');

                    $this->save_campaign_image($client_id, $campaign_title, $campaign_image);
                }
            }
        }
        else
        {
            /**
             * Create and save a new campaign image
             */
            $campaign_image = $this->create_unique_image($client_id, 'campaigns');

            $this->save_campaign_image($client_id, $campaign_title, $campaign_image);
        }

        return $campaign_image;
    }

    /**
     * Get an image associated to a clients recipient or generate one
     * @param string $recipient
     */
    private function recipient_image_from_recipient($client_id, $recipient)
    {
        $recipient_image = '';
        $email_address = '';
        $email_address_hash = '';

        $this->load->helper('email');

        $this->db->select('id, client_id, recipient_email, recipient_email_hash, image, created');

        $this->db->where('client_id', $client_id);

        if (valid_email($recipient))
        {
            $this->db->where('recipient_email', $recipient);
            $email_address = $recipient;
        }
        else
        {
            $this->db->where('recipient_email_hash', $recipient);
            $email_address_hash = $recipient;
        }

        $query = $this->db->get('recipients');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $recipient_details)
            {
                $recipient_image = $recipient_details["image"];
            }
        }
        else
        {
            /**
             * Generate and save a new image
             */
            $recipient_image = $this->create_unique_image($client_id, 'recipients');

            $this->save_recipient_image($client_id, $recipient_image, $email_address, $email_address_hash);
        }

        return $recipient_image;
    }

    /**
     * Create a unique string for use in an image
     * @param int $client_id
     * @param string $table
     */
    private function create_unique_image($client_id, $table)
    {
        $this->load->helper('string');

        $new_short_code = '';
        $random_string = '';

        $existing_short_codes = $this->list_used_short_codes($client_id, $table);

        $length = $this->find_longest_value($existing_short_codes);

        $existing_short_codes = $this->short_codes_filter($existing_short_codes, $length);

        $possibilities = pow(62, $length); // a to z, A to Z, 0 - 9

        /**
         * increase the length of generated short codes if all the possabilites are gone using the current longth
         */
        if (count($existing_short_codes) >= $possibilities)
        {
            $length++;
        }

        while ($random_string == '')
        {
            $random_string = random_string('alnum', $length); // this could sometimes return an empty string
        }

        /**
         * check to make sure its not used already
         */
        while ($new_short_code == '')
        {
            if (in_array($random_string, $existing_short_codes))
            {
                $new_short_code = '';
                $random_string = random_string('alnum', $length);
            }
            else
            {
                $new_short_code = $random_string;
                break;
            }
        }

        return $new_short_code;
    }

    /**
     * Create a list of already used short codes
     * @return array $short_codes
     */
    private function list_used_short_codes($client_id, $table)
    {
        $short_codes = array();

        $this->db->select('image');

        $this->db->where('client_id', $client_id);

        $this->db->from($table);

        $this->db->order_by('LENGTH(image)', 'DESC');

        $this->db->distinct();

        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $row)
            {
                $short_codes[] = $row['image'];
            }
        }

        return $short_codes;
    }

    /**
     * Given an unsorted array of values, find the string length of the longest value
     * @param array $existing_short_codes
     * @return int $length;
     */
    private function find_longest_value($existing_short_codes)
    {
        $length = 1;
        $previous_length = 0;

        foreach ($existing_short_codes as $value)
        {
            if (strlen($value) > $previous_length)
            {
                $length = strlen($value);
                $previous_length = strlen($value);
            }
        }

        return $length;
    }

    /**
     * Fileter an array of short codes, remove any that are shorter than the given length
     * @param array $existing_short_codes
     * @param int $length
     * @return array $new_short_codes_array
     */
    private function short_codes_filter($existing_short_codes, $length)
    {
        $new_short_codes_array = array();

        foreach ($existing_short_codes as $short_code)
        {
            if (strlen($short_code) >= $length)
            {
                $new_short_codes_array[] = $short_code;
            }
        }

        return $new_short_codes_array;
    }

    /**
     * Save a new generated campaign image
     * @param int $client_id
     * @param string $campaign_title
     * @param string $campaign_image
     * @param int $ttl
     */
    private function save_campaign_image($client_id, $campaign_title, $campaign_image, $ttl = '24')
    {
        $data = array(
            'client_id' => $client_id,
            'campaign_title' => $campaign_title,
            'image' => $campaign_image,
            'created' => date("Y-m-d H:i:s"),
            'ttl' => $ttl
        );

        $this->db->insert('campaigns', $data);
    }

    /**
     * Save a new recipient image
     * @param int $client_id
     * @param string $recipient_image
     */
    private function save_recipient_image($client_id, $recipient_image, $email_address = '', $email_address_hash = '')
    {
        $data = array(
            'client_id' => $client_id,
            'image' => $recipient_image,
            'created' => date("Y-m-d H:i:s")
        );

        if ($email_address != '')
        {
            $data['recipient_email'] = $email_address;
            $data['recipient_email_hash'] = md5($email_address);
        }

        if ($email_address_hash != '')
        {
            $data['recipient_email_hash'] = $email_address_hash;
        }

        $this->db->insert('recipients', $data);
    }

    /**
     * Record a record that a particular image was loaded
     * @param string $client_image
     * @param string $campaign_image
     * @param string $receipient_image 
     */
    function record_image_load($client_image, $campaign_image, $receipient_image)
    {
        if (stristr($receipient_image, '.') == TRUE)
        {
            $receipient_image = current(explode(".", $receipient_image));
        }

        $data = array(
            'client_image' => $client_image,
            'campaign_image' => $campaign_image,
            'recipient_image' => $receipient_image,
            'ip_address' => $this->input->ip_address(),
            'browser_agent' => $this->input->user_agent(),
            'created' => date("Y-m-d H:i:s")
        );

        $this->db->insert('opens', $data);
    }

    /**
     * Given an API key and a Campaign title, return the clients image and the campaign image
     * @param string $api_key
     * @param string $campaign_title
     * @return array $images
     */
    function client_and_campaign_image($api_key, $campaign_title)
    {
        $images = array();

        $query = $this->db->query('SELECT clients.image as client_image, campaigns.image as campaign_image FROM clients INNER JOIN campaigns ON clients.id = campaigns.client_id WHERE clients.api_key = \'' . $api_key . '\' AND campaigns.campaign_title = \'' . $campaign_title . '\'');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $row)
            {
                $images['client_image'] = $row["client_image"];
                $images['campaign_image'] = $row["campaign_image"];
            }
        }

        return $images;
    }

    /**
     * Given a client image and a campaign image return the distinct opens
     * @param string $client_image
     * @param string $campaign_image
     */
    function campaign_distinct_opens($client_image, $campaign_image)
    {
        $opens = array();

        $query = $this->db->query('SELECT DISTINCT opens.recipient_image, recipients.recipient_email FROM opens INNER JOIN recipients ON opens.recipient_image = recipients.image where client_image=\'' . $client_image . '\' and campaign_image = \'' . $campaign_image . '\' ');

        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $row)
            {
                $opens[] = $row;
            }
        }

        return $opens;
    }

}

/* End of file durl_model.php */
/* Location: ./application/models/durl_model.php */