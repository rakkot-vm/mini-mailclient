<?php

namespace app\models;

use Exception;
use Yii;
//use yii\base\Exception;

/**
 * This is the model class for table "mail".
 *
 * @property int $id
 * @property string $mailto
 * @property string $mailfrom
 * @property int $date
 * @property string $subject
 * @property string $body
 */
class Mail extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'mail';
    }

    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date'], 'integer'],
            [['body'], 'string'],
            [['mailto', 'mailfrom'], 'string', 'max' => 255],
            [['subject'], 'string', 'max' => 998],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mailto' => 'кому',
            'mailfrom' => 'от',
            'date' => 'Date',
            'subject' => 'Тема',
            'body' => 'Body',
        ];
    }

    public static function getImap()
    {
        $imap = imap_open( Yii::$app->params['mailParams']['imapHost'],
            Yii::$app->params['mailParams']['email'],
            Yii::$app->params['mailParams']['pass']);

        if($imap) {
            return $imap;
        }

        $error = 'Error imap_open, '.imap_last_error();
        throw new Exception($error);
    }

    public function send()
    {
        $this->date = time();
        $this->mailfrom = Yii::$app->params['mailParams']['email'];

        if($this->validate()){
            Yii::$app->mailer->compose()
                ->setFrom(Yii::$app->params['mailFrom'])
                ->setTo($this->mailto)
                ->setSubject($this->subject)
                ->setHtmlBody($this->body)
                ->send();
            $this->save();

            return $this->id;
        }
    }

    public function getMailto($msg_header)
    {
        return $msg_header->to[0]->mailbox."@".$msg_header->to[0]->host;
    }

    public function getMailfrom($msg_header)
    {
        return $msg_header->from[0]->mailbox."@".$msg_header->to[0]->host;
    }

    public function getSubject($msg_header)
    {
        $mime = imap_mime_header_decode($msg_header->subject);

        $subject = '';
        foreach($mime as $key => $m){

            if(strtolower($m->charset) != 'utf-8'){
                $subject .= iconv(strtolower($m->charset), "utf-8", $m->text);
            }else{
                $subject .= $m->text;
            }
        }

        return $subject;
    }

    public function getDate($msg_header)
    {
        return time($msg_header->MailDate);
    }

    public function getBody($imap, $i)
    {
        $msg_structure = imap_fetchstructure($imap, $i);
        $msg_body      = imap_fetchbody($imap, $i, 1);

        $body = "";

        $recursive_data = $this->_recursive_search($msg_structure);

        if($recursive_data["encoding"] == 0 ||
            $recursive_data["encoding"] == 1){
            $body = $msg_body;
        }

        if($recursive_data["encoding"] == 4){
            $body = $this->_structure_encoding($recursive_data["encoding"], $msg_body);
        }

        if($recursive_data["encoding"] == 3){
            $body = $this->_structure_encoding($recursive_data["encoding"], $msg_body);
        }

        if($recursive_data["encoding"] == 2){
            $body = $this->_structure_encoding($recursive_data["encoding"], $msg_body);
        }

        if(strtolower($recursive_data["charset"]) != "utf-8"){
            $body = iconv(strtolower($recursive_data["charset"]), "utf-8", $msg_body);
        }
    }

    private function _recursive_search($structure){

        $encoding = "";

        if($structure->subtype == "HTML" ||
            $structure->type == 0){

            if($structure->parameters[0]->attribute == "charset"){

                $charset = $structure->parameters[0]->value;
            }

            return array(
                "encoding" => $structure->encoding,
                "charset"  => strtolower($charset),
                "subtype"  => $structure->subtype
            );
        }else{

            if(isset($structure->parts[0])){

                return $this->_recursive_search($structure->parts[0]);
            }else{

                if($structure->parameters[0]->attribute == "charset"){

                    $charset = $structure->parameters[0]->value;
                }

                return array(
                    "encoding" => $structure->encoding,
                    "charset"  => strtolower($charset),
                    "subtype"  => $structure->subtype
                );
            }
        }
    }

    private function _structure_encoding($encoding, $msg_body){

        switch((int) $encoding){

            case 4:
                $body = imap_qprint($msg_body);
                break;

            case 3:
                $body = imap_base64($msg_body);
                break;

            case 2:
                $body = imap_binary($msg_body);
                break;

            case 1:
                $body = imap_8bit($msg_body);
                break;

            case 0:
                $body = $msg_body;
                break;

            default:
                $body = "";
                break;
        }

        return $body;
    }

    public function getImapMail($imap, $i)
    {
        $msg_header = imap_header($imap, $i);

        $this->mailto = $this->getMailto($msg_header);
        $this->mailfrom = $this->getMailfrom($msg_header);
        $this->date = $this->getDate($msg_header);
        $this->subject = $this->getSubject($msg_header);
        $this->body = $this->getBody($imap, $i);
    }
}
