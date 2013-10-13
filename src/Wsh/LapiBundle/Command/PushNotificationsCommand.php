<?php

namespace Wsh\LapiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use RMS\PushNotificationsBundle\Message\iOSMessage;

class PushNotificationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName("wsh:lapibundle:notifications");
        $this->setDescription("Send push notifications with alert status to users.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $numberOfSentNotifications = 0;

        $offerProvider = $this->getContainer()->get('wsh_lapi.provider.qtravel');
        $notificationService = $this->getContainer()->get('rms_push_notifications');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $userRepo = $em->getRepository('WshLapiBundle:User');

        $users = $userRepo->findAll();

        foreach ($users as $user) {
            $numberOfUpdatedOffers = 0;
            $numberOfUpdatedAlerts = 0;
            $alerts = $user->getAlerts();


            if ($alerts) {
                foreach ($alerts as $alert) {
                    $queryParameters = $alert->getSearchQueryParams();
                    $lastNotificationDate = $alert->getLastNotificationDate();
                    $queryParameters->f_adate_min = $lastNotificationDate->format("Y-m-d");
                    $response = $offerProvider->findOffersByParams($queryParameters);
                    $json = json_decode($response);

                    if (is_object($json)) {
                        $numOfOffers = $json->p->p_offers;

                        if ($numOfOffers != 0) {
                            $numberOfUpdatedAlerts++;
                            $numberOfUpdatedOffers += $numOfOffers;
                        }
                    }
                }
            }

            if ($numberOfUpdatedAlerts != 0 && $numberOfUpdatedOffers != 0) {
                $notification = new iOSMessage();
                $notification->setDeviceIdentifier($user->getAppId());
                if ($numberOfUpdatedOffers == 1) {
                    $notification->setMessage(sprintf('There is %s updated offers for'
                       .' %s of Yours alerts.', $numberOfUpdatedOffers, $numberOfUpdatedAlerts));
                } else {
                    $notification->setMessage(sprintf('There are %s updated offers for'
                        .'%s of Yours alerts.', $numberOfUpdatedOffers, $numberOfUpdatedAlerts));
                }

                $notificationService->send($notification);
                $numberOfSentNotifications++;
            }


            $output->writeln(sprintf('There is <fg=green>%s</fg=green> updated offers for <fg=green>%s</fg=green> of alerts'
                .' for user with id <fg=red>%s</fg=red>.',
                $numberOfUpdatedOffers, $numberOfUpdatedAlerts, $user->getId())); // TEMP
        }

        $output->writeln(sprintf('<fg=green>%s</fg=green> notifications were sent.', $numberOfSentNotifications)); /* TEMP */

    }
}