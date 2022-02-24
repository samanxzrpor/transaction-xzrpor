<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class TransactionController extends Controller
{
    private $responseFromTransaction;

    public function store()
    {
        DB::transaction(function (){
            $transactionService = new TransferService($this->prepareRequierdInfoToSend());
            $this->responseFromTransaction = $transactionService->transfer();

            $transactionDataToSave = $this->prepareAndSaveResponseData($this->responseFromTransaction);
            $transaction = Transaction::create($transactionDataToSave);

            $RecipientUserId = $this->findRecipientByCardNumber($transaction->destinationNumber);
            $transmitterUserId = Auth::user();

            $RecipientUserId->transactions()->attach($transaction->id);
            $transmitterUserId->transactions()->attach($transaction->id);
        });
    }


    public function getTransactions()
    {
        return Auth::user()->transactions;
    }


    private function prepareDataToSave(array $response)
    {
        $response = json_decode($response, true);
        $dataToSave = [
            'track_id' => $response['track_id'],
            'destinationNumber' => $response['result']['destinationNumber'],
            'sourceNumber' => $response['result']['sourceNumber'],
            'amount' => $response['result']['amount'],
            'description' => $response['result']['description'],
            'payment_number' => $response['result']['payment_number'],
        ];
        return $dataToSave;
    }


    private function findRecipientByCardNumber(string $cardNumber)
    {
        return BankAccount::where('account_number')->first()->user();
    }


    private function prepareRequierdInfoToSend()
    {
        return [
            "amount" => 1 ,
            "description" => "شرح تراکنش" ,
            "destinationFirstname" => "خلیلی  حسینی  بیابانی" ,
            "destinationLastname" => "سمیه   غز اله  فریماه" ,
            "destinationNumber" => "IR120620000000302876732005" ,
            "paymentNumber" => "123456" ,
            "deposit" => "776700000",
            "sourceFirstName" => "مارتین" ,
            "sourceLastName" => "اسکورسیزی" ,
            "reasonDescription" => "1"
        ];
    }


    private function saveBankAccountData()
    {
        BankAccount::create([
            'user_id' => Auth::user()->id ,
            'account_number' => $this->responseFromTransaction['result']['sourceNumber']
        ]);
    }

}
