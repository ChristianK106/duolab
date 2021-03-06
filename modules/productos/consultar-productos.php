<?php
require '../../global/connection.php';
$FILTER_PROD = $_POST["FILTER"];
$ESTADO_PROD = $_POST["ESTADO"];

if ($FILTER_PROD == "ALL") {

    $sqlquery_adic = "";
    if($ESTADO_PROD != "ALL"){
        $sqlquery_adic = " WHERE tp.active_status = $ESTADO_PROD ";
    }

    $sqlStatement = $pdo->prepare("SELECT tp.id AS IDPROD, tp.code AS CODE, tp.description AS DESCPROD, tp.name AS NOMPROD, tp.brand AS MARCA, tp.stock_quantity AS CANTIDAD, ROUND(tp.unit_price,2) AS PRECIO, tp.unit_value AS VALORMEDIDA, tpo.business_name AS PROVEEDOR, tp.provider_reference AS PROVEEDOR_REF, tp.expiration_date AS FECVENC, tp.registration_date AS FECREG, tp.active_status AS ESTADO FROM tbl_product tp JOIN tbl_provider tpo ON tp.provider_id=tpo.id $sqlquery_adic ORDER BY tp.id DESC");
    $sqlStatement->execute();
    $rowsNumber = $sqlStatement->rowCount();
    $json_data = array();
    if ($rowsNumber > 0) {        
        foreach ($sqlStatement as $ROW) {
            $ROWDATA['ID'] = $ROW["IDPROD"];
            $ROWDATA['CODIGO'] = $ROW["CODE"];
            $ROWDATA['NOMBRE'] = $ROW["NOMPROD"];
            $ROWDATA['DESCPROD'] = $ROW["DESCPROD"];
            $ROWDATA['MARCA'] = $ROW["MARCA"];
            $ROWDATA['CANTIDAD'] = $ROW["CANTIDAD"];
            $ROWDATA['PRECIO'] = $ROW["PRECIO"];
            $ROWDATA['VALORMEDIDA'] = $ROW["VALORMEDIDA"];

            if ($ROW["VALORMEDIDA"] == ""){
                $ROWDATA['VALORMEDIDA'] = "-";
            }else{
                $ROWDATA['VALORMEDIDA'] = $ROW["VALORMEDIDA"];
            }

            $ROWDATA['PROVEEDOR'] = $ROW["PROVEEDOR"];

            if ($ROW["PROVEEDOR_REF"] == ""){
                $ROWDATA['PROVEEDOR_REF'] = "-";
            }else{
                $ROWDATA['PROVEEDOR_REF'] = $ROW["PROVEEDOR_REF"];
            }

            if ($ROW["FECVENC"] == "1970-01-01"){
                $ROWDATA['FECVENC'] = "-";
            }else{
                $ROWDATA['FECVENC'] = date("d/m/Y",strtotime($ROW["FECVENC"]));
            }

            $ROWDATA['FECREG'] = date("d/m/Y H:i",strtotime($ROW["FECREG"]));
            $ROWDATA['ESTADO'] = $ROW["ESTADO"]==1?"Activo":"Inactivo";
            
            array_push($json_data, $ROWDATA);
        }        
    }
    echo json_encode(array("data" => $json_data));
} else {
    $ID_REAL = str_replace("PROD-","",$FILTER_PROD);

    $sqlquery_adic = "";
    if($ESTADO_PROD != "ALL"){
        $sqlquery_adic = " AND active_status = $ESTADO_PROD";
    }

    $sqlStatement = $pdo->prepare("SELECT * FROM tbl_product
        WHERE id=:PRODID $sqlquery_adic");
    $sqlStatement->bindParam("PRODID", $ID_REAL, PDO::PARAM_INT);
    $sqlStatement->execute();
    $rowsNumber = $sqlStatement->rowCount();
    $json_data = array();

    if ($rowsNumber > 0) {        
        foreach ($sqlStatement as $ROW) {
            $ROWDATA['CODIGO'] = $ROW["id"];
            $ROWDATA['CODPROD'] = $ROW["code"];
            $ROWDATA['DESCRIPTION'] = $ROW["description"];
            $ROWDATA['NOMBRE'] = $ROW["name"];
            $ROWDATA['MARCA'] = $ROW["brand"];
            $ROWDATA['CANTIDAD'] = $ROW["stock_quantity"];
            $ROWDATA['UNITVALUE'] = $ROW["unit_value"];
            $ROWDATA['PRECIO'] = $ROW["unit_price"];
            $ROWDATA['PROVEEDOR'] = $ROW["provider_id"];
            $ROWDATA['PROVEEDOR_REF'] = $ROW["provider_reference"];
            $ROWDATA['FECVENC'] = date("Y-m-d",strtotime($ROW["expiration_date"]));
            $ROWDATA['ESTADO'] = $ROW["active_status"]==1?false:true;
            array_push($json_data, $ROWDATA);
        }        
    }
    echo json_encode($json_data);
}
