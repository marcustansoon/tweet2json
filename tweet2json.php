<?php

class Tweet2JSON
{
    private function innerHTML(\DOMElement $element): string
    {
        $doc = $element->ownerDocument;
        $html = "";
        foreach ($element->childNodes as $node) {
            $html .= $doc->saveHTML($node);
        }
        return $html;
    }

    public function getJSON(string $twitterName): array
    {
        // Get raw JSON from Twitter API
        $rawJSONContent = json_decode(
            file_get_contents(
                "https://cdn.syndication.twimg.com/timeline/profile?screen_name=$twitterName"
            ),
            true
        );

        // Make sure content is valid
        if (
            !boolval($rawJSONContent) ||
            $rawJSONContent["headers"]["status"] !== 200
        ) {
            return [];
        }

        // Get raw HTML
        $rawHTMLContent = $rawJSONContent["body"];

        // Instantiate DOMDocument
        $doc = new DOMDocument();
        $doc->loadHTML($rawHTMLContent);
        $tweetNodes = $doc->getElementsByTagName("ol")[0]->childNodes ?? [];

        // Init
        $tweets = [];

        foreach ($tweetNodes as $tweetNode) {
            if (get_class($tweetNode) === "DOMText") {
                continue;
            }

            // Get timestamp
            ($time = $tweetNode->getElementsByTagName("time")[0] ?? null) &&
                ($time = $time->getAttribute("datetime") ?? null);

            ($temp = new DOMDocument()) &&
                $temp->loadHTML($doc->saveHTML($tweetNode));
            $xpath = new DomXpath($temp);

            // Get text
            $text =
                $xpath->query('//p[@class="timeline-Tweet-text"]')->item(0)
                    ->nodeValue ?? null;

            // Like Tweet URL (Twitter)
            ($likeTweetURL =
                $xpath->query('//a[@data-scribe="element:heart"]')->item(0) ??
                null) && ($likeTweetURL = $likeTweetURL->getAttribute("href"));

            // Share Tweet URL (Twitter)
            ($shareTweetURL_twitter =
                $xpath->query('//a[@data-scribe="element:twitter"]')->item(0) ??
                null) &&
                ($shareTweetURL_twitter = $shareTweetURL_twitter->getAttribute(
                    "href"
                ));

            // Share Tweet URL (Facebook)
            ($shareTweetURL_facebook =
                $xpath
                    ->query('//a[@data-scribe="element:facebook"]')
                    ->item(0) ?? null) &&
                ($shareTweetURL_facebook = $shareTweetURL_facebook->getAttribute(
                    "href"
                ));

            // Share Tweet URL (LinkedIn)
            ($shareTweetURL_linkedin =
                $xpath
                    ->query('//a[@data-scribe="element:linkedin"]')
                    ->item(0) ?? null) &&
                ($shareTweetURL_linkedin = $shareTweetURL_linkedin->getAttribute(
                    "href"
                ));

            // Share Tweet URL (Tumblr)
            ($shareTweetURL_tumblr =
                $xpath->query('//a[@data-scribe="element:tumblr"]')->item(0) ??
                null) &&
                ($shareTweetURL_tumblr = $shareTweetURL_tumblr->getAttribute(
                    "href"
                ));

            // Images
            $imageURLs = [];
            $imageNodes = $xpath->query("//img[@data-image]");
            foreach ($imageNodes as $imageNode) {
                if ($imageNode->getAttribute("alt") === "Embedded video") {
                    $imageURLs[] =
                        $imageNode->getAttribute("data-image") . ".png"; // Thumbnail URL
                    $imageURLs[] =
                        str_replace(
                            "https://pbs.twimg.com/tweet_video_thumb",
                            "https://video.twimg.com/tweet_video",
                            $imageNode->getAttribute("data-image")
                        ) . ".mp4"; // Video URL
                } else {
                    $imageURLs[] =
                        $imageNode->getAttribute("data-image") .
                        "." .
                        $imageNode->getAttribute("data-image-format");
                }
            }

            $tweets[] = [
                "timestamp" => $time,
                "text" => $text,
                "like-tweet-url" => $likeTweetURL,
                "media" => $imageURLs,
                "share-tweet-url-twitter" => $shareTweetURL_twitter,
                "share-tweet-url-facebook" => $shareTweetURL_facebook,
                "share-tweet-url-linkedin" => $shareTweetURL_linkedin,
                "share-tweet-url-tumblr" => $shareTweetURL_tumblr,
            ];
        }

        return $tweets;
    }
}

// Example usage :
echo "<pre>";
echo json_encode((new Tweet2JSON())->getJSON("TwitterDev"), JSON_PRETTY_PRINT);
echo "</pre>";
?>
