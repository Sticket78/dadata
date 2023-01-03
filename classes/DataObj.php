<?php


namespace classes;


class DataObj
{

    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function show_data()
    {
        return $this->data["name"];
    }
    public function get_inn()
    {
        return $this->data["inn"];
    }
    public function get_correct_name()
    {
        $correct_name=$this->data["name"]["short"].", ".$this->data["opf"]["short"];
        return $correct_name;
    }
    public function get_correct_address()
    {
        return $this->data["address"]["value"];
    }
    public function get_correct_index()
    {
        return $this->data["address"]["data"]["postal_code"];
    }
    public function get_correct_contact_person()
    {
        return $this->data["management"];
    }
}