<?php

namespace Wsh\LapiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use RMS\PushNotificationsBundle\Message\iOSMessage;

class PushNotificationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName("wsh:lapi:notifications");
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
            if($user->getApplePushToken() != null) {
                $numberOfUpdatedOffers = 0;
                $numberOfUpdatedAlerts = 0;
                $alerts = $user->getAlerts();


                if ($alerts) {
                    foreach ($alerts as $alert) {

                        try {
                            $queryParameters = $alert->getSearchQueryParams();
                            $lastNotificationDate = $alert->getLastNotificationDate();
                            $queryParameters->f_adate_min = $lastNotificationDate->format("Y-m-d");
                            $response = $offerProvider->findOffersByParams($queryParameters);
                        } catch(\Exception $e) {
                            $output->writeln(sprintf('<fg=red>An error occurs while trying to send notification'
                            .'for user with id %s</fg=red>.', $user->getId())); //TEMP
                            goto end;
                        }

                        $json = json_decode($response);

                        if (is_object($json)) {
                            $numOfOffers = $json->p->p_offers;
                            $numOfPages = $json->p->p_pages;
                            $alert->setNumberOfPagesInUpdate($numOfPages);
                            $alert->setPreviousNotificationDate($alert->getLastNotificationDate());
                            $alert->setLastNotificationDate(new \DateTime());

                            if ($numOfOffers != 0) {
                                $numberOfUpdatedAlerts++;
                                $numberOfUpdatedOffers += $numOfOffers;
                            }
                        }
                        $em->persist($alert);
                    }
                }

                if ($numberOfUpdatedAlerts != 0 && $numberOfUpdatedOffers != 0) {
                    $notification = new iOSMessage();
                    $notification->setDeviceIdentifier($user->getApplePushToken());
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

                end:
            } else {
                $output->writeln(sprintf('<fg=red>For user with id %s push notifications token is not set'
                    .'</fg=red>.', $user->getId())); //TEMP
            }
        }

        $em->flush();

        $output->writeln(sprintf('<fg=green>%s</fg=green> notifications were sent.', $numberOfSentNotifications)); /* TEMP */

    }
}