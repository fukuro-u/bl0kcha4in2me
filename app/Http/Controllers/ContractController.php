<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContractController extends Controller
{

    private $default_address = '0x7790a6dae3174a60e171a25a040f913b5d6054d4';
    private $default_api_key = '58DVUIQVR3A15AGQSD5FP3W778NFRPSERI';
    public function checkNewContract($address = null, $api_key = null)
    {
        // Your BscScan address;
        if ( is_null($address))
            $address =  $this->default_address;

        // Your BscScan API key
        if ( is_null($api_key))
            $api_key =  $this->default_api_key;
        
        
        // Create URL to query BscScan API
        $url = 'https://api.bscscan.com/api?module=account&action=txlist&address='.$address.'&startblock=0&endblock=99999999&sort=desc&apikey='.$api_key;
        
        // Call BscScan API to get information on transactions for your address
        $data = json_decode(file_get_contents($url), true);
        
        // Define time limit as 24 hours ago
        $limit_time = time() - (30 * 24 * 60 * 60);

        $message = '';
        
        // Loop through all returned transactions
        foreach ($data['result'] as $tx) {
            // If the time of the transaction is greater than the time limit
            if ($tx['timeStamp'] > $limit_time) {
                // If the transaction has input field and contains constructor
                if ( empty($tx['to']) && !empty($tx['input']) && !empty($tx['contractAddress'])) {

                    // Get the contract address from the transaction
                    $contractAddress = $tx['contractAddress'];

                    // Create message with contract name and link
                    $message .= '<br /> A new contract has been created at BscScan address.<br />
                     Contract Name: <b style="color:red;">'.$this->getContractName($contractAddress, $api_key). '</b><br />
                     Contract Address: <a href="https://bscscan.com/address/'. $contractAddress .'"> https://bscscan.com/address/'.$contractAddress.'</a>';
                    //break;
                }
            }
        }
        return $message;
    }


    public function getContractName($contractAddress, $api_key){
        $url = "https://api.bscscan.com/api?module=contract&action=getsourcecode&address=" . $contractAddress . "&apikey=" . $api_key;
        $response = file_get_contents($url);
        $data = json_decode($response,true);
        return $data['result'][0]['ContractName'];
    }

    public function sendContract2Me($message, $email){
         // Send email notification to your email address
         $to = $email;
         $subject = 'New Contract Created';
         $headers = 'From: YOUR_EMAIL_ADDRESS';
         mail($to, $subject, $message, $headers);
    }
}
