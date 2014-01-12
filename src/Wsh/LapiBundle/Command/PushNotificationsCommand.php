<?php

namespace Wsh\LapiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Wsh\LapiBundle\Entity\OfferUpdate;

use RMS\PushNotificationsBundle\Message\iOSMessage;

class PushNotificationsCommand extends ContainerAwareCommand
{
    private $offerProvider;
    private $output;
    private $em;
    private $notificationService;

    private $allOffersInDB;
    private $pagesToFetch;
    private $offerCount;

    private $skippedPages;
    private $pagesWithoutErrors;
    private $pagesWithErrors;

    private $offerUpdateEntity;

    private $updatedOffers = 0;

    const  OFFERS_ON_PAGE = 100;

    protected function configure()
    {
        $this->setName("wsh:lapi:notifications");
        $this->setDescription("Send push notifications with alert status to users.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $pagesWithError = 0;

        $this->offerProvider = $this->getContainer()->get('wsh_lapi.provider.qtravel');
        $this->output = $output;
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->notificationService = $this->getContainer()->get('rms_push_notifications');
        $this->pagesWithErrors = 0;
        $this->pagesWithoutErrors = 0;
        $this->skippedPages = 0;
        $offerRepo = $this->em->getRepository('WshLapiBundle:Offer');

        $this->allOffersInDB = $offerRepo->findAll();
        $this->offerCount = sizeof($this->allOffersInDB);

        $this->pagesToFetch = ceil($this->offerCount / self::OFFERS_ON_PAGE);

        $output->writeln("");
        $output->writeln(sprintf("Ofert w bazie - <fg=green>%s</fg=green> \r\nStron do pobrania - <fg=green>%s</fg=green>",
            $this->offerCount, $this->pagesToFetch ));

        $this->offerCount--;


        $this->offerUpdateEntity = new OfferUpdate();
        $this->offerUpdateEntity->setStartedAt(new \DateTime());
        $this->offerUpdateEntity->setStatus('Fetching pages from provider');
        $this->em->persist($this->offerUpdateEntity);
        $this->em->flush();

        $queryParameters = (object) array(
            'empty_q' => 't'
        );

        for ($page = 1; $page <= $this->pagesToFetch; $page++) {


            $offersIds = null;

            $maxOfferId = ($page * self::OFFERS_ON_PAGE) - 1;
            $minOfferId = $maxOfferId - (self::OFFERS_ON_PAGE) + 1;

            for ($i = $minOfferId; $i <= $maxOfferId; $i++) {
                if(array_key_exists($i, $this->allOffersInDB)) {
                    $id = explode("-", $this->allOffersInDB[$i]->getId());
                    $offersIds .= $id[1];

                    $offersIds .= ($i != $this->offerCount && $i != $maxOfferId) ? ',' : '';
                }
            }

            $queryParameters->page = $page;
            $queryParameters->f_oid = $offersIds;
            $offersFromProvider = $this->fetchPage($queryParameters);


            if ($offersFromProvider !== false) {
                $output->writeln(sprintf("Strona <fg=green>%s</fg=green> pobrana bez bledow przy <fg=green>%s</fg=green> "
                ."probie.", $page, $offersFromProvider->try));

                $numberOfOffersWithUpdatedPricePage = 0;

                foreach ($offersFromProvider->offers->o as $offer) {

                    $offerCode = $offer->o_details->o_id;
                    $offerDB = $offerRepo->findOfferWithIdLike($offerCode);

                    foreach($offerDB as $offerFromDB) {

                        if ($offerFromDB) {
                            if ($offerFromDB->getPrice() != $offer->o_best->o_b_price) {
                                $numberOfOffersWithUpdatedPricePage++;
                                $this->updatedOffers++;
                                $offerFromDB->setIsPriceLastUpdated(true);
                                $offerFromDB->setPrice($offer->o_best->o_b_price);
                                foreach ($offerFromDB->getReadStatus() as $rds) {
                                    $rds->setIsRead(false);
                                    $this->em->persist($rds);
                                }
                            } else {
                                $offerFromDB->setIsPriceLastUpdated(false);
                            }

                            $this->em->persist($offerFromDB);
                        }
                    }
                }

                $this->em->flush();

                $output->writeln(sprintf("Ilosc ofert z zaktualizowana cena na %s stronie: "
                    ."<fg=green>%s</fg=green>, wszystkich: <fg=green>%s</fg=green>", $page,
                    $numberOfOffersWithUpdatedPricePage, $this->updatedOffers));

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
        $success = true;
        $try = 0;
        $maxTry =  10;

        for ($i = 1; $i <= $maxTry; $i++) {
            try {
                $try++;

                $response = $this->offerProvider->findOffersByParams($queryParams);

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
                $success = ($i == $maxTry) ? false : true;
                $this->output->writeln(sprintf("<error>---[ERROR]-Nastapil blad podczas pobierania %s strony po raz %s. %s</error>",
                    $queryParams->page, $i, $again));
            }
        }

        if (!$success) {
                $this->output->writeln(sprintf("<error>---[ERROR]-Nastapily bledy przy %s-krotnej probie pobrania %s strony. "
                    ."Strona zostanie pominieta.</error>", $maxTry, $queryParams->page));
                $this->skippedPages++;

            return false;
        }

        end:

        $json->try = $try;

        if ($success && $try == 1) {
            $this->pagesWithoutErrors++;
        } else if($success && $try > 1) {
            $this->pagesWithErrors++;
        }

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
        $this->offerUpdateEntity->setUpdatedOffers($this->updatedOffers);
        $this->offerUpdateEntity->setStatus('Sending notifications');
        $this->em->persist($this->offerUpdateEntity);
        $this->em->flush();

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

        $this->offerUpdateEntity->setFinishedAt(new \DateTime());
        $this->offerUpdateEntity->setStatus('Finished');
        $this->offerUpdateEntity->setSentNotifications($numberOfSentNotif);
        $this->em->persist($this->offerUpdateEntity);
        $this->em->flush();

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
}