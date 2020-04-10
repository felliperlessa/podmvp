<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PagSeguro\Configuration\Configure;

class SubscriptionController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $this->makePagSeguroSession();

        var_dump(session()->get('pagseguro_session_code'));

        return view('subscription');
    }

    private function makePagSeguroSession()
    {
        if(!session()->has('pagseguro_session_code')) {
            $sessionCode = \PagSeguro\Services\Session::create(
                Configure::getAccountCredentials()
            );

            session()->put('pagseguro_session_code', $sessionCode->getResult());
        }
    }

    public function createPlan()
    {

        \PagSeguro\Configuration\Configure::setEnvironment('sandbox');

        $plan = new \PagSeguro\Domains\Requests\DirectPreApproval\Plan();
        $plan->setRedirectURL('http://meusite.com');
        $plan->setReference('http://meusite.com');
        $plan->setPreApproval()->setName('Plano XXXX');
        $plan->setPreApproval()->setCharge('AUTO');
        $plan->setPreApproval()->setPeriod('MONTHLY');
        $plan->setPreApproval()->setAmountPerPayment('100.00');

        $plan->setPreApproval()->setDetails('detalhes do plano');

        $plan->setReviewURL('http://meusite.com./review');
        $plan->setMaxUses(100);
        $plan->setReceiver()->withParameters(env('PAGSEGURO_EMAIL'));

        try {
            $response = $plan->register(
                new \PagSeguro\Domains\AccountCredentials(env('PAGSEGURO_EMAIL'), env('PAGSEGURO_TOKEN_SANDBOX')) // credencias do vendedor no pagseguro
            );

            echo '<pre>';
            print_r($response);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


    public function proccess(Request $request)
    {

        $user = auth()->user();
        $datapost = $request->all();
        $reference = 'XPTO';
        $email = env('PAGSEGURO_ENV') == 'sandbox' ? 'test@sandbox.pagseguro.com.br' : $user->email;
        \PagSeguro\Configuration\Configure::setEnvironment('sandbox');

        $preApproval = new \PagSeguro\Domains\Requests\DirectPreApproval\Accession();
        $preApproval->setPlan('C93EAD10A2A23B0CC427AF96951B8EA6');
        $preApproval->setReference($reference);
        $preApproval->setSender()->setName($user->name);//assinante
        $preApproval->setSender()->setEmail($email);//assinante
        $preApproval->setSender()->setHash($datapost['hash']);
        $preApproval->setSender()->setIp('127.0.0.1');//assinante
        $preApproval->setSender()->setAddress()->withParameters('Av. Brig. Faria Lima',
            '1384',
            'Jardim Paulistano',
            '01452002',
            'São Paulo',
            'SP',
            'BRA',
            'apto. 114');//assinante
        $document = new \PagSeguro\Domains\DirectPreApproval\Document();
        $document->withParameters('CPF', '27121238918'); //assinante
        $preApproval->setSender()->setDocuments($document);
        $preApproval->setSender()->setPhone()->withParameters('21', '973481221'); //assinante
        $preApproval->setPaymentMethod()->setCreditCard()->setToken($datapost['card_token']); //token do cartão de crédito gerado via javascript
        $preApproval->setPaymentMethod()->setCreditCard()->setHolder()->setName($datapost['card_name']); //nome do titular do cartão de crédito
        $preApproval->setPaymentMethod()->setCreditCard()->setHolder()->setBirthDate('10/10/1990'); //data de nascimento do titular do cartão de crédito
        $document = new \PagSeguro\Domains\DirectPreApproval\Document();
        $document->withParameters('CPF', '27121238918'); //cpf do titular do cartão de crédito
        $preApproval->setPaymentMethod()->setCreditCard()->setHolder()->setDocuments($document);
        $preApproval->setPaymentMethod()->setCreditCard()->setHolder()->setPhone()->withParameters('21', '973481221'); //telefone do titular do cartão de crédito
        $preApproval->setPaymentMethod()->setCreditCard()->setHolder()->setBillingAddress()->withParameters('Av. Brig. Faria Lima',
            '1384',
            'Jardim Paulistano',
            '01452002',
            'São Paulo',
            'SP',
            'BRA',
            'apto. 114'); //endereço do titular do cartão de crédito


        try {
            $response = $preApproval->register(
                new \PagSeguro\Domains\AccountCredentials(env('PAGSEGURO_EMAIL'), env('PAGSEGURO_TOKEN_SANDBOX')) // credencias do vendedor no pagseguro
            );

            echo '<pre>';
            print_r($response);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        var_dump($response);
    }
}
