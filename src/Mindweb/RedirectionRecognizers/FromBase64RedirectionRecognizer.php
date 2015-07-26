<?php
namespace Mindweb\RedirectionRecognizers;

use Mindweb\Recognizer\Event;
use Mindweb\Recognizer\Recognizer;

class FromBase64RedirectionRecognizer extends Recognizer
{
    /**
     * @param Event\AttributionEvent $attributionEvent
     */
    public function recognize(Event\AttributionEvent $attributionEvent)
    {
        $encodedResource = $this->getEncodedResource($attributionEvent);

        $redirectionMeta = $this->getRedirectionMeta($encodedResource);
        if (empty($redirectionMeta)) {
            return;
        }

        if (md5(json_encode($redirectionMeta)) !== $this->getSign($encodedResource)) {
            return;
        }

        $attributionEvent->attribute(
            '_redirection',
            $redirectionMeta
        );
    }

    /**
     * @param Event\AttributionEvent $attributionEvent
     * @return array
     */
    private function getEncodedResource(Event\AttributionEvent $attributionEvent)
    {
        return urldecode($attributionEvent->getRequest()->getQueryString());
    }

    /**
     * @param string $encodedResource
     * @return string
     */
    private function getSign($encodedResource)
    {
        return substr($encodedResource, 0, 32);
    }

    /**
     * @param string $encodedResource
     * @return array
     */
    private function getRedirectionMeta($encodedResource)
    {
        $decoded = base64_decode(substr($encodedResource, 32));
        if ($decoded === false) {
            return array();
        }

        $jsonDecode = json_decode($decoded, true);
        if ($jsonDecode === null) {
            return array();
        }

        return $jsonDecode;
    }
}