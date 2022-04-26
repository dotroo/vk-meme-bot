<?php

namespace App\Console\Worker;

use App\ImgurBundle\Client\ImgurApiClient;
use App\VKBundle\Client\VkClient;
use App\VKBundle\Model\BotResponseModel;
use GuzzleHttp\Exception\GuzzleException;
use Imgur\Api\Gallery;
use Symfony\Component\Console\Style\SymfonyStyle;
use VK\Exceptions\Api\VKApiMessagesDenySendException;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;

class NewMessageWorker implements WorkerInterface
{
    const QUEUE_NAME = 'new.message';

    const COMMUNITY_BOT_COMMAND_LENGTH = 2;
    const CHAT_BOT_COMMAND_LENGTH = 3;
    const GALLERY_SEARCH_TOP = 'top';

    /** @var VkClient  */
    private VkClient $vkApiClient;

    /** @var ImgurApiClient  */
    private ImgurApiClient $imgurApiClient;

    public function __construct(VkClient $vkApiClient, ImgurApiClient $imgurApiClient)
    {
        $this->vkApiClient = $vkApiClient;
        $this->imgurApiClient = $imgurApiClient;
    }

    /**
     * @inheritDoc
     */
    public function run(array $workload, SymfonyStyle $io): void
    {
        $io->writeln('Start worker');
        $io->writeln('Workload: ' . json_encode($workload));

        $botResponse = BotResponseModel::fromVkObject($workload);

        $intention = $this->getMessageIntention($botResponse->getText());

        if (!is_null($intention)) {
            $io->writeln('Recognised intention: ' . $intention);

            $client = $this->imgurApiClient->getClient();

            /** @var Gallery $galleryService */
            $galleryService = $client->api('gallery');
            $result = $galleryService->search($intention, self::GALLERY_SEARCH_TOP);

            $image = null;
            if (!empty($result)) {
                //returns the first found image
                shuffle($result);
                foreach ($result as $item) {
                    switch ($item['is_album']) {
                        case true:
                            $image = reset($item['images']);
                            break;
                        case false:
                            $image = (array)$item;
                            break;
                    }

                    $io->writeln('Got an image');
                    $io->writeln(json_encode($image));
                    break;
                }
            }

            if (!is_null($image)) {
                try {
                    $vkImage = $this->processImage($image['link'], $botResponse->getPeerId());
                    $io->writeln('Image uploaded: ' . json_encode($vkImage, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    $botResponse->setAttachment(sprintf('photo%d_%d', $vkImage[0]['owner_id'], $vkImage[0]['id']));
                    $botResponse->setMessage($image['description']);
                } catch (\Throwable $e) {
                    $io->error($e->getMessage());
                    $botResponse->setMessage('Error: ' . $e->getMessage());
                }
            } else {
                $botResponse->setMessage('Мем не найден');
            }
        }

        $messages = $this->vkApiClient->getApiClient()->messages();
        $io->writeln('Sending the reply');
        $io->writeln($botResponse->getMessage() ?: $botResponse->getAttachment());

        try{
            $messages->send($this->vkApiClient->getVkAccessKey(), $botResponse->toVkApi());
        } catch (VKApiException | VKClientException $e) {
            $io->error($e->getMessage());
        }
    }

    /**
     * @param string $text
     * @return string|null
     */
    protected function getMessageIntention(string $text): ?string
    {
        $messageParts = explode(' ', $text);
        $intention = null;

        switch (count($messageParts)) {
            case self::COMMUNITY_BOT_COMMAND_LENGTH:
                if (strtolower($messageParts[0]) === 'meme') {
                    $intention = $messageParts[1];
                }
                break;
            case self::CHAT_BOT_COMMAND_LENGTH:
                if (strtolower($messageParts[1]) === 'meme') {
                    $intention = $messageParts[2];
                }
                break;
            default:
                break;
        }

        return $intention;
    }

    /**
     * @param string $imageUrl
     * @param int $vkPeerId
     * @return array
     * @throws GuzzleException
     * @throws VKApiMessagesDenySendException
     * @throws VKApiException
     * @throws VKClientException
     */
    protected function processImage(string $imageUrl, int $vkPeerId): array
    {
        $tempfname = tempnam(sys_get_temp_dir(), 'tmp_img_');
        $image = file_get_contents($imageUrl);
        file_put_contents($tempfname, $image);

        $vkImage = $this->vkApiClient->uploadImage($tempfname, $vkPeerId);

        unlink($tempfname);

        return $vkImage;
    }
}
