<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Service\Hash;

/**
 * See https://gitlab.com/cleonet-solutions/point-s/lease19/blob/master/src/src/Service/Hashing.php
 * @package App\Service
 */
class Hmac implements HashInterface
{

    /**
     * @var string
     */
    private $hmac;

    /**
     * Hmac constructor.
     *
     * @param string $hmac
     */
    public function __construct(string $hmac)
    {
        $this->hmac = $hmac;
    }

    /**
     * @param string $hash
     * @param array|string $data
     *
     * @return bool
     * @throws \Exception
     */
    public function verifyHash($hash, $data): bool
    {
        if ($hash !== self::hash($data)) {
            return false;
        }

        $date = $this->extractDateFromData($data);

        // Check future date
        if ($date > (new \DateTime())) {
            return false;
        }

        // Check expiry
        if ((new \DateTime())->diff($date) > new \DateInterval('PT24H')) {
            return false;
        }

        return true;
    }

    /**
     * @param array|string $data
     *
     * @return string
     */
    public function hash($data): string
    {
        $data = $this->normalizeData($data);

        $binKey  = pack("H*", $this->hmac);
        $content = http_build_query($data);

        return strtoupper(hash_hmac('sha512', $content, $binKey));
    }

    /**
     * @param array|string $data
     *
     * @return \DateTime|false
     * @throws \Exception
     */
    protected function extractDateFromData($data)
    {
        if (is_string($data)) {
            $data = parse_str($data, $data);
        }

        if (empty($data['date']) || date_create_from_format('Y-m-d\TH:i:sP', $data['date']) === null) {
            throw new \Exception('Data / data string must contain a date field ex. &date=...|["date" => "..."] (format: \'Y-m-d\TH:i:sP\')'); //TODO: maybe we can make the field name configurable
        }

        return date_create_from_format('Y-m-d\TH:i:sP', $data['date']);
    }

    /**
     * @param array|string $data
     *
     * @return array
     */
    protected function normalizeData($data)
    {
        $normalizedData = [];
        if ( ! is_array($data)) {
            parse_str($data . '', $normalizedData);
        }else {
            $normalizedData = $data;
        }

        return $normalizedData;
    }
}
