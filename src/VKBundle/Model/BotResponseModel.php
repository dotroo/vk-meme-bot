<?php

namespace App\VKBundle\Model;

class BotResponseModel
{
    private const DEFAULT_REPLY = 'Чтобы получить мем, напиши мне "meme {тематика}" (латиницей)';

    /**
     * Reply destination
     *
     * @var int|null
     */
    private ?int $peerId = null;

    /**
     * Bot's reply
     *
     * @var string|null
     */
    private ?string $message = self::DEFAULT_REPLY;

    /**
     * Text from the user
     *
     * @var string
     */
    private string $text;

    /**
     * @var string|null
     */
    private ?string $attachment = null;

    /**
     * @param int $peerId
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
        return new self((int)$object['peer_id'], (string)$object['text']);
    }

    /**
     * @return array
     */
    public function toVkApi(): array
    {
        $replyParams = [
            'peer_id' => $this->peerId,
            'message' => $this->message,
            'attachment' => $this->attachment,
        ];

        $result = array_filter($replyParams, static fn ($value) => $value !== null);

        $result['random_id'] = mt_rand();

        return $result;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $attachment
     */
    public function setAttachment(string $attachment): void
    {
        $this->attachment = $attachment;
    }

    /**
     * @return string|null
     */
    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    /**
     * @return int|null
     */
    public function getPeerId(): ?int
    {
        return $this->peerId;
    }

}
