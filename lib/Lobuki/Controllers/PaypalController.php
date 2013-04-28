<?php

require_once 'Narvalo.php';
require_once 'Lobuki.php';

use Lobuki;
use Narvalo\Web;
use Narvalo\Web\Mvc;

/*
 * Controller
 */

/* {{{ PaypalController */

class PaypalController extends Lobuki\LobukiBaseController {
    const EOL = "\r\n";
    const BACKUP_EMAIL = "policromia@gmail.com";

    /* {{{ validate() */

    // Async notification coming from PayPal
    public function validate() {
        // See _retourcommande.php
        // lire le formulaire provenant du système PayPal et ajouter 'cmd'
        $req = 'cmd=_notify-validate';

        foreach ($_POST as $key => $value) {
            $value = \urlencode(\stripslashes($value));
            $req .= "&$key=$value";
        }

        // renvoyer au système PayPal pour validation
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . \strlen($req) . "\r\n\r\n";
        $fp = \fsockopen($this->currentSettings->paypalNotifyUrl(),
            443, $errno, $errstr, 30);

        /*
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];
         */

        $body = '';

        if (!$fp) {
            // ERREUR HTTP
        } else {
            \fputs($fp, $header . $req);
            while (!\feof($fp)) {
                $body = \fgets($fp, 1024);
                if (\strcmp($body, "VERIFIED") == 0) {
                    // vérifier que payment_status a la valeur Completed
                    // vérifier que txn_id n'a pas été précédemment traité
                    // vérifier que receiver_email est votre adresse email PayPal principale
                    // vérifier que payment_amount et payment_currency sont corrects
                    // traiter le paiement
                }
                else if (\strcmp($body, "INVALID") == 0) {
                    // consigner pour étude manuelle
                }
            }

            \fclose($fp);
        }

        \mail(self::BACKUP_EMAIL, 'Lobuki validate', $body);

        // Model
        $model = new LobukiPaypalNotifyModel();

        return new LobukiPaypalNotifyView($model);
    }

    /* }}} */
    /* {{{ synch() */

    public function synch() {
        // See confirm.php
        // And @commande.php for confirmation
        $fp = \fsockopen($this->currentSettings->paypalNotifyUrl(),
            443, $errno, $errstr, 30);

        $isSuccess = FALSE;
        $body = '';

        if (!$fp) {
            // HTTP ERROR

        } else {
            // read the post from PayPal system and add 'cmd'
            $tx_token = $_GET['tx'];

            $req = 'cmd=_notify-synch'
                . '&tx=' . $tx_token
                . '&at=' . $this->currentSettings->paypalAuthToken();

            // post back to PayPal system to validate
            $header .= 'POST /cgi-bin/webscr HTTP/1.0 ' . self::EOL
                . 'Content-Type: application/x-www-form-urlencoded' . self::EOL
                . 'Content-Length: ' . strlen($req) . self::EOL . self::EOL;

            \fputs($fp, $header . $req);

            // read the body data
            $in_body = FALSE;

            while (!\feof($fp)) {
                $line = \fgets($fp, 1024);

                if (0 == \strcmp($line, self::EOL)) {
                    $in_body = TRUE;
                } else if ($in_body) {
                    $body .= $line;
                }
            }

            // parse the data
            $lines = \explode("\n", $body);

            $keyarray = array();

            $count = \count($lines);

            if (\strcmp($lines[0], "SUCCESS") == 0) {
                $isSuccess = TRUE;

                $basket = new Basket(
                    $this->dataStore, LobukiSession::GetBasketId());
                $basket->reset();

                for ($i = 1; $i < $count; $i++) {
                    list($key, $val) = \explode('=', $lines[$i]);
                    $keyarray[\urldecode($key)] = \urldecode($val);
                }

                // check the payment_status is Completed
                // check that txn_id has not been previously processed
                // check that receiver_email is your Primary PayPal email
                // check that payment_amount/payment_currency are correct
                // process payment
                /*
                $firstname = $keyarray['first_name'];
                $lastname  = $keyarray['last_name'];
                $itemname  = $keyarray['item_name'];
                $amount    = $keyarray['payment_gross'];
                 */

            } else if (\strcmp($lines[0], "FAIL") == 0) {
                // log for manual investigation
            }
        }

        \fclose($fp);

        \mail(self::BACKUP_EMAIL, 'Lobuki synch', $body);

        // Model
        $model = new LobukiPaypalSynchModel();
        $model->title = 'Merci';
        $model->isSuccess = $isSuccess;

        return new LobukiPaypalSynchView($model);
    }

    /* }}} */

    /* {{{
    private function envoiRecapCommande($idCommande,$mailto){ 
        $boundary = md5( uniqid ( rand() ) );

        $mailheaders = "From: Votre commande Lobuki-Sticker <contact@lobuki-sticker.com>\r\n";
        $mailheaders .="Reply-To: <contact@lobuki-sticker.com>\r\n";
        $mailheaders .="MIME-Version: 1.0\r\n";
        $mailheaders .="Content-Type: multipart/alternative; boundary=\"$boundary\"";
        $mailtoc = "tettedgui@gmail.com";

        $mailsubject = "Votre commande sur lobuki-sticker.com";

        $pathPage = SITEPATH;
        $page = "@commande.php?" . base64_encode($idCommande);

        //$body_html = recupMessage($pathPage.$page);
        //$body_html = stripslashes($body_html);
        //$tag_strg_replace = "images/";
        //$body_html = str_replace($tag_strg_replace, SITEPATH."images/", $body_html);
        //$lien = SITEPATH."@commande.php?" . base64_encode("id_commande=".$idCommande);

        $body_txt = "Bonjour,

        Votre logiciel de messagerie ne vous permet pas de lire en html l'e-mail récapitulatif de votre commande.

        Vous pouvez copier-coller ce lien dans votre navigateur pour le lire.
        $lien

        L'équipe Lobuki-sticker.com";

        $message = "--" . $boundary . "\n";
        $message .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= $body_txt;
        $message .= "\n\n";
        $message .= "--" . $boundary . "\n";
        $message .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= $body_html;
        $message .= "\n\n";
        $message .= "--" . $boundary . "--\n";

        return mail($mailto, $mailsubject, $message, $mailheaders);
    }
		function recupMessage($file) {
				
				if($myfile = fopen($file,"r")) {
						while(!feof($myfile))
								{	
											$file_log = $file_log . fgetc($myfile);

												}
							fclose ($myfile);
							$file_log = addslashes($file_log);
								}	

					return $file_log;
		}

		}}} */
}

/* }}} */

/*
 * Views
 */

/* {{{ LobukiPaypalNotifyView */

class LobukiPaypalNotifyView extends Mvc\PageBase {
    public function __construct(LobukiPaypalNotifyModel $_model_) {
          $this->model = $_model_;
    }

    public function getViewPath() {
        return Lobuki\LobukiApp::ViewPath('paypal', 'notify', 'main.php');
    }
}

/* }}} */
/* {{{ LobukiPaypalSynchView */

class LobukiPaypalSynchView extends Lobuki\LobukiChildView {
    public function __construct(LobukiPaypalSynchModel $_model_ = NULL) {
        parent::__construct('paypal', 'synch', $_model_);
    }
}

/* }}} */

/*
 * Models
 */

/* {{{ LobukiPaypalNotifyModel */

class LobukiPaypalNotifyModel {
}

/* }}} */
/* {{{ LobukiPaypalSynchModel */

class LobukiPaypalSynchModel extends Lobuki\LobukiMasterModel {
    public $isSuccess;
}

/* }}} */

// EOF

