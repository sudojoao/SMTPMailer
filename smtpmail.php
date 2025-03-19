<?php
/*
 * SMTPMailer - A simple PHP script to send email letters via SMTP server! No Libraries! No composer! Import the script into your project and configure imediatly
 *
 * Copyright (c) 2025 João Vieira <mail@joaovieira.eu>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

if(file_exists(dirname(__FILE__) . "/config.php")) {
    define('SECURE_ACCESS', true);
    require_once(dirname(__FILE__) . "/config.php");
} else {
    die("Configuration file not found!");
}

class SMTPMail
{
    private $config = array();
    private $recipients = array(); 

    private $letterSubject = "";
    private $letterBody = "";
    private $server_response = "";

    public function __construct()
    {
        $this->resetConfig();
        $this->config['smtp_debug']    = false;
        $this->config['smtp_type']     = "plain";
        $this->config['smtp_charset']  = 'UTF-8';
    }

    private function mEncode($string)
    {
        return "=?" . $this->config['smtp_charset'] . "?B?". base64_encode($string) . "?=";
    }

    private function send($socket, $data)
    {
        if($this->config['smtp_debug'])
            echo "CLIENT ->: " . str_replace("\r\n", "", $data) . "\n";
        fputs($socket, $data);
    }

    private function server_parse($socket, $response, $line = __LINE__)
    {
        $this->server_response = "";
        while(substr($this->server_response, 3, 1) != ' ')
        {
            if(!($this->server_response = fgets($socket, 800)))
            {
                if($this->config['smtp_debug'])
                    echo "SERVER <-: " . $this->server_response . "\n -- $response\n -- $line\n";
                return false;
            }
            if($this->config['smtp_debug'])
                echo "SERVER <-: " . str_replace("\r\n", "", $this->server_response) . "\n";
        }

        if(!(substr($this->server_response, 0, 3) == $response))
        {
            if($this->config['smtp_debug'])
                echo "SERVER <-: " . $this->server_response . "\n -- $response\n -- $line\n";
            return false;
        }

        return true;
    }

    public function createSimpleLetter($subject, $body, $format = 'html')
    {
        if(!empty($format))
            $this->config['smtp_type'] = $format;
        $this->letterSubject = $subject;
        $this->letterBody    = $body;
    }

    public function createLetter($nameFrom, $mailFrom, $nameTo, $mailTo, $subject, $body, $format = '')
    {
        if(!empty($format))
            $this->config['smtp_type'] = $format;
        $this->letterBody = $body;

        $this->config['smtp_reply_name'] = $nameFrom;
        $this->config['smtp_reply_mail'] = $mailFrom;
        $this->config['smtp_nameto']   = $nameTo;
        $this->config['smtp_to']       = $mailTo;
    }

    public function resetConfig()
    {
        // Google SMTP settings
        $this->config['smtp_host']     = "smtp.gmail.com";
        $this->config['smtp_port']     = 587;
        $this->config['smtp_auth']     = true;

        // CONFIG FILE
        $this->config['smtp_username'] = SMTP_EMAIL;
        $this->config['smtp_password'] = SMTP_PASSWORD;
        $this->config['smtp_namefrom'] = SMTP_NAME;
        $this->config['smtp_from']     = SMTP_EMAIL;
    }

    public function setFormat($format)
    {
        $this->config['smtp_type'] = $format;
    }

    public function setDebugPrint($debug)
    {
        $this->config['smtp_debug'] = $debug;
    }

    public function setSender($name, $mail)
    {
        $this->config['smtp_reply_name'] = $name;
        $this->config['smtp_reply_mail'] = $mail;
    }

    public function setAuth($required, $login, $password)
    {
        $this->config['smtp_auth'] = $required;
        $this->config['smtp_username'] = $login;
        $this->config['smtp_password'] = $password;
    }

    public function setRecipient($email, $name = "")
    {
        $this->recipients[] = array(
            'email' => $email,
            'name' => $name ?: $email
        );
    }

    public function clearRecipients()
    {
        $this->recipients = array();
    }

    public function sendLetter()
    {
        $this->server_response = "";
        $SEND    =  "";
        $SEND   .=  "Date: " . date("D, d M Y H:i:s O") . "\r\n";
        $SEND   .=  "Subject: " . $this->mEncode($this->letterSubject) . "\r\n";
        
        $toHeader = "";
        foreach ($this->recipients as $recipient) {
            $toHeader .= $this->mEncode($recipient['name']) . " <" . $recipient['email'] . ">, ";
        }
        $toHeader = rtrim($toHeader, ", ");

        $SEND   .=
                    "MIME-Version: 1.0\r\n" .
                    "Content-Type: text/" . $this->config['smtp_type'] . "; charset=\"" . $this->config['smtp_charset'] . "\"\r\n" .
                    "Content-Transfer-Encoding: 8bit\r\n" .
                    "From: " .      $this->mEncode($this->config['smtp_namefrom']) .   " <" . $this->config['smtp_from']. ">\r\n" .
                    "To: " . $toHeader . "\r\n" .
                    "X-Mailer: Wohlstand's PHP SMTP Mail script v2.0\r\n" .
                    "X-Priority: 3\r\n" .
                    "\r\n\r\n";

        $SEND .=  $this->letterBody. "\r\n";

        if(($socket = fsockopen($this->config['smtp_host'], $this->config['smtp_port'], $errno, $errstr, 10)) == NULL )
        {
            if($this->config['smtp_debug'])
                echo $errno."FAILED CONNECT to ".$this->config['smtp_host'] . " :".$errstr;
            return false;
        }

        if(!$this->server_parse($socket, "220", __LINE__))
        {
            if($this->config['smtp_debug'])
                echo '<p>FAILED GREETING!</p>\n';
            return false;
        }

        $clientAddres = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : $this->config['smtp_host'];
        $this->send($socket, "EHLO " . $clientAddres . "\r\n");
        if(!$this->server_parse($socket, "250", __LINE__))
        {
            if($this->config['smtp_debug'])
                echo "HELO FAILED!\n";
            fclose($socket);
            return false;
        }

        //STARTTLS 
        $this->send($socket, "STARTTLS\r\n");
        if(!$this->server_parse($socket, "220", __LINE__))
        {
            if($this->config['smtp_debug'])
                echo "STARTTLS FAILED!\n";
            fclose($socket);
            return false;
        }

        //TLS
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        if($this->config['smtp_auth'])
        {
            $this->send($socket, "AUTH LOGIN\r\n");
            if(!$this->server_parse($socket, "334", __LINE__))
            {
                if ($this->config['smtp_debug'])
                    echo "<p>LOGIN FAILED.</p>\n";
                fclose($socket);
                return false;
            }

            $this->send($socket, base64_encode($this->config['smtp_username'])."\r\n");
            if(!$this->server_parse($socket, "334", __LINE__))
            {
                if ($this->config['smtp_debug'])
                    echo '<p>USERNAME FAILED!</p>\n';
                fclose($socket);
                return false;
            }

            $this->send($socket, base64_encode($this->config['smtp_password'])."\r\n");
            if(!$this->server_parse($socket, "235", __LINE__))
            {
                if($this->config['smtp_debug'])
                    echo '<p>PASSWORD FAILED</p>\n';
                fclose($socket);
                return false;
            }
        }

        $this->send($socket, "MAIL FROM: <" . $this->config['smtp_from'] . ">\r\n");
        if (!$this->server_parse($socket, "250", __LINE__)) {
           if ($this->config['smtp_debug']) echo '<p>FAILED MAIL FROM: </p>\n';
           fclose($socket);
           return false;
        }

        foreach ($this->recipients as $recipient) {
            $this->send($socket, "RCPT TO: <" . $recipient['email'] . ">\r\n");
            if (!$this->server_parse($socket, "250", __LINE__))
            {
                if ($this->config['smtp_debug']) echo '<p>FAILED RCPT TO: ' . $recipient['email'] . '</p>\n';
                fclose($socket);
                return false;
            }
        }

        $this->send($socket, "DATA\r\n");
        if (!$this->server_parse($socket, "354", __LINE__)) {
            if ($this->config['smtp_debug'])
                echo '<p>FAILED DATA</p>\n';
            fclose($socket);
            return false;
        }

        $this->send($socket, $SEND . "\r\n.\r\n");
        if (!$this->server_parse($socket, "250", __LINE__))
        {
            if ($this->config['smtp_debug'])
                echo '<p>FAILED SEND</p>\n';
            fclose($socket);
            return false;
        }

        $this->send($socket, "QUIT\r\n");
        fclose($socket);
        return true;
    }
}
