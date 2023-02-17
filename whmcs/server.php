<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function server_MetaData()
{
    return array(
        'DisplayName' => 'WHMCS Server',
        'APIVersion' => '1.0',
        'RequiresServer' => true,
        'DefaultNonSSLPort' => '1111',
        'DefaultSSLPort' => '1112',
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
    );
}

function server_SuspendAccount(array $params)
{
    try {
        if (!$params['domain']) {
            return 'Dominio nao informado!';
        }
        
        $data = [ "domain" => $params['domain'] ];
        $server = server_API($params, "suspend", $data, "POST");
        $server = json_decode($server);
        
        if ($server->status == "success") {
            return 'success';
        } else {
            return 'Houve um erro ao suspender a conta.';   
        }
    } catch (Exception $e) {
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function server_UnsuspendAccount(array $params)
{
    try {
        if (!$params['domain']) {
            return 'Dominio nao informado!';
        }
        
        $data = [ "domain" => $params['domain'] ];
        $server = server_API($params, "unsuspend", $data, "POST");
        $server = json_decode($server);

        if ($server->status == "success") {
            return 'success';
        } else {
            return 'Houve um erro ao remover a suspensão da conta.';   
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function server_GetHostname(array $params)
{
    $hostname = $params['serverhostname'] . ':' . $params['serverport'];
    $hostname = ($params['serversecure'] == true ? 'https://' : 'http://') . $hostname;
    
    return $hostname;
}

function server_API(array $params, $endpoint, array $data = [], $method = "GET")
{
    if (!$data['domain']) {
        return 'Necessário informar um domínio.';
    }
    
    $url = server_GetHostname($params) . '/' . $endpoint;
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_PORT => $params['serverport'],
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);
    
    $headers = [ 
        "X-Auth-Token: " . $params['serverpassword']
    ];

    if ($method === 'POST' || $method === 'PATCH') {
        $jsonData = json_encode($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        array_push($headers, "Content-Type: application/json");
        array_push($headers, "Content-Length: " . strlen($jsonData));
    }
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);

    if ($err) {
        return $err;
    } else {
        return $response;
    }
}