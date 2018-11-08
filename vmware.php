<?php


function vmware_ConfigOptions()
{
    return [
    'Url' => array(
        'Type' => 'text',
        'Size' => '64',
        'Default' => '',
        'Description' => 'Vcenter Url',
    ),
    'User' => array(
        'Type' => 'text',
        'Size' => '64',
        'Default' => '',
        'Description' => 'Vcenter User',
    ),
    'Password' => array(
        'Type' => 'password',
        'Size' => '25',
        'Default' => '',
        'Description' => 'Vcenter password',
    )
  ];
}

function vmware_TerminateAccount($params)
{
    $vm = vmware_vm($params);
    if ($vm->machine) {
        $vm->stop();
        return 'success';
    }
    return 'An error occured';
}

function vmware_SuspendAccount($params)
{
    $vm = vmware_vm($params);
    if ($vm->machine) {
        $vm->stop();

        return 'success';
    }
    return 'An error occured';
}

function vmware_UnsuspendAccount($params)
{
    $vm = vmware_vm($params);
    if ($vm->machine) {
        $vm->start();
        return 'success';
    }
    return 'An error occured';
}

function vmware_AdminServicesTabFields($params)
{
    $vm = vmware_vm($params);

    if ($vm->machine) {
        echo "Vm exists and status: {$vm->machine['power_state']}.";
    } else {
        echo "VM does not exists.";
    }
}
function vmware_send_suspend($params, $message)
{
}

function vmware_vm($params)
{
    require_once 'Client.php';
    $ip = $params['customfields']['VMIP'];
    $url = $params['configoption1'];
    $username = $params['configoption2'];
    $password = $params['configoption3'];
    $url = rtrim($url, "/");
    $client = new Client($url, $username, $password);
    $vm = $client->find($ip);
    return $vm;
}
