<?php

namespace App\Utils;
use Nette, Latte, Nette\Mail\Message, App\Model;
use Nette\Mail\IMailer;


class ScheduledTasks //extends Nette\Object
{

    use Nette\SmartObject;

    /** @var Nette\Application\UI\ITemplateFactory */
    private $templateFactory;

    /** @var Model\Reports */
    private $reports;

    /** @var IMailer */
    private $mailer;


    function __construct(IMailer $mailer, Model\Reports $reports)
    {
        $this->mailer = $mailer;
        $this->reports = $reports;
    }

    public function emailMonthlyReport(){
        $path = __DIR__ . '/../AdminModule/templates/Email/report.latte';
        $latte = new Latte\Engine; 

        $lmwt = $this->reports->last_month_with_types();        

        $parameters = array('nadpis' => "Měsíční report",
        'lmwt' => $lmwt,
        'text' => "Minulý měsíc ".date('n/Y', strtotime("-1 day"))." byly založeny firmy v násedlujících kategoiích.",
        );
        
        $html = $latte->renderToString($path, $parameters);

        $mail = new Message;
        $mail->setFrom('1. Pirátská <urad@pirati.cz>')
                ->addTo("steve@strebl.com")                
                ->setSubject("Test schedule")
                ->setHTMLBody($html);
        $this->mailer->send($mail);
    }
}