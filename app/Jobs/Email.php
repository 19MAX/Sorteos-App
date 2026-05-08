<?php

namespace App\Jobs;

use CodeIgniter\Queue\BaseJob;
use App\Models\ParticipantModel;

class Email extends BaseJob
{
    public function process()
    {
        $email = service('email', null, false);

        $to       = $this->data['to']       ?? '';
        $subject  = $this->data['subject']  ?? 'Notificación';
        $template = $this->data['template'] ?? null;
        $viewData = $this->data['viewData'] ?? [];

        if ($template) {
            $message = view($template, $viewData);
        } else {
            $message = $this->data['message']  ?? '';
        }

        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($message);

        if (!$email->send(false)) {
            log_message('error', '[Email Job] Falló envío: ' . $email->printDebugger('headers'));
            throw new \Exception($email->printDebugger('headers'));
        }

        log_message('info', "[Email Job] Correo enviado a {$to}: {$subject}");

        return true;
    }
}