<?php

namespace App\VKBundle\Model;

class BotResponseModel
{
    private const DEFAULT_REPLY = 'Чтобы получить мем, напиши мне "мем {тематика}"';

    /**
     * Reply destination
     *
     * @var int
     */
    private int $peerId;

    /**
     * Bot's reply
     *
     * @var string
     */
    private string $message = self::DEFAULT_REPLY;

    /**
     * Text from the user
     *
     * @var string
     */
    private string $text;

    /**
     * @param int $peerId
     * @param string $message
     * @param string $text
     */
    protected function __construct(int $peerId, string $text)
    {
        $this->peerId = $peerId;
        $this->text = $text;
    }

    /**
     * @param array $object
     * @return static
     */
    public static function fromVkObject(array $object): self
    {
        return new self($object['peer_id'], $object['text']);
    }

    /**
     * @return array
     */
    public function toVkApi(): array
    {
        return [
            'peer_id' => $this->peerId,
            'message' => $this->message,
            'random_id' => rand(),
        ];
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}
