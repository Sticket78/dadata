<?php

namespace classes;

require_once (__DIR__."/classes/NewObj.php");
require_once (__DIR__."/classes/DataObj.php");
require_once (__DIR__.'/vendor/autoload.php');
$token = "f30af198fb44fce09960519b5ad0359adb26b454";
$secret = null;
$dadata = new \Dadata\DadataClient($token, $secret);
$fields = array("branch_type" => "MAIN");

$json_source=json_decode(file_get_contents('source.txt'), true);
//echo "count of json_source=".count($json_source)."<br>";
$count=1;
$arResult=array();
foreach ($json_source as $source) {
    //print_r($source);
    //echo "<br>";
    if (!empty($source["ИНН"])) { // есть ИНН в исходнике - ищем по ИНН
        $response = $dadata->findById("party", $source["ИНН"], 1, $fields);
        print_r($response);
        echo "<br>";
        $new_obj=new NewObj($source, $response[0]);

        $new_obj->check_name();
        $new_obj->check_address();
        $new_obj->check_contact_person();
        $arResult[]=$new_obj->show_all();
        //print_r($new_obj->show_all());
        //echo '<br><br>';
    }
    else { // ИНН не заполнен в исходнике -> либо пробуем искать по адресу либо оставляем как есть
        if ((!empty($source["Адрес"])) and (!empty($source["Название компании"]))) {
            // echo "тут нет ИНН - но есть адрес и название";
            $response = $dadata->suggest("party", $source["Название компании"]); //выборка по названию
            // выбираем по адресу из выборки
            if (count($response)>1) {
                foreach ($response as $resp) {
                    if ($resp["data"]["address"]["value"]==$source["Адрес"]) {
                        $response=array();
                        $response[0]=$resp;
                        break;
                    }
                }
            }
            $new_obj = new NewObj($source, $response[0]);
            if (empty($response)) $new_obj->fill_all_errors(); // пустая выборка - заполняем ошибки
            else { // есть объект из dadata - далее стандартная проверка по полям + инн
                $new_obj->check_name();
                $new_obj->check_address();
                $new_obj->check_contact_person();
                $new_obj->check_inn();
            }

        }
        else { // тут поиск по dadata невозможен = оставляем как есть и добавляем HasError и WrongFields
            $new_obj = new NewObj($source, $response);
            $new_obj->fill_all_errors();
        }
        $arResult[]=$new_obj->show_all();
        //print_r($new_obj->show_all());
        //echo '<br><br>';
    }


    $count++;
}
// ---------------- output -----------------
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">

    <style>
        .result_output {display:none;}
        .result_output {display:flex; justify-content: space-between; flex-wrap:wrap;}
        .result_output .result_output_container.response div {display:none;}
        .result_output.active .result_output_container.response div {display:block;}
        .result_output_container {width:calc(50% - 10px);margin:0 0 20px 0;}
    </style>
</head>
<body>

<?php
echo json_encode($arResult, JSON_UNESCAPED_UNICODE).'<br><br>';
?>
<button class="tab_open">Показать исходники для сравнения</button>
<div class="result_output">
    <div class="result_output_container"><h3> Результат</h3></div>
    <div class="result_output_container"><h3> Иходник</h3></div>
<?php
for ($i=0; $i<count($arResult); $i++) {
    echo '<div class="result_output_container">';
    foreach ($arResult[$i] as $key =>$value) {
        $output =  (is_array($value)) ? implode(", ", $value):$value;
        echo '<div>'.$key.' : '.$output.'</div>';
    }
    echo '</div>';
    echo '<div class="result_output_container response">';
    foreach ($json_source[$i] as $key =>$value) {
        echo '<div>'.$key.' : '.$value.'</div>';
    }
    echo '</div>';
}

?>
</div>
<script>
    const tab_section=document.querySelector('.result_output')
    const tab_open=document.querySelector('.tab_open')
    tab_open.addEventListener('click', ()=>{
        tab_section.classList.toggle('active')
    })
</script>
</body>
</html>