<?php

namespace App\Utils;

use Swift_Mailer;
use Swift_Image;
use Swift_SmtpTransport;
use Swift_Attachment;
use Swift_Message;



class LigaMessage
{
    const NOTIFICATION_EMAIL=1;
    const NOTIFICATION_SMS=2;
    const NOTIFICATION_WHATSAPP=2;

    private $host;
    private $encryption;
    private $port;
    private $username;
    private $password;
    private $transport;
    private $mailer;
    private $mail;
    private $default_sender;


    public function __construct()
    {

        $params = FilterLiga::filterParams(['HOST', 'PORT', 'ENCRYPTION', 'USERNAME', 'PASSWORD', 'SENDER'], 'APP_MAILER');
 
        $this->default_sender = $params['sender'];
        $this->transport = new Swift_SmtpTransport($params['host'], $params['port'], $params['encryption']);
        $this->transport->setUsername($params['username']);
        $this->transport->setPassword($params['password']);
    }
    
    
     /**
     *
     * @param string $type_notification
     * @param string $email
     * @param string $subject    
     * @param string $body    
     * 
     */
     public function sendMessaje($type_notification,$email,$subject,$body){
        
        switch ($type_notification){            
                
            case self::NOTIFICATION_EMAIL:            
                $this->sendEmail($email,null, $subject,$body);
                break;
            case self::NOTIFICATION_SMS:
                $this->sendSMS();
                break;  
            case self::NOTIFICATION_WHATSAPP:
                $this->sendWhatsapp();
                break;
            default:
                $this->sendEmail($email,null, $subject,$body);                                        
        }       
    }

    /**
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $body
     * @param type $attachment
     * @return integer|null
     * @throws \App\Utils\Mailer\Exception
     */
    public function sendEmail($to, $from, $subject, $body, $attachments = [], $embeddedImages = [])
    {
        try {
            
            $from = $from ?? $this->default_sender;
            $this->mail = new Swift_Message();
            $this->mail->setSubject($subject);
            $this->mail->setFrom([$from]);
            $this->mail->setTo(explode(',',$to));

            foreach ($attachments as $attachment){


                if ($attachment['type'] == 'path') {
                    $atta = Swift_Attachment::fromPath($attachment['content']);
                    $atta->setFilename($attachment['name']);

                }
                if ($attachment['type'] == 'url') {
                    $atta = new Swift_Attachment();
                    $atta->setFilename($attachment['name'])
                                ->setContentType('application/pdf')
                                ->setBody($attachment['content']);
                }
                $this->mail->attach($atta);
            }

            foreach ($embeddedImages as $key => $embeddedImage)
                $body = str_replace($key,$this->mail->embed(Swift_Image::fromPath($embeddedImage)),$body);

            $this->mail->setBody($body, 'text/html');

            $this->mailer = new Swift_Mailer($this->transport);

            $result = $this->mailer->send($this->mail);
            return $result;
        }
        catch (Exception $ex) {
            throw $ex;
        }
    }
    
    public function sendWhatsapp(){
        try {
            //PENDIENTE
            return true;
        } catch (Exception $ex) {

        }        
    }
    
    public function sendSMS(){
        try {
            //PENDIENTE
            return true;
        } catch (Exception $ex) {

        }        
    }

}
