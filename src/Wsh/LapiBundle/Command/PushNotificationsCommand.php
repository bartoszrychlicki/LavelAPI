<?php

namespace Wsh\LapiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use RMS\PushNotificationsBundle\Message\iOSMessage;

class PushNotificationsCommand extends ContainerAwareCommand
{
    private $offerProvider;
    private $output;
    private $numberOfTry;
    private $em;
    private $notificationService;

    protected function configure()
    {
        $this->setName("wsh:lapi:notifications");
        $this->setDescription("Send push notifications with alert status to users.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $numberOfOffersWithUpdatedPriceAll = 0;
        $numberOfOffersWithUpdatedPricePage = 0;
        $pagesWithError = 0;

        $this->offerProvider = $this->getContainer()->get('wsh_lapi.provider.qtravel');
        $this->output = $output;
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->notificationService = $this->getContainer()->get('rms_push_notifications');
        $offerRepo = $this->em->getRepository('WshLapiBundle:Offer');

        $dateToUpdateFrom = new \DateTime('today');
        $dateToUpdateFrom = $dateToUpdateFrom->format("Y-m-d");

        $queryParameters = (object) array(
            'empty_q' => 't',
            'f_adate_min' => $dateToUpdateFrom,
            'page' => 1
        );


        $json = $this->fetchPage($queryParameters);

        if ($json === false) {
            $this->output->writeln(sprintf("<error>---[ERROR]-Nastapil blad przy dziesieciokrotnej probie pobrania pierwszej strony. "
                ."Operacja zostanie przerwana.</error>"));
            die();
        }

        $numOfOffers = $json->p->p_offers;
        $numOfPages = $json->p->p_pages;
        $time = ceil($json->time);
        $estimatedTimeSec = $numOfPages * 6;

        $output->writeln("");
        $output->writeln(sprintf("Znaleziono <fg=green>%s</fg=green> zaktualizowanych ofert na "
            ."<fg=green>%s</fg=green> stronach zaktualizowanych dnia <fg=red>%s</fg=red>. "
            ."Czas pobierania pierwszej strony: <fg=green>%s</fg=green>. "
            ."Szacowany czas pobierania i aktualizowania ofert: "
            ."<fg=green>%s</fg=green> sec.", $numOfOffers, $numOfPages, $dateToUpdateFrom, $time, $estimatedTimeSec));

        for ($page = 1; $page <= $numOfPages; $page++) {
            $queryParameters->page = $page;
            $offersFromProvider = $page == 1 ? $json : $this->fetchPage($queryParameters);
            $estimatedTimeSec -= $time;

            if ($offersFromProvider !== false) {
                $output->writeln(sprintf("Strona <fg=green>%s</fg=green> pobrana bez bledow przy <fg=green>%s</fg=green> "
                ."probie. Szacowany pozostaly czas: <fg=green>%s</fg=green> sec", $page, $offersFromProvider->try, $estimatedTimeSec));

                $numberOfOffersWithUpdatedPricePage = 0;

                foreach ($offersFromProvider->offers->o as $offer) {

                    $offerCode = $offer->o_details->o_code;
                    $offerFromDB = $offerRepo->findOneBy(array('id' => $offerCode));

                    if ($offerFromDB) {
                        if ($offerFromDB->getPrice() != $offer->o_details->o_bprice) {
                            $numberOfOffersWithUpdatedPricePage++;
                            $numberOfOffersWithUpdatedPriceAll++;
                            foreach ($offerFromDB->getReadStatus() as $rds) {
                                $rds->setIsRead(false);
                                $this->em->persist($rds);
                            }
                        }
                    }
                }

                $offers = $this->offerProvider->handleOfferResponse($offersFromProvider);

                foreach ($offers->getKeys() as $key) {
                    if ($key > 100) {
                        $this->em->persist($offers->get($key));
                    }
                }

                $this->em->flush();

                $output->writeln(sprintf("Ilosc ofert z zaktualizowana cena na %s stronie: "
                    ."<fg=green>%s</fg=green>, wszystkich: <fg=green>%s</fg=green>", $page,
                    $numberOfOffersWithUpdatedPricePage, $numberOfOffersWithUpdatedPriceAll));

            } else {
                $pagesWithError++;
            }

        }

        $this->recalculateAlerts();
        $this->sendNotifications();
        $this->resetAlertsUpdatedOffers();

    }

    private function fetchPage($queryParams)
    {
        $json = null;
        $sucess = true;
        $try = 0;
        $maxTry = ($queryParams->page == 1) ? 10 : 3;
        $timeStart = 0;
        $timeStop = 0;

        for ($i = 1; $i <= $maxTry; $i++) {
            try {
                $try++;

                if ($queryParams->page == 1) { $timeStart = $this->getmicrotime();}
                $response = $this->offerProvider->findOffersByParams($queryParams);
                if ($queryParams->page == 1) { $timeStop = $this->getmicrotime();}

                if (!$response || empty($response)) {
                    throw new \Exception();
                }

                $json = json_decode($response);

                if (!$json || !is_object($json) || empty($json) || !($json->offers->o) || count($json->offers->o) <= 0) {
                    throw new \Exception();
                } else {
                    goto end;
                }

            } catch (\Exception $e) {
                $numEnd = null;
                $again = ($i == $maxTry) ? null : "Ponawianie proby.";
                $sucess = ($i == $maxTry) ? false : true;
                $this->output->writeln(sprintf("<error>---[ERROR]-Nastapil blad podczas pobierania %s strony po raz %s. %s</error>",
                    $queryParams->page, $i, $again));
            }
        }

        if (!$sucess) {
            if ($queryParams->page !== 1) {
                $this->output->writeln(sprintf("<error>---[ERROR]-Nastapily bledy przy %s-krotnej probie pobrania %s strony. "
                    ."Strona zostanie pominieta.</error>", $maxTry, $queryParams->page));
            }

            return false;
        }

        end:


        if ($queryParams->page == 1) {
            $json->time = $timeStop - $timeStart;
        }

        $json->try = $try;
        return $json;
    }

    private function recalculateAlerts()
    {
        $alertRepo = $this->em->getRepository('WshLapiBundle:Alert');
        $alerts = $alertRepo->findAll();



        if ($alerts) {
            foreach ($alerts as $alert) {
                $offersUpdated = 0;
                $offersReaded = 0;
                $offersUnreaded = 0;
                if ($alert->getOffers() || sizeof($alert->getOffers()) > 0) {
                    foreach ($alert->getOffers() as $offer) {
                        if ($offer->getReadStatus() || sizeof($offer->getReadStatus()) > 0) {
                            foreach ($offer->getReadStatus() as $rds) {
                                if ($rds->getAlertId()->getId() == $alert->getId()) {
                                    if ($rds->getIsRead() == true) {
                                        $offersReaded++;
                                    } elseif ($rds->getIsread() == false && $offer->getIsPriceLastUpdated() == true) {
                                        $offersUnreaded++;
                                        $offersUpdated++;
                                    } elseif ($rds->getIsread() == false && $offer->getIsPriceLastUpdated() == false) {
                                        $offersUnreaded++;
                                    }
                                }
                            }
                        }
                    }

                    $alert->setOffersRead($offersReaded);
                    $alert->setOffersWithUpdatedPrice($offersUpdated);
                    $alert->setOffersUnread($alert->getOffersTotal() - $offersReaded);
                    $this->em->persist($alert);
                }
            }
        }

        $this->em->flush();

    }

    private function sendNotifications()
    {
        $userRepo = $this->em->getRepository('WshLapiBundle:User');
        $users = $userRepo->findAll();

        $numberOfSentNotif = 0;

        foreach ($users as $user) {
            if ($user->getApplePushToken() !== null) {
                $numberOfOffersWithupdatedPrice = 0;
                $alerts = $user->getAlerts();

                foreach ($alerts as $alert) {
                    $numberOfOffersWithupdatedPrice += $alert->getOffersWithUpdatedPrice();
                }

                if ($numberOfOffersWithupdatedPrice > 0) {

                    $notification = new iOSMessage();
                    $notification->setDeviceIdentifier($user->getApplePushToken());

                    if ($numberOfOffersWithupdatedPrice == 1) {
                        $notification->setMessage(sprintf("W jednej ofercie pasującej do Twoich alertów została zaktualizowana cena. "
                        , $numberOfOffersWithupdatedPrice));
                    } else if ($numberOfOffersWithupdatedPrice > 1) {
                        $notification->setMessage(sprintf("W %s ofertach pasujących do Twoich alertów została zaktualizowana cena. "
                        , $numberOfOffersWithupdatedPrice));
                    }

                    $this->notificationService->send($notification);
                    $numberOfSentNotif++;
                }
            }
        }

        $this->output->writeln(sprintf("Zostalo wyslanych %s powiadomien.", $numberOfSentNotif));
    }

    private function resetAlertsUpdatedOffers()
    {
        $alertRepo = $this->em->getRepository('WshLapiBundle:Alert');
        $alerts = $alertRepo->findAll();

        foreach ($alerts as $alert) {
            $alert->setOffersWithUpdatedPrice(0);
            $this->em->persist($alert);
        }

        $this->em->flush();

    }

    private function getmicrotime()
    {
        $microtime = explode(' ', microtime());
        return $microtime[1] . substr($microtime[0], 1);
    }
}