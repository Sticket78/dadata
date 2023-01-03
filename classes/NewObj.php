<?php


namespace classes;


class NewObj
{
    private $source;
    private $data;
    public function __construct($source, $data)
    {
        $this->source = $source;
        $this->data=new DataObj($data["data"]);
        $this->source["HasError"]="false";
        $this->source["WrongFields"]=array("-");

    }
    public function show_all()
    {
        return $this->source;
    }
    public function check_inn()
    {
        $correct_inn=$this->data->get_inn();
        if (!empty($correct_inn)) $this->source["ИНН"]=$correct_inn;
        else $this->add_error("ИНН");
    }
    public function check_name()
    {
        $this->source["Название компании"]=$this->data->get_correct_name();
    }
    public function burn_HasError () {
        $this->source["HasError"]="true";
    }
    public function get_WrongFields() {
        return $this->source["WrongFields"];
    }
    public function set_WrongFields($arr)
    {
        $this->source["WrongFields"]=$arr;
    }
    public function add_error($value)
    {
        $this->burn_HasError();
        $wrongF=$this->get_WrongFields();
        if ($wrongF[0]=='-') $wrongF=array();
        if (empty($wrongF)) $this->set_WrongFields(array($value));
        else $this->set_WrongFields(array_merge($wrongF, array($value)));
    }
    public function fill_all_errors()
    {
        foreach ($this->source as $key => $value) {
            if (empty($value)) $this->add_error($key);
        }
    }
    public function check_address() // проверяем и заполняем адрес и индекс
    {
        $correct_address=$this->data->get_correct_address();
        if (empty($correct_address)) { // если нет в dadata адреса - добавляем в WrongFields, + HasError = true
            $this->add_error('Адрес');
            $correct_index=$this->data->get_correct_index();
            // нет адреса из dadata проверяем также индекс
            if (empty($correct_index)) $this->add_error('Индекс');
            else $this->source["Индекс"]=$correct_index;
        }
        else { // есть адрес из dadata + проверяем на индекс в адресе
            $arAddress=explode(", ", $correct_address);
            $pattern='#^[0-9]{6}$#';
            if (preg_match($pattern, $arAddress[0])) { // если есть индекс в адресе ["address"]["value"]- вырезаем
                $this->source["Адрес"]=implode(", ", array_slice($arAddress, 1));
                $correct_index=$this->data->get_correct_index(); // проверяем есть ли индекс в Dadata в ["address"]["postal_code"]
                if (empty($correct_index)) { // в Dadata нет индекса в отдельном поле
                    // не знаю, может ли такое быть что в общей строке адреса есть индекс, а в поле ["address"]["postal_code"] нет
                    // но на всякий случай
                    $this->source["Индекс"]=$arAddress[0];
                }
                else {
                    $this->source["Индекс"]=$correct_index;
                }
            }
            else { // если нет индекса в строке адреса в dadata в ["address"]["value"]
                $this->source["Адрес"]=$correct_address;
                $correct_index=$this->data->get_correct_index();
                if (empty($correct_index)) { // нет индекса в dadata - добавляем индекс в WrongFields, + HasError = true
                    $this->add_error('Индекс');
                }
                else {
                    $this->source["Индекс"]=$correct_index; // берем из dadata ["address"]["postal_code"]
                }
            }
        }
    }
    public function check_contact_person() // проверяем контактное лицо и должность
    {
        if (empty($this->source["Контактное лицо"])) {
            $new_person=$this->data->get_correct_contact_person();
            if (!empty($new_person["name"])) $this->source["Контактное лицо"]=$new_person["name"];
            else $this->add_error("Контактное лицо");
            if (!empty($new_person["post"])) $this->source["Должность"]=$new_person["post"];
            else $this->add_error("Должность");
        }
    }

}