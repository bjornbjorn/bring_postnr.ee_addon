<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bring_postnr_ft extends EE_Fieldtype {

    var $info = array(
        'name'      => 'Bring Postnr',
        'version'   => '1.0'
    );

    public $has_array_data = TRUE;
    private $tag_prefix = '';

    public function __construct() {
        parent::__construct();

        $this->EE->lang->loadfile('bring_postnr');
    }

    public function display_field($data)
    {
        return form_input(array(
            'name'  => $this->field_name,
            'id'    => $this->field_id,
            'value' => $data
        ));
    }

    public function install()
    {
        parent::install();
        $this->EE->load->dbforge();

        $bring_postnr_fields = array(
            'bring_postnr_id' => array(
                'type' => 'int',
                'constraint' => '10',
                'unsigned' => TRUE,
                'auto_increment' => TRUE),
            'post_code' => array(
                'type' => 'char',
                'constraint' => "4"),
            'city' => array(
                'type' => 'varchar',
                'constraint' => "255",
                'null' => FALSE,),
            'category' => array(
                'type' => 'char',
                'constraint' => "1")
        );

        $this->EE->dbforge->add_field($bring_postnr_fields);
        $this->EE->dbforge->add_key('bring_postnr_id', TRUE);
        $this->EE->dbforge->create_table('bring_postnr');
    }

    public function uninstall()
    {
        parent::uninstall();
        $this->EE->load->dbforge();
        $this->EE->dbforge->drop_table('bring_postnr');
    }

    private function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    private function get_postcode_info($postnr)
    {
        if($postnr != '') {

            $url = 'http://adressesok.posten.no/api/v1/postal_codes.json?postal_code='.$postnr;

            if($this->get_http_response_code($url) != "404"){
                $request_content = file_get_contents($url);
                if($request_content) {
                    $response = json_decode($request_content);
                    if($response && $response->status == 'ok') {
                        return $response;
                    }
                }
            }
        }

        return FALSE;
    }

    public function validate($post_code)
    {
        if ($post_code == '')
        {
            return TRUE;
        }

        if(preg_match('/([\d]{4})/i', $post_code, $result))
        {
            if($result[0] != $post_code) {
                return lang('bring_postnr_does_not_validate');
            } else {

                $q = $this->EE->db->get_where('bring_postnr', array('post_code' => $post_code));
                if($q->num_rows() == 0) {
                    $post_code_info = $this->get_postcode_info($post_code);
                    if($post_code_info) {
                        return TRUE;
                    }
                } else {
                    // found it in db, so it's PROBABLY correct then
                    return TRUE;
                }

                return lang('bring_postnr_could_not_find_post_code');
            }
        } else {
            return lang('bring_postnr_does_not_validate');
        }
    }

    /**
     * Save the postal code info the the database so we don't have to look it up in
     * the tag replacement function
     *
     * @param $post_code
     */
    public function post_save($post_code)
    {
        $post_code_info = $this->get_postcode_info($post_code);
        if($post_code_info) {

            $info = $post_code_info->postal_codes[0];
            $insert_arr = array(
                'post_code' => $info->postal_code,
                'city' => $info->city,
                'category' => $info->category
            );

            $q = $this->EE->db->get_where('bring_postnr', array('post_code' => $post_code));
            if($q->num_rows() == 0) {
                $this->EE->db->insert('bring_postnr', $insert_arr);
            } else {
                // in case info has been updated
                $this->EE->db->where('post_code', $post_code)->update('bring_postnr', $insert_arr);
            }
        }
    }

    /**
     * Replace tags contents
     *
     * @param $post_code
     * @param array $params
     * @param bool $tagdata
     * @return string
     */
    public function replace_tag($post_code, $params, $tagdata)
    {

        if(isset($params['tag_prefix'])) {
            $this->tag_prefix = $params['tag_prefix'];
        }

        $vars = array(
            $this->tag_prefix.'post_code' => '',
            $this->tag_prefix.'city' => '',
            $this->tag_prefix.'category' => '',
            $this->tag_prefix.'city_ucfirst' => '',
        );

        // look up info
        $q = $this->EE->db->get_where('bring_postnr', array('post_code' => $post_code));
        if($q->num_rows() > 0) {
            $city = $q->row('city');
            $city_lower = mb_strtolower($city);

            $vars = array(
                $this->tag_prefix.'post_code' => $post_code,
                $this->tag_prefix.'city' => $city,
                $this->tag_prefix.'category' => $q->row('category'),
                $this->tag_prefix.'city_lower' => $city_lower,
                $this->tag_prefix.'city_ucfirst' => ucfirst($city_lower),
            );
        }

        if($tagdata) {
            return $this->EE->TMPL->parse_variables($tagdata, array($vars));
        } else {
            return $post_code;
        }
    }
}

/* End of file ft.bring_postnr.php */
/* Location: ./system/expressionengine/third_party/bring_postnr/ft.bring_postnr.php */