<?php

namespace App\Jobs;

use Exception;
use CodeIgniter\Queue\BaseJob;

class Email extends BaseJob
{
    public function process()
    {
        $email  = service('email', null, false);
        $result = $email
            ->setTo('maxisebas19@gmail.com')
            ->setSubject('My test email')
            ->setMessage($this->data['message'])
            ->send(false);

        if (! $result) {
            throw new Exception($email->printDebugger('headers'));
        }

        return $result;
    }
}
