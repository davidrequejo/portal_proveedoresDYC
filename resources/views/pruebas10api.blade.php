<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://192.168.50.236:8080/proveedor/crear',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "CodIdentificador": "22021400",
  "Descripcion": "PROVEEDOR PRUEBA POSTMAN",
  "RUC": "20605122613",
  "Direccion": "Av. Prueba 123",
  "Email": "test@test.com",
  "NaturalJuridica": true,
  "Activo": true
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: HRBCRTFpcyv4O9pom8y_ELumwf96u0V39pq2P-rIc-pSuGTKoxwww-GGQjuhvH60',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
