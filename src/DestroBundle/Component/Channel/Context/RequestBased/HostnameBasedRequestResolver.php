<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DestroBundle\Component\Channel\Context\RequestBased;

use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Locale\Model\Locale;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\Channel\Context\RequestBased\RequestResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class HostnameBasedRequestResolver implements RequestResolverInterface
{
    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepository;

    /**
     * @var RepositoryInterface
     */
    private $localeRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param RepositoryInterface $localeRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository, RepositoryInterface $localeRepository)
    {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findChannel(Request $request): ?ChannelInterface
    {
        // Returns null if there is not setting for this host.
        $channelUsingHost = $this->channelRepository->findOneByHostname($request->getHost());

        // Returns default locale if there's nothing.
        /* @var Locale $locale */
        $locale = $this->localeRepository->findOneBy(['code' => $request->getLocale()]);
        $channel = $locale->getChannel();


        // Use channel derived from hostname, if it's set.
        if ($channelUsingHost != null){
            return $channelUsingHost;
        }

        // Using channel assigned to locale instead.
        if ($channel != null) {

            return $channel;
        }

        // Using default channel.
        $channels = $this->channelRepository->findByName("Default");
        return array_pop($channels);
    }
}
