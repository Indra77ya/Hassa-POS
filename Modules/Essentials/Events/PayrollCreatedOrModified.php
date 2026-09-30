<?php

namespace Modules\Essentials\Events;

use Illuminate\Queue\SerializesModels;
use App\Transaction;

class PayrollCreatedOrModified
{
    use SerializesModels;

    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
